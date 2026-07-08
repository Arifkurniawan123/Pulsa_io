<?php

namespace App\Models;

use CodeIgniter\Model;

class SaldoDigiflazzModel extends Model
{
    protected $table = 'tbl_saldo_digiflazz';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'saldo', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    public function getSaldoUser(string $userId): int
    {
        $result = $this->where('user_id', $userId)->first();
        return $result['saldo'] ?? 0;
    }

    public function tambahSaldo(string $userId, int $nominal): bool
    {
        $existing = $this->where('user_id', $userId)->first();
        if (!$existing) {
            return $this->insert(['user_id' => $userId, 'saldo' => $nominal]);
        }
        return $this->update($existing['id'], ['saldo' => $existing['saldo'] + $nominal]);
    }

    public function kurangiSaldo(string $userId, int $nominal): bool
    {
        $existing = $this->where('user_id', $userId)->first();
        if (!$existing || $existing['saldo'] < $nominal) return false;
        return $this->update($existing['id'], ['saldo' => $existing['saldo'] - $nominal]);
    }
}