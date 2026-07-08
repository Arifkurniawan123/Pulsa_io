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

        // Untuk PUT dari API (form-urlencoded)
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

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
        ];

        $providers = $this->providerService->getAll($filters);
        $stats     = $this->providerService->getStats();

        if ($this->isApi()) {
            return $this->apiResponse(true, 'Data provider berhasil diambil', 200, [
                'providers' => $providers,
                'stats'     => $stats,
            ]);
        }

        return view('provider/index', [
            'title'     => 'Data Provider',
            'providers' => $providers,
            'stats'     => $stats,
            'page'      => 'provider',
        ]);
    }

    public function getAllowed()
    {
        $allowedKode   = ['TSEL', 'XL', 'ISAT', 'TRI', 'SMART', 'AXIS', 'BYU'];
        $providerModel = new \App\Models\ProviderModel();
        $data = $providerModel
            ->whereIn('kode_provider', $allowedKode)
            ->where('status', 'active')
            ->orderBy('nama_provider', 'ASC')
            ->findAll();

        return $this->apiResponse(true, 'Data provider berhasil diambil', 200, $data);
    }

    public function create()
    {
        return view('provider/create', [
            'title' => 'Tambah Provider',
            'page'  => 'provider',
        ]);
    }

    public function store()
    {
        $data = $this->getRequestData();
        unset($data['_method']);

        $errors = $this->providerService->validateCreate($data);

        if (!empty($errors)) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Validasi gagal', 422, null, $errors);
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        try {
            $this->providerService->create($data);

            if ($this->isApi()) {
                return $this->apiResponse(true, 'Provider berhasil ditambahkan', 201);
            }
            return redirect()->to('/provider')->with('success', 'Provider berhasil ditambahkan');
        } catch (\Exception $e) {
            if ($this->isApi()) {
                return $this->apiResponse(false, $e->getMessage(), 500);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $provider = $this->providerService->find($id);

        if (!$provider) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Provider tidak ditemukan', 404);
            }
            return redirect()->to('/provider')->with('error', 'Provider tidak ditemukan');
        }

        if ($this->isApi()) {
            return $this->apiResponse(true, 'Data provider ditemukan', 200, $provider);
        }

        return view('provider/edit', [
            'title'    => 'Edit Provider',
            'provider' => $provider,
            'page'     => 'provider',
        ]);
    }

    public function edit($id)
    {
        return $this->show($id);
    }

    public function update($id)
    {
        $provider = $this->providerService->find($id);

        if (!$provider) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Provider tidak ditemukan', 404);
            }
            return redirect()->to('/provider')->with('error', 'Provider tidak ditemukan');
        }

        $data = $this->getRequestData();
        unset($data['_method']);

        $errors = $this->providerService->validateUpdate($data, $id);

        if (!empty($errors)) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Validasi gagal', 422, null, $errors);
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        try {
            $this->providerService->update($id, $data);

            if ($this->isApi()) {
                return $this->apiResponse(true, 'Provider berhasil diupdate', 200);
            }
            return redirect()->to('/provider')->with('success', 'Provider berhasil diupdate');
        } catch (\Exception $e) {
            if ($this->isApi()) {
                return $this->apiResponse(false, $e->getMessage(), 500);
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

        return redirect()->to('/provider')->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function destroy($id)
    {
        return $this->delete($id);
    }
}