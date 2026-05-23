<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\Produk as ServicesProduk;
use App\Validation\Produk as ValidationProduk;
use Config\Services;

class Produk extends BaseController
{
    protected $produkService;
    protected $ruleValidation;

    public function __construct()
    {
        $this->produkService   = new ServicesProduk();
        $this->ruleValidation  = new ValidationProduk();
    }

    private function isApi(): bool
    {
        return str_contains($this->request->getUri()->getPath(), 'api/');
    }

    public function index()
    {
        $dataProduk = $this->produkService->getData();
        $produk     = $dataProduk['success'] ? $dataProduk['data'] : [];

        if ($this->isApi()) {
            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => 'Data produk berhasil diambil',
                'code'    => 200,
                'data'    => $produk,
            ]);
        }

        return view('produk/index', [
            'page'       => 'produk',
            'title'      => 'Pulsa Io - Produk',
            'table_name' => 'Data Produk',
            'produk'     => $produk,
        ]);
    }

    public function create()
    {
        $dataKategori = $this->produkService->getDataKategori();
        $dataSatuan   = $this->produkService->getDataSatuan();

        return view('produk/create', [
            'page'      => 'produk',
            'title'     => 'Pulsa Io - Tambah Produk',
            'form_name' => 'Form Tambah Produk',
            'kategori'  => $dataKategori['data'] ?? [],
            'satuan'    => $dataSatuan['data'] ?? [],
        ]);
    }

    public function store()
    {
        $rules = $this->ruleValidation->ruleStore();

        if (!$this->validate($rules)) {
            if ($this->isApi()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'code'    => 422,
                    'errors'  => $this->validator->getErrors(),
                ]);
            }
            return redirect()->back()->withInput()->with('validation', Services::validation());
        }

        $data = [
            'produk'   => $this->request->getPost('produk'),
            'harga'    => $this->request->getPost('harga'),
            'stok'     => $this->request->getPost('stok'),
            'kategori' => $this->request->getPost('kategori'),
            'satuan'   => $this->request->getPost('satuan'),
        ];

        $result = $this->produkService->createData($data);

        if ($this->isApi()) {
            return $this->response
                ->setStatusCode($result['success'] ? 201 : 500)
                ->setJSON([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'code'    => $result['success'] ? 201 : 500,
                ]);
        }

        return redirect()
            ->to('/master-data/produk')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function edit($id)
    {
        $result = $this->produkService->getById($id);

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
            return redirect()->to('/master-data/produk')->with('error', $result['message']);
        }

        $dataKategori = $this->produkService->getDataKategori();
        $dataSatuan   = $this->produkService->getDataSatuan();

        return view('produk/edit', [
            'page'      => 'produk',
            'title'     => 'Pulsa Io - Edit Produk',
            'form_name' => 'Form Edit Produk',
            'produk'    => $result['data'],
            'kategori'  => $dataKategori['data'] ?? [],
            'satuan'    => $dataSatuan['data'] ?? [],
        ]);
    }

    public function update($id)
    {
        $rules = $this->ruleValidation->ruleUpdate();

        if (!$this->validate($rules)) {
            if ($this->isApi()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'code'    => 422,
                    'errors'  => $this->validator->getErrors(),
                ]);
            }
            return redirect()->back()->withInput()->with('validation', Services::validation());
        }

        $data = [
            'produk'   => $this->request->getPost('produk'),
            'harga'    => $this->request->getPost('harga'),
            'stok'     => $this->request->getPost('stok'),
            'kategori' => $this->request->getPost('kategori'),
            'satuan'   => $this->request->getPost('satuan'),
        ];

        $result = $this->produkService->updateData($id, $data);

        if ($this->isApi()) {
            return $this->response
                ->setStatusCode($result['success'] ? 200 : 500)
                ->setJSON([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'code'    => $result['success'] ? 200 : 500,
                ]);
        }

        return redirect()
            ->to('/master-data/produk')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function destroy($id)
    {
        $result = $this->produkService->deleteData($id);

        if ($this->isApi() || $this->request->isAJAX()) {
            return $this->response
                ->setStatusCode($result['code'] ?? 500)
                ->setJSON($result);
        }

        return redirect()
            ->to('/master-data/produk')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}