<?php

namespace App\Models;

use CodeIgniter\Model;

class TopupSaldoHistoryModel extends Model
{
    protected $table = 'tbl_topup_saldo_history';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'nominal', 'metode_pembayaran', 'tipe_transaksi',
        'status', 'referensi_id', 'keterangan', 'created_by',
        'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;

    public function getHistory(string $userId, ?string $tipeTransaksi = null, int $limit = 50, int $offset = 0): array
    {
        $builder = $this->where('user_id', $userId);
        if ($tipeTransaksi) $builder->where('tipe_transaksi', $tipeTransaksi);
        return $builder->orderBy('created_at', 'DESC')->limit($limit, $offset)->findAll();
    }

    public function addHistory(array $data): int
    {
        return $this->insert($data);
    }
}