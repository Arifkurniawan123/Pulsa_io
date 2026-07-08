<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTopupBankTable extends Migration
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
            'nama_bank' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Nama bank (BCA, BNI, Mandiri, BRI, CIMB, dll)',
            ],
            'nomor_rekening' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'comment' => 'Nomor rekening tujuan',
            ],
            'atas_nama' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Nama pemilik rekening',
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
        $this->forge->createTable('tbl_topup_bank');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_topup_bank');
    }
}
