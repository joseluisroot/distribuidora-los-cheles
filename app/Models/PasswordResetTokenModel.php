<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetTokenModel extends Model
{
    protected $table      = 'password_reset_tokens';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id','token_hash','expires_at','used_at','ip','user_agent','created_at'];

    public function createToken(int $userId, int $ttlSeconds = 3600): string
    {
        $plain = bin2hex(random_bytes(32)); // 64 chars
        $hash  = password_hash($plain, PASSWORD_DEFAULT);

        $this->insert([
            'user_id'    => $userId,
            'token_hash' => $hash,
            'expires_at' => date('Y-m-d H:i:s', time() + $ttlSeconds),
            'ip'         => service('request')->getIPAddress(),
            'user_agent' => substr((string) service('request')->getUserAgent(), 0, 255),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $plain; // se manda por email
    }

    public function validateToken(string $plain)
    {
        // Busca el token más reciente que no esté usado y no esté vencido
        $row = $this->where('used_at', null)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->orderBy('id','DESC')
            ->first();

        if (! $row) return null;

        if (! password_verify($plain, $row['token_hash'])) {
            return null;
        }

        return $row;
    }

    public function markUsed(int $id): bool
    {
        return $this->update($id, ['used_at' => date('Y-m-d H:i:s')]);
    }
}
