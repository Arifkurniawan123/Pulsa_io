<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\ProviderService;

class Provider extends BaseController
{
    protected $providerService;

    public function __construct()
    {
        $this->providerService = new ProviderService();
    }

    private function isApi(): bool
    {
        return str_contains($this->request->getUri()->getPath(), 'api/');
    }

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
        ];

        $providers = $this->providerService->getAll($filters);
        $stats     = $this->providerService->getStats();

        if ($this->isApi()) {
            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => 'Data provider berhasil diambil',
                'code'    => 200,
                'data'    => $providers,
                'stats'   => $stats,
            ]);
        }

        return view('provider/index', [
            'title'     => 'Data Provider',
            'providers' => $providers,
            'stats'     => $stats,
            'page'      => 'provider',
        ]);
    }

    // ========== TAMBAHAN UNTUK FLUTTER ==========
    /**
     * Get only allowed providers (Telkomsel, XL, Indosat, Tri, Smartfren, Axis, by.U)
     * Endpoint: GET /api/provider/allowed
     */
    public function getAllowed()
    {
        $allowedKode = ['TSEL', 'XL', 'ISAT', 'TRI', 'SMART', 'AXIS', 'BYU'];
        $providerModel = new \App\Models\ProviderModel();
        $data = $providerModel
            ->whereIn('kode_provider', $allowedKode)
            ->where('status', 'active')
            ->orderBy('nama_provider', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Data provider berhasil diambil',
            'data'    => $data
        ]);
    }
    // ==========================================

    public function create()
    {
        return view('provider/create', [
            'title' => 'Tambah Provider',
            'page'  => 'provider',
        ]);
    }

    public function store()
    {
        $data   = $this->request->getPost();
        $errors = $this->providerService->validateCreate($data);

        if (!empty($errors)) {
            if ($this->isApi()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'code'    => 422,
                    'errors'  => $errors,
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        try {
            $this->providerService->create($data);

            if ($this->isApi()) {
                return $this->response->setStatusCode(201)->setJSON([
                    'success' => true,
                    'message' => 'Provider berhasil ditambahkan',
                    'code'    => 201,
                ]);
            }
            return redirect()->to('/provider')->with('success', 'Provider berhasil ditambahkan');
        } catch (\Exception $e) {
            if ($this->isApi()) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'code'    => 500,
                ]);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $provider = $this->providerService->find($id);

        if ($this->isApi()) {
            if (!$provider) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Provider tidak ditemukan',
                    'code'    => 404,
                ]);
            }
            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => 'Data provider ditemukan',
                'code'    => 200,
                'data'    => $provider,
            ]);
        }

        if (!$provider) {
            return redirect()->to('/provider')->with('error', 'Provider tidak ditemukan');
        }

        return view('provider/edit', [
            'title'    => 'Edit Provider',
            'provider' => $provider,
            'page'     => 'provider',
        ]);
    }

    public function update($id)
    {
        $provider = $this->providerService->find($id);

        if (!$provider) {
            if ($this->isApi()) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Provider tidak ditemukan',
                    'code'    => 404,
                ]);
            }
            return redirect()->to('/provider')->with('error', 'Provider tidak ditemukan');
        }

        $data   = $this->request->getPost();
        $errors = $this->providerService->validateUpdate($data, $id);

        if (!empty($errors)) {
            if ($this->isApi()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'code'    => 422,
                    'errors'  => $errors,
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        try {
            $this->providerService->update($id, $data);

            if ($this->isApi()) {
                return $this->response->setStatusCode(200)->setJSON([
                    'success' => true,
                    'message' => 'Provider berhasil diupdate',
                    'code'    => 200,
                ]);
            }
            return redirect()->to('/provider')->with('success', 'Provider berhasil diupdate');
        } catch (\Exception $e) {
            if ($this->isApi()) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'code'    => 500,
                ]);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function delete($id)
    {
        $result = $this->providerService->deleteData($id);

        if ($this->isApi() || $this->request->isAJAX()) {
            return $this->response
                ->setStatusCode($result['code'] ?? 500)
                ->setJSON($result);
        }

        return redirect()
            ->to('/provider')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}