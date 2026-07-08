<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\Produk as ProdukService;

class Produk extends BaseController
{
    protected $produkService;

    public function __construct()
    {
        $this->produkService = new ProdukService();
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

        $result = $this->produkService->getData($search, $limit, $offset);

        if ($this->isApi()) {
            return $this->apiResponse(true, 'Data produk berhasil diambil', 200, [
                'data'  => $result['data'],
                'total' => $result['total'] ?? count($result['data']),
                'page'  => (int) $page,
                'limit' => (int) $limit,
            ]);
        }

        return view('produk/index', [
            'page'       => 'produk',
            'title'      => 'Pulsa Io - Produk',
            'table_name' => 'Data Produk',
            'produk'     => $result['data'],
            'search'     => $search,
        ]);
    }

    public function create()
    {
        $kategori = $this->produkService->getDataKategori();
        $satuan   = $this->produkService->getDataSatuan();

        return view('produk/create', [
            'page'      => 'produk',
            'title'     => 'Pulsa Io - Tambah Produk',
            'form_name' => 'Form Tambah Produk',
            'kategori'  => $kategori['data'] ?? [],
            'satuan'    => $satuan['data'] ?? [],
        ]);
    }

    public function store()
    {
        $data = $this->getRequestData();
        unset($data['_method']);

        $rules = [
            'produk'   => 'required',
            'harga'    => 'required|numeric|greater_than[0]',
            'stok'     => 'required|numeric|greater_than[0]',
            'kategori' => 'required',
            'satuan'   => 'required',
        ];

        if (!$this->validateData($data, $rules)) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Validasi gagal', 422, null, $this->validator->getErrors());
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $result = $this->produkService->createData($data);

        if ($this->isApi()) {
            return $this->apiResponse($result['success'], $result['message'], $result['success'] ? 201 : 500);
        }

        return redirect()->to('/master-data/produk')->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function show($id)
    {
        $result = $this->produkService->getById($id);

        if ($this->isApi()) {
            return $this->apiResponse(
                $result['success'],
                $result['message'],
                $result['success'] ? 200 : 404,
                $result['data'] ?? null
            );
        }

        return $this->edit($id);
    }

    public function edit($id)
    {
        $result   = $this->produkService->getById($id);
        $kategori = $this->produkService->getDataKategori();
        $satuan   = $this->produkService->getDataSatuan();

        if (!$result['success']) {
            return redirect()->to('/master-data/produk')->with('error', $result['message']);
        }

        return view('produk/edit', [
            'page'      => 'produk',
            'title'     => 'Pulsa Io - Edit Produk',
            'form_name' => 'Form Edit Produk',
            'produk'    => $result['data'],
            'kategori'  => $kategori['data'] ?? [],
            'satuan'    => $satuan['data'] ?? [],
        ]);
    }

    public function update($id)
    {
        $data = $this->getRequestData();
        unset($data['_method']);

        $rules = [
            'produk'   => 'required',
            'harga'    => 'required|numeric|greater_than[0]',
            'stok'     => 'required|numeric|greater_than[0]',
            'kategori' => 'required',
            'satuan'   => 'required',
        ];

        if (!$this->validateData($data, $rules)) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Validasi gagal', 422, null, $this->validator->getErrors());
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $result = $this->produkService->updateData($id, $data);

        if ($this->isApi()) {
            return $this->apiResponse($result['success'], $result['message'], $result['success'] ? 200 : 500);
        }

        return redirect()->to('/master-data/produk')->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function destroy($id)
    {
        $result = $this->produkService->deleteData($id);

        if ($this->isApi() || $this->request->isAJAX()) {
            return $this->response
                ->setStatusCode($result['code'] ?? 500)
                ->setJSON($result);
        }

        return redirect()->to('/master-data/produk')->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function delete($id)
    {
        return $this->destroy($id);
    }
}