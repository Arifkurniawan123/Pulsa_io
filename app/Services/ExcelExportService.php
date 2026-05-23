<?php
// app/Services/ExcelExportService.php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExcelExportService
{
    protected $spreadsheet;
    protected $sheet;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
    }

    /**
     * Export laporan penjualan produk ke Excel
     */
    public function exportLaporanProduk($data, $startDate = null, $endDate = null, $filename = 'laporan-penjualan-produk')
    {
        try {
            // Set judul
            $this->sheet->setCellValue('A1', 'LAPORAN PENJUALAN PRODUK');
            $this->sheet->mergeCells('A1:F1');
            $this->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $this->sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Set periode
            $periode = $startDate && $endDate ? 
                "Periode: " . date('d/m/Y', strtotime($startDate)) . " - " . date('d/m/Y', strtotime($endDate)) :
                "Periode: Semua Data";
            
            $this->sheet->setCellValue('A2', $periode);
            $this->sheet->mergeCells('A2:F2');
            $this->sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $this->sheet->getStyle('A2')->getFont()->setItalic(true);

            // Header tabel
            $headers = ['No', 'Nama Produk', 'Jumlah Terjual', 'Total Pendapatan', 'Nama Kasir', 'Tanggal'];
            $col = 'A';
            $row = 4;

            foreach ($headers as $header) {
                $this->sheet->setCellValue($col . $row, $header);
                $this->sheet->getStyle($col . $row)->getFont()->setBold(true);
                $this->sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
                $this->sheet->getStyle($col . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $col++;
            }

            // Data transaksi
            $row++;
            $no = 1;
            $totalPendapatan = 0;
            
            foreach ($data as $produk) {
                $this->sheet->setCellValue('A' . $row, $no);
                $this->sheet->setCellValue('B' . $row, $produk->nama_produk ?? $produk['nama_produk']);
                $this->sheet->setCellValue('C' . $row, $produk->total_terjual ?? $produk['total_terjual']);
                $this->sheet->setCellValue('D' . $row, 'Rp ' . number_format($produk->total_pendapatan ?? $produk['total_pendapatan'], 0, ',', '.'));
                $this->sheet->setCellValue('E' . $row, $produk->kasir ?? $produk['kasir']);
                $this->sheet->setCellValue('F' . $row, date('d/m/Y', strtotime($produk->tanggal ?? $produk['tanggal'])));
                
                $totalPendapatan += ($produk->total_pendapatan ?? $produk['total_pendapatan']);
                
                // Style border untuk setiap baris
                for ($i = 'A'; $i <= 'F'; $i++) {
                    $this->sheet->getStyle($i . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                
                $row++;
                $no++;
            }

            // Total row
            $row++;
            $this->sheet->setCellValue('A' . $row, 'TOTAL');
            $this->sheet->setCellValue('D' . $row, 'Rp ' . number_format($totalPendapatan, 0, ',', '.'));
            $this->sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $this->sheet->getStyle('D' . $row)->getFont()->setBold(true);
            $this->sheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFFCC');

            // Auto size column
            foreach (range('A', 'F') as $columnID) {
                $this->sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Simpan file
            $writer = new Xlsx($this->spreadsheet);
            
            // Set header untuk download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '_' . date('Ymd_His') . '.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            log_message('error', 'Export Excel Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Export laporan penjualan pulsa ke Excel
     */
    public function exportLaporanPulsa($data, $summary, $startDate = null, $endDate = null, $filename = 'laporan-penjualan-pulsa')
    {
        try {
            // Set judul
            $this->sheet->setCellValue('A1', 'LAPORAN PENJUALAN PULSA');
            $this->sheet->mergeCells('A1:K1');
            $this->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $this->sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Set periode
            $periode = $startDate && $endDate ? 
                "Periode: " . date('d/m/Y', strtotime($startDate)) . " - " . date('d/m/Y', strtotime($endDate)) :
                "Periode: Semua Data";
            
            $this->sheet->setCellValue('A2', $periode);
            $this->sheet->mergeCells('A2:K2');
            $this->sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $this->sheet->getStyle('A2')->getFont()->setItalic(true);

            // Set summary
            $this->sheet->setCellValue('A4', 'TOTAL TRANSAKSI:');
            $this->sheet->setCellValue('B4', number_format($summary['total_transaksi'] ?? 0));
            
            $this->sheet->setCellValue('A5', 'TOTAL PENJUALAN:');
            $this->sheet->setCellValue('B5', 'Rp ' . number_format($summary['total_penjualan'] ?? 0, 0, ',', '.'));
            
            $this->sheet->setCellValue('A6', 'TOTAL KEUNTUNGAN:');
            $this->sheet->setCellValue('B6', 'Rp ' . number_format($summary['total_keuntungan'] ?? 0, 0, ',', '.'));
            
            $this->sheet->getStyle('A4:A6')->getFont()->setBold(true);

            // Header tabel
            $headers = ['No', 'No Transaksi', 'Tanggal', 'Provider', 'Nominal', 'No Tujuan', 'Harga Jual', 'Keuntungan', 'Pembayaran', 'Status'];
            $col = 'A';
            $row = 8;

            foreach ($headers as $header) {
                $this->sheet->setCellValue($col . $row, $header);
                $this->sheet->getStyle($col . $row)->getFont()->setBold(true);
                $this->sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
                $this->sheet->getStyle($col . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $col++;
            }

            // Data transaksi
            $row++;
            $no = 1;
            
            foreach ($data as $trx) {
                $this->sheet->setCellValue('A' . $row, $no);
                $this->sheet->setCellValue('B' . $row, $trx['no_transaksi']);
                $this->sheet->setCellValue('C' . $row, date('d/m/Y H:i', strtotime($trx['created_at'])));
                $this->sheet->setCellValue('D' . $row, $trx['nama_provider'] ?? '-');
                $this->sheet->setCellValue('E' . $row, number_format($trx['nominal_paket'] ?? $trx['nominal'], 0, ',', '.'));
                $this->sheet->setCellValue('F' . $row, $trx['no_tujuan']);
                $this->sheet->setCellValue('G' . $row, 'Rp ' . number_format($trx['harga_jual'], 0, ',', '.'));
                $this->sheet->setCellValue('H' . $row, 'Rp ' . number_format($trx['keuntungan'], 0, ',', '.'));
                $this->sheet->setCellValue('I' . $row, ucfirst($trx['metode_pembayaran']));
                $this->sheet->setCellValue('J' . $row, ucfirst($trx['status']));
                
                // Style border untuk setiap baris
                for ($i = 'A'; $i <= 'J'; $i++) {
                    $this->sheet->getStyle($i . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                
                $row++;
                $no++;
            }

            // Auto size column
            foreach (range('A', 'J') as $columnID) {
                $this->sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Set format angka untuk kolom tertentu
            $this->sheet->getStyle('E8:E' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $this->sheet->getStyle('G8:H' . $row)->getNumberFormat()->setFormatCode('#,##0');

            // Simpan file
            $writer = new Xlsx($this->spreadsheet);
            
            // Set header untuk download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '_' . date('Ymd_His') . '.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            log_message('error', 'Export Excel Error: ' . $e->getMessage());
            return false;
        }
    }
}