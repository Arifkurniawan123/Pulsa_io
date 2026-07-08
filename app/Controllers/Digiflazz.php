<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProviderModel;
use App\Models\NominalModel;
use App\Models\PenjualanPulsaModel;
use App\Services\TopupSaldoService;

class Digiflazz extends BaseController
{
    private $username;
    private $apiKey;
    private $baseUrl;

    private $allowedBrands = [
        'TELKOMSEL', 'XL', 'INDOSAT', 'TRI', 'AXIS', 'SMARTFREN', 'by.U', 'Three'
    ];

    public function __construct()
    {
        $this->username = 'vayitiD8paYD';
        $this->apiKey   = 'dev-747cd1b0-55b3-11f1-8563-41bc985b6cd3';
        $this->baseUrl  = 'https://api.digiflazz.com/v1';
    }

    public function priceList()
    {
        $client = \Config\Services::curlrequest();
        $sign   = md5($this->username . $this->apiKey . 'pricelist');
        $response = $client->post($this->baseUrl . '/price-list', [
            'json' => ['username' => $this->username, 'sign' => $sign]
        ]);
        return $this->response->setJSON(json_decode($response->getBody(), true));
    }

    public function syncProducts()
    {
        $client = \Config\Services::curlrequest();
        $sign   = md5($this->username . $this->apiKey . 'pricelist');
        $response = $client->post($this->baseUrl . '/price-list', [
            'json' => ['username' => $this->username, 'sign' => $sign]
        ]);
        $result = json_decode($response->getBody(), true);
        
        if (!isset($result['data']) || !is_array($result['data'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal mengambil data dari DigiFlazz']);
        }
        
        $providerModel = new ProviderModel();
        $nominalModel  = new NominalModel();
        $synced = 0;
        
        foreach ($result['data'] as $product) {
            if (!is_array($product)) continue;
            if (empty($product['category']) || $product['category'] !== 'Pulsa') continue;
            if (!in_array($product['brand'], $this->allowedBrands)) continue;
            
            $brand = $product['brand'];
            $normalizedBrand = $this->normalizeBrand($brand);
            $nominalValue = $this->extractNominal($product['product_name']);
            if ($nominalValue === 0) continue;
            
            $provider = $providerModel->where('kode_provider', $normalizedBrand)->first();
            if (!$provider) {
                $providerId = $providerModel->insert([
                    'nama_provider' => $brand,
                    'kode_provider' => $normalizedBrand,
                    'status'        => 'active'
                ]);
            } else {
                $providerId = $provider['id'];
            }
            
            $existing = $nominalModel->where('provider_id', $providerId)
                ->where('nominal', $nominalValue)->first();
                
            if (!$existing) {
                $hargaModal = isset($product['price']) ? (float) $product['price'] : 0;
                $hargaJual  = $hargaModal + ($hargaModal * 0.05);
                if ($hargaModal > 0) {
                    $nominalModel->insert([
                        'provider_id'  => $providerId,
                        'nominal'      => $nominalValue,
                        'harga_modal'  => $hargaModal,
                        'harga_jual'   => $hargaJual,
                        'status'       => 'active'
                    ]);
                    $synced++;
                }
            }
        }
        
        return $this->response->setJSON([
            'success' => true,
            'message' => "Sinkronisasi selesai. $synced produk baru ditambahkan."
        ]);
    }

    public function topup()
    {
        $metodePayment = $this->request->getPost('metode_pembayaran') ?? 'tunai';
        
        // 🔥 Validasi berbeda berdasarkan metode pembayaran
        if ($metodePayment === 'saldo') {
            // Untuk SALDO, buyer_sku_code tidak wajib
            $rules = [
                'customer_no'    => 'required|numeric|min_length[10]|max_length[15]',
                'nominal_id'     => 'required|integer',
                'harga_jual'     => 'required|numeric',
                'metode_pembayaran' => 'required|in_list[tunai,saldo,transfer,grip]'
            ];
        } else {
            // Untuk metode lain, buyer_sku_code wajib
            $rules = [
                'buyer_sku_code' => 'required',
                'customer_no'    => 'required|numeric|min_length[10]|max_length[15]',
                'metode_pembayaran' => 'required|in_list[tunai,saldo,transfer,grip]'
            ];
        }
        
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $hargaJual = (float) $this->request->getPost('harga_jual') ?? 0;
        $nominalId = (int) $this->request->getPost('nominal_id') ?? 0;
        $customerNo = $this->request->getPost('customer_no');

        // 🔥 Ambil user_id dari JWT payload
        $userId = $this->request->user->user_id ?? null;
        
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'User tidak terautentikasi'
            ]);
        }

