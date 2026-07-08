<?php

namespace App\Services;

use App\Models\SaldoDigiflazzModel;
use App\Models\TopupSaldoHistoryModel;

class TopupSaldoService
{
    protected $saldoModel;
    protected $historyModel;

    public function __construct()
    {
        $this->saldoModel = new SaldoDigiflazzModel();
        $this->historyModel = new TopupSaldoHistoryModel();
    }

    public function validateTopupSaldo(int $nominal): array
    {
        if ($nominal < 50000) return ['valid' => false, 'message' => 'Nominal minimum Rp 50.000'];
        if ($nominal > 1000000) return ['valid' => false, 'message' => 'Nominal maksimal Rp 1.000.000'];
        if ($nominal % 50000 !== 0) return ['valid' => false, 'message' => 'Nominal harus kelipatan Rp 50.000'];
        return ['valid' => true];
    }

    public function simulasiTopupSaldo(string $userId, int $nominal, string $metodePayment): array
    {
        $validasi = $this->validateTopupSaldo($nominal);
        if (!$validasi['valid']) {
            return ['success' => false, 'message' => $validasi['message']];
        }
        
        $randomStatus = rand(1, 100);
        $status = $randomStatus <= 90 ? 'berhasil' : 'pending';
        
        if ($status === 'berhasil') {
            $this->saldoModel->tambahSaldo($userId, $nominal);
        }
        
        $refId = 'TOPUP-' . time() . '-' . rand(100, 999);
        
        $this->historyModel->addHistory([
            'user_id' => $userId,
            'nominal' => $nominal,
            'metode_pembayaran' => $metodePayment,
            'tipe_transaksi' => 'topup_saldo',
            'status' => $status,
            'referensi_id' => $refId,
            'keterangan' => "Simulasi top-up via $metodePayment",
            'created_by' => $userId,
        ]);
        
        return [
            'success' => true,
            'message' => $status === 'berhasil' ? 'Top-up saldo berhasil' : 'Top-up saldo pending',
            'data' => [
                'ref_id' => $refId,
                'nominal' => $nominal,
                'metode' => $metodePayment,
                'status' => $status,
                'saldo_baru' => $this->saldoModel->getSaldoUser($userId),
            ]
        ];
    }

    // ✅ Method getSaldo (DIPERLUKAN untuk cek saldo sebelum topup pulsa)
    public function getSaldo(string $userId): int
    {
        return $this->saldoModel->getSaldoUser($userId);
    }

    public function getHistory(string $userId, ?string $tipe = null, int $limit = 50): array
    {
        return $this->historyModel->getHistory($userId, $tipe, $limit);
    }

    public function kurangiSaldoUntukPulsa(string $userId, int $nominalHarga): array
    {
        $saldoSekarang = $this->saldoModel->getSaldoUser($userId);
        
        if ($saldoSekarang < $nominalHarga) {
            return ['success' => false, 'message' => 'Saldo tidak cukup', 'saldo_sekarang' => $saldoSekarang];
        }
        
        $berhasil = $this->saldoModel->kurangiSaldo($userId, $nominalHarga);
        
        if ($berhasil) {
            return ['success' => true, 'saldo_baru' => $this->saldoModel->getSaldoUser($userId)];
        }
        
        return ['success' => false, 'message' => 'Gagal mengurangi saldo'];
    }

    public function catatHistoryTopupPulsa(string $userId, int $nominal, string $noTujuan, string $provider, string $metode, string $status): bool
    {
        return $this->historyModel->addHistory([
            'user_id' => $userId,
            'nominal' => $nominal,
            'metode_pembayaran' => $metode,
            'tipe_transaksi' => 'topup_pulsa',
            'status' => $status,
            'referensi_id' => 'PULSA-' . time(),
            'keterangan' => "Top-up $provider ke $noTujuan",
            'created_by' => $userId,
        ]);
    }
}