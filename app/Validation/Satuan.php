<?php

namespace App\Validation;

class Satuan
{
    public function ruleStore()
    {
        return [
            'satuan' => [
                'rules' => 'required|min_length[2]|is_unique[tbl_satuan.nama_satuan]',
                'errors' => [
                    'required' => 'Field satuan wajib diisi',
                    'min_length' => 'Field satuan minimal 2 karakter',
                    'is_unique' => 'Nama satuan sudah digunakan'
                ]
            ]
        ];
    }

    public function ruleUpdate($id)
    {
        return [
            'satuan' => [
                'rules' => 'required|min_length[2]|is_unique[tbl_satuan.nama_satuan,id,' . $id . ']',
                'errors' => [
                    'required' => 'Field satuan wajib diisi',
                    'min_length' => 'Field satuan minimal 2 karakter',
                    'is_unique' => 'Nama satuan sudah digunakan'
                ]
            ]
        ];
    }
}
