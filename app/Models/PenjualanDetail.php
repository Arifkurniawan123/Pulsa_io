<?php

namespace App\Models;

use CodeIgniter\Model;

class PenjualanDetail extends Model
{
    protected $table            = 'tbl_penjualan_detail';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'penjualan_id', 'produk_id', 'jumlah', 'harga', 'sub_total', 'created_at', 'updated_at'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $deletedField  = '';

    public function findAllSoldProduk(?string $start = null, ?string $end = null)
    {
        $builder = $this->select("
            DATE(tbl_penjualan.created_at) as tanggal,
            tbl_produk.nama_produk,
            SUM(tbl_penjualan_detail.jumlah) as total_terjual,
            SUM(tbl_penjualan_detail.sub_total) as total_pendapatan,
            tbl_user.nama_lengkap as kasir
        ")
            ->join('tbl_produk', 'tbl_produk.id = tbl_penjualan_detail.produk_id')
            ->join('tbl_penjualan', 'tbl_penjualan.id = tbl_penjualan_detail.penjualan_id')
            ->join('tbl_user', 'tbl_user.id = tbl_penjualan.created_by')
            ->groupBy('tanggal, tbl_produk.id, tbl_penjualan.created_by')
            ->orderBy('tanggal', 'DESC');

        if ($start && $end) {
            $builder->where('tbl_penjualan.created_at >=', $start . ' 00:00:00');
            $builder->where('tbl_penjualan.created_at <=', $end . ' 23:59:59');
        }

        return $builder->findAll();
    }
}
