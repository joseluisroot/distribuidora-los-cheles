<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class EnsureTransactionalSecurityTables extends Migration
{
    public function up()
    {
        if ($this->db->DBDriver !== 'MySQLi') return;
        foreach (['roles', 'users', 'password_reset_tokens', 'access_lock', 'access_grants', 'access_audit'] as $table) {
            if (!$this->db->tableExists($table)) continue;
            $name = $this->db->prefixTable($table);
            $engine = $this->db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$name])->getRowArray();
            if (strtoupper($engine['ENGINE'] ?? '') !== 'INNODB') {
                $this->db->query('ALTER TABLE ' . $this->db->protectIdentifiers($name) . ' ENGINE=InnoDB');
            }
        }
    }

    public function down()
    {
        // Deliberately retain transactional engines. Reverting to MyISAM would remove safety guarantees.
    }
}
