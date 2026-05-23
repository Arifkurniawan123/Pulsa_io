<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\Satuan as ServicesSatuan;
use App\Validation\Satuan as ValidationSatuan;
use Config\Services;

class Satuan extends BaseController
{
    protected $satuanService;
    protected $ruleValidation;

    public function __construct()
    {
        $this->satuanService  = new ServicesSatuan();
        $this->ruleValidation = new ValidationSatuan();
    }

    private function isApi(): bool
    {
        return str_contains($this->request->getUri()->getPath(), 'api/');
    }

    public function index()
    {
        $dataSatuan = $this->satuanService->getData();
        $satuan     = $dataSatuan['success'] ? $dataSatuan['data'] : [];

        if ($this->isApi()) {
            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => 'Data satuan berhasil diambil',
                'code'    => 200,
                'data'    => $satuan,
            ]);
        }

        return view('satuan/index', [
            'page'       => 'satuan',
            'title'      => 'Pulsa Io - Satuan',
            'table_name' => 'Data Satuan',
            'satuan'     => $satuan,
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

        $data   = ['satuan' => $this->request->getPost('satuan')];
        $result = $this->satuanService->createData($data);

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
        return redirect()->to('/master-data/satuan')->with('success', $result['message']);
    }

    public function edit($id)
    {
        $result = $this->satuanService->getById($id);

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

        $data   = ['satuan' => $this->request->getPost('satuan')];
        $result = $this->satuanService->updateData($id, $data);

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
        return redirect()->to('/master-data/satuan')->with('success', $result['message']);
    }

    public function destroy($id)
    {
        $result = $this->satuanService->deleteData($id);

        return $this->response
            ->setStatusCode($result['code'])
            ->setJSON($result);
    }
}