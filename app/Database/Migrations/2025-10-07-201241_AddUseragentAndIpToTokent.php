<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUseragentAndIpToTokent extends Migration
{
    public function up()
    {
        $this->forge->addColumn('password_reset_tokens', [
            'ip' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false, 'after' => 'used_at'],
        ]);
    }

    public function down()
    {
        // user_agent pertenece a CreatePasswordResetTokens.
        $this->forge->dropColumn('password_reset_tokens', 'ip');
    }
}
