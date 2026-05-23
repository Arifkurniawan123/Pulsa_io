<?php

namespace App\Models;

use CodeIgniter\Model;

class Produk extends Model
{
    protected $table            = 'tbl_produk';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'object';
    protected $protectFields    = true;

    protected $allowedFields    = [
        'id', 'nama_produk', 'jenis', 'harga', 'stok',
        'kategori_id', 'satuan','satuan_id', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findAllDataWithRelation()
    {
        return $this->select('tbl_produk.*, tbl_kategori.nama_kategori, tbl_satuan.nama_satuan')
                    ->join('tbl_kategori', 'tbl_kategori.id = tbl_produk.kategori_id', 'left')
                    ->join('tbl_satuan', 'tbl_satuan.id = tbl_produk.satuan_id', 'left')
                    ->findAll();
    }

    public function findAllDataWithStokReady()
    {
        return $this->where('stok >', '0')->findAll();
    }

    // Method baru untuk hanya produk fisik
    public function whereFisik()
    {
        return $this->where('jenis', 'fisik');
    }
}