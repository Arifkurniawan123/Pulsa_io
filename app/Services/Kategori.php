<?php

namespace App\Services;

use App\Models\Kategori as ModelsKategori;

class Kategori
{
    protected $kategoriModel;
    
    public function __construct()
    {
        $this->kategoriModel = new ModelsKategori();
    }

    // ================================================================
    // GET DATA WITH SEARCH
    // ================================================================
    public function getData($search = null, $limit = 100, $offset = 0)
    {
        try {
            $builder = $this->kategoriModel;

            if (!empty($search)) {
                $builder->like('nama_kategori', $search);
            }

            $total = $builder->countAllResults(false);
            $data  = $builder->orderBy('id', 'DESC')
                             ->findAll($limit, $offset);

            return [
                'success' => true,
                'data'    => $data,
                'total'   => $total,
            ];
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return [
                'success' => false,
                'data'    => [],
                'total'   => 0,
            ];
        }
    }

    public function getById($id)
    {
        try {
            $data = $this->kategoriModel->where('id', $id)->first();
            if (!$data) {
                return [
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'data'    => [],
                ];
            }

            return [
                'success' => true,
                'message' => 'Data ditemukan',
                'data'    => $data,
            ];
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan : ' . $th->getMessage(),
                'data'    => [],
            ];
        }
    }

    public function createData($data)
    {
        $newData = [
            'nama_kategori' => $data['kategori'],
            'created_at'    => date('Y-m-d H:i:s'),
        ];

        try {
            if (!$this->kategoriModel->insert($newData)) {
                return [
                    'success' => false,
                    'message' => 'Gagal menyimpan data kategori'
                ];
            }

            return [
                'success' => true,
                'message' => 'Berhasil menyimpan data kategori'
            ];
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan : ' . $th->getMessage(),
            ];
        }
    }

    public function updateData($id, $data)
    {
        $existing = $this->kategoriModel->where('id', $id)->first();
        if (!$existing) {
            return [
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ];
        }

        $newData = [
            'nama_kategori' => $data['kategori'],
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        try {
            if (!$this->kategoriModel->update($existing->id, $newData)) {
                return [
                    'success' => false,
                    'message' => 'Gagal update data kategori'
                ];
            }

            return [
                'success' => true,
                'message' => 'Berhasil update data kategori'
            ];
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan : ' . $th->getMessage(),
            ];
        }
    }

    public function deleteData($id)
    {
        $existing = $this->kategoriModel->where('id', $id)->first();
        if (!$existing) {
            return [
                'success' => false,
                'code'    => 404,
                'message' => 'Data tidak ditemukan'
            ];
        }

        try {
            if (!$this->kategoriModel->delete($existing->id)) {
                return [
                    'success' => false,
                    'code'    => 500,
                    'message' => 'Gagal hapus data kategori'
                ];
            }

            return [
                'success' => true,
                'code'    => 200,
                'message' => 'Berhasil hapus data kategori'
            ];
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return [
                'success' => false,
                'code'    => 500,
                'message' => 'Terjadi kesalahan : ' . $th->getMessage(),
            ];
        }
    }
}