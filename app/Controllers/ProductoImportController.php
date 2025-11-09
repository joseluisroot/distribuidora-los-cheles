<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InventarioModel;
use App\Models\ProductoModel;
use CodeIgniter\HTTP\ResponseInterface;

class ProductoImportController extends BaseController
{
    /** GET: formulario simple de carga */
    public function form()
    {
        return view('productos/importar_form', [
            'title' => 'Importar productos (CSV)',
        ]);
    }

    /** GET: descarga de plantilla CSV */
    public function template()
    {
        $filename = 'plantilla_productos.csv';
        $csv = "sku,nombre,descripcion,precio_base,stock,is_activo\n"
            . "P-001,Producto demo,Descripción opcional,10.50,100,1\n";

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->setBody($csv);
    }

    /** POST: pre-visualizar CSV (validaciones por fila) */
    public function preview()
    {
        helper('text');

        $file = $this->request->getFile('csv');
        $updateExisting = (bool) $this->request->getPost('update_existing');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Archivo inválido o no enviado.');
        }

        // Guardar temporalmente para parsear
        $tmpPath = $file->getTempName();
        [$headers, $rows, $errors] = $this->parseCsv($tmpPath);

        if (empty($headers)) {
            return redirect()->back()->with('error', 'El CSV no tiene cabeceras válidas.');
        }

        // Validar filas
        $results = [];
        $seenSkus = [];
        foreach ($rows as $i => $r) {
            $line = $i + 2; // +2 por cabecera (línea 1) y base cero
            $rowErrors = [];

            $sku   = trim((string)($r['sku'] ?? ''));
            $nombre= trim((string)($r['nombre'] ?? ''));
            $desc  = trim((string)($r['descripcion'] ?? ''));
            $precio= $this->toFloat($r['precio_base'] ?? '');
            $stock = $this->toInt($r['stock'] ?? '');
            $activo= isset($r['is_activo']) ? (string)$r['is_activo'] : '1';

            if ($sku === '')   $rowErrors[] = 'SKU es requerido';
            if ($nombre === '')$rowErrors[] = 'Nombre es requerido';
            if ($precio === null || $precio < 0) $rowErrors[] = 'Precio base inválido';
            if ($stock === null || $stock < 0)   $rowErrors[] = 'Stock inválido';
            if (!in_array($activo, ['0','1',''], true)) $rowErrors[] = 'is_activo debe ser 0 o 1';

            // Duplicados dentro del mismo archivo
            if ($sku !== '') {
                if (isset($seenSkus[$sku])) {
                    $rowErrors[] = 'SKU duplicado en el archivo (primero en línea '.$seenSkus[$sku].')';
                } else {
                    $seenSkus[$sku] = $line;
                }
            }

            $results[] = [
                'line'   => $line,
                'raw'    => $r,
                'ok'     => empty($rowErrors),
                'errors' => $rowErrors,
                // normalizado para el commit:
                'data'   => [
                    'sku'         => $sku,
                    'nombre'      => $nombre,
                    'descripcion' => $desc,
                    'precio_base' => $precio ?? 0,
                    'stock'       => $stock ?? 0,
                    'is_activo'   => ($activo === '0') ? 0 : 1,
                ],
            ];
        }

