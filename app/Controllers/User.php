<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\User as ServicesUser;
use App\Validation\User as ValidationUser;

class User extends BaseController
{
    protected $userService;
    protected $ruleValidation;

    public function __construct()
    {
        $this->userService    = new ServicesUser();
        $this->ruleValidation = new ValidationUser();
    }

    private function isApi(): bool
    {
        return str_contains($this->request->getUri()->getPath(), 'api/');
    }

    public function index()
    {
        $dataUser = $this->userService->getData();
        $user     = $dataUser['success'] ? $dataUser['data'] : [];

        if ($this->isApi()) {
            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => 'Data user berhasil diambil',
                'code'    => 200,
                'data'    => $user,
            ]);
        }

        return view('user/index', [
            'page'       => 'user',
            'title'      => 'Pulsa Io - User',
            'table_name' => 'Data User',
            'user'       => $user,
        ]);
    }

    public function create()
    {
        $dataRole = $this->userService->getDataRole();
        $role     = $dataRole['success'] ? $dataRole['data'] : [];

        return view('user/create', [
            'page'      => 'user',
            'title'     => 'Pulsa Io - User',
            'form_name' => 'Form tambah data User',
            'roles'     => $role,
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
            return redirect()->back()->withInput()->with('validation', $this->validator->getErrors());
        }

        $imageName = 'default-profile.png';
        $dataImage = $this->request->getFile('image');
        if (!empty($dataImage) && $dataImage->isValid()) {
            $imageName = $dataImage->getRandomName();
            $dataImage->move(FCPATH . 'assets/img/user/', $imageName);
        }

        $data = [
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'role'         => $this->request->getPost('role'),
            'username'     => $this->request->getPost('username'),
            'password'     => $this->request->getPost('password'),
            'email'        => $this->request->getPost('email'),
            'no_tlp'       => $this->request->getPost('no_tlp'),
            'alamat'       => $this->request->getPost('alamat'),
            'image'        => $imageName,
        ];

        $result = $this->userService->createData($data);

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
        return redirect()->to('/setting/user')->with('success', $result['message']);
    }

    public function edit($id)
    {
        $result = $this->userService->getById($id);

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
            return redirect()->to('/setting/user')->with('error', $result['message']);
        }

        $dataRole = $this->userService->getDataRole();
        $role     = $dataRole['success'] ? $dataRole['data'] : [];

        return view('user/edit', [
            'page'      => 'user',
            'title'     => 'Pulsa Io - User',
            'form_name' => 'Form edit data User',
            'roles'     => $role,
            'user'      => $result['data'],
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
            return redirect()->back()->withInput()->with('validation', $this->validator->getErrors());
        }

        $imageName = 'default-profile.png';
        $dataImage = $this->request->getFile('image');
        if (!empty($dataImage) && $dataImage->isValid()) {
            $imageName = $dataImage->getRandomName();
            $dataImage->move(FCPATH . 'assets/img/user/', $imageName);
        }

        $data = [
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'role'         => $this->request->getPost('role'),
            'username'     => $this->request->getPost('username'),
            'password'     => $this->request->getPost('password'),
            'email'        => $this->request->getPost('email'),
            'no_tlp'       => $this->request->getPost('no_tlp'),
            'alamat'       => $this->request->getPost('alamat'),
            'old_img'      => $this->request->getPost('old-img'),
            'image'        => $imageName,
        ];

        $result = $this->userService->updateData($id, $data);

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
        return redirect()->to('/setting/user')->with('success', $result['message']);
    }

    public function destroy($id)
    {
        $result = $this->userService->deleteData($id);

        return $this->response
            ->setStatusCode($result['code'])
            ->setJSON($result);
    }
}