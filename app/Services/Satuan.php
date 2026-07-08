<?php

namespace App\Services;

use App\Models\Satuan as ModelsSatuan;

class Satuan
{
    protected $satuanModel;
    
    public function __construct()
    {
        $this->satuanModel = new ModelsSatuan();
    }

    // ================================================================
    // GET DATA WITH SEARCH
    // ================================================================
    public function getData($search = null, $limit = 100, $offset = 0)
    {
        try {
            $builder = $this->satuanModel;

            if (!empty($search)) {
                $builder->like('nama_satuan', $search);
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
            $data = $this->satuanModel->where('id', $id)->first();
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
            'nama_satuan' => $data['satuan'],
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        try {
            if (!$this->satuanModel->insert($newData)) {
                return [
                    'success' => false,
                    'message' => 'Gagal menyimpan data satuan'
                ];
            }

            return [
                'success' => true,
                'message' => 'Berhasil menyimpan data satuan'
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
        $existing = $this->satuanModel->where('id', $id)->first();
        if (!$existing) {
            return [
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ];
        }

        $newData = [
            'nama_satuan' => $data['satuan'],
            'updated_at'  => date('Y-m-d H:i:s'),
        ];

        try {
            if (!$this->satuanModel->update($existing->id, $newData)) {
                return [
                    'success' => false,
                    'message' => 'Gagal update data satuan'
                ];
            }

            return [
                'success' => true,
                'message' => 'Berhasil update data satuan'
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
        $existing = $this->satuanModel->where('id', $id)->first();
        if (!$existing) {
            return [
                'success' => false,
                'code'    => 404,
                'message' => 'Data tidak ditemukan'
            ];
        }

        try {
            if (!$this->satuanModel->delete($existing->id)) {
                return [
                    'success' => false,
                    'code'    => 500,
                    'message' => 'Gagal hapus data satuan'
                ];
            }

            return [
                'success' => true,
                'code'    => 200,
                'message' => 'Berhasil hapus data satuan'
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