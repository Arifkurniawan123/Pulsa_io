<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTopupEwalletTable extends Migration
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
                'type' => 'CHAR',
                'constraint' => 36,
                'comment' => 'User yang melakukan topup',
            ],
            'ref_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Reference ID unik untuk transaksi',
            ],
            'metode_ewallet' => [
                'type' => 'ENUM',
                'constraint' => ['gcash', 'dana', 'ovo', 'linkaja', 'gopay'],
                'comment' => 'Metode e-wallet yang digunakan',
            ],
            'nomor_telepon' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'comment' => 'Nomor telepon untuk e-wallet',
            ],
            'nominal' => [
                'type' => 'BIGINT',
                'default' => 0,
                'comment' => 'Nominal top-up',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'proses', 'berhasil', 'gagal'],
                'default' => 'pending',
                'comment' => 'Status transaksi',
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Catatan tambahan',
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
        $this->forge->addUniqueKey('ref_id');
        $this->forge->createTable('tbl_topup_ewallet');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_topup_ewallet');
    }
}
