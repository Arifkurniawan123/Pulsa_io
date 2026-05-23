<?php

namespace App\Services;

use App\Models\PenjualanPulsaModel;
use App\Models\NominalModel;
use App\Models\ProviderModel;

class LaporanPulsa
{
    protected $penjualanPulsaModel;
    protected $nominalModel;
    protected $providerModel;
    
    public function __construct()
    {
        $this->penjualanPulsaModel = new PenjualanPulsaModel();
        $this->nominalModel = new NominalModel();
        $this->providerModel = new ProviderModel();
    }

    /**
     * Ambil semua data penjualan pulsa dengan relasi
     */
    public function getData($startDate = null, $endDate = null)
    {
        try {
            $data = $this->penjualanPulsaModel->getAllWithRelations($startDate, $endDate);
            
            return [
                'success' => true,
                'data'    => $data,
            ];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::getData - ' . $th->getMessage());
            return [
                'success' => false,
                'data'    => [],
            ];
        }
    }

    /**
     * Ambil laporan ringkas penjualan pulsa
     */
    public function getSummaryReport($startDate = null, $endDate = null)
    {
        try {
            $data = $this->penjualanPulsaModel->getSummaryReport($startDate, $endDate);
            return [
                'success' => true,
                'data'    => $data,
            ];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::getSummaryReport - ' . $th->getMessage());
            return [
                'success' => false,
                'data'    => [],
            ];
        }
    }

    /**
     * Ambil laporan harian penjualan pulsa
     */
    public function getDailySalesReport($startDate = null, $endDate = null)
    {
        try {
            $data = $this->penjualanPulsaModel->getDailySalesReport($startDate, $endDate);
            return [
                'success' => true,
                'data'    => $data,
            ];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::getDailySalesReport - ' . $th->getMessage());
            return [
                'success' => false,
                'data'    => [],
            ];
        }
    }

    /**
     * Ambil laporan penjualan per provider
     */
    public function getSalesByProvider($startDate = null, $endDate = null)
    {
        try {
            $data = $this->penjualanPulsaModel->getSalesByProvider($startDate, $endDate);
            return [
                'success' => true,
                'data'    => $data,
            ];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::getSalesByProvider - ' . $th->getMessage());
            return [
                'success' => false,
                'data'    => [],
            ];
        }
    }

    /**
     * Ambil laporan penjualan per metode pembayaran
     */
    public function getSalesByPaymentMethod($startDate = null, $endDate = null)
    {
        try {
            $data = $this->penjualanPulsaModel->getSalesByPaymentMethod($startDate, $endDate);
            return [
                'success' => true,
                'data'    => $data,
            ];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::getSalesByPaymentMethod - ' . $th->getMessage());
            return [
                'success' => false,
                'data'    => [],
            ];
        }
    }

    /**
     * Ambil statistik transaksi berdasarkan status
     */
    public function getTransactionStats($startDate = null, $endDate = null)
    {
        try {
            $data = $this->penjualanPulsaModel->getTransactionStats($startDate, $endDate);
            return [
                'success' => true,
                'data'    => $data,
            ];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::getTransactionStats - ' . $th->getMessage());
            return [
                'success' => false,
                'data'    => [],
            ];
        }
    }

    /**
     * Ambil transaksi terbaru
     */
    public function getRecentTransactions($limit = 10)
    {
        try {
            $data = $this->penjualanPulsaModel->getRecentTransactions($limit);
            return [
                'success' => true,
                'data'    => $data,
            ];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::getRecentTransactions - ' . $th->getMessage());
            return [
                'success' => false,
                'data'    => [],
            ];
        }
    }

    /**
     * Ambil semua provider aktif
     */
    public function getProviders()
    {
        try {
            $data = $this->providerModel->where('status', 'active')->findAll();
            return [
                'success' => true,
                'data'    => $data,
            ];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::getProviders - ' . $th->getMessage());
            return [
                'success' => false,
                'data'    => [],
            ];
        }
    }

    /**
     * Ambil semua nominal pulsa aktif
     */
    public function getNominals()
    {
        try {
            $data = $this->nominalModel->where('status', 'active')->findAll();
            return [
                'success' => true,
                'data'    => $data,
            ];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::getNominals - ' . $th->getMessage());
            return [
                'success' => false,
                'data'    => [],
            ];
        }
    }

