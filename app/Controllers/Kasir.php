<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\Kasir as ServicesKasir;
use App\Services\LaporanPulsa as ServicesLaporanPulsa;
use Config\Services;

class Kasir extends BaseController
{
    protected $kasirService;
    protected $laporanPulsaService;

    public function __construct()
    {
        $this->kasirService        = new ServicesKasir();
        $this->laporanPulsaService = new ServicesLaporanPulsa();
    }

    private function isApi(): bool
    {
        return str_contains($this->request->getUri()->getPath(), 'api/');
    }

    /**
     * Ambil data dari request (support JSON dan POST)
     */
    private function getRequestData(): array
    {
        $contentType = $this->request->getHeaderLine('Content-Type');

        // Jika Content-Type JSON
        if (strpos($contentType, 'application/json') !== false) {
            $json = $this->request->getJSON(true);
            if (is_array($json) && !empty($json)) {
                return $json;
            }
        }

        // Jika Content-Type form-encode atau form-data
        if (strpos($contentType, 'application/x-www-form-urlencoded') !== false ||
            strpos($contentType, 'multipart/form-data') !== false) {
            $post = $this->request->getPost();
            if (is_array($post) && !empty($post)) {
                return $post;
            }
        }

        // Fallback: coba dari POST
        $post = $this->request->getPost();
        if (is_array($post) && !empty($post)) {
            return $post;
        }

        // Fallback: raw input
        $raw = $this->request->getBody();
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
            parse_str($raw, $parsed);
            if (is_array($parsed) && !empty($parsed)) {
                return $parsed;
            }
        }

