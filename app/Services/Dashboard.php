<?php

namespace App\Services;

class Dashboard
{
    public function getDashboardSummary()
    {
        $db = \Config\Database::connect();
        $today = date('Y-m-d');

        // Data produk (tetap seperti semula)
        $totalTransaksi = $db->table('tbl_penjualan')
            ->where('DATE(created_at)', $today)
            ->countAllResults();

        $totalPendapatan = $db->table('tbl_penjualan')
            ->selectSum('total')
            ->where('DATE(created_at)', $today)
            ->get()->getRow()->total ?? 0;

        $totalProdukTerjual = $db->table('tbl_penjualan_detail')
            ->selectSum('jumlah')
            ->join('tbl_penjualan', 'tbl_penjualan.id = tbl_penjualan_detail.penjualan_id')
            ->where('DATE(tbl_penjualan.created_at)', $today)
            ->get()->getRow()->jumlah ?? 0;

        $StokHabis = $db->table('tbl_produk')
            ->where('stok', 0)
            ->countAllResults();

        // ===== TAMBAHAN DATA PENJUALAN PULSA =====
        // Data penjualan pulsa hari ini
        $transaksiPulsa = $db->table('tbl_penjualan_pulsa')
            ->where('DATE(created_at)', $today)
            ->where('status', 'sukses')
            ->countAllResults();

        $pendapatanPulsa = $db->table('tbl_penjualan_pulsa')
            ->selectSum('harga_jual')
            ->where('DATE(created_at)', $today)
            ->where('status', 'sukses')
            ->get()->getRow()->harga_jual ?? 0;

        $keuntunganPulsa = $db->table('tbl_penjualan_pulsa')
            ->selectSum('keuntungan')
            ->where('DATE(created_at)', $today)
            ->where('status', 'sukses')
            ->get()->getRow()->keuntungan ?? 0;

        // Data transaksi pulsa berdasarkan metode pembayaran
        $metodePembayaranPulsa = $db->table('tbl_penjualan_pulsa')
            ->select('metode_pembayaran, COUNT(*) as jumlah')
            ->where('DATE(created_at)', $today)
            ->where('status', 'sukses')
            ->groupBy('metode_pembayaran')
            ->get()->getResultArray();

        return [
            // Data produk lama
            'transaksiHariIni' => $totalTransaksi,
            'pendapatanHariIni' => $totalPendapatan,
            'produkTerjualHariIni' => $totalProdukTerjual,
            'StokHabis' => $StokHabis,
            
            // Data pulsa baru
            'transaksiPulsaHariIni' => $transaksiPulsa,
            'pendapatanPulsaHariIni' => $pendapatanPulsa,
            'keuntunganPulsaHariIni' => $keuntunganPulsa,
            'metodePembayaranPulsa' => $metodePembayaranPulsa,
            
            
            // Total gabungan
            'totalTransaksiGabungan' => $totalTransaksi + $transaksiPulsa,
            'totalPendapatanGabungan' => $totalPendapatan + $pendapatanPulsa,
        ];
    }
}