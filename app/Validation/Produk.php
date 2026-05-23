<?php

namespace App\Validation;

use Config\Services;

class Produk
{
    public function ruleStore()
    {
        return [
            'produk' => [
                'rules' => 'required|min_length[3]|max_length[50]',
                'errors' => [
                    'required' => 'Nama produk wajib diisi',
                    'min_length' => 'Nama produk minimal 3 karakter',
                    'max_length' => 'Nama produk maksimal 50 karakter'
                ]
            ],
            'harga' => [
                'rules' => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required' => 'Harga wajib diisi',
                    'numeric' => 'Harga harus berupa angka',
                    'greater_than' => 'Harga harus lebih dari 0'
                ]
            ],
            'stok' => [
                'rules' => 'required|integer|greater_than_equal_to[0]',
                'errors' => [
                    'required' => 'Stok wajib diisi',
                    'integer' => 'Stok harus berupa angka bulat',
                    'greater_than_equal_to' => 'Stok tidak boleh kurang dari 0'
                ]
            ],
            'kategori' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kategori wajib dipilih'
                ]
            ],
            'satuan' => [ // Satuan sekarang selalu required
                'rules' => 'required',
                'errors' => [
                    'required' => 'Satuan wajib dipilih'
                ]
            ]
        ];
    }

    public function ruleUpdate()
    {
        return $this->ruleStore(); // Gunakan rule yang sama
    }
}