    /**
     * Ambil data transaksi berdasarkan ID
     */
    public function getById($id)
    {
        try {
            $data = $this->penjualanPulsaModel->getTransactionDetail($id);
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
            log_message('error', 'LaporanPulsa::getById - ' . $th->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan : ' . $th->getMessage(),
                'data'    => [],
            ];
        }
    }

    /**
     * Buat data transaksi pulsa baru
     */
    public function createData($data)
    {
        if ($this->penjualanPulsaModel->isDuplicateTransaction($data['no_tujuan'], $data['nominal_id'])) {
            return [
                'success' => false,
                'message' => 'Transaksi dengan nomor tujuan dan nominal yang sama sudah dilakukan dalam 5 menit terakhir.',
            ];
        }

        $nominal = $this->nominalModel->find($data['nominal_id']);
        if (!$nominal) {
            return [
                'success' => false,
                'message' => 'Data nominal tidak ditemukan.',
            ];
        }

        $hargaModal = $nominal['harga_modal'];
        $hargaJual  = $nominal['harga_jual'];
        $keuntungan = $hargaJual - $hargaModal;

        $noTransaksi = $this->penjualanPulsaModel->generateNoTransaksi();

        $newData = [
            'no_transaksi'      => $noTransaksi,
            'no_tujuan'         => $data['no_tujuan'],
            'provider_id'       => $data['provider_id'],
            'nominal_id'        => $data['nominal_id'],
            'nominal'           => $nominal['nominal'],
            'harga_modal'       => $hargaModal,
            'harga_jual'        => $hargaJual,
            'keuntungan'        => $keuntungan,
            'metode_pembayaran' => $data['metode_pembayaran'],
            'status'            => 'sukses',
            'created_by'        => $data['created_by'],
        ];

        try {
            if (!$this->penjualanPulsaModel->insert($newData)) {
                $errors = $this->penjualanPulsaModel->errors();
                return [
                    'success' => false,
                    'message' => 'Gagal menyimpan data transaksi: ' . implode(', ', $errors)
                ];
            }

            return [
                'success' => true,
                'message' => 'Transaksi pulsa berhasil disimpan'
            ];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::createData - ' . $th->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan : ' . $th->getMessage(),
            ];
        }
    }

    /**
     * Update data transaksi pulsa
     */
    public function updateData($id, $data)
    {
        $existing = $this->penjualanPulsaModel->find($id);
        if (!$existing) {
            return [
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ];
        }

        $nominal = $this->nominalModel->find($data['nominal_id']);
        if (!$nominal) {
            return [
                'success' => false,
                'message' => 'Data nominal tidak ditemukan.'
            ];
        }
        
        $updateData = [
            'no_tujuan'         => $data['no_tujuan'],
            'provider_id'       => $data['provider_id'],
            'nominal_id'        => $data['nominal_id'],
            'nominal'           => $nominal['nominal'],
            'harga_modal'       => $nominal['harga_modal'],
            'harga_jual'        => $nominal['harga_jual'],
            'keuntungan'        => $nominal['harga_jual'] - $nominal['harga_modal'],
            'metode_pembayaran' => $data['metode_pembayaran'],
            'status'            => $data['status'],
        ];

        try {
            if (!$this->penjualanPulsaModel->update($id, $updateData)) {
                $errors = $this->penjualanPulsaModel->errors();
                return [
                    'success' => false,
                    'message' => 'Gagal update data transaksi: ' . implode(', ', $errors),
                ];
            }

            return [
                'success' => true,
                'message' => 'Transaksi pulsa berhasil diupdate',
            ];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::updateData - ' . $th->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan : ' . $th->getMessage(),
            ];
        }
    }

    /**
     * Hapus data transaksi pulsa
     */
    public function deleteData($id)
    {
        $existing = $this->penjualanPulsaModel->find($id);
        if (!$existing) {
            return [
                'success' => false,
                'code'    => 404,
                'message' => 'Data tidak ditemukan'
            ];
        }

        try {
            if (!$this->penjualanPulsaModel->delete($id)) {
                return [
                    'success' => false,
                    'code'    => 500,
                    'message' => 'Gagal hapus data transaksi'
                ];
            }

            return [
                'success' => true,
                'code'    => 200,
                'message' => 'Data transaksi berhasil dihapus'
            ];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::deleteData - ' . $th->getMessage());
            return [
                'success' => false,
                'code'    => 500,
                'message' => 'Terjadi kesalahan : ' . $th->getMessage(),
            ];
        }
    }
}