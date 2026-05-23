<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProviderModel;
use App\Models\NominalModel;
use App\Models\PenjualanPulsaModel;

class Digiflazz extends BaseController
{
    private $username;
    private $apiKey;
    private $baseUrl;

    // Daftar provider yang akan disync (whitelist)
    private $allowedBrands = [
        'TELKOMSEL', 'XL', 'INDOSAT', 'TRI', 'AXIS', 'SMARTFREN', 'by.U', 'Three'
    ];

    public function __construct()
    {
        $this->username = 'vayitiD8paYD';
        $this->apiKey   = 'dev-747cd1b0-55b3-11f1-8563-41bc985b6cd3';
        $this->baseUrl  = 'https://api.digiflazz.com/v1';
    }

    /**
     * Ambil daftar harga (price list) dari DigiFlazz
     */
    public function priceList()
    {
        $client = \Config\Services::curlrequest();
        $sign   = md5($this->username . $this->apiKey . 'pricelist');

        $response = $client->post($this->baseUrl . '/price-list', [
            'json' => [
                'username' => $this->username,
                'sign'     => $sign
            ]
        ]);

        return $this->response->setJSON(json_decode($response->getBody(), true));
    }

    /**
     * Sinkronisasi produk dari DigiFlazz ke database lokal (hanya pulsa)
     */
    public function syncProducts()
    {
        $client = \Config\Services::curlrequest();
        $sign   = md5($this->username . $this->apiKey . 'pricelist');

        $response = $client->post($this->baseUrl . '/price-list', [
            'json' => [
                'username' => $this->username,
                'sign'     => $sign
            ]
        ]);

        $result = json_decode($response->getBody(), true);

        if (!isset($result['data']) || !is_array($result['data'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil data dari DigiFlazz'
            ]);
        }

        $providerModel = new ProviderModel();
        $nominalModel  = new NominalModel();
        $synced        = 0;

        foreach ($result['data'] as $product) {
            // Pastikan product adalah array
            if (!is_array($product)) continue;

            // Hanya pulsa dan brand yang diizinkan
            if (empty($product['category']) || $product['category'] !== 'Pulsa') continue;
            if (!in_array($product['brand'], $this->allowedBrands)) continue;

            $brand = $product['brand'];
            // Normalisasi brand (Three -> TRI, by.U -> BYU, dll)
            $normalizedBrand = $this->normalizeBrand($brand);

            // Ekstrak nominal dari product_name (contoh: "XL 10.000" -> 10000)
            $nominalValue = $this->extractNominal($product['product_name']);
            if ($nominalValue === 0) continue;

            // Cari atau buat provider
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

            // Cek apakah nominal sudah ada
            $existing = $nominalModel->where('provider_id', $providerId)
                ->where('nominal', $nominalValue)
                ->first();

            if (!$existing) {
                $hargaModal = isset($product['price']) ? (float) $product['price'] : 0;
                $hargaJual  = $hargaModal + ($hargaModal * 0.05); // markup 5%

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

    /**
     * Topup pulsa
     */
    public function topup()
    {
        $rules = [
            'buyer_sku_code' => 'required',
            'customer_no'    => 'required|numeric|min_length[10]|max_length[15]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $client = \Config\Services::curlrequest();

        $refId = 'INV-' . time() . '-' . rand(100, 999);
        $sign  = md5($this->username . $this->apiKey . $refId);

        $payload = [
            'username'        => $this->username,
            'buyer_sku_code'  => $this->request->getPost('buyer_sku_code'),
            'customer_no'     => $this->request->getPost('customer_no'),
            'ref_id'          => $refId,
            'sign'            => $sign
        ];

        $testing = $this->request->getPost('testing') ?? true;
        if ($testing) {
            $payload['testing'] = true;
        }

        $response = $client->post($this->baseUrl . '/transaction', [
            'json' => $payload
        ]);

        $result = json_decode($response->getBody(), true);

        // Simpan ke database
        if (isset($result['data']) && is_array($result['data'])) {
            $data = $result['data'];
            $penjualanPulsa = new PenjualanPulsaModel();

            // Dapatkan provider_id dari buyer_sku_code (coba cek database dulu)
            $providerId = $this->getProviderIdBySku($this->request->getPost('buyer_sku_code'));

            $insertData = [
                'no_transaksi'       => $refId,
                'no_tujuan'          => $this->request->getPost('customer_no'),
                'provider_id'        => $providerId,
                'nominal_id'         => 0,
                'nominal'            => $data['price'] ?? 0,
                'harga_modal'        => $data['price'] ?? 0,
                'harga_jual'         => $this->request->getPost('harga_jual') ?? ($data['price'] ?? 0),
                'keuntungan'         => ($this->request->getPost('harga_jual') ?? ($data['price'] ?? 0)) - ($data['price'] ?? 0),
                'metode_pembayaran'  => $this->request->getPost('metode_pembayaran') ?? 'tunai',
                'status'             => $data['status'] ?? 'pending',
                'api_ref'            => $refId,
                'created_by'         => session()->get('user_id')
            ];
            $penjualanPulsa->insert($insertData);
        }

        return $this->response->setJSON($result);
    }

    // Helper: normalisasi brand
    private function normalizeBrand($brand)
    {
        $map = [
            'TELKOMSEL' => 'TSEL',
            'XL'        => 'XL',
            'INDOSAT'   => 'ISAT',
            'TRI'       => 'TRI',
            'Three'     => 'TRI',
            'AXIS'      => 'AXIS',
            'SMARTFREN' => 'SMART',
            'by.U'      => 'BYU'
        ];
        return $map[$brand] ?? strtoupper($brand);
    }

    // Helper: ekstrak nominal dari nama produk
    private function extractNominal($productName)
    {
        // Contoh: "XL 10.000" -> 10000, "Telkomsel 5.000" -> 5000
        if (preg_match('/(\d+[\.]?\d*)/', $productName, $matches)) {
            // Hapus titik ribuan
            $number = str_replace('.', '', $matches[1]);
            return (int) $number;
        }
        return 0;
    }

    // Helper mapping sku ke provider_id (sementara, idealnya dari database)
    private function getProviderIdBySku($sku)
    {
        $map = [
            's10' => 1, 's20' => 1, 's50' => 1, 's100' => 1,
            'x10' => 2, 'x5'  => 2,
            'i10' => 3, 'i5'  => 3,
            't10' => 4, 't5'  => 4,
            'ax10'=> 5, 'ax5' => 5,
            'sm10'=> 6,
        ];
        return $map[$sku] ?? 0;
    }
}