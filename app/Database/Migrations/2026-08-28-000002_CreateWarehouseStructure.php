<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Physical structure only. No stock migration or commercial data is created. */
class CreateWarehouseStructure extends Migration
{
    public function up()
    {
        $attributes = $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : [];
        $id = ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true];
        $reference = ['type' => 'INT', 'unsigned' => true];
        $this->forge->addField([
            'id' => $id,
            'codigo' => ['type' => 'VARCHAR', 'constraint' => 30],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 120],
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 20], // central | sucursal
            'activa' => ['type' => 'BOOLEAN', 'default' => 1],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('codigo');
        $this->forge->createTable('sedes', false, $attributes);

        $this->forge->addField([
            'id' => $id, 'sede_id' => $reference,
            'codigo' => ['type' => 'VARCHAR', 'constraint' => 30],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 120],
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 20], // bodega | sala
            'activa' => ['type' => 'BOOLEAN', 'default' => 1],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['sede_id', 'codigo']);
        $this->forge->addForeignKey('sede_id', 'sedes', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('almacenes', false, $attributes);

        $this->forge->addField([
            'id' => $id, 'almacen_id' => $reference,
            'codigo' => ['type' => 'VARCHAR', 'constraint' => 50],
            'pasillo' => ['type' => 'VARCHAR', 'constraint' => 30],
            'fila' => ['type' => 'VARCHAR', 'constraint' => 30],
            'columna' => ['type' => 'VARCHAR', 'constraint' => 30],
            'activa' => ['type' => 'BOOLEAN', 'default' => 1],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['almacen_id', 'codigo']);
        $this->forge->addUniqueKey(['almacen_id', 'pasillo', 'fila', 'columna']);
        $this->forge->addForeignKey('almacen_id', 'almacenes', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('ubicaciones', false, $attributes);
    }

    public function down()
    {
        $this->forge->dropTable('ubicaciones');
        $this->forge->dropTable('almacenes');
        $this->forge->dropTable('sedes');
    }
}
