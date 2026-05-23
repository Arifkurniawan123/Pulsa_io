<?php

namespace App\Models;

use CodeIgniter\Model;

class Penjualan extends Model
{
    protected $table            = 'tbl_penjualan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;

    protected $returnType       = 'object';
    protected $protectFields    = true;

    protected $allowedFields    = [
        'id',
        'no_invoice',
        'created_by',
        'total',
        'ppn',
        'diskon',
        'metode_pembayaran',
        'status',
        'created_at',
        'updated_at'
    ];

    // Date management
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua transaksi berdasarkan kasir
     */
    public function getByKasir(string $userId)
    {
        return $this->where('created_by', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Ambil detail transaksi per ID
     */
    public function getDetail(string $id)
    {
        return $this->where('id', $id)->first();
    }
}
