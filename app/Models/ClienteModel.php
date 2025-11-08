<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table            = 'clientes';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'nombre', 'telefono', 'email', 'status',
    ];

    protected $useTimestamps = true;

    public function countActive(): int
    {
        return (int) $this->where('status', 'ACTIVO')->countAllResults();
    }
}
