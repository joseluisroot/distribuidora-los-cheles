<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetTokenModel extends Model
{
    protected $table         = 'password_reset_tokens';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['user_id','token','expires_at','used_at'];
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    public function createToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $this->insert([
            'user_id'    => $userId,
            'token'      => $token,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
        ]);
        return $token;
    }

    public function validateToken(string $token): ?array
    {
        $row = $this->where('token',$token)->first();
        if (!$row) return null;
        if ($row['used_at']) return null;
        if (strtotime($row['expires_at']) < time()) return null;
        return $row;
    }
}