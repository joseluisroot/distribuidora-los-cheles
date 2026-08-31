<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use DomainException;

final class ProductPresentationService
{
    public const WHOLESALE_MINIMUM = 3;
    public function __construct(private ?BaseConnection $db = null) { $this->db ??= db_connect(); }

    public function forProduct(int $productId): array
    {
        return $this->db->table('producto_presentaciones')->where('producto_id',$productId)
            ->orderBy('unidades_base')->orderBy('codigo')->get()->getResultArray();
    }

    public function create(int $actorId, int $productId, array $input): int
    {
        if ($actorId < 1 || $productId < 1 || !$this->db->table('productos')->where('id',$productId)->countAllResults())
            throw new DomainException('El producto seleccionado no existe.');
        $data=$this->validated($productId,$input); $reason=$this->reason($input['reason'] ?? '');
        if ($this->db->table('producto_presentaciones')->where(['producto_id'=>$productId,'codigo'=>$data['codigo']])->countAllResults())
            throw new DomainException('Ese código de presentación ya existe para el producto.');
        $id=0; $this->db->transBegin();
        try {
            $this->db->table('producto_presentaciones')->insert($data); $id=(int)$this->db->insertID();
            $this->audit($actorId,$id,'create',null,['id'=>$id]+$data,$reason);
            if (!$this->db->transStatus()) throw new DomainException('No se pudo guardar la presentación.');
            $this->db->transCommit();
        } catch (\Throwable $e) { $this->db->transRollback(); throw $e; }
        return $id;
    }

    public function priceForQuantity(array $presentation, int $quantity): string
    {
        if ($quantity < 1) throw new DomainException('La cantidad debe ser mayor que cero.');
        return $this->money($quantity >= self::WHOLESALE_MINIMUM
            ? $presentation['precio_mayoreo'] : $presentation['precio_detalle'], 'seleccionado');
    }

    public function update(int $actorId, int $productId, int $presentationId, array $input): void
    {
        if ($actorId < 1 || $productId < 1 || $presentationId < 1) throw new DomainException('La presentación seleccionada no es válida.');
        $before=$this->db->table('producto_presentaciones')->where(['id'=>$presentationId,'producto_id'=>$productId])->get()->getRowArray();
        if (!$before) throw new DomainException('La presentación seleccionada no existe.');
        $data=$this->validated($productId,$input);
        if ($this->db->table('producto_presentaciones')->where(['producto_id'=>$productId,'codigo'=>$data['codigo']])->where('id !=',$presentationId)->countAllResults())
            throw new DomainException('Ese código de presentación ya existe para el producto.');
        $reason=$this->reason($input['reason'] ?? '');
        $data['activa']=!empty($input['activa']) ? 1 : 0;
        $data['created_at']=$before['created_at']; $data['updated_at']=date('Y-m-d H:i:s');
        $this->db->transBegin();
        try {
            $this->db->table('producto_presentaciones')->where('id',$presentationId)->update($data);
            $this->audit($actorId,$presentationId,'update',$before,['id'=>$presentationId]+$data,$reason);
            if (!$this->db->transStatus()) throw new DomainException('No se pudo actualizar la presentación.');
            $this->db->transCommit();
        } catch (\Throwable $e) { $this->db->transRollback(); throw $e; }
    }

    private function validated(int $productId,array $input): array
    {
        $code=strtoupper(trim((string)($input['codigo'] ?? ''))); $name=trim((string)($input['nombre'] ?? ''));
        $units=filter_var($input['unidades_base'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>1000000]]);
        if (!preg_match('/^[A-Z0-9][A-Z0-9-]{1,29}$/D',$code)) throw new DomainException('El código de presentación no es válido.');
        if (mb_strlen($name)<2 || mb_strlen($name)>80) throw new DomainException('El nombre de presentación no es válido.');
        if (!$units) throw new DomainException('La equivalencia debe contener al menos una unidad base.');
        $detail=$this->money($input['precio_detalle'] ?? null,'detalle'); $wholesale=$this->money($input['precio_mayoreo'] ?? null,'mayoreo');
        if ((int)str_replace('.','',$wholesale) > (int)str_replace('.','',$detail)) throw new DomainException('El precio mayorista no puede superar el precio detalle.');
        return ['producto_id'=>$productId,'codigo'=>$code,'nombre'=>$name,'unidades_base'=>(int)$units,
            'precio_detalle'=>$detail,'precio_mayoreo'=>$wholesale,'minimo_mayoreo'=>self::WHOLESALE_MINIMUM,
            'activa'=>1,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>null];
    }

    private function money($value,string $label): string
    {
        $value=trim((string)$value);
        if (!preg_match('/^(?:0|[1-9][0-9]{0,9})(?:\.[0-9]{1,2})?$/D',$value)) throw new DomainException('El precio '.$label.' no es válido.');
        [$whole,$decimal]=array_pad(explode('.',$value,2),2,'');
        return $whole.'.'.str_pad($decimal,2,'0');
    }

    private function reason($value): string
    {
        $value=trim((string)$value); if ($value==='' || mb_strlen($value)>500) throw new DomainException('Indica el motivo del cambio.'); return $value;
    }

    private function audit(int $actorId,int $id,string $action,?array $before,array $after,string $reason): void
    {
        $this->db->table('producto_presentacion_audit')->insert(['actor_id'=>$actorId,'presentacion_id'=>$id,'action'=>$action,
            'before_data'=>$before ? json_encode($before) : null,'after_data'=>json_encode($after),'reason'=>$reason,'created_at'=>date('Y-m-d H:i:s')]);
    }
}
