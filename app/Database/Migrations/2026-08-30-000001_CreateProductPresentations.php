<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateProductPresentations extends Migration
{
    public function up()
    {
        $attributes = $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : [];
        // The original CI4 migration created `productos` with the server default
        // engine (MyISAM in this local installation). A referenced table must be
        // transactional before MySQL can enforce the presentation relationship.
        if ($this->db->DBDriver === 'MySQLi') {
            $this->db->query('ALTER TABLE `productos` ENGINE=InnoDB');
        }
        $this->forge->addField([
            'id' => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'producto_id' => ['type'=>'INT','unsigned'=>true],
            'codigo' => ['type'=>'VARCHAR','constraint'=>30],
            'nombre' => ['type'=>'VARCHAR','constraint'=>80],
            'unidades_base' => ['type'=>'INT','unsigned'=>true,'default'=>1],
            'precio_detalle' => ['type'=>'DECIMAL','constraint'=>'12,2'],
            'precio_mayoreo' => ['type'=>'DECIMAL','constraint'=>'12,2'],
            'minimo_mayoreo' => ['type'=>'INT','unsigned'=>true,'default'=>3],
            'activa' => ['type'=>'BOOLEAN','default'=>1],
            'created_at' => ['type'=>'DATETIME'],
            'updated_at' => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['producto_id','codigo'], 'product_presentation_code_unique');
        $this->forge->addForeignKey('producto_id','productos','id','RESTRICT','RESTRICT');
        $this->forge->createTable('producto_presentaciones', false, $attributes);

        $this->forge->addField([
            'id' => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'actor_id' => ['type'=>'INT','unsigned'=>true],
            'presentacion_id' => ['type'=>'INT','unsigned'=>true],
            'action' => ['type'=>'VARCHAR','constraint'=>30],
            'before_data' => ['type'=>'TEXT','null'=>true],
            'after_data' => ['type'=>'TEXT'],
            'reason' => ['type'=>'VARCHAR','constraint'=>500],
            'created_at' => ['type'=>'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('presentacion_id');
        $this->forge->addForeignKey('presentacion_id','producto_presentaciones','id','RESTRICT','RESTRICT');
        $this->forge->createTable('producto_presentacion_audit', false, $attributes);
    }

    public function down()
    {
        $this->forge->dropTable('producto_presentacion_audit');
        $this->forge->dropTable('producto_presentaciones');
    }
}
