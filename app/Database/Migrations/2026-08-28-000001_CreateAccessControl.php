<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccessControl extends Migration
{
    public function up()
    {
        $attributes = $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : [];
        $this->forge->addField(['id' => ['type' => 'INT'], 'version' => ['type' => 'INT', 'default' => 0]]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('access_lock', false, $attributes);
        $this->db->table('access_lock')->insert(['id' => 1, 'version' => 0]);
        $this->forge->addColumn('users', [
            'auth_version' => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'subject_type' => ['type' => 'VARCHAR', 'constraint' => 10],
            'subject_id' => ['type' => 'INT', 'unsigned' => true],
            'permission' => ['type' => 'VARCHAR', 'constraint' => 80],
            'scope' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'global'],
            'effect' => ['type' => 'VARCHAR', 'constraint' => 5],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['subject_type', 'subject_id', 'permission', 'scope'], 'access_grant_unique');
        $this->forge->createTable('access_grants', false, $attributes);
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'actor_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'action' => ['type' => 'VARCHAR', 'constraint' => 80],
            'subject_type' => ['type' => 'VARCHAR', 'constraint' => 10],
            'subject_id' => ['type' => 'INT', 'unsigned' => true],
            'permission' => ['type' => 'VARCHAR', 'constraint' => 80],
            'scope' => ['type' => 'VARCHAR', 'constraint' => 40],
            'before_effect' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'after_effect' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 500],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('access_audit', false, $attributes);
        // No implicit grants: bootstrap is an explicit local CLI operation.
    }

    public function down()
    {
        $this->forge->dropTable('access_lock');
        $this->forge->dropTable('access_audit');
        $this->forge->dropTable('access_grants');
        // SQLite's table rebuild can fail with prefixed foreign keys.
        if ($this->db->DBDriver === 'SQLite3') {
            $this->db->query('ALTER TABLE ' . $this->db->protectIdentifiers($this->db->prefixTable('users'))
                . ' DROP COLUMN auth_version');
        } else {
            $this->forge->dropColumn('users', 'auth_version');
        }
    }
}