        // 🔥 Jika metode SALDO (simulasi internal, tanpa panggil Digiflazz)
        if ($metodePayment === 'saldo') {
            $topupSaldoService = new TopupSaldoService();
            
            // Cek saldo cukup tidak
            $cekSaldo = $topupSaldoService->getSaldo($userId);
            
            if ($cekSaldo < $hargaJual) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Saldo tidak mencukupi. Saldo Anda: Rp ' . number_format($cekSaldo, 0, ',', '.')
                ]);
            }
            
            // Kurangi saldo
            $hasilKurangi = $topupSaldoService->kurangiSaldoUntukPulsa($userId, $hargaJual);
            
            if (!$hasilKurangi['success']) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => $hasilKurangi['message']
                ]);
            }
            
            $refId = 'INV-' . time() . '-' . rand(100, 999);
            $status = 'sukses';
            $message = 'Transaksi sukses (simulasi saldo)';
            $price = $hargaJual;

            // Catat transaksi ke tbl_penjualan_pulsa
            $penjualanPulsa = new PenjualanPulsaModel();
            // Cari provider_id dari nominal_id
            $nominalModel = new NominalModel();
            $nominal = $nominalModel->find($nominalId);
            $providerId = $nominal ? $nominal['provider_id'] : 0;
            
            $insertData = [
                'no_transaksi'       => $refId,
                'no_tujuan'          => $customerNo,
                'provider_id'        => $providerId,
                'nominal_id'         => $nominalId,
                'nominal'            => $price,
                'harga_modal'        => $price,
                'harga_jual'         => $hargaJual,
                'keuntungan'         => 0,
                'metode_pembayaran'  => $metodePayment,
                'status'             => $status,
                'api_ref'            => $refId,
                'created_by'         => $userId
            ];
            $penjualanPulsa->insert($insertData);

            // Catat history topup pulsa
            $topupSaldoService->catatHistoryTopupPulsa(
                $userId,
                $hargaJual,
                $customerNo,
                'SALDO',
                $metodePayment,
                $status
            );

            // Ambil saldo terbaru untuk response
            $saldoBaru = $topupSaldoService->getSaldo($userId);

            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'data' => [
                    'status' => $status,
                    'message' => $message,
                    'price' => $price,
                    'ref_id' => $refId,
                    'saldo_baru' => $saldoBaru
                ]
            ]);
        }

        // ============================================================
        // Untuk metode lain (tunai, transfer) panggil DigiFlazz
        // ============================================================
        $buyerSkuCode = $this->request->getPost('buyer_sku_code');
        $testing = $this->request->getPost('testing') ?? true;
        
        $client = \Config\Services::curlrequest();
        $refId = 'INV-' . time() . '-' . rand(100, 999);
        $sign  = md5($this->username . $this->apiKey . $refId);
        
        $payload = [
            'username'        => $this->username,
            'buyer_sku_code'  => $buyerSkuCode,
            'customer_no'     => $customerNo,
            'ref_id'          => $refId,
            'sign'            => $sign
        ];
        
        if ($testing) {
            $payload['testing'] = true;
        }

        $response = $client->post($this->baseUrl . '/transaction', ['json' => $payload]);
        $result = json_decode($response->getBody(), true);

        // Simpan ke database
        if (isset($result['data']) && is_array($result['data'])) {
            $data = $result['data'];
            $penjualanPulsa = new PenjualanPulsaModel();
            $nominalModel = new NominalModel();
            $nominal = $nominalModel->find($nominalId);
            $providerId = $nominal ? $nominal['provider_id'] : 0;
            
            $apiStatus = isset($data['status']) ? strtolower($data['status']) : 'proses';
            $validStatus = in_array($apiStatus, ['proses', 'sukses', 'gagal']) ? $apiStatus : 'proses';
            
            $insertData = [
                'no_transaksi'       => $refId,
                'no_tujuan'          => $customerNo,
                'provider_id'        => $providerId,
                'nominal_id'         => $nominalId,
                'nominal'            => $data['price'] ?? 0,
                'harga_modal'        => $data['price'] ?? 0,
                'harga_jual'         => $hargaJual,
                'keuntungan'         => $hargaJual - ($data['price'] ?? 0),
                'metode_pembayaran'  => $metodePayment,
                'status'             => $validStatus,
                'api_ref'            => $refId,
                'api_status'         => $data['status'] ?? null,
                'created_by'         => $userId
            ];
            $penjualanPulsa->insert($insertData);
        }

        return $this->response->setJSON($result);
    }

    private function normalizeBrand($brand)
    {
        $map = [
            'TELKOMSEL' => 'TSEL', 'XL' => 'XL', 'INDOSAT' => 'ISAT',
            'TRI' => 'TRI', 'Three' => 'TRI', 'AXIS' => 'AXIS',
            'SMARTFREN' => 'SMART', 'by.U' => 'BYU'
        ];
        return $map[$brand] ?? strtoupper($brand);
    }

    private function extractNominal($productName)
    {
        if (preg_match('/(\d+[\.]?\d*)/', $productName, $matches)) {
            return (int) str_replace('.', '', $matches[1]);
        }
        return 0;
    }
}