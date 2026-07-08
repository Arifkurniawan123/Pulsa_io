<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\User as UserService;

class User extends BaseController
{
    protected $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    private function isApi(): bool
    {
        return str_contains($this->request->getUri()->getPath(), 'api/');
    }

    /**
     * Ambil data dari request (support JSON, POST, Form-encode)
     */
    private function getRequestData(): array
    {
        $contentType = $this->request->getHeaderLine('Content-Type');
        
        if (strpos($contentType, 'application/json') !== false) {
            $jsonData = $this->request->getJSON(true);
            if (is_array($jsonData) && !empty($jsonData)) {
                return $jsonData;
            }
        }

        $postData = $this->request->getPost();
        if (is_array($postData) && !empty($postData)) {
            return $postData;
        }

        $rawBody = $this->request->getBody();
        if (!empty($rawBody)) {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
            parse_str($rawBody, $parsed);
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
    // GET ALL
    // ================================================================
    public function index()
    {
        $result = $this->userService->getData();

        if ($this->isApi()) {
            return $this->apiResponse(true, 'Data user berhasil diambil', 200, $result['data']);
        }

        return view('user/index', [
            'page' => 'user',
            'title' => 'Pulsa Io - User',
            'table_name' => 'Data User',
            'user' => $result['data'],
        ]);
    }

    // ================================================================
    // CREATE (GET Form)
    // ================================================================
    public function create()
    {
        $roles = $this->userService->getDataRole();

        return view('user/create', [
            'page' => 'user',
            'title' => 'Pulsa Io - User',
            'form_name' => 'Form tambah data User',
            'roles' => $roles['data'],
        ]);
    }

    // ================================================================
    // STORE (POST)
    // ================================================================
    public function store()
    {
        $data = $this->getRequestData();
        
        if (isset($data['_method'])) {
            unset($data['_method']);
        }

        // 🔥 VALIDASI
        $rules = [
            'nama_lengkap' => [
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Nama lengkap wajib diisi',
                    'min_length' => 'Nama lengkap minimal 3 karakter',
                ]
            ],
            'role' => [
                'rules' => 'required|in_list[2,3]',
                'errors' => [
                    'required' => 'Role wajib dipilih',
                    'in_list' => 'Role harus Admin atau Kasir',
                ]
            ],
            'username' => [
                'rules' => 'required|min_length[6]|is_unique[tbl_user.username]',
                'errors' => [
                    'required' => 'Username wajib diisi',
                    'min_length' => 'Username minimal 6 karakter',
                    'is_unique' => 'Username sudah digunakan',
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[6]',
                'errors' => [
                    'required' => 'Password wajib diisi',
                    'min_length' => 'Password minimal 6 karakter',
                ]
            ],
            'email' => [
                'rules' => 'permit_empty|valid_email|is_unique[tbl_user.email]',
                'errors' => [
                    'valid_email' => 'Format email tidak valid',
                    'is_unique' => 'Email sudah digunakan',
                ]
            ],
            'no_tlp' => [
                'rules' => 'permit_empty|regex_match[/^(\+62|62|08)[0-9]{8,13}$/]',
                'errors' => [
                    'regex_match' => 'Format nomor telepon tidak valid. Gunakan 08xxxx atau 62xxxx atau +62xxxx (8-13 digit)',
                ]
            ],
            'alamat' => [
                'rules' => 'permit_empty|min_length[10]',
                'errors' => [
                    'min_length' => 'Alamat minimal 10 karakter',
                ]
            ],
            'image' => [
                'rules' => 'permit_empty|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png]|max_size[image,1024]',
                'errors' => [
                    'is_image' => 'File yang diunggah bukan gambar',
                    'mime_in' => 'Format gambar hanya boleh JPG, JPEG, atau PNG',
                    'max_size' => 'Ukuran gambar maksimal 1 MB',
                ]
            ]
        ];

        if (!$this->validateData($data, $rules)) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Validasi gagal', 422, null, $this->validator->getErrors());
            }
            
            // 🔥 KIRIM ERROR KE VIEW
            return redirect()
                ->back()
                ->withInput()
                ->with('validation', $this->validator->getErrors())
                ->with('error', 'Mohon periksa kembali input anda');
        }

        // 🔥 Handle image
        $imageName = 'default-profile.png';
        $file = $this->request->getFile('image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $imageName = $file->getRandomName();
            $file->move(FCPATH . 'assets/img/user/', $imageName);
        }
        $data['image'] = $imageName;

        // 🔥 Proses simpan
        $result = $this->userService->createData($data);

        if ($this->isApi()) {
            return $this->apiResponse($result['success'], $result['message'], $result['success'] ? 201 : 500);
        }

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }
        return redirect()->to('/setting/user')->with('success', $result['message']);
    }

    // ================================================================
    // GET BY ID
    // ================================================================
    public function show($id)
    {
        $result = $this->userService->getById($id);

        if ($this->isApi()) {
            return $this->apiResponse($result['success'], $result['message'], $result['success'] ? 200 : 404, $result['data'] ?? null);
        }

        return $this->edit($id);
    }

    // ================================================================
    // EDIT (GET Form)
    // ================================================================
    public function edit($id)
    {
        $result = $this->userService->getById($id);

        if (!$result['success']) {
            return redirect()->to('/setting/user')->with('error', $result['message']);
        }

        $roles = $this->userService->getDataRole();

        return view('user/edit', [
            'page' => 'user',
            'title' => 'Pulsa Io - User',
            'form_name' => 'Form edit data User',
            'roles' => $roles['data'],
            'user' => $result['data'],
        ]);
    }

    // ================================================================
    // UPDATE (PUT)
    // ================================================================
    public function update($id)
    {
        $data = $this->getRequestData();
        
        if (isset($data['_method'])) {
            unset($data['_method']);
        }

        // 🔥 VALIDASI UPDATE
        $rules = [
            'nama_lengkap' => [
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Nama lengkap wajib diisi',
                    'min_length' => 'Nama lengkap minimal 3 karakter',
                ]
            ],
            'role' => [
                'rules' => 'required|in_list[2,3]',
                'errors' => [
                    'required' => 'Role wajib dipilih',
                    'in_list' => 'Role harus Admin atau Kasir',
                ]
            ],
            'username' => [
                'rules' => "required|min_length[6]|is_unique[tbl_user.username,id,{$id}]",
                'errors' => [
                    'required' => 'Username wajib diisi',
                    'min_length' => 'Username minimal 6 karakter',
                    'is_unique' => 'Username sudah digunakan',
                ]
            ],
            'password' => [
                'rules' => 'permit_empty|min_length[6]',
                'errors' => [
                    'min_length' => 'Password minimal 6 karakter',
                ]
            ],
            'email' => [
                'rules' => "permit_empty|valid_email|is_unique[tbl_user.email,id,{$id}]",
                'errors' => [
                    'valid_email' => 'Format email tidak valid',
                    'is_unique' => 'Email sudah digunakan',
                ]
            ],
            'no_tlp' => [
                'rules' => 'permit_empty|regex_match[/^(\+62|62|08)[0-9]{8,13}$/]',
                'errors' => [
                    'regex_match' => 'Format nomor telepon tidak valid. Gunakan 08xxxx atau 62xxxx atau +62xxxx (8-13 digit)',
                ]
            ],
            'alamat' => [
                'rules' => 'permit_empty|min_length[10]',
                'errors' => [
                    'min_length' => 'Alamat minimal 10 karakter',
                ]
            ],
            'image' => [
                'rules' => 'permit_empty|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png]|max_size[image,1024]',
                'errors' => [
                    'is_image' => 'File yang diunggah bukan gambar',
                    'mime_in' => 'Format gambar hanya boleh JPG, JPEG, atau PNG',
                    'max_size' => 'Ukuran gambar maksimal 1 MB',
                ]
            ]
        ];

        if (!$this->validateData($data, $rules)) {
            if ($this->isApi()) {
                return $this->apiResponse(false, 'Validasi gagal', 422, null, $this->validator->getErrors());
            }
            
            // 🔥 KIRIM ERROR KE VIEW
            return redirect()
                ->back()
                ->withInput()
                ->with('validation', $this->validator->getErrors())
                ->with('error', 'Mohon periksa kembali input anda');
        }

        // 🔥 Handle image
        $imageName = $data['old_img'] ?? 'default-profile.png';
        $file = $this->request->getFile('image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $imageName = $file->getRandomName();
            $file->move(FCPATH . 'assets/img/user/', $imageName);
        }
        $data['image'] = $imageName;
        $data['old_img'] = $this->request->getPost('old-img') ?? 'default-profile.png';

        // 🔥 Proses update
        $result = $this->userService->updateData($id, $data);

        if ($this->isApi()) {
            return $this->apiResponse($result['success'], $result['message'], $result['success'] ? 200 : 500);
        }

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }
        return redirect()->to('/setting/user')->with('success', $result['message']);
    }

    // ================================================================
    // DELETE
    // ================================================================
    public function destroy($id)
    {
        $result = $this->userService->deleteData($id);
        return $this->response->setStatusCode($result['code'])->setJSON($result);
    }

    public function delete($id)
    {
        return $this->destroy($id);
    }
}