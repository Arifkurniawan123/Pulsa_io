<?php

namespace App\Validation;

use App\Models\ProviderModel;

class Provider
{
    protected $providerModel;

    public function __construct()
    {
        $this->providerModel = new ProviderModel();
    }

    /**
     * Validation rules for store (create)
     */
    public function ruleStore()
    {
        return [
            'nama_provider' => [
                'rules'  => 'required|min_length[2]|max_length[100]',
                'errors' => [
                    'required'   => 'Nama provider harus diisi',
                    'min_length' => 'Nama provider minimal 2 karakter',
                    'max_length' => 'Nama provider maksimal 100 karakter'
                ]
            ],
            'kode_provider' => [
                'rules'  => 'required|min_length[2]|max_length[100]|alpha_numeric|is_unique[tbl_provider_pulsa.kode_provider]',
                'errors' => [
                    'required'   => 'Kode provider harus diisi',
                    'min_length' => 'Kode provider minimal 2 karakter',
                    'max_length' => 'Kode provider maksimal 100 karakter',
                    'alpha_numeric' => 'Kode provider hanya boleh huruf dan angka',
                    'is_unique'  => 'Kode provider sudah digunakan'
                ]
            ],
            'status' => [
                'rules'  => 'required|in_list[active,inactive]',
                'errors' => [
                    'required' => 'Status harus diisi',
                    'in_list'  => 'Status harus Active atau Inactive'
                ]
            ]
        ];
    }

    /**
     * Validation rules for update
     */
    public function ruleUpdate($id)
    {
        return [
            'nama_provider' => [
                'rules'  => 'required|min_length[2]|max_length[100]',
                'errors' => [
                    'required'   => 'Nama provider harus diisi',
                    'min_length' => 'Nama provider minimal 2 karakter',
                    'max_length' => 'Nama provider maksimal 100 karakter'
                ]
            ],
            'kode_provider' => [
                'rules'  => "required|min_length[2]|max_length[100]|alpha_numeric|is_unique[tbl_provider_pulsa.kode_provider,id,{$id}]",
                'errors' => [
                    'required'   => 'Kode provider harus diisi',
                    'min_length' => 'Kode provider minimal 2 karakter',
                    'max_length' => 'Kode provider maksimal 100 karakter',
                    'alpha_numeric' => 'Kode provider hanya boleh huruf dan angka',
                    'is_unique'  => 'Kode provider sudah digunakan'
                ]
            ],
            'status' => [
                'rules'  => 'required|in_list[active,inactive]',
                'errors' => [
                    'required' => 'Status harus diisi',
                    'in_list'  => 'Status harus Active atau Inactive'
                ]
            ]
        ];
    }
}