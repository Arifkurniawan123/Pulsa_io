<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTopupSaldoHistoryTable extends Migration
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
            'nominal' => [
                'type' => 'BIGINT',
                'default' => 0,
            ],
            'metode_pembayaran' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'tipe_transaksi' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'topup_saldo atau topup_pulsa',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'pending',
                'comment' => 'berhasil, gagal, pending',
            ],
            'referensi_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
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
        $this->forge->addKey('user_id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('tbl_topup_saldo_history');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_topup_saldo_history');
    }
}
