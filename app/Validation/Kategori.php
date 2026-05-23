<?php

namespace App\Validation;

class Kategori
{
    public function ruleStore()
    {
        return [
            'kategori' => [
                'rules' => 'required|min_length[3]|is_unique[tbl_kategori.nama_kategori]',
                'errors' => [
                    'required' => 'Field kategori wajib diisi',
                    'min_length' => 'Field kategori minimal 3 karakter',
                    'is_unique' => 'Nama kategori sudah digunakan'
                ]
            ]
        ];
    }

    public function ruleUpdate($id)
    {
        return [
            'kategori' => [
                'rules' => 'required|min_length[3]|is_unique[tbl_kategori.nama_kategori,id,' . $id . ']',
                'errors' => [
                    'required' => 'Field kategori wajib diisi',
                    'min_length' => 'Field kategori minimal 3 karakter',
                    'is_unique' => 'Nama kategori sudah digunakan'
                ]
            ]
        ];
    }
}
