<?php

namespace App\Models;

use CodeIgniter\Model;

class TopupEwalletModel extends Model
{
    protected $table = 'tbl_topup_ewallet';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id', 'ref_id', 'metode_ewallet', 'nomor_telepon',
        'nominal', 'status', 'keterangan', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get history topup ewallet untuk user
     */
    public function getHistoryByUser(string $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();
    }

    /**
     * Get topup by ref_id
     */
    public function getByRefId(string $refId): ?array
    {
        return $this->where('ref_id', $refId)->first();
    }

    /**
     * Create topup ewallet record
     */
    public function createTopup(string $userId, string $metodeEwallet, string $nomorTelepon, int $nominal): int
    {
        $refId = 'EWLT-' . time() . '-' . rand(100, 999);
        $data = [
            'user_id' => $userId,
            'ref_id' => $refId,
            'metode_ewallet' => $metodeEwallet,
            'nomor_telepon' => $nomorTelepon,
            'nominal' => $nominal,
            'status' => 'pending',
            'keterangan' => "Top-up $metodeEwallet ke $nomorTelepon"
        ];
        return $this->insert($data) ? $this->getInsertID() : 0;
    }

    /**
     * Update status topup
     */
    public function updateStatus(int $id, string $status, ?string $keterangan = null): bool
    {
        $data = ['status' => $status];
        if ($keterangan) {
            $data['keterangan'] = $keterangan;
        }
        return $this->update($id, $data);
    }

    /**
     * Count pending transactions
     */
    public function countPending(string $userId): int
    {
        return $this->where('user_id', $userId)
            ->where('status', 'pending')
            ->countAllResults();
    }

    /**
     * Get total successful topups
     */
    public function getTotalSuccessful(string $userId): int
    {
        return $this->where('user_id', $userId)
            ->where('status', 'berhasil')
            ->selectSum('nominal')
            ->first()['nominal'] ?? 0;
    }
}
