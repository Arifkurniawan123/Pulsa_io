<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\TopupSaldoService;

class TopupSaldo extends BaseController
{
    protected $topupSaldoService;

    public function __construct()
    {
        $this->topupSaldoService = new TopupSaldoService();
    }

    private function getUserId()
    {
        $userData = $this->request->user ?? session()->get('user_data');
        return $userData->user_id ?? $userData->id ?? session()->get('user_id');
    }

    public function getSaldo()
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false, 'message' => 'Unauthorized - User not found'
            ]);
        }
        $saldo = $this->topupSaldoService->getSaldo($userId);
        return $this->response->setJSON([
            'success' => true,
            'data' => ['saldo' => $saldo]
        ]);
    }

    public function getHistory()
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false, 'message' => 'Unauthorized'
            ]);
        }
        $tipe = $this->request->getGet('tipe');
        if ($tipe === 'null' || $tipe === '') {
            $tipe = null;
        }
        $tipe = $tipe ?? 'topup_saldo';
        $limit = $this->request->getGet('limit') ?? 50;
        $history = $this->topupSaldoService->getHistory($userId, $tipe, $limit);
        return $this->response->setJSON([
            'success' => true,
            'data' => $history
        ]);
    }

    public function simulasi()
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false, 'message' => 'Unauthorized'
            ]);
        }
        $nominal = $this->request->getPost('nominal');
        $metode = $this->request->getPost('metode_pembayaran');
        if (!$nominal || !$metode) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false, 'message' => 'Nominal dan metode pembayaran harus diisi'
            ]);
        }
        $result = $this->topupSaldoService->simulasiTopupSaldo($userId, (int) $nominal, $metode);
        return $this->response->setJSON($result);
    }
}