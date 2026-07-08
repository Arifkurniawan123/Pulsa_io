<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\Satuan as SatuanService;

class Satuan extends BaseController
{
    protected $satuanService;

    public function __construct()
    {
        $this->satuanService = new SatuanService();
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
    // INDEX - SUPPORT SEARCH
    // ================================================================
    public function index()
    {
        $search = $this->request->getGet('search');
        $limit  = $this->request->getGet('limit') ?? 100;
        $page   = $this->request->getGet('page') ?? 1;
        $offset = ($page - 1) * $limit;

        $result = $this->satuanService->getData($search, $limit, $offset);

        if ($this->isApi()) {
            return $this->apiResponse(true, 'Data satuan berhasil diambil', 200, [
                'data'  => $result['data'],
                'total' => $result['total'] ?? count($result['data']),
                'page'  => (int) $page,
                'limit' => (int) $limit,
            ]);
        }

        return view('satuan/index', [
            'page'       => 'satuan',
            'title'      => 'Pulsa Io - Satuan',
            'table_name' => 'Data Satuan',
            'satuan'     => $result['data'],
            'search'     => $search,
        ]);
    }

    public function create()
    {
        return view('satuan/create', [
            'page'      => 'satuan',
            'title'     => 'Pulsa Io - Satuan',
            'form_name' => 'Form tambah data satuan',
        ]);
    }

    public function store()
    {
        $data = $this->getRequestData();
        unset($data['_method']);

        if (empty($data['satuan'])) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Nama satuan harus diisi', 422);
            }
            return redirect()->back()->withInput()->with('error', 'Nama satuan harus diisi');
        }

        $result = $this->satuanService->createData($data);

        if ($this->isApi()) {
            return $this->apiResponse($result['success'], $result['message'], $result['success'] ? 201 : 500);
        }

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }
        return redirect()->to('/master-data/satuan')->with('success', $result['message']);
    }

    public function show($id)
    {
        $result = $this->satuanService->getById($id);

        if ($this->isApi()) {
            if (!$result['success']) {
                return $this->apiResponse(false, $result['message'], 404);
            }
            return $this->apiResponse(true, 'Data ditemukan', 200, $result['data']);
        }

        return $this->edit($id);
    }

    public function edit($id)
    {
        $result = $this->satuanService->getById($id);

        if (!$result['success']) {
            return redirect()->to('/master-data/satuan')->with('error', $result['message']);
        }

        return view('satuan/edit', [
            'page'      => 'satuan',
            'title'     => 'Pulsa Io - Satuan',
            'form_name' => 'Form edit data satuan',
            'satuan'    => $result['data'],
        ]);
    }

    public function update($id)
    {
        $data = $this->getRequestData();
        unset($data['_method']);

        if (empty($data['satuan'])) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Nama satuan harus diisi', 422);
            }
            return redirect()->back()->withInput()->with('error', 'Nama satuan harus diisi');
        }

        $result = $this->satuanService->updateData($id, $data);

        if ($this->isApi()) {
            return $this->apiResponse($result['success'], $result['message'], $result['success'] ? 200 : 500);
        }

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }
        return redirect()->to('/master-data/satuan')->with('success', $result['message']);
    }

    public function destroy($id)
    {
        $result = $this->satuanService->deleteData($id);
        return $this->response->setStatusCode($result['code'])->setJSON($result);
    }

    public function delete($id)
    {
        return $this->destroy($id);
    }
}