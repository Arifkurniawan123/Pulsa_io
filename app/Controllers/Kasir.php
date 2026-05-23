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

    public function index()
    {
        $dataProdukFisik   = $this->kasirService->getDataProdukFisik();
        $dataProdukDigital = $this->kasirService->getDataProdukDigital();
        $statistik         = $this->kasirService->getStatistikHarian();

        if ($this->isApi()) {
            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => 'Data kasir berhasil diambil',
                'code'    => 200,
                'data'    => [
                    'produk_fisik'   => $dataProdukFisik['success'] ? $dataProdukFisik['data'] : [],
                    'produk_digital' => $dataProdukDigital['success'] ? $dataProdukDigital['data'] : [],
                    'statistik'      => $statistik['success'] ? $statistik['data'] : null,
                    'cart'           => session()->get('kasir_cart') ?? [],
                ],
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

    public function add()
    {
        $jenis  = $this->request->getPost('jenis_produk');
        $result = [];

        if ($jenis === 'fisik') {
            $validationRules = [
                'produk_id' => 'required',
                'jumlah'    => 'required|numeric|greater_than[0]',
            ];

            if (!$this->validate($validationRules)) {
                if ($this->isApi()) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'success' => false,
                        'message' => 'Validasi gagal',
                        'code'    => 422,
                        'errors'  => $this->validator->getErrors(),
                    ]);
                }
                return redirect()->to('/menu/kasir')
                    ->with('validation_errors', $this->validator->getErrors())
                    ->withInput();
            }

            $produkId = $this->request->getPost('produk_id');
            $jumlah   = (int) $this->request->getPost('jumlah');
            $result   = $this->kasirService->tambahProdukFisikKeKeranjang($produkId, $jumlah);

        } elseif ($jenis === 'digital') {
            $validationRules = [
                'no_tujuan_pulsa'         => 'required|numeric',
                'provider_id'             => 'required',
                'nominal_id'              => 'required',
                'metode_pembayaran_pulsa' => 'required|in_list[tunai,saldo,transfer,grip]',
            ];

            if (!$this->validate($validationRules)) {
                if ($this->isApi()) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'success' => false,
                        'message' => 'Validasi gagal',
                        'code'    => 422,
                        'errors'  => $this->validator->getErrors(),
                    ]);
                }
                return redirect()->to('/menu/kasir')
                    ->with('validation_errors', $this->validator->getErrors())
                    ->withInput();
            }

            $data   = [
                'no_tujuan_pulsa'         => $this->request->getPost('no_tujuan_pulsa'),
                'provider_id'             => $this->request->getPost('provider_id'),
                'nominal_id'              => $this->request->getPost('nominal_id'),
                'metode_pembayaran_pulsa' => $this->request->getPost('metode_pembayaran_pulsa'),
            ];
            $result = $this->kasirService->tambahProdukDigitalKeKeranjang($data);

        } else {
            $result = ['success' => false, 'message' => 'Jenis produk tidak valid.'];
        }

        if ($this->isApi()) {
            return $this->response
                ->setStatusCode($result['success'] ? 200 : 400)
                ->setJSON([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'code'    => $result['success'] ? 200 : 400,
                    'cart'    => session()->get('kasir_cart') ?? [],
                ]);
        }

        if (!$result['success']) {
            return redirect()->to('/menu/kasir')->with('error', $result['message'])->withInput();
        }
        return redirect()->to('/menu/kasir')->with('success', $result['message']);
    }

    public function remove()
    {
        $itemId = $this->request->getPost('item_id');

        if (!$itemId) {
            if ($this->isApi()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Item tidak ditemukan.',
                    'code'    => 400,
                ]);
            }
            return redirect()->to('/menu/kasir')->with('error', 'Item tidak ditemukan.');
        }

        $result = $this->kasirService->hapusDariKeranjang($itemId);

        if ($this->isApi()) {
            return $this->response
                ->setStatusCode($result['success'] ? 200 : 400)
                ->setJSON([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'code'    => $result['success'] ? 200 : 400,
                    'cart'    => session()->get('kasir_cart') ?? [],
                ]);
        }

        if (!$result['success']) {
            return redirect()->to('/menu/kasir')->with('error', $result['message']);
        }
        return redirect()->to('/menu/kasir')->with('success', $result['message']);
    }

    public function checkout()
    {
        $cart   = session()->get('kasir_cart') ?? [];
        $userId = session()->get('user_id');

        if (empty($cart)) {
            if ($this->isApi()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada item di keranjang.',
                    'code'    => 400,
                ]);
            }
            return redirect()->to('/menu/kasir')->with('error', 'Tidak ada item di keranjang.');
        }

        $validationRules = [
            'metode_pembayaran' => 'required|in_list[tunai,saldo,transfer,grip]',
        ];

        if (!$this->validate($validationRules)) {
            if ($this->isApi()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'code'    => 422,
                    'errors'  => $this->validator->getErrors(),
                ]);
            }
            return redirect()->to('/menu/kasir')
                ->with('validation_errors', $this->validator->getErrors());
        }

        $data = [
            'ppn_percent' => (float) $this->request->getPost('ppn_percent'),
            'ppn'         => (float) $this->request->getPost('ppn'),
            'diskon'      => (float) $this->request->getPost('diskon'),
            'total'       => (float) $this->request->getPost('grand_total'),
            'metode'      => $this->request->getPost('metode_pembayaran'),
            'created_by'  => $userId,
        ];

        $result = $this->kasirService->checkout($data);

        if ($this->isApi()) {
            return $this->response
                ->setStatusCode($result['success'] ? 200 : 500)
                ->setJSON([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'code'    => $result['success'] ? 200 : 500,
                    'data'    => $result['data'] ?? null,
                ]);
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