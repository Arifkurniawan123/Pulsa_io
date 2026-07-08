<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\LaporanPulsa as LaporanPulsaService;

class LaporanPulsa extends BaseController
{
    protected $laporanPulsaService;

    public function __construct()
    {
        $this->laporanPulsaService = new LaporanPulsaService();
        helper(['form', 'url']);
    }

    private function isApi(): bool
    {
        return str_contains($this->request->getUri()->getPath(), 'api/');
    }

    /**
     * Ambil data dari request (support JSON, POST, Form-encode)
     */
    private function getRequestData(): array
    {
        $contentType = $this->request->getHeaderLine('Content-Type');
        
        if (strpos($contentType, 'application/json') !== false) {
            $jsonData = $this->request->getJSON(true);
            if (is_array($jsonData) && !empty($jsonData)) {
                return $jsonData;
            }
        }

        if (strpos($contentType, 'application/x-www-form-urlencoded') !== false || 
            strpos($contentType, 'multipart/form-data') !== false) {
            $postData = $this->request->getPost();
            if (is_array($postData) && !empty($postData)) {
                return $postData;
            }
        }

        $postData = $this->request->getPost();
        if (is_array($postData) && !empty($postData)) {
            return $postData;
        }

        $rawBody = $this->request->getBody();
        if (!empty($rawBody)) {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
            parse_str($rawBody, $parsed);
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

    /**
     * Validasi nomor HP (08, 62, +62)
     */
    private function validatePhoneNumber($noTujuan): bool
    {
        return preg_match('/^(08|62|\+62)[0-9]{8,13}$/', $noTujuan) === 1;
    }

    // ================================================================
    // GET ALL + FILTER
    // ================================================================
    public function index()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');
        $status    = $this->request->getGet('status');

        $transactions = $this->laporanPulsaService->getData($startDate, $endDate, $status);
        $summary      = $this->laporanPulsaService->getSummaryReport($startDate, $endDate);

        if ($this->isApi()) {
            return $this->apiResponse(true, 'Data laporan pulsa berhasil diambil', 200, [
                'transactions' => $transactions['success'] ? $transactions['data'] : [],
                'summary'      => $summary['success'] ? $summary['data'] : [],
                'filters'      => [
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                    'status'     => $status,
                ]
            ]);
        }

        return view('laporan_pulsa/index', [
            'title'        => 'Laporan Pulsa',
            'page'         => 'laporan-pulsa',
            'transactions' => $transactions['success'] ? $transactions['data'] : [],
            'summary'      => $summary['success'] ? $summary['data'] : [],
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'status'       => $status,
        ]);
    }

    // ================================================================
    // GET BY ID
    // ================================================================
    public function show($id)
    {
        $result = $this->laporanPulsaService->getById($id);

        if ($this->isApi()) {
            if (!$result['success']) {
                return $this->apiResponse(false, $result['message'], 404);
            }
            return $this->apiResponse(true, 'Data ditemukan', 200, $result['data']);
        }

        if (!$result['success']) {
            return redirect()->to('/laporan-pulsa')->with('error', $result['message']);
        }

        return view('laporan_pulsa/show', [
            'title'       => 'Detail Transaksi Pulsa',
            'page'        => 'laporan-pulsa',
            'transaction' => $result['data'],
        ]);
    }

    // ================================================================
    // CREATE (GET Form)
    // ================================================================
    public function create()
    {
        $providers = $this->laporanPulsaService->getProviders();
        $nominals  = $this->laporanPulsaService->getNominals();

        return view('laporan_pulsa/create', [
            'title'     => 'Tambah Transaksi Pulsa',
            'page'      => 'laporan-pulsa',
            'providers' => $providers['success'] ? $providers['data'] : [],
            'nominals'  => $nominals['success'] ? $nominals['data'] : [],
        ]);
    }

    // ================================================================
    // STORE (POST)
    // ================================================================
    public function store()
    {
        $data = $this->getRequestData();
        
        if (isset($data['_method'])) {
            unset($data['_method']);
        }

        // 🔥 VALIDASI
        $rules = [
            'no_tujuan' => [
                'rules' => 'required|numeric|min_length[10]|max_length[15]',
                'errors' => [
                    'required' => 'Nomor tujuan harus diisi',
                    'numeric' => 'Nomor tujuan harus berupa angka',
                    'min_length' => 'Nomor tujuan minimal 10 digit',
                    'max_length' => 'Nomor tujuan maksimal 15 digit',
                ]
            ],
            'provider_id' => [
                'rules' => 'required|integer|is_not_unique[tbl_provider_pulsa.id]',
                'errors' => [
                    'required' => 'Provider harus dipilih',
                    'integer' => 'Provider tidak valid',
                    'is_not_unique' => 'Provider tidak ditemukan',
                ]
            ],
            'nominal_id' => [
                'rules' => 'required|integer|is_not_unique[tbl_nominal_pulsa.id]',
                'errors' => [
                    'required' => 'Nominal harus dipilih',
                    'integer' => 'Nominal tidak valid',
                    'is_not_unique' => 'Nominal tidak ditemukan',
                ]
            ],
            'metode_pembayaran' => [
                'rules' => 'required|in_list[tunai,saldo,transfer,grip]',
                'errors' => [
                    'required' => 'Metode pembayaran harus diisi',
                    'in_list' => 'Metode pembayaran harus tunai, saldo, transfer, atau grip',
                ]
            ]
        ];

        if (!$this->validateData($data, $rules)) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Validasi gagal', 422, null, $this->validator->getErrors());
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 🔥 VALIDASI NO HP (AWALAN 08, 62, +62)
        if (!$this->validatePhoneNumber($data['no_tujuan'])) {
            $errors = ['no_tujuan' => 'Nomor HP harus diawali 08, 62, atau +62 (8-13 digit)'];
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Validasi gagal', 422, null, $errors);
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        // 🔥 Ambil user_id
        $userId = $this->isApi() 
            ? ($this->request->user->user_id ?? null) 
            : session()->get('user_id');

        if (!$userId && $this->isApi()) {
            return $this->apiResponse(false, 'Unauthorized - User tidak ditemukan', 401);
        }

        $data['created_by'] = $userId;

        // 🔥 Proses simpan
        $result = $this->laporanPulsaService->createData($data);

        if ($this->isApi()) {
            return $this->apiResponse($result['success'], $result['message'], $result['success'] ? 201 : 500);
        }

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }

        return redirect()->to('/laporan-pulsa')->with('success', $result['message']);
    }

    // ================================================================
    // EDIT (GET Form)
    // ================================================================
    public function edit($id)
    {
        $result = $this->laporanPulsaService->getById($id);

        if (!$result['success']) {
            return redirect()->to('/laporan-pulsa')->with('error', $result['message']);
        }

        $providers = $this->laporanPulsaService->getProviders();
        $nominals  = $this->laporanPulsaService->getNominals();

        return view('laporan_pulsa/edit', [
            'title'       => 'Edit Transaksi Pulsa',
            'page'        => 'laporan-pulsa',
            'transaction' => $result['data'],
            'providers'   => $providers['success'] ? $providers['data'] : [],
            'nominals'    => $nominals['success'] ? $nominals['data'] : [],
        ]);
    }

    // ================================================================
    // UPDATE (PUT)
    // ================================================================
    public function update($id)
    {
        $data = $this->getRequestData();
        
        if (isset($data['_method'])) {
            unset($data['_method']);
        }

        // 🔥 VALIDASI
        $rules = [
            'no_tujuan' => [
                'rules' => 'required|numeric|min_length[10]|max_length[15]',
                'errors' => [
                    'required' => 'Nomor tujuan harus diisi',
                    'numeric' => 'Nomor tujuan harus berupa angka',
                    'min_length' => 'Nomor tujuan minimal 10 digit',
                    'max_length' => 'Nomor tujuan maksimal 15 digit',
                ]
            ],
            'provider_id' => [
                'rules' => 'required|integer|is_not_unique[tbl_provider_pulsa.id]',
                'errors' => [
                    'required' => 'Provider harus dipilih',
                    'integer' => 'Provider tidak valid',
                    'is_not_unique' => 'Provider tidak ditemukan',
                ]
            ],
            'nominal_id' => [
                'rules' => 'required|integer|is_not_unique[tbl_nominal_pulsa.id]',
                'errors' => [
                    'required' => 'Nominal harus dipilih',
                    'integer' => 'Nominal tidak valid',
                    'is_not_unique' => 'Nominal tidak ditemukan',
                ]
            ],
            'metode_pembayaran' => [
                'rules' => 'required|in_list[tunai,saldo,transfer,grip]',
                'errors' => [
                    'required' => 'Metode pembayaran harus diisi',
                    'in_list' => 'Metode pembayaran harus tunai, saldo, transfer, atau grip',
                ]
            ],
            'status' => [
                'rules' => 'required|in_list[proses,sukses,gagal]',
                'errors' => [
                    'required' => 'Status harus diisi',
                    'in_list' => 'Status harus proses, sukses, atau gagal',
                ]
            ]
        ];

        if (!$this->validateData($data, $rules)) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Validasi gagal', 422, null, $this->validator->getErrors());
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 🔥 VALIDASI NO HP (AWALAN 08, 62, +62)
        if (!$this->validatePhoneNumber($data['no_tujuan'])) {
            $errors = ['no_tujuan' => 'Nomor HP harus diawali 08, 62, atau +62 (8-13 digit)'];
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Validasi gagal', 422, null, $errors);
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        // 🔥 Proses update
        $result = $this->laporanPulsaService->updateData($id, $data);

        if ($this->isApi()) {
            return $this->apiResponse($result['success'], $result['message'], $result['success'] ? 200 : 500);
        }

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }

        return redirect()->to('/laporan-pulsa')->with('success', $result['message']);
    }

    // ================================================================
    // DELETE
    // ================================================================
    public function delete($id)
    {
        $result = $this->laporanPulsaService->deleteData($id);

        if ($this->isApi() || $this->request->isAJAX()) {
            return $this->response
                ->setStatusCode($result['code'] ?? 500)
                ->setJSON($result);
        }

        return redirect()
            ->to('/laporan-pulsa')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    // ================================================================
    // DESTROY (alias delete)
    // ================================================================
    public function destroy($id)
    {
        return $this->delete($id);
    }

    // ================================================================
    // GET PROVIDERS
    // ================================================================
    public function getProviders()
    {
        $result = $this->laporanPulsaService->getProviders();

        if ($this->isApi()) {
            return $this->apiResponse(
                $result['success'], 
                $result['success'] ? 'Data provider berhasil diambil' : 'Gagal mengambil data provider', 
                $result['success'] ? 200 : 500, 
                $result['success'] ? $result['data'] : null
            );
        }

        return $this->response->setJSON($result);
    }

    // ================================================================
    // GET NOMINALS BY PROVIDER
    // ================================================================
    public function getNominals($providerId)
    {
        $nominalModel = new \App\Models\NominalModel();
        $data = $nominalModel
            ->where('provider_id', $providerId)
            ->where('status', 'active')
            ->orderBy('nominal', 'ASC')
            ->findAll();

        if ($this->isApi()) {
            return $this->apiResponse(true, 'Data nominal berhasil diambil', 200, $data);
        }

        return $this->response->setJSON(['success' => true, 'data' => $data]);
    }

    // ================================================================
    // EXPORT EXCEL
    // ================================================================
    public function exportExcel()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');

        $dataResult   = $this->laporanPulsaService->getData($startDate, $endDate);
        $summaryResult = $this->laporanPulsaService->getSummaryReport($startDate, $endDate);

        if (!$dataResult['success'] || !$summaryResult['success']) {
            return redirect()->back()->with('error', 'Gagal mengambil data untuk export');
        }

        $excelService = new \App\Services\ExcelExportService();
        return $excelService->exportLaporanPulsa(
            $dataResult['data'], 
            $summaryResult['data'], 
            $startDate, 
            $endDate
        );
    }

    // ================================================================
    // EXPORT PDF
    // ================================================================
    public function exportPDF()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');

        $dataResult   = $this->laporanPulsaService->getData($startDate, $endDate);
        $summaryResult = $this->laporanPulsaService->getSummaryReport($startDate, $endDate);

        if (!$dataResult['success'] || !$summaryResult['success']) {
            return redirect()->back()->with('error', 'Gagal mengambil data untuk export');
        }

        $pdfService = new \App\Services\PDFExportService();
        return $pdfService->exportLaporanPulsa(
            $dataResult['data'], 
            $summaryResult['data'], 
            $startDate, 
            $endDate
        );
    }
}