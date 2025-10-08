<?php

namespace App\Models;

use CodeIgniter\Model;

class PrecioEscaladoModel extends Model
{
    protected $table = 'precios_escalados';
    protected $primaryKey = 'id';
    protected $allowedFields = ['producto_id','min_cantidad','precio'];
    protected $useTimestamps = true;
    protected $returnType = 'array';

    public function getMejorEscala(int $productoId, int $cantidad): ?array
    {
        return $this->where('producto_id',$productoId)
            ->where('min_cantidad <=', $cantidad)
            ->orderBy('min_cantidad','DESC')
            ->first();
    }

    // App/Models/PrecioEscaladoModel.php
    public function precioParaCantidad(int $productoId, int $cantidad, float $precioBase): float
    {
        $row = $this->where('producto_id', $productoId)
            ->where('min_cantidad <=', $cantidad)
            ->orderBy('min_cantidad','DESC')
            ->first();

        return $row ? (float)$row['precio'] : $precioBase;
    }

    // App/Models/PrecioEscaladoModel.php
    public function escalasPorProducto(array $productoIds): array
    {
        if (empty($productoIds)) return [];
        $rows = $this->whereIn('producto_id', $productoIds)
            ->orderBy('producto_id','ASC')
            ->orderBy('min_cantidad','ASC')
            ->findAll();
        $map = [];
        foreach ($rows as $r) {
            $pid = (int)$r['producto_id'];
            $map[$pid][] = [
                'min_cantidad' => (int)$r['min_cantidad'],
                'precio'       => (float)$r['precio'],
            ];
        }
        return $map; // [pid => [ {min_cantidad, precio}, ... ASC ]]
    }

    public static function precioAplicable(?array $escalas, int $qty, float $precioBase): float
    {
        if (empty($escalas)) return $precioBase;
        $aplicado = $precioBase;
        foreach ($escalas as $e) {
            if ($qty >= $e['min_cantidad']) {
                $aplicado = $e['precio']; // como están ASC, este será el último <= qty
            } else {
                break;
            }
        }
        return (float)$aplicado;
    }

}