<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\Kategori as ServicesKategori;
use App\Validation\Kategori as ValidationKategori;
use Config\Services;

class Kategori extends BaseController
{
    protected $kategoriService;
    protected $ruleValidation;

    public function __construct()
    {
        $this->kategoriService = new ServicesKategori();
        $this->ruleValidation  = new ValidationKategori();
    }

    private function isApi(): bool
    {
        return str_contains($this->request->getUri()->getPath(), 'api/');
    }

    public function index()
    {
        $dataKategori = $this->kategoriService->getData();
        $kategori     = $dataKategori['success'] ? $dataKategori['data'] : [];

        if ($this->isApi()) {
            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => 'Data kategori berhasil diambil',
                'code'    => 200,
                'data'    => $kategori,
            ]);
        }

        return view('kategori/index', [
            'page'       => 'kategori',
            'title'      => 'Pulsa Io - Kategori',
            'table_name' => 'Data Kategori',
            'kategori'   => $kategori,
        ]);
    }

    public function create()
    {
        return view('kategori/create', [
            'page'      => 'kategori',
            'title'     => 'Pulsa Io - Kategori',
            'form_name' => 'Form tambah data kategori',
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

        $data   = ['kategori' => $this->request->getPost('kategori')];
        $result = $this->kategoriService->createData($data);

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
        return redirect()->to('/master-data/kategori')->with('success', $result['message']);
    }

    public function edit($id)
    {
        $result = $this->kategoriService->getById($id);

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
            return redirect()->to('/master-data/kategori')->with('error', $result['message']);
        }

        return view('kategori/edit', [
            'page'      => 'kategori',
            'title'     => 'Pulsa Io - Kategori',
            'form_name' => 'Form edit data kategori',
            'kategori'  => $result['data'],
        ]);
    }

    public function update($id)
    {
        $rules = $this->ruleValidation->ruleUpdate($id);

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

        $data   = ['kategori' => $this->request->getPost('kategori')];
        $result = $this->kategoriService->updateData($id, $data);

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
        return redirect()->to('/master-data/kategori')->with('success', $result['message']);
    }

    public function destroy($id)
    {
        $result = $this->kategoriService->deleteData($id);

        return $this->response
            ->setStatusCode($result['code'])
            ->setJSON($result);
    }
}