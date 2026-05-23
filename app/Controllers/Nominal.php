<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\NominalService;

class Nominal extends BaseController
{
    protected $nominalService;

    public function __construct()
    {
        $this->nominalService = new NominalService();
    }

    private function isApi(): bool
    {
        return str_contains($this->request->getUri()->getPath(), 'api/');
    }

    public function index()
    {
        $filters = [
            'search'      => $this->request->getGet('search'),
            'provider_id' => $this->request->getGet('provider_id'),
            'status'      => $this->request->getGet('status'),
        ];

        $nominals  = $this->nominalService->getAll($filters);
        $providers = $this->nominalService->getActiveProviders();
        $stats     = $this->nominalService->getStats();

        if ($this->isApi()) {
            return $this->response->setStatusCode(200)->setJSON([
                'success'   => true,
                'message'   => 'Data nominal berhasil diambil',
                'code'      => 200,
                'data'      => $nominals,
                'providers' => $providers,
                'stats'     => $stats,
            ]);
        }

        return view('nominal/index', [
            'title'     => 'Data Nominal Pulsa',
            'nominals'  => $nominals,
            'providers' => $providers,
            'stats'     => $stats,
            'page'      => 'nominal',
        ]);
    }

    // ========== TAMBAHAN UNTUK FLUTTER ==========
    /**
     * Get nominal by provider_id
     * Endpoint: GET /api/nominal/provider/{provider_id}
     */
    public function getByProvider($providerId)
    {
        $nominalModel = new \App\Models\NominalModel();
        $data = $nominalModel
            ->where('provider_id', $providerId)
            ->where('status', 'active')
            ->orderBy('nominal', 'ASC')
            ->findAll();

        if (empty($data)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data nominal tidak ditemukan',
                'data'    => []
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Data nominal berhasil diambil',
            'data'    => $data
        ]);
    }
    // ==========================================

    public function create()
    {
        return view('nominal/create', [
            'title'     => 'Tambah Nominal Pulsa',
            'providers' => $this->nominalService->getActiveProviders(),
            'page'      => 'nominal',
        ]);
    }

    public function store()
    {
        $data   = $this->request->getPost();
        $errors = $this->nominalService->validateCreate($data);

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
            $this->nominalService->create($data);

            if ($this->isApi()) {
                return $this->response->setStatusCode(201)->setJSON([
                    'success' => true,
                    'message' => 'Data nominal berhasil ditambahkan',
                    'code'    => 201,
                ]);
            }
            return redirect()->to('/nominal')->with('success', 'Data nominal berhasil ditambahkan');
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
        $nominal = $this->nominalService->find($id);

        if ($this->isApi()) {
            if (!$nominal) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Data nominal tidak ditemukan',
                    'code'    => 404,
                ]);
            }
            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => 'Data nominal ditemukan',
                'code'    => 200,
                'data'    => $nominal,
            ]);
        }

        if (!$nominal) {
            return redirect()->to('/nominal')->with('error', 'Data nominal tidak ditemukan');
        }

        return view('nominal/edit', [
            'title'     => 'Edit Nominal Pulsa',
            'nominal'   => $nominal,
            'providers' => $this->nominalService->getActiveProviders(),
            'page'      => 'nominal',
        ]);
    }

    public function update($id)
    {
        $nominal = $this->nominalService->findBasic($id);

        if (!$nominal) {
            if ($this->isApi()) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Data nominal tidak ditemukan',
                    'code'    => 404,
                ]);
            }
            return redirect()->to('/nominal')->with('error', 'Data nominal tidak ditemukan');
        }

        $data   = $this->request->getPost();
        $errors = $this->nominalService->validateUpdate($data, $id);

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
            $this->nominalService->update($id, $data);

            if ($this->isApi()) {
                return $this->response->setStatusCode(200)->setJSON([
                    'success' => true,
                    'message' => 'Data nominal berhasil diupdate',
                    'code'    => 200,
                ]);
            }
            return redirect()->to('/nominal')->with('success', 'Data nominal berhasil diupdate');
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
        $result = $this->nominalService->deleteData($id);

        if ($this->isApi() || $this->request->isAJAX()) {
            return $this->response
                ->setStatusCode($result['code'] ?? 500)
                ->setJSON($result);
        }

        return redirect()
            ->to('/nominal')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}