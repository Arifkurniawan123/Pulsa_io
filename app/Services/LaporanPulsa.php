<?php

namespace App\Services;

use App\Models\PenjualanPulsaModel;
use App\Models\NominalModel;
use App\Models\ProviderModel;
use App\Traits\WebSocketTrait;  // ← TAMBAHKAN

class LaporanPulsa
{
    use WebSocketTrait;  // ← TAMBAHKAN

    protected $penjualanPulsaModel;
    protected $nominalModel;
    protected $providerModel;
    
    public function __construct()
    {
        $this->penjualanPulsaModel = new PenjualanPulsaModel();
        $this->nominalModel = new NominalModel();
        $this->providerModel = new ProviderModel();
    }

    // ==================== GET DATA ====================
    public function getData($startDate = null, $endDate = null, $status = null)
    {
        try {
            $data = $this->penjualanPulsaModel->getAllWithRelations($startDate, $endDate, $status);
            return ['success' => true, 'data' => $data];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::getData - ' . $th->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    public function getSummaryReport($startDate = null, $endDate = null)
    {
        try {
            $data = $this->penjualanPulsaModel->getSummaryReport($startDate, $endDate);
            return ['success' => true, 'data' => $data];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::getSummaryReport - ' . $th->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    public function getById($id)
    {
        try {
            $data = $this->penjualanPulsaModel->getTransactionDetail($id);
            if (!$data) {
                return ['success' => false, 'message' => 'Data tidak ditemukan', 'data' => []];
            }
            return ['success' => true, 'message' => 'Data ditemukan', 'data' => $data];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::getById - ' . $th->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan : ' . $th->getMessage(), 'data' => []];
        }
    }

    // ==================== CREATE ====================
    public function createData($data)
    {
        // Cek duplikat
        if ($this->penjualanPulsaModel->isDuplicateTransaction($data['no_tujuan'], $data['nominal_id'])) {
            return [
                'success' => false,
                'message' => 'Transaksi dengan nomor tujuan dan nominal yang sama sudah dilakukan dalam 5 menit terakhir.'
            ];
        }

        $nominal = $this->nominalModel->find($data['nominal_id']);
        if (!$nominal) {
            return ['success' => false, 'message' => 'Data nominal tidak ditemukan.'];
        }

        $hargaModal = $nominal['harga_modal'] ?? 0;
        $hargaJual  = $nominal['harga_jual'] ?? 0;
        $nilaiNominal = $nominal['nominal'] ?? 0;
        $keuntungan = $hargaJual - $hargaModal;

        $noTransaksi = $this->penjualanPulsaModel->generateNoTransaksi();

        $newData = [
            'no_transaksi'      => $noTransaksi,
            'no_tujuan'         => $data['no_tujuan'],
            'provider_id'       => $data['provider_id'],
            'nominal_id'        => $data['nominal_id'],
            'nominal'           => $nilaiNominal,
            'harga_modal'       => $hargaModal,
            'harga_jual'        => $hargaJual,
            'keuntungan'        => $keuntungan,
            'metode_pembayaran' => $data['metode_pembayaran'],
            'status'            => 'sukses',
            'created_by'        => $data['created_by'] ?? null,
        ];

        try {
            if (!$this->penjualanPulsaModel->insert($newData)) {
                $errors = $this->penjualanPulsaModel->errors();
                return ['success' => false, 'message' => 'Gagal menyimpan data transaksi: ' . implode(', ', $errors)];
            }

            // 🔥 KIRIM NOTIFIKASI WEBSOCKET (PULSA)
            $provider = $this->providerModel->find($data['provider_id']);
            
            $this->sendWebSocketNotification([
                'type'         => 'pulsa',
                'no_transaksi' => $noTransaksi,
                'no_tujuan'    => $data['no_tujuan'],
                'nominal'      => (int) $nilaiNominal,
                'harga_jual'   => (int) $hargaJual,
                'provider'     => $provider['nama_provider'] ?? '-',
                'kasir'        => session()->get('name') ?? 'Kasir',
                'created_at'   => date('H:i:s')
            ]);

            return ['success' => true, 'message' => 'Transaksi pulsa berhasil disimpan'];

        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::createData - ' . $th->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan : ' . $th->getMessage()];
        }
    }

    // ==================== UPDATE ====================
    public function updateData($id, $data)
    {
        $existing = $this->penjualanPulsaModel->find($id);
        if (!$existing) {
            return ['success' => false, 'message' => 'Data tidak ditemukan'];
        }

        $nominal = $this->nominalModel->find($data['nominal_id']);
        if (!$nominal) {
            return ['success' => false, 'message' => 'Data nominal tidak ditemukan.'];
        }

        $hargaModal = $nominal['harga_modal'] ?? 0;
        $hargaJual  = $nominal['harga_jual'] ?? 0;
        $nilaiNominal = $nominal['nominal'] ?? 0;
        $keuntungan = $hargaJual - $hargaModal;

        $updateData = [
            'no_tujuan'         => $data['no_tujuan'],
            'provider_id'       => $data['provider_id'],
            'nominal_id'        => $data['nominal_id'],
            'nominal'           => $nilaiNominal,
            'harga_modal'       => $hargaModal,
            'harga_jual'        => $hargaJual,
            'keuntungan'        => $keuntungan,
            'metode_pembayaran' => $data['metode_pembayaran'],
            'status'            => $data['status'],
        ];

        try {
            if (!$this->penjualanPulsaModel->update($id, $updateData)) {
                $errors = $this->penjualanPulsaModel->errors();
                return ['success' => false, 'message' => 'Gagal update data transaksi: ' . implode(', ', $errors)];
            }
            return ['success' => true, 'message' => 'Transaksi pulsa berhasil diupdate'];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::updateData - ' . $th->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan : ' . $th->getMessage()];
        }
    }

    // ==================== DELETE ====================
    public function deleteData($id)
    {
        $existing = $this->penjualanPulsaModel->find($id);
        if (!$existing) {
            return ['success' => false, 'code' => 404, 'message' => 'Data tidak ditemukan'];
        }

        try {
            if (!$this->penjualanPulsaModel->delete($id)) {
                return ['success' => false, 'code' => 500, 'message' => 'Gagal hapus data transaksi'];
            }
            return ['success' => true, 'code' => 200, 'message' => 'Data transaksi berhasil dihapus'];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::deleteData - ' . $th->getMessage());
            return ['success' => false, 'code' => 500, 'message' => 'Terjadi kesalahan : ' . $th->getMessage()];
        }
    }

    // ==================== UNTUK DROPDOWN ====================
    public function getProviders()
    {
        try {
            $data = $this->providerModel->where('status', 'active')->findAll();
            return ['success' => true, 'data' => $data];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::getProviders - ' . $th->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    public function getNominals()
    {
        try {
            $data = $this->nominalModel->where('status', 'active')->findAll();
            return ['success' => true, 'data' => $data];
        } catch (\Throwable $th) {
            log_message('error', 'LaporanPulsa::getNominals - ' . $th->getMessage());
            return ['success' => false, 'data' => []];
        }
    }
}