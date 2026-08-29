<?php
namespace App\Models;
use CodeIgniter\Model;

class PasswordResetTokenModel extends Model
{
    protected $table = 'password_reset_tokens';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id','token_hash','expires_at','used_at','ip','user_agent','created_at'];

    public function createToken(int $userId, int $ttlSeconds = 3600): string
    {
        $secret = bin2hex(random_bytes(32));
        $id = $this->insert([
            'user_id' => $userId, 'token_hash' => hash('sha256', $secret),
            'expires_at' => date('Y-m-d H:i:s', time() + $ttlSeconds),
            'ip' => service('request')->getIPAddress(),
            'user_agent' => substr((string) service('request')->getUserAgent(), 0, 255),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $id . '.' . $secret;
    }

    public function validateToken(string $plain): ?array
    {
        if (!preg_match('/^([1-9][0-9]{0,18})\.([a-f0-9]{64})$/D', $plain, $parts)) {
            return null;
        }
        $row = $this->where('id', $parts[1])->where('used_at', null)
            ->where('expires_at >', date('Y-m-d H:i:s'))->first();
        return $row && hash_equals($row['token_hash'], hash('sha256', $parts[2])) ? $row : null;
    }

    public function resetPassword(string $plain, string $password): bool
    {
        $row = $this->validateToken($plain);
        if (!$row) return false;
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->db->transBegin();
        try {
            $this->db->table($this->table)->where('id', $row['id'])->where('used_at', null)
                ->where('expires_at >', date('Y-m-d H:i:s'))->update(['used_at' => date('Y-m-d H:i:s')]);
            if ($this->db->affectedRows() !== 1) {
                $this->db->transRollback();
                return false;
            }
            $this->db->table('users')->where('id', $row['user_id'])->where('is_active', 1)
                ->where('deleted_at', null)->set('auth_version', 'auth_version + 1', false)
                ->update(['password' => $hash, 'updated_at' => date('Y-m-d H:i:s')]);
            if ($this->db->affectedRows() !== 1 || !$this->db->transStatus()) {
                $this->db->transRollback();
                return false;
            }
            $this->db->table($this->table)->where('user_id', $row['user_id'])->where('used_at', null)
                ->update(['used_at' => date('Y-m-d H:i:s')]);
            if (!$this->db->transStatus()) throw new \RuntimeException('Reset transaction failed.');
            $this->db->transCommit();
            return true;
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
