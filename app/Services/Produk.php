<?php

namespace App\Services;

use App\Models\Kategori;
use App\Models\Produk as ModelsProduk;
use App\Models\Satuan;
use Ramsey\Uuid\Uuid;
use Exception;

class Produk
{
    protected $produkModel;
    protected $kategoriModel;
    protected $satuanModel;

    public function __construct()
    {
        $this->produkModel   = new ModelsProduk();
        $this->kategoriModel = new Kategori();
        $this->satuanModel   = new Satuan();
    }

    // ================================================================
    // GET ALL PRODUK DENGAN SEARCH
    // ================================================================
    public function getData($search = null, $limit = 100, $offset = 0): array
    {
        try {
            $builder = $this->produkModel->whereFisik();

            if (!empty($search)) {
                $builder->like('nama_produk', $search);
            }

            $total = $builder->countAllResults(false);
            $data  = $builder->findAllDataWithRelation($limit, $offset);

            return [
                'success' => true,
                'message' => 'Data produk berhasil diambil',
                'data'    => $data,
                'total'   => $total,
                'code'    => 200,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengambil data produk: ' . $e->getMessage(),
                'data'    => [],
                'total'   => 0,
                'code'    => 500,
            ];
        }
    }

    public function getDataKategori(): array
    {
        try {
            $data = $this->kategoriModel->findAll();
            return [
                'success' => true,
                'message' => 'Data kategori berhasil diambil',
                'data'    => $data,
                'code'    => 200,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengambil data kategori: ' . $e->getMessage(),
                'data'    => [],
                'code'    => 500,
            ];
        }
    }

    public function getDataSatuan(): array
    {
        try {
            $data = $this->satuanModel->findAll();
            return [
                'success' => true,
                'message' => 'Data satuan berhasil diambil',
                'data'    => $data,
                'code'    => 200,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengambil data satuan: ' . $e->getMessage(),
                'data'    => [],
                'code'    => 500,
            ];
        }
    }

    public function getById(string $id): array
    {
        try {
            $data = $this->produkModel->find($id);

            if (!$data) {
                return [
                    'success' => false,
                    'message' => 'Data produk tidak ditemukan',
                    'data'    => [],
                    'code'    => 404,
                ];
            }

            return [
                'success' => true,
                'message' => 'Data produk ditemukan',
                'data'    => $data,
                'code'    => 200,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengambil data produk: ' . $e->getMessage(),
                'data'    => [],
                'code'    => 500,
            ];
        }
    }

    public function createData(array $data): array
    {
        try {
            $id = Uuid::uuid4()->toString();

            $newData = [
                'id'          => $id,
                'nama_produk' => $data['produk'],
                'jenis'       => 'fisik',
                'harga'       => $data['harga'],
                'stok'        => $data['stok'],
                'kategori_id' => $data['kategori'],
                'satuan_id'   => $data['satuan'],
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];

            $this->produkModel->insert($newData);

            return [
                'success' => true,
                'message' => 'Produk berhasil ditambahkan',
                'data'    => $newData,
                'code'    => 201,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menambah produk: ' . $e->getMessage(),
                'data'    => [],
                'code'    => 500,
            ];
        }
    }

    public function updateData(string $id, array $data): array
    {
        try {
            if (!$this->produkModel->find($id)) {
                return [
                    'success' => false,
                    'message' => 'Produk tidak ditemukan',
                    'code'    => 404,
                ];
            }

            $updateData = [
                'nama_produk' => $data['produk'],
                'jenis'       => 'fisik',
                'harga'       => $data['harga'],
                'stok'        => $data['stok'],
                'kategori_id' => $data['kategori'],
                'satuan_id'   => $data['satuan'],
                'updated_at'  => date('Y-m-d H:i:s'),
            ];

            $this->produkModel->update($id, $updateData);

            return [
                'success' => true,
                'message' => 'Produk berhasil diperbarui',
                'data'    => $updateData,
                'code'    => 200,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal memperbarui produk: ' . $e->getMessage(),
                'code'    => 500,
            ];
        }
    }

    public function deleteData(string $id): array
    {
        try {
            if (!$this->produkModel->find($id)) {
                return [
                    'success' => false,
                    'message' => 'Produk tidak ditemukan',
                    'code'    => 404,
                ];
            }

            $this->produkModel->delete($id);

            return [
                'success' => true,
                'message' => 'Produk berhasil dihapus',
                'code'    => 200,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menghapus produk: ' . $e->getMessage(),
                'code'    => 500,
            ];
        }
    }
}