        // Empaquetar datos (solo filas ok) en JSON para el commit
        $payload = [
            'update_existing' => $updateExisting,
            'items' => array_values(array_map(fn($x) => $x['data'], array_filter($results, fn($x) => $x['ok']))),
        ];
        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);

        return view('productos/importar_preview', [
            'title'          => 'Previsualización de importación',
            'headers'        => $headers,
            'results'        => $results,
            'csvErrors'      => $errors, // errores de parseo general
            'payloadJson'    => $payloadJson,
            'updateExisting' => $updateExisting,
        ]);
    }

    /** POST: procesar importación */
    public function process()
    {
        $json = (string)$this->request->getPost('payload');
        if ($json === '') {
            return redirect()->to('productos/importar')->with('error', 'No se recibió payload de importación.');
        }

        $payload = json_decode($json, true);
        if (!is_array($payload) || !isset($payload['items'])) {
            return redirect()->to('productos/importar')->with('error', 'Payload inválido.');
        }

        $updateExisting = !empty($payload['update_existing']);
        $items = $payload['items'];

        $pModel = new ProductoModel();
        $iModel = new InventarioModel();

        $created = 0; $updated = 0; $skipped = 0; $errors = [];

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($items as $idx => $d) {
            try {
                // ¿Existe SKU?
                $existing = $pModel->where('sku', $d['sku'])->first();

                if ($existing) {
                    if (!$updateExisting) {
                        $skipped++;
                        continue;
                    }
                    // Actualizar
                    $pModel->update($existing['id'], [
                        'nombre'      => $d['nombre'],
                        'descripcion' => $d['descripcion'],
                        'precio_base' => $d['precio_base'],
                        'is_activo'   => $d['is_activo'],
                        // imagen_url se deja como está (genérica/actual)
                    ]);

                    // stock: set absoluto al valor indicado
                    $iModel->where('producto_id', (int)$existing['id'])
                        ->set(['stock' => (int)$d['stock']])
                        ->update();

                    $updated++;
                } else {
                    // Crear
                    $newId = $pModel->insert([
                        'sku'         => $d['sku'],
                        'nombre'      => $d['nombre'],
                        'descripcion' => $d['descripcion'],
                        'precio_base' => $d['precio_base'],
                        'is_activo'   => $d['is_activo'],
                        // imagen_url => null (la vista usa placeholder genérico)
                    ], true);

                    // inventario inicial
                    $iModel->insert([
                        'producto_id' => $newId,
                        'stock'       => (int)$d['stock'],
                    ]);

                    $created++;
                }
            } catch (\Throwable $e) {
                $errors[] = "SKU {$d['sku']}: ".$e->getMessage();
            }
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return redirect()->to('productos/importar')->with('error', 'Fallo al aplicar la importación.');
        }

        $msg = "Importación completada: creados={$created}, actualizados={$updated}, omitidos={$skipped}.";
        if (!empty($errors)) {
            $msg .= " Errores: ".count($errors).".";
            return redirect()->to('productos/importar')->with('message', $msg)->with('errors', $errors);
        }
        return redirect()->to('productos')->with('message', $msg);
    }

    /** ---- Helpers internos ---- */

    /** Devuelve [headers,array<assoc>,errors] */
    private function parseCsv(string $path): array
    {
        $errors = [];
        $rows = [];

        // Intentar detectar delimitador (coma/semicolon)
        $sample = file_get_contents($path, false, null, 0, 4096) ?: '';
        $delimiter = (substr_count($sample, ';') > substr_count($sample, ',')) ? ';' : ',';

        if (($fh = fopen($path, 'r')) === false) {
            return [[], [], ['No se pudo abrir el archivo.']];
        }

        // leer cabeceras (limpiando BOM)
        $header = fgetcsv($fh, 0, $delimiter);
        if (!$header) {
            fclose($fh);
            return [[], [], ['No se pudo leer la cabecera.']];
        }
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]); // BOM UTF-8

        // normalizar cabeceras (lower)
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $required = ['sku','nombre','precio_base','stock','is_activo','descripcion'];
        // Permite archivos sin 'descripcion' o 'is_activo' (opcionales)
        // Solo aseguramos sku/nombre/precio/stock
        foreach (['sku','nombre','precio_base','stock'] as $req) {
            if (!in_array($req, $header, true)) {
                $errors[] = "Falta la columna requerida: {$req}";
            }
        }
        if (!empty($errors)) {
            fclose($fh);
            return [$header, [], $errors];
        }

        while (($data = fgetcsv($fh, 0, $delimiter)) !== false) {
            if ($data === [null] || $data === false) continue;

            $row = [];
            foreach ($header as $i => $col) {
                $row[$col] = isset($data[$i]) ? trim((string)$data[$i]) : null;
            }
            // Rellenar faltantes opcionales
            $row += ['descripcion'=>null, 'is_activo'=>'1'];

            $rows[] = $row;
        }

        fclose($fh);
        return [$header, $rows, $errors];
    }

    private function toFloat($val): ?float
    {
        if ($val === '' || $val === null) return null;
        // aceptar decimales con coma
        $v = str_replace([' ', ','], ['', '.'], (string)$val);
        if (!is_numeric($v)) return null;
        return (float)$v;
    }

    private function toInt($val): ?int
    {
        if ($val === '' || $val === null) return null;
        $v = preg_replace('/[^0-9\-]/','', (string)$val);
        if ($v === '' || !is_numeric($v)) return null;
        return (int)$v;
    }
}
