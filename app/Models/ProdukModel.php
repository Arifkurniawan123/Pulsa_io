<?php
namespace App\Models;

use CodeIgniter\Model;

class ProdukModel extends Model
{
    protected $table = 'tbl_produk';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'nama_produk', 'jenis', 'harga', 'stok', 'kategori_id', 'satuan_id'];
    protected $useTimestamps = true;

    public function getProductsWithCategory()
    {
        return $this->select('tbl_produk.*, tbl_kategori.name_kategori, tbl_satuan.nama_satuan')
            ->join('tbl_kategori', 'tbl_kategori.id = tbl_produk.kategori_id', 'left')
            ->join('tbl_satuan', 'tbl_satuan.id = tbl_produk.satuan_id', 'left')
            ->orderBy('tbl_produk.jenis', 'DESC')
            ->orderBy('tbl_produk.nama_produk', 'ASC')
            ->findAll();
    }

    public function getActiveProducts()
    {
        return $this->where('stok >', 0)->findAll();
    }

    public function getProductsByType($jenis)
    {
        return $this->where('jenis', $jenis)->where('stok >', 0)->findAll();
    }

    public function getDigitalProducts()
    {
        return $this->where('jenis', 'digital')->where('stok >', 0)->findAll();
    }

    public function generateProductId($jenis = 'fisik')
    {
        $prefix = $jenis == 'digital' ? 'PUL' : 'P';
        $last = $this->like('id', $prefix)->orderBy('id', 'DESC')->first();
        
        if ($last) {
            $number = (int) substr($last['id'], strlen($prefix)) + 1;
            return $prefix . str_pad($number, 2, '0', STR_PAD_LEFT);
        }
        return $prefix . '01';
    }
}