<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TopupEwalletModel;
use App\Models\TopupBankModel;
use App\Models\SaldoDigiflazzModel;
use App\Models\TopupSaldoHistoryModel;
use App\Services\TopupSaldoService;

class TopupEwalletBank extends BaseController
{
    protected $topupEwalletModel;
    protected $topupBankModel;
    protected $topupSaldoService;

    public function __construct()
    {
        $this->topupEwalletModel = new TopupEwalletModel();
        $this->topupBankModel = new TopupBankModel();
        $this->topupSaldoService = new TopupSaldoService();
    }

    // ======================= OPTIONS CORS =======================
    public function options()
    {
        return $this->response
            ->setStatusCode(200)
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setJSON(['success' => true]);
    }

    // ======================= E-WALLET =======================
    public function topupEwalletInitiate()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Method not allowed']);
        }

        $rules = [
            'metode_ewallet' => 'required|in_list[gcash,dana,ovo,linkaja,gopay]',
            'nomor_telepon' => 'required|numeric|min_length[10]|max_length[15]',
            'nominal' => 'required|numeric|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $userId = $this->request->user->user_id ?? null;
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'User tidak terautentikasi'
            ]);
        }

        $metodeEwallet = $this->request->getPost('metode_ewallet');
        $nomorTelepon = $this->request->getPost('nomor_telepon');
        $nominal = (int) $this->request->getPost('nominal');

        $id = $this->topupEwalletModel->createTopup($userId, $metodeEwallet, $nomorTelepon, $nominal);
        if (!$id) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Gagal membuat transaksi topup'
            ]);
        }

        $topup = $this->topupEwalletModel->find($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Transaksi topup e-wallet berhasil dibuat',
            'data' => [
                'id' => $topup['id'],
                'ref_id' => $topup['ref_id'],
                'metode' => $topup['metode_ewallet'],
                'nomor_telepon' => $topup['nomor_telepon'],
                'nominal' => $topup['nominal'],
                'status' => $topup['status'],
                'created_at' => $topup['created_at']
            ]
        ]);
    }

    public function topupEwalletConfirm($refId)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Method not allowed']);
        }

        $userId = $this->request->user->user_id ?? null;
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'User tidak terautentikasi'
            ]);
        }

        $topup = $this->topupEwalletModel->getByRefId($refId);
        if (!$topup || $topup['user_id'] !== $userId) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ]);
        }

        if ($topup['status'] !== 'pending') {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Status transaksi sudah ' . $topup['status']
            ]);
        }

        $this->topupEwalletModel->updateStatus($topup['id'], 'berhasil', 'Top-up berhasil diproses');
        $saldoModel = new SaldoDigiflazzModel();
        $saldoModel->tambahSaldo($userId, $topup['nominal']);

        $historyModel = new TopupSaldoHistoryModel();
        $historyModel->addHistory([
            'user_id' => $userId,
            'nominal' => $topup['nominal'],
            'metode_pembayaran' => $topup['metode_ewallet'],
            'tipe_transaksi' => 'topup_saldo',
            'status' => 'berhasil',
            'referensi_id' => $topup['ref_id'],
            'keterangan' => "Top-up via E-Wallet ({$topup['metode_ewallet']}) ke {$topup['nomor_telepon']}",
            'created_by' => $userId,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Top-up e-wallet berhasil dikonfirmasi',
            'data' => [
                'ref_id' => $topup['ref_id'],
                'nominal' => $topup['nominal'],
                'status' => 'berhasil',
                'saldo_baru' => $saldoModel->getSaldoUser($userId)
            ]
        ]);
    }

    public function topupEwalletHistory()
    {
        $userId = $this->request->user->user_id ?? null;
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'User tidak terautentikasi'
            ]);
        }

        $limit = $this->request->getGet('limit') ?? 50;
        $offset = $this->request->getGet('offset') ?? 0;
        $history = $this->topupEwalletModel->getHistoryByUser($userId, $limit, $offset);

        return $this->response->setJSON([
            'success' => true,
            'data' => $history,
            'total' => count($history)
        ]);
    }

    public function getSupportedEwallets()
    {
        $ewallets = [
            ['code' => 'gcash', 'name' => 'GCash', 'country' => 'PH'],
            ['code' => 'dana', 'name' => 'DANA', 'country' => 'ID'],
            ['code' => 'ovo', 'name' => 'OVO', 'country' => 'ID'],
            ['code' => 'linkaja', 'name' => 'LinkAja', 'country' => 'ID'],
            ['code' => 'gopay', 'name' => 'GoPay', 'country' => 'ID'],
        ];
        return $this->response->setJSON(['success' => true, 'data' => $ewallets]);
    }

    // ======================= BANK =======================
    public function topupBankInitiate()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Method not allowed']);
        }

        $rules = [
            'nama_bank' => 'required|string|min_length[3]|max_length[100]',
            'nomor_rekening' => 'required|numeric|min_length[10]|max_length[20]',
            'atas_nama' => 'required|string|min_length[3]|max_length[100]',
            'nominal' => 'required|numeric|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $userId = $this->request->user->user_id ?? null;
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'User tidak terautentikasi'
            ]);
        }

        $namaBank = $this->request->getPost('nama_bank');
        $nomorRekening = $this->request->getPost('nomor_rekening');
        $atasNama = $this->request->getPost('atas_nama');
        $nominal = (int) $this->request->getPost('nominal');

        $id = $this->topupBankModel->createTopup($userId, $namaBank, $nomorRekening, $atasNama, $nominal);
        if (!$id) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Gagal membuat transaksi topup'
            ]);
        }

        $topup = $this->topupBankModel->find($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Transaksi topup bank berhasil dibuat (waiting for payment confirmation)',
            'data' => [
                'id' => $topup['id'],
                'ref_id' => $topup['ref_id'],
                'nama_bank' => $topup['nama_bank'],
                'nomor_rekening' => $topup['nomor_rekening'],
                'atas_nama' => $topup['atas_nama'],
                'nominal' => $topup['nominal'],
                'status' => $topup['status'],
                'created_at' => $topup['created_at']
            ]
        ]);
    }

    public function topupBankConfirm($refId)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Method not allowed']);
        }

        $userId = $this->request->user->user_id ?? null;
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'User tidak terautentikasi'
            ]);
        }

        $topup = $this->topupBankModel->getByRefId($refId);
        if (!$topup || $topup['user_id'] !== $userId) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ]);
        }

        if ($topup['status'] !== 'pending') {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Status transaksi sudah ' . $topup['status']
            ]);
        }

        $this->topupBankModel->updateStatus($topup['id'], 'berhasil', 'Pembayaran bank transfer berhasil diverifikasi');
        $saldoModel = new SaldoDigiflazzModel();
        $saldoModel->tambahSaldo($userId, $topup['nominal']);

        $historyModel = new TopupSaldoHistoryModel();
        $historyModel->addHistory([
            'user_id' => $userId,
            'nominal' => $topup['nominal'],
            'metode_pembayaran' => $topup['nama_bank'],
            'tipe_transaksi' => 'topup_saldo',
            'status' => 'berhasil',
            'referensi_id' => $topup['ref_id'],
            'keterangan' => "Top-up via Bank ({$topup['nama_bank']}) ke {$topup['nomor_rekening']} atas nama {$topup['atas_nama']}",
            'created_by' => $userId,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Top-up bank transfer berhasil dikonfirmasi',
            'data' => [
                'ref_id' => $topup['ref_id'],
                'nominal' => $topup['nominal'],
                'status' => 'berhasil',
                'saldo_baru' => $saldoModel->getSaldoUser($userId)
            ]
        ]);
    }

    public function topupBankHistory()
    {
        $userId = $this->request->user->user_id ?? null;
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'User tidak terautentikasi'
            ]);
        }

        $limit = $this->request->getGet('limit') ?? 50;
        $offset = $this->request->getGet('offset') ?? 0;
        $history = $this->topupBankModel->getHistoryByUser($userId, $limit, $offset);

        return $this->response->setJSON([
            'success' => true,
            'data' => $history,
            'total' => count($history)
        ]);
    }

    public function getSupportedBanks()
    {
        $banks = [
            ['code' => 'BCA', 'name' => 'Bank Central Asia', 'type' => 'transfer'],
            ['code' => 'BNI', 'name' => 'Bank Negara Indonesia', 'type' => 'transfer'],
            ['code' => 'Mandiri', 'name' => 'Bank Mandiri', 'type' => 'transfer'],
            ['code' => 'BRI', 'name' => 'Bank Rakyat Indonesia', 'type' => 'transfer'],
            ['code' => 'CIMB', 'name' => 'CIMB Niaga', 'type' => 'transfer'],
            ['code' => 'Permata', 'name' => 'Bank Permata', 'type' => 'transfer'],
            ['code' => 'Danamon', 'name' => 'Bank Danamon', 'type' => 'transfer'],
            ['code' => 'Maybank', 'name' => 'Maybank Indonesia', 'type' => 'transfer'],
        ];
        return $this->response->setJSON(['success' => true, 'data' => $banks]);
    }
}