<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\LaporanPulsa as ServicesLaporanPulsa;
use App\Services\ExcelExportService;
use App\Services\PDFExportService;

class LaporanPulsa extends BaseController
{
    protected $laporanPulsaService;
    protected $excelExportService;
    protected $pdfExportService;

    public function __construct()
    {
        $this->laporanPulsaService = new ServicesLaporanPulsa();
        $this->excelExportService  = new ExcelExportService();
        $this->pdfExportService    = new PDFExportService();
        helper(['form', 'url']);
    }

    private function isApi(): bool
    {
        return str_contains($this->request->getUri()->getPath(), 'api/');
    }

    public function index()
    {
        $startDate    = $this->request->getGet('start_date');
        $endDate      = $this->request->getGet('end_date');
        $transactions = $this->laporanPulsaService->getData($startDate, $endDate);
        $summary      = $this->laporanPulsaService->getSummaryReport($startDate, $endDate);

        if ($this->isApi()) {
            return $this->response->setStatusCode(200)->setJSON([
                'success'      => true,
                'message'      => 'Data laporan pulsa berhasil diambil',
                'code'         => 200,
                'data'         => $transactions['success'] ? $transactions['data'] : [],
                'summary'      => $summary['success'] ? $summary['data'] : [],
                'start_date'   => $startDate,
                'end_date'     => $endDate,
            ]);
        }

        return view('laporan_pulsa/index', [
            'title'        => 'Laporan Penjualan Pulsa',
            'transactions' => $transactions['success'] ? $transactions['data'] : [],
            'summary'      => $summary['success'] ? $summary['data'] : [],
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'page'         => 'laporan_pulsa',
        ]);
    }

    public function create()
    {
        $dataProvider = $this->laporanPulsaService->getProviders();
        $dataNominal  = $this->laporanPulsaService->getNominals();

        return view('laporan_pulsa/create', [
            'title'      => 'Tambah Transaksi Pulsa',
            'providers'  => $dataProvider['success'] ? $dataProvider['data'] : [],
            'nominals'   => $dataNominal['success'] ? $dataNominal['data'] : [],
            'page'       => 'laporan_pulsa',
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function store()
    {
        $validationRules = [
            'no_tujuan'         => 'required|numeric',
            'provider_id'       => 'required|integer',
            'nominal_id'        => 'required|integer',
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
            return redirect()->back()->withInput()->with('validation', $this->validator->getErrors());
        }

        $data = [
            'no_tujuan'         => $this->request->getPost('no_tujuan'),
            'provider_id'       => $this->request->getPost('provider_id'),
            'nominal_id'        => $this->request->getPost('nominal_id'),
            'metode_pembayaran' => $this->request->getPost('metode_pembayaran'),
            'created_by'        => session()->get('user_id'),
        ];

        $result = $this->laporanPulsaService->createData($data);

        if ($this->isApi()) {
            return $this->response
                ->setStatusCode($result['success'] ? 201 : 500)
                ->setJSON([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'code'    => $result['success'] ? 201 : 500,
                ]);
        }

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }
        return redirect()->to('/laporan-pulsa')->with('success', $result['message']);
    }

    public function edit($id)
    {
        $result = $this->laporanPulsaService->getById($id);

        if ($this->isApi()) {
            return $this->response
                ->setStatusCode($result['success'] ? 200 : 404)
                ->setJSON([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'code'    => $result['success'] ? 200 : 404,
                    'data'    => $result['data'] ?? null,
                ]);
        }

        if (!$result['success']) {
            return redirect()->to('/laporan-pulsa')->with('error', $result['message']);
        }

        $dataProvider = $this->laporanPulsaService->getProviders();
        $dataNominal  = $this->laporanPulsaService->getNominals();

        return view('laporan_pulsa/edit', [
            'title'      => 'Edit Transaksi Pulsa',
            'transaksi'  => $result['data'],
            'providers'  => $dataProvider['success'] ? $dataProvider['data'] : [],
            'nominals'   => $dataNominal['success'] ? $dataNominal['data'] : [],
            'page'       => 'laporan_pulsa',
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function update($id)
    {
        $validationRules = [
            'no_tujuan'         => 'required|numeric',
            'provider_id'       => 'required|integer',
            'nominal_id'        => 'required|integer',
            'metode_pembayaran' => 'required|in_list[tunai,saldo,transfer,grip]',
            'status'            => 'required|in_list[proses,sukses,gagal]',
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
            return redirect()->back()->withInput()->with('validation', $this->validator->getErrors());
        }

        $data = [
            'no_tujuan'         => $this->request->getPost('no_tujuan'),
            'provider_id'       => $this->request->getPost('provider_id'),
            'nominal_id'        => $this->request->getPost('nominal_id'),
            'metode_pembayaran' => $this->request->getPost('metode_pembayaran'),
            'status'            => $this->request->getPost('status'),
        ];

        $result = $this->laporanPulsaService->updateData($id, $data);

        if ($this->isApi()) {
            return $this->response
                ->setStatusCode($result['success'] ? 200 : 500)
                ->setJSON([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'code'    => $result['success'] ? 200 : 500,
                ]);
        }

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }
        return redirect()->to('/laporan-pulsa')->with('success', $result['message']);
    }

    public function delete($id)
    {
        $result = $this->laporanPulsaService->deleteData($id);

        return $this->response
            ->setStatusCode($result['code'])
            ->setJSON([
                'success' => $result['success'],
                'message' => $result['message'],
                'code'    => $result['code'],
            ]);
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

    public function exportExcel()
    {
        $startDate    = $this->request->getGet('start_date');
        $endDate      = $this->request->getGet('end_date');
        $dataResult   = $this->laporanPulsaService->getData($startDate, $endDate);
        $summaryResult = $this->laporanPulsaService->getSummaryReport($startDate, $endDate);

        if (!$dataResult['success'] || !$summaryResult['success']) {
            return redirect()->back()->with('error', 'Gagal mengambil data untuk export');
        }

        return $this->excelExportService->exportLaporanPulsa(
            $dataResult['data'], $summaryResult['data'], $startDate, $endDate
        );
    }

    public function exportPDF()
    {
        $startDate    = $this->request->getGet('start_date');
        $endDate      = $this->request->getGet('end_date');
        $dataResult   = $this->laporanPulsaService->getData($startDate, $endDate);
        $summaryResult = $this->laporanPulsaService->getSummaryReport($startDate, $endDate);

        if (!$dataResult['success'] || !$summaryResult['success']) {
            return redirect()->back()->with('error', 'Gagal mengambil data untuk export');
        }

        return $this->pdfExportService->exportLaporanPulsa(
            $dataResult['data'], $summaryResult['data'], $startDate, $endDate
        );
    }
}