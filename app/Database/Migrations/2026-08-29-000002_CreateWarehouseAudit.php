<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateWarehouseAudit extends Migration
{
    public function up()
    {
        $attributes = $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : [];
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'actor_id' => ['type' => 'INT', 'unsigned' => true],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 20],
            'entity_id' => ['type' => 'INT', 'unsigned' => true],
            'action' => ['type' => 'VARCHAR', 'constraint' => 30],
            'before_data' => ['type' => 'TEXT', 'null' => true],
            'after_data' => ['type' => 'TEXT'],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 500],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['entity_type', 'entity_id']);
        $this->forge->addKey('created_at');
        $this->forge->createTable('warehouse_audit', false, $attributes);
    }

    public function down()
    {
        $this->forge->dropTable('warehouse_audit');
    }
}
