<?php

namespace App\Models;

use CodeIgniter\Model;

class PenjualanPulsaModel extends Model
{
    protected $table = 'tbl_penjualan_pulsa';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'no_transaksi', 'no_tujuan', 'nominal_id', 'provider_id',
        'nominal', 'harga_modal', 'harga_jual', 'keuntungan',
        'metode_pembayaran', 'status', 'created_at', 'updated_at',
        'created_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'no_transaksi' => 'required|max_length[50]',
        'no_tujuan' => 'required|numeric|max_length[15]',
        'nominal_id' => 'required|integer',
        'provider_id' => 'required|integer',
        'metode_pembayaran' => 'required|in_list[tunai,saldo,transfer,grip]',
        'status' => 'required|in_list[proses,sukses,gagal]'
    ];

    protected $validationMessages = [
        'no_tujuan' => [
            'required' => 'Nomor tujuan harus diisi',
            'numeric' => 'Nomor tujuan harus berupa angka'
        ],
        'provider_id' => [
            'required' => 'Provider harus dipilih'
        ],
        'nominal_id' => [
            'required' => 'Nominal pulsa harus dipilih'
        ]
    ];

    public function getAllWithRelations($startDate = null, $endDate = null, $status = null)
    {
        $builder = $this->db->table($this->table . ' AS pp')
            ->select("
                pp.id,
                pp.no_transaksi,
                pp.no_tujuan,
                pp.nominal_id,
                pp.provider_id,
                pp.nominal,
                pp.harga_modal,
                pp.harga_jual,
                pp.keuntungan,
                pp.metode_pembayaran,
                pp.status,
                pp.created_by,
                pp.created_at,
                pp.updated_at,
                COALESCE(provider.nama_provider, '-') AS nama_provider,
                COALESCE(provider.kode_provider, '-') AS kode_provider,
                COALESCE(nominal_data.nominal, pp.nominal) AS nominal_paket,
                COALESCE(user_data.nama_lengkap, 'Sistem') AS nama_user
            ")
            ->join('tbl_provider_pulsa AS provider', 'provider.id = pp.provider_id', 'left')
            ->join('tbl_nominal_pulsa AS nominal_data', 'nominal_data.id = pp.nominal_id', 'left')
            ->join('tbl_user AS user_data', 'user_data.id = pp.created_by', 'left')
            ->orderBy('pp.created_at', 'DESC');

        if ($startDate && $endDate) {
            $builder->where("DATE(pp.created_at) >=", $startDate)
                    ->where("DATE(pp.created_at) <=", $endDate);
        }

        if ($status) {
            $builder->where("pp.status", $status);
        }

        return $builder->get()->getResultArray();
    }

    public function getTransactionDetail($id)
    {
        return $this->db->table($this->table . ' AS pp')
            ->select("
                pp.id,
                pp.no_transaksi,
                pp.no_tujuan,
                pp.nominal_id,
                pp.provider_id,
                pp.nominal,
                pp.harga_modal,
                pp.harga_jual,
                pp.keuntungan,
                pp.metode_pembayaran,
                pp.status,
                pp.created_by,
                pp.created_at,
                pp.updated_at,
                COALESCE(provider.nama_provider, '-') AS nama_provider,
                COALESCE(provider.kode_provider, '-') AS kode_provider,
                COALESCE(nominal_data.nominal, pp.nominal) AS nominal_paket,
                COALESCE(nominal_data.harga_modal, pp.harga_modal) AS harga_modal_paket,
                COALESCE(nominal_data.harga_jual, pp.harga_jual) AS harga_jual_paket,
                COALESCE(user_data.nama_lengkap, 'Sistem') AS nama_user
            ")
            ->join('tbl_provider_pulsa AS provider', 'provider.id = pp.provider_id', 'left')
            ->join('tbl_nominal_pulsa AS nominal_data', 'nominal_data.id = pp.nominal_id', 'left')
            ->join('tbl_user AS user_data', 'user_data.id = pp.created_by', 'left')
            ->where('pp.id', $id)
            ->get()
            ->getRowArray();
    }

    public function getSummaryReport($startDate = null, $endDate = null)
    {
        $builder = $this->db->table($this->table)
            ->select("
                COUNT(id) AS total_transaksi,
                SUM(harga_jual) AS total_penjualan,
                SUM(keuntungan) AS total_keuntungan,
                AVG(keuntungan) AS rata_rata_keuntungan
            ")
            ->where('status', 'sukses');

        if ($startDate && $endDate) {
            $builder->where("DATE(created_at) >=", $startDate)
                    ->where("DATE(created_at) <=", $endDate);
        }

        return $builder->get()->getRowArray();
    }

    public function getDailySalesReport($startDate = null, $endDate = null)
    {
        $builder = $this->db->table($this->table)
            ->select("
                DATE(created_at) as tanggal,
                COUNT(id) AS total_transaksi,
                SUM(harga_jual) AS total_penjualan,
                SUM(keuntungan) AS total_keuntungan
            ")
            ->where('status', 'sukses')
            ->groupBy('DATE(created_at)')
            ->orderBy('tanggal', 'ASC');

        if ($startDate && $endDate) {
            $builder->where("DATE(created_at) >=", $startDate)
                    ->where("DATE(created_at) <=", $endDate);
        }

        return $builder->get()->getResultArray();
    }

    public function getSalesByProvider($startDate = null, $endDate = null)
    {
        $builder = $this->db->table($this->table . ' AS pp')
            ->select("
                provider.nama_provider,
                COUNT(pp.id) AS total_transaksi,
                SUM(pp.harga_jual) AS total_penjualan,
                SUM(pp.keuntungan) AS total_keuntungan
            ")
            ->join('tbl_provider_pulsa AS provider', 'provider.id = pp.provider_id')
            ->where('pp.status', 'sukses')
            ->groupBy('pp.provider_id')
            ->orderBy('total_penjualan', 'DESC');

        if ($startDate && $endDate) {
            $builder->where("DATE(pp.created_at) >=", $startDate)
                    ->where("DATE(pp.created_at) <=", $endDate);
        }

        return $builder->get()->getResultArray();
    }

    public function generateNoTransaksi()
    {
        $prefix = 'PULSA-' . date('Ymd');
        $lastTransaction = $this->like('no_transaksi', $prefix)
                               ->orderBy('id', 'DESC')
                               ->first();

        $newNumber = $lastTransaction ? 
            ((int) substr($lastTransaction['no_transaksi'], -3) + 1) : 1;

        return $prefix . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    public function isDuplicateTransaction($noTujuan, $nominalId, $timeRange = '-5 minutes')
    {
        $timeThreshold = date('Y-m-d H:i:s', strtotime($timeRange));
        
        return $this->where('no_tujuan', $noTujuan)
                    ->where('nominal_id', $nominalId)
                    ->where('created_at >=', $timeThreshold)
                    ->where('status !=', 'gagal')
                    ->countAllResults() > 0;
    }

    public function updateStatus($id, $status)
    {
        if (!in_array($status, ['proses', 'sukses', 'gagal'])) {
            return false;
        }

        return $this->update($id, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getTransactionStats($startDate = null, $endDate = null)
    {
        $builder = $this->db->table($this->table)
            ->select("
                status,
                COUNT(*) as total,
                SUM(harga_jual) as total_penjualan
            ")
            ->groupBy('status');

        if ($startDate && $endDate) {
            $builder->where("DATE(created_at) >=", $startDate)
                    ->where("DATE(created_at) <=", $endDate);
        }

        $result = $builder->get()->getResultArray();
        
        $stats = [
            'sukses' => ['total' => 0, 'total_penjualan' => 0],
            'proses' => ['total' => 0, 'total_penjualan' => 0],
            'gagal' => ['total' => 0, 'total_penjualan' => 0]
        ];

        foreach ($result as $row) {
            $stats[$row['status']] = [
                'total' => (int)$row['total'],
                'total_penjualan' => (float)$row['total_penjualan']
            ];
        }

        return $stats;
    }

    public function getRecentTransactions($limit = 10)
    {
        return $this->db->table($this->table . ' AS pp')
            ->select("
                pp.id,
                pp.no_transaksi,
                pp.no_tujuan,
                pp.nominal_id,
                pp.provider_id,
                pp.nominal,
                pp.harga_modal,
                pp.harga_jual,
                pp.keuntungan,
                pp.metode_pembayaran,
                pp.status,
                pp.created_by,
                pp.created_at,
                pp.updated_at,
                COALESCE(provider.nama_provider, '-') AS nama_provider,
                COALESCE(user_data.nama_lengkap, 'Sistem') AS nama_user
            ")
            ->join('tbl_provider_pulsa AS provider', 'provider.id = pp.provider_id', 'left')
            ->join('tbl_user AS user_data', 'user_data.id = pp.created_by', 'left')
            ->orderBy('pp.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function getSalesByPaymentMethod($startDate = null, $endDate = null)
    {
        $builder = $this->db->table($this->table)
            ->select("
                metode_pembayaran,
                COUNT(*) as total_transaksi,
                SUM(harga_jual) as total_penjualan
            ")
            ->where('status', 'sukses')
            ->groupBy('metode_pembayaran')
            ->orderBy('total_penjualan', 'DESC');

        if ($startDate && $endDate) {
            $builder->where("DATE(created_at) >=", $startDate)
                    ->where("DATE(created_at) <=", $endDate);
        }

        return $builder->get()->getResultArray();
    }
}