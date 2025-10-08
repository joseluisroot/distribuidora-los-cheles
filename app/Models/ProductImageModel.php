<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductImageModel extends Model
{
    protected $table         = 'product_images';
    protected $primaryKey    = 'id';
    protected $useSoftDeletes= true;
    protected $useTimestamps = true;
    protected $returnType    = 'array';
    protected $allowedFields = [
        'producto_id','path','thumb_path','alt','sort_order','is_primary'
    ];

    public function byProducto(int $productoId): array
    {
        return $this->where('producto_id', $productoId)
            ->orderBy('is_primary','DESC')
            ->orderBy('sort_order','ASC')
            ->orderBy('id','ASC')
            ->findAll();
    }

    public function setPrimary(int $productoId, int $imageId): void
    {
        $this->where('producto_id',$productoId)->set(['is_primary'=>0])->update();
        $this->update($imageId, ['is_primary'=>1]);
    }
}