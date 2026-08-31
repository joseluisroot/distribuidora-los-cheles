<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Repairs installations where the first I2 migration was applied as MyISAM. */
final class EnsureTransactionalWarehouseStructure extends Migration
{
    public function up()
    {
        if ($this->db->DBDriver !== 'MySQLi') {
            return;
        }

        foreach (['sedes', 'almacenes', 'ubicaciones'] as $table) {
            if ($this->db->tableExists($table)) {
                $name = $this->db->escapeIdentifiers($this->db->prefixTable($table));
                $this->db->query("ALTER TABLE {$name} ENGINE=InnoDB");
            }
        }

        $this->ensureForeignKey('almacenes', 'sede_id', 'sedes', 'fk_almacenes_sede');
        $this->ensureForeignKey('ubicaciones', 'almacen_id', 'almacenes', 'fk_ubicaciones_almacen');
    }

    private function ensureForeignKey(string $table, string $column, string $parent, string $constraint): void
    {
        if (!$this->db->tableExists($table) || !$this->db->tableExists($parent)) {
            return;
        }
        $exists = $this->db->query(
            'SELECT 1 FROM information_schema.KEY_COLUMN_USAGE '
            . 'WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=? AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1',
            [$this->db->prefixTable($table), $column]
        )->getRowArray();
        if ($exists) {
            return;
        }

        $tableName = $this->db->escapeIdentifiers($this->db->prefixTable($table));
        $columnName = $this->db->escapeIdentifiers($column);
        $parentName = $this->db->escapeIdentifiers($this->db->prefixTable($parent));
        $constraintName = $this->db->escapeIdentifiers($constraint);
        $this->db->query("ALTER TABLE {$tableName} ADD CONSTRAINT {$constraintName} FOREIGN KEY ({$columnName}) REFERENCES {$parentName} (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT");
    }

    public function down()
    {
        // Storage integrity is intentionally not downgraded.
    }
}