        return [];
    }

    private function apiResponse($success, $message, $code, $data = null, $errors = null)
    {
        $response = ['success' => $success, 'message' => $message, 'code' => $code];
        if ($data !== null) $response['data'] = $data;
        if ($errors !== null) $response['errors'] = $errors;
        return $this->response->setStatusCode($code)->setJSON($response);
    }

    public function index()
    {
        $dataProdukFisik   = $this->kasirService->getDataProdukFisik();
        $dataProdukDigital = $this->kasirService->getDataProdukDigital();
        $statistik         = $this->kasirService->getStatistikHarian();

        if ($this->isApi()) {
            return $this->apiResponse(true, 'Data kasir berhasil diambil', 200, [
                'produk_fisik'   => $dataProdukFisik['success'] ? $dataProdukFisik['data'] : [],
                'produk_digital' => $dataProdukDigital['success'] ? $dataProdukDigital['data'] : [],
                'statistik'      => $statistik['success'] ? $statistik['data'] : null,
                'cart'           => session()->get('kasir_cart') ?? [],
            ]);
        }

        $startDate   = $this->request->getGet('start_date');
        $endDate     = $this->request->getGet('end_date');
        $laporanPulsa = $this->laporanPulsaService->getData($startDate, $endDate);
        $summaryPulsa = $this->laporanPulsaService->getSummaryReport($startDate, $endDate);

        return view('kasir', [
            'title'         => 'Pulsa Io - Kasir',
            'produk_fisik'  => $dataProdukFisik['success'] ? $dataProdukFisik['data'] : [],
            'produk_digital' => $dataProdukDigital['success'] ? $dataProdukDigital['data'] : [],
            'statistik'     => $statistik['success'] ? $statistik['data'] : null,
            'laporan_pulsa' => $laporanPulsa['success'] ? $laporanPulsa['data'] : [],
            'summary_pulsa' => $summaryPulsa['success'] ? $summaryPulsa['data'] : [],
            'start_date'    => $startDate,
            'end_date'      => $endDate,
            'validation'    => Services::validation(),
            'page'          => 'kasir',
        ]);
    }

    // ================================================================
    // ADD - Tambah ke keranjang (Support JSON & Form-encode)
    // ================================================================
    public function add()
    {
        // 🔥 AMBIL DATA DARI REQUEST (support JSON & Form-encode)
        $data = $this->getRequestData();

        // 🔥 AMBIL JENIS PRODUK
        $jenis = $data['jenis_produk'] ?? null;
        $result = [];

        if ($jenis === 'fisik') {
            $validationRules = [
                'produk_id' => 'required',
                'jumlah'    => 'required|numeric|greater_than[0]',
            ];

            if (!$this->validateData($data, $validationRules)) {
                if ($this->isApi()) {
                    return $this->apiResponse(false, 'Validasi gagal', 422, null, $this->validator->getErrors());
                }
                return redirect()->to('/menu/kasir')
                    ->with('validation_errors', $this->validator->getErrors())
                    ->withInput();
            }

            $produkId = $data['produk_id'];
            $jumlah   = (int) $data['jumlah'];
            $result   = $this->kasirService->tambahProdukFisikKeKeranjang($produkId, $jumlah);

        } elseif ($jenis === 'digital') {
            $validationRules = [
                'no_tujuan_pulsa'         => 'required|numeric',
                'provider_id'             => 'required',
                'nominal_id'              => 'required',
                'metode_pembayaran_pulsa' => 'required|in_list[tunai,saldo,transfer,grip]',
            ];

            if (!$this->validateData($data, $validationRules)) {
                if ($this->isApi()) {
                    return $this->apiResponse(false, 'Validasi gagal', 422, null, $this->validator->getErrors());
                }
                return redirect()->to('/menu/kasir')
                    ->with('validation_errors', $this->validator->getErrors())
                    ->withInput();
            }

            $pulsaData = [
                'no_tujuan_pulsa'         => $data['no_tujuan_pulsa'],
                'provider_id'             => $data['provider_id'],
                'nominal_id'              => $data['nominal_id'],
                'metode_pembayaran_pulsa' => $data['metode_pembayaran_pulsa'],
            ];
            $result = $this->kasirService->tambahProdukDigitalKeKeranjang($pulsaData);

        } else {
            $result = ['success' => false, 'message' => 'Jenis produk tidak valid.'];
        }

        if ($this->isApi()) {
            return $this->apiResponse(
                $result['success'],
                $result['message'],
                $result['success'] ? 200 : 400,
                ['cart' => session()->get('kasir_cart') ?? []]
            );
        }

        if (!$result['success']) {
            return redirect()->to('/menu/kasir')->with('error', $result['message'])->withInput();
        }
        return redirect()->to('/menu/kasir')->with('success', $result['message']);
    }

    // ================================================================
    // REMOVE - Hapus dari keranjang
    // ================================================================
    public function remove()
    {
        $data = $this->getRequestData();
        $itemId = $data['item_id'] ?? null;

        if (!$itemId) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Item tidak ditemukan.', 400);
            }
            return redirect()->to('/menu/kasir')->with('error', 'Item tidak ditemukan.');
        }

        $result = $this->kasirService->hapusDariKeranjang($itemId);

        if ($this->isApi()) {
            return $this->apiResponse(
                $result['success'],
                $result['message'],
                $result['success'] ? 200 : 400,
                ['cart' => session()->get('kasir_cart') ?? []]
            );
        }

        if (!$result['success']) {
            return redirect()->to('/menu/kasir')->with('error', $result['message']);
        }
        return redirect()->to('/menu/kasir')->with('success', $result['message']);
    }

    // ================================================================
    // CHECKOUT
    // ================================================================
    public function checkout()
    {
        $data = $this->getRequestData();
        $cart = session()->get('kasir_cart') ?? [];
        $userId = session()->get('user_id');

        if (empty($cart)) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Tidak ada item di keranjang.', 400);
            }
            return redirect()->to('/menu/kasir')->with('error', 'Tidak ada item di keranjang.');
        }

        $validationRules = [
            'metode_pembayaran' => 'required|in_list[tunai,saldo,transfer,grip]',
        ];

        if (!$this->validateData($data, $validationRules)) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Validasi gagal', 422, null, $this->validator->getErrors());
            }
            return redirect()->to('/menu/kasir')
                ->with('validation_errors', $this->validator->getErrors());
        }

        $checkoutData = [
            'ppn_percent' => (float) ($data['ppn_percent'] ?? 0),
            'ppn'         => (float) ($data['ppn'] ?? 0),
            'diskon'      => (float) ($data['diskon'] ?? 0),
            'total'       => (float) ($data['grand_total'] ?? 0),
            'metode'      => $data['metode_pembayaran'],
            'created_by'  => $userId,
        ];

        $result = $this->kasirService->checkout($checkoutData);

        if ($this->isApi()) {
            return $this->apiResponse(
                $result['success'],
                $result['message'],
                $result['success'] ? 200 : 500,
                $result['data'] ?? null
            );
        }

        if (!$result['success']) {
            return redirect()->to('/menu/kasir')->with('error', $result['message']);
        }
        return redirect()->to('/menu/kasir')->with('success', $result['message']);
    }

    public function getNominals($providerId)
    {
        $nominalModel = new \App\Models\NominalModel();

        try {
            $nominals = $nominalModel->where('provider_id', $providerId)
                ->where('status', 'active')
                ->findAll();

            return $this->response->setJSON([
                'success'  => true,
                'nominals' => $nominals,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success'  => false,
                'message'  => $e->getMessage(),
                'nominals' => [],
            ]);
        }
    }
}