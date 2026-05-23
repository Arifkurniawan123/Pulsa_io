<?php

namespace App\Validation;

class User
{
    public function ruleStore()
    {
        return [
            'nama_lengkap' => [
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Field nama wajib diisi',
                    'min_length' => 'Field nama minimal 3 karakter',
                ]
            ],
            'role' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Field role wajib di pilih',
                ]
            ],
            'username' => [
                'rules' => 'required|min_length[6]|is_unique[tbl_user.username]',
                'errors' => [
                    'required' => 'Field username wajib diisi',
                    'min_length' => 'Field username minimal 6 karakter',
                    'is_unique' => 'Username sudah digunakan',
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[6]',
                'errors' => [
                    'required' => 'Field password wajib diisi',
                    'min_length' => 'Field password minimal 6 karakter',
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
                'rules' => 'permit_empty|regex_match[/^(\\+62|62|08)[0-9]{8,13}$/]',
                'errors' => [
                    'regex_match' => 'Format nomor telepon tidak valid',
                ]
            ],
            'alamat' => [
                'rules' => 'permit_empty|min_length[10]',
                'errors' => [
                    'min_length' => 'Field alamat minimal 10 karakter',
                ]
            ],
            'image' => [
                'rules' => 'is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png]|max_size[image,1024]',
                'errors' => [
                    'is_image' => 'File yang diunggah bukan gambar',
                    'mime_in' => 'Format gambar hanya boleh JPG, JPEG, atau PNG',
                    'max_size' => 'Ukuran gambar maksimal 1 MB'
                ]
            ]
        ];
    }

    public function ruleUpdate($id)
    {
        return [
            'nama_lengkap' => [
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Field nama wajib diisi',
                    'min_length' => 'Field nama minimal 3 karakter',
                ]
            ],
            'role' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Field role wajib di pilih',
                ]
            ],
            'username' => [
                'rules' => 'required|min_length[6]|is_unique[tbl_user.username,id,' . $id . ']',
                'errors' => [
                    'required' => 'Field username wajib diisi',
                    'min_length' => 'Field username minimal 6 karakter',
                    'is_unique' => 'Username sudah digunakan',
                ]
            ],
            'password' => [
                'rules' => 'permit_empty|min_length[6]',
                'errors' => [
                    'min_length' => 'Field password minimal 6 karakter',
                ]
            ],
            'email' => [
                'rules' => 'permit_empty|valid_email|is_unique[tbl_user.email,id,' . $id . ']',
                'errors' => [
                    'valid_email' => 'Format email tidak valid',
                    'is_unique' => 'Email sudah digunakan',
                ]
            ],
            'no_tlp' => [
                'rules' => 'permit_empty|regex_match[/^(\\+62|62|08)[0-9]{8,13}$/]',
                'errors' => [
                    'regex_match' => 'Format nomor telepon tidak valid',
                ]
            ],
            'alamat' => [
                'rules' => 'permit_empty|min_length[10]',
                'errors' => [
                    'min_length' => 'Field alamat minimal 10 karakter',
                ]
            ],
            'image' => [
                'rules' => 'is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png]|max_size[image,1024]',
                'errors' => [
                    'is_image' => 'File yang diunggah bukan gambar',
                    'mime_in' => 'Format gambar hanya boleh JPG, JPEG, atau PNG',
                    'max_size' => 'Ukuran gambar maksimal 1 MB'
                ]
            ]
        ];
    }
}
