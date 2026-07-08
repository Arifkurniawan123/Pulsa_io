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

    private function getRequestData(): array
    {
        $contentType = $this->request->getHeaderLine('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            $json = $this->request->getJSON(true);
            if (is_array($json) && !empty($json)) {
                return $json;
            }
        }

        $post = $this->request->getPost();
        if (is_array($post) && !empty($post)) {
            return $post;
        }

        $raw = $this->request->getBody();
        if (!empty($raw) && !str_contains($contentType, 'application/json')) {
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

    // ================================================================
    // GET ALL + FILTER (HANYA DATA NOMINAL)
    // ================================================================
    public function index()
    {
        $filters = [
            'search'      => $this->request->getGet('search'),
            'provider_id' => $this->request->getGet('provider_id'),
            'status'      => $this->request->getGet('status'),
        ];

        // 🔥 AMBIL DATA NOMINAL SAJA
        $nominals = $this->nominalService->getAll($filters);

        if ($this->isApi()) {
            return $this->apiResponse(true, 'Data nominal berhasil diambil', 200, $nominals);
        }

        // 🔥 UNTUK WEB TETAP PAKAI PROVIDERS & STATS (KARENA BUTUH DROPDOWN)
        $providers = $this->nominalService->getActiveProviders();
        $stats     = $this->nominalService->getStats();

        return view('nominal/index', [
            'title'     => 'Data Nominal Pulsa',
            'nominals'  => $nominals,
            'providers' => $providers,
            'stats'     => $stats,
            'page'      => 'nominal',
        ]);
    }

    // ================================================================
    // GET BY PROVIDER (TETAP ADA UNTUK DROPDOWN)
    // ================================================================
    public function getByProvider($providerId)
    {
        $nominalModel = new \App\Models\NominalModel();
        $data = $nominalModel
            ->where('provider_id', $providerId)
            ->where('status', 'active')
            ->orderBy('nominal', 'ASC')
            ->findAll();

        return $this->apiResponse(true, 'Data nominal berhasil diambil', 200, $data);
    }

    // ================================================================
    // GET BY ID
    // ================================================================
    public function show($id)
    {
        $nominal = $this->nominalService->find($id);

        if ($this->isApi()) {
            if (!$nominal) {
                return $this->apiResponse(false, 'Data nominal tidak ditemukan', 404);
            }
            return $this->apiResponse(true, 'Data nominal ditemukan', 200, $nominal);
        }

        return $this->edit($id);
    }

    // ================================================================
    // CREATE (GET Form)
    // ================================================================
    public function create()
    {
        return view('nominal/create', [
            'title'     => 'Tambah Nominal Pulsa',
            'providers' => $this->nominalService->getActiveProviders(),
            'page'      => 'nominal',
        ]);
    }

    // ================================================================
    // STORE (POST)
    // ================================================================
    public function store()
    {
        $data = $this->getRequestData();
        unset($data['_method']);

        $errors = $this->nominalService->validateCreate($data);

        if (!empty($errors)) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Validasi gagal', 422, null, $errors);
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        try {
            $this->nominalService->create($data);

            if ($this->isApi()) {
                return $this->apiResponse(true, 'Data nominal berhasil ditambahkan', 201);
            }
            return redirect()->to('/nominal')->with('success', 'Data nominal berhasil ditambahkan');
        } catch (\Exception $e) {
            if ($this->isApi()) {
                return $this->apiResponse(false, $e->getMessage(), 500);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ================================================================
    // EDIT (GET Form)
    // ================================================================
    public function edit($id)
    {
        $nominal = $this->nominalService->find($id);

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

    // ================================================================
    // UPDATE (PUT)
    // ================================================================
    public function update($id)
    {
        $nominal = $this->nominalService->findBasic($id);

        if (!$nominal) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Data nominal tidak ditemukan', 404);
            }
            return redirect()->to('/nominal')->with('error', 'Data nominal tidak ditemukan');
        }

        $data = $this->getRequestData();
        unset($data['_method']);

        $errors = $this->nominalService->validateUpdate($data, $id);

        if (!empty($errors)) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Validasi gagal', 422, null, $errors);
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        try {
            $this->nominalService->update($id, $data);

            if ($this->isApi()) {
                return $this->apiResponse(true, 'Data nominal berhasil diupdate', 200);
            }
            return redirect()->to('/nominal')->with('success', 'Data nominal berhasil diupdate');
        } catch (\Exception $e) {
            if ($this->isApi()) {
                return $this->apiResponse(false, $e->getMessage(), 500);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ================================================================
    // DELETE
    // ================================================================
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

    public function destroy($id)
    {
        return $this->delete($id);
    }
}