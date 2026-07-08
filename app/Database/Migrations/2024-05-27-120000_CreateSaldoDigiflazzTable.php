<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSaldoDigiflazzTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'saldo' => [
                'type' => 'BIGINT',
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('user_id');
        $this->forge->createTable('tbl_saldo_digiflazz');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_saldo_digiflazz');
    }
}
