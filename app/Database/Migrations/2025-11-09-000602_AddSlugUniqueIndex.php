<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSlugUniqueIndex extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $db->query('CREATE UNIQUE INDEX ux_productos_slug ON productos (slug)');
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->query('DROP INDEX ux_productos_slug ON productos');
    }
}
