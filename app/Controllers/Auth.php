<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\Auth as AuthService;

class Auth extends BaseController
{
    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
        helper('jwt');
    }

    public function index()
    {
        $data = [
            'title' => 'Pulsa Io - Login',
        ];
        return view('login', $data);
    }

    public function attemptLogin()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (empty($username)) {
            $json = $this->request->getJSON(true) ?? [];
            $username = $json['username'] ?? null;
            $password = $json['password'] ?? null;
        }

        $isApi = str_contains($this->request->getUri()->getPath(), 'api/');
        $result = $this->authService->login($username, $password);

        if ($isApi) {
            if (!$result['success']) {
                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => $result['message'],
                    'code'    => 401,
                ]);
            }

            $userId = session()->get('user_id');
            $roleId = session()->get('role_id');
            $name = session()->get('name');
            $image = session()->get('image');
            $uname = session()->get('username');

            $token = generate_jwt([
                'user_id'  => $userId,
                'role_id'  => $roleId,
                'name'     => $name,
                'username' => $uname,
            ]);

            $db = \Config\Database::connect();
            $db->table('tbl_jwt_tokens')->insert([
                'user_id'     => $userId,
                'token'       => $token,
                'device_info' => $this->request->getHeaderLine('User-Agent'),
                'fcm_token'   => $this->request->getPost('fcm_token') ?? null,
                'is_active'   => 1,
                'expired_at'  => date('Y-m-d H:i:s', time() + JWT_EXPIRE),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => $result['message'],
                'code'    => 200,
                'data'    => [
                    'token' => $token,
                    'user'  => [
                        'id'       => $userId,
                        'name'     => $name,
                        'username' => $uname,
                        'role_id'  => $roleId,
                        'image'    => $image,
                    ],
                ],
            ]);
        }

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }

        if (session()->get('role_id') != 2) {
            return redirect()->to('/menu/kasir')->with('success', $result['message']);
        }

        return redirect()->to('/dashboard')->with('success', $result['message']);
    }

    public function attemptLogout()
    {
        $isApi = str_contains($this->request->getUri()->getPath(), 'api/');

        if ($isApi) {
            $token = get_jwt_from_header();
            if ($token) {
                $db = \Config\Database::connect();
                $db->table('tbl_jwt_tokens')->where('token', $token)->update(['is_active' => 0]);
            }
            $this->authService->logout();
            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => 'Berhasil logout.',
                'code'    => 200,
            ]);
        }

        $this->authService->logout();
        return redirect()->to('/login');
    }
}