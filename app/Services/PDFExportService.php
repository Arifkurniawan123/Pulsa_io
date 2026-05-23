<?php
// app/Services/PDFExportService.php

namespace App\Services;

use TCPDF;

class PDFExportService extends TCPDF
{
    protected $startDate;
    protected $endDate;
    protected $reportTitle;
    protected $companyName;

    public function __construct($orientation = 'L', $unit = 'mm', $format = 'A4')
    {
        parent::__construct($orientation, $unit, $format);
        $this->reportTitle = 'LAPORAN PENJUALAN PULSA';

        $this->Ln(3);

        $this->companyName = 'TOKO PULSA';
    }

    public function setPeriod($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    // Page header
    public function Header()
    {
        // Logo (opsional)
        $image_file = FCPATH . 'assets/img/logo.jpg';
        if (file_exists($image_file)) {
            $this->Image($image_file, 15, 10, 20, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        
        // Judul utama
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 10, $this->reportTitle, 0, 1, 'C', 0, '', 0, false, 'M', 'M');

        $this->Ln(3);
        
        // Nama perusahaan
        $this->SetFont('helvetica', 'I', 10);
        $this->Cell(0, 5, $this->companyName, 0, 1, 'C', 0, '', 0, false, 'M', 'M');

        $this->Ln(3);
        
        // Periode
        $periode = $this->startDate && $this->endDate ? 
            'Periode: ' . date('d/m/Y', strtotime($this->startDate)) . ' - ' . date('d/m/Y', strtotime($this->endDate)) :
            'Periode: Semua Data';
        
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 5, $periode, 0, 1, 'C', 0, '', 0, false, 'M', 'M');

        $this->Ln(3);
        
        // Tanggal cetak
        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 5, 'Dicetak: ' . date('d/m/Y H:i:s'), 0, 1, 'C', 0, '', 0, false, 'M', 'M');
        
        // Garis pemisah
        $this->Line(15, 40, $this->getPageWidth() - 15, 40);
        
        $this->Ln(5);
    }

    // Page footer
    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        
        // Garis pemisah footer
        $this->Line(15, $this->GetY(), $this->getPageWidth() - 15, $this->GetY());
        
        $this->SetFont('helvetica', 'I', 8);
        
        // Halaman
        $this->Cell(0, 10, 'Halaman ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 
            0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

    /**
     * Export laporan penjualan produk ke PDF
     */
    public function exportLaporanProduk($data, $startDate = null, $endDate = null, $filename = 'laporan-penjualan-produk')
    {
        try {
            $this->reportTitle = 'LAPORAN PENJUALAN PRODUK';
            $this->setPeriod($startDate, $endDate);
            
            // set document information
            $this->SetCreator('Sistem Pulsa');
            $this->SetAuthor('Admin');
            $this->SetTitle($this->reportTitle);
            $this->SetSubject('Laporan Penjualan Produk');
            $this->SetKeywords('Laporan, Penjualan, Produk');

            // set default monospaced font
            $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $this->SetMargins(15, 50, 15);
            $this->SetHeaderMargin(10);
            $this->SetFooterMargin(10);

            // set auto page breaks
            $this->SetAutoPageBreak(TRUE, 15);

            // set image scale factor
            $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // add a page
            $this->AddPage('P');

            // Data tabel
            if (!empty($data)) {
                // Header tabel
                $headers = ['No', 'Nama Produk', 'Jumlah Terjual', 'Total Pendapatan', 'Nama Kasir', 'Tanggal'];
                $columnWidths = [12, 50, 25, 35, 35, 30];
                
                // Set header style
                $this->SetFillColor(70, 130, 180); // Steel blue
                $this->SetTextColor(255, 255, 255);
                $this->SetDrawColor(70, 130, 180);
                $this->SetLineWidth(0.3);
                $this->SetFont('helvetica', 'B', 9);
                
                $x = 15;
                $y = $this->GetY();
                $height = 7;
                
                foreach ($headers as $i => $header) {
                    $this->Rect($x, $y, $columnWidths[$i], $height, 'DF');
                    $this->SetXY($x, $y + 1);
                    $this->Cell($columnWidths[$i], $height - 1, $header, 0, 0, 'C');
                    $x += $columnWidths[$i];
                }
                
                $this->SetY($y + $height);
                $this->SetTextColor(0, 0, 0);
                
                // Data rows
                $this->SetFont('helvetica', '', 8);
                $rowNum = 1;
                $fill = false;
                $totalPendapatan = 0;
                
                foreach ($data as $produk) {
                    // Cek jika perlu halaman baru
                    if ($this->GetY() > 250) {
                        $this->AddPage('P');
                        // Ulangi header
                        $x = 15;
                        $y = $this->GetY();
                        $this->SetFillColor(70, 130, 180);
                        $this->SetTextColor(255, 255, 255);
                        $this->SetFont('helvetica', 'B', 9);
                        
                        foreach ($headers as $i => $header) {
                            $this->Rect($x, $y, $columnWidths[$i], $height, 'DF');
                            $this->SetXY($x, $y + 1);
                            $this->Cell($columnWidths[$i], $height - 1, $header, 0, 0, 'C');
                            $x += $columnWidths[$i];
                        }
                        
                        $this->SetY($y + $height);
                        $this->SetTextColor(0, 0, 0);
                        $this->SetFont('helvetica', '', 8);
                        $fill = false;
                    }
                    
                    // Warna background bergantian
                    if ($fill) {
                        $this->SetFillColor(245, 245, 245); // Abu-abu muda
                    } else {
                        $this->SetFillColor(255, 255, 255); // Putih
                    }
                    
                    $this->SetDrawColor(200, 200, 200);
                    
                    $x = 15;
                    $y = $this->GetY();
                    $height = 8;
                    $namaProdukt = $produk->nama_produk ?? $produk['nama_produk'];
                    $totalTerjual = $produk->total_terjual ?? $produk['total_terjual'];
                    $totalPend = $produk->total_pendapatan ?? $produk['total_pendapatan'];
                    $kasir = $produk->kasir ?? $produk['kasir'];
                    $tanggal = $produk->tanggal ?? $produk['tanggal'];
                    
                    $totalPendapatan += $totalPend;
                    
                    // No
                    $this->Rect($x, $y, $columnWidths[0], $height, 'DF');
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidths[0], $height, $rowNum, 0, 0, 'C');
                    $x += $columnWidths[0];
                    
                    // Nama Produk
                    $this->Rect($x, $y, $columnWidths[1], $height, 'DF');
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidths[1], $height, substr($namaProdukt, 0, 30), 0, 0, 'L');
                    $x += $columnWidths[1];
                    
                    // Jumlah Terjual
                    $this->Rect($x, $y, $columnWidths[2], $height, 'DF');
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidths[2], $height, $totalTerjual, 0, 0, 'C');
                    $x += $columnWidths[2];
                    
                    // Total Pendapatan
                    $this->Rect($x, $y, $columnWidths[3], $height, 'DF');
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidths[3], $height, 'Rp ' . number_format($totalPend, 0, ',', '.'), 0, 0, 'R');
                    $x += $columnWidths[3];
                    
                    // Nama Kasir
                    $this->Rect($x, $y, $columnWidths[4], $height, 'DF');
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidths[4], $height, substr($kasir, 0, 20), 0, 0, 'L');
                    $x += $columnWidths[4];
                    
                    // Tanggal
                    $this->Rect($x, $y, $columnWidths[5], $height, 'DF');
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidths[5], $height, date('d/m/Y', strtotime($tanggal)), 0, 0, 'C');
                    
                    $this->SetY($y + $height);
                    $rowNum++;
                    $fill = !$fill;
                }
                
                // Total row
                $this->SetFillColor(255, 255, 204); // Light yellow
                $this->SetFont('helvetica', 'B', 9);
                $x = 15;
                $y = $this->GetY();
                
                // No & Nama Produk cells (merged)
                $this->Rect($x, $y, $columnWidths[0] + $columnWidths[1] + $columnWidths[2], 8, 'DF');
                $this->SetXY($x + 5, $y);
                $this->Cell($columnWidths[0] + $columnWidths[1] + $columnWidths[2] - 5, 8, 'TOTAL', 0, 0, 'L');
                $x += $columnWidths[0] + $columnWidths[1] + $columnWidths[2];
                
                // Total Pendapatan
                $this->Rect($x, $y, $columnWidths[3], 8, 'DF');
                $this->SetXY($x, $y);
                $this->Cell($columnWidths[3], 8, 'Rp ' . number_format($totalPendapatan, 0, ',', '.'), 0, 0, 'R');
                $x += $columnWidths[3];
                
                // Kasir & Tanggal cells (merged)
                $this->Rect($x, $y, $columnWidths[4] + $columnWidths[5], 8, 'DF');
                
            } else {
                $this->SetFont('helvetica', '', 12);
                $this->Cell(0, 20, 'Tidak ada data transaksi', 0, 1, 'C');
            }
            
            // Informasi footer tambahan
            $this->SetY(-25);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 5, '* Laporan ini dibuat secara otomatis oleh sistem', 0, 1, 'L');
            $this->Cell(0, 5, '* Data diambil dari database tanggal ' . date('d/m/Y H:i:s'), 0, 1, 'L');
            
            // Output PDF
            $filename = $filename . '_' . date('Ymd_His') . '.pdf';
            $this->Output($filename, 'D');
            exit;

        } catch (\Exception $e) {
            log_message('error', 'Export PDF Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Export laporan penjualan pulsa ke PDF
     */
    public function exportLaporanPulsa($data, $summary, $startDate = null, $endDate = null, $filename = 'laporan-penjualan-pulsa')
    {
        try {
            $this->reportTitle = 'LAPORAN PENJUALAN PULSA';
            $this->setPeriod($startDate, $endDate);
            
            // set document information
            $this->SetCreator('Sistem Pulsa');
            $this->SetAuthor('Admin');
            $this->SetTitle($this->reportTitle);
            $this->SetSubject('Laporan Penjualan Pulsa');
            $this->SetKeywords('Laporan, Penjualan, Pulsa');

            // set default monospaced font
            $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $this->SetMargins(15, 50, 15);
            $this->SetHeaderMargin(10);
            $this->SetFooterMargin(10);

            // set auto page breaks
            $this->SetAutoPageBreak(TRUE, 15);

            // set image scale factor
            $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // add a page (Landscape orientation)
            $this->AddPage('L');

            // Buat summary box
            $this->createSummaryBox($summary);
            
            $this->Ln(5);

            // Data tabel
            if (!empty($data)) {
                // Header tabel
                $headers = ['No', 'No Transaksi', 'Tanggal', 'Provider', 'Nominal', 'No Tujuan', 'Harga Jual', 'Keuntungan', 'Pembayaran', 'Status'];
                $columnWidths = [12, 35, 30, 25, 25, 35, 30, 30, 25, 20];
                
                $this->createTableHeader($headers, $columnWidths);
                
                // Data rows
                $this->SetFont('helvetica', '', 8);
                $rowNum = 1;
                $fill = false;
                
                foreach ($data as $trx) {
                    // Cek jika perlu halaman baru
                    if ($this->GetY() > 180) {
                        $this->AddPage('L');
                        $this->createTableHeader($headers, $columnWidths);
                        $this->SetFont('helvetica', '', 8);
                        $fill = false;
                    }
                    
                    // Warna background bergantian
                    if ($fill) {
                        $this->SetFillColor(245, 245, 245); // Abu-abu muda
                    } else {
                        $this->SetFillColor(255, 255, 255); // Putih
                    }
                    
                    $this->SetDrawColor(200, 200, 200);
                    
                    $x = 15;
                    $y = $this->GetY();
                    $height = 8;
                    
                    // Cell 1: No
                    $this->Rect($x, $y, $columnWidths[0], $height, 'DF');
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidths[0], $height, $rowNum, 0, 0, 'C');
                    $x += $columnWidths[0];
                    
                    // Cell 2: No Transaksi
                    $this->Rect($x, $y, $columnWidths[1], $height, 'DF');
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidths[1], $height, $trx['no_transaksi'], 0, 0, 'L');
                    $x += $columnWidths[1];
                    
                    // Cell 3: Tanggal
                    $this->Rect($x, $y, $columnWidths[2], $height, 'DF');
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidths[2], $height, date('d/m/Y H:i', strtotime($trx['created_at'])), 0, 0, 'C');
                    $x += $columnWidths[2];
                    
                    // Cell 4: Provider
                    $this->Rect($x, $y, $columnWidths[3], $height, 'DF');
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidths[3], $height, $trx['nama_provider'] ?? '-', 0, 0, 'L');
                    $x += $columnWidths[3];
                    
                    // Cell 5: Nominal
                    $this->Rect($x, $y, $columnWidths[4], $height, 'DF');
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidths[4], $height, number_format($trx['nominal_paket'] ?? $trx['nominal'], 0, ',', '.'), 0, 0, 'R');
                    $x += $columnWidths[4];
                    
                    // Cell 6: No Tujuan
                    $this->Rect($x, $y, $columnWidths[5], $height, 'DF');
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidths[5], $height, $trx['no_tujuan'], 0, 0, 'L');
                    $x += $columnWidths[5];
                    
                    // Cell 7: Harga Jual
                    $this->Rect($x, $y, $columnWidths[6], $height, 'DF');
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidths[6], $height, 'Rp ' . number_format($trx['harga_jual'], 0, ',', '.'), 0, 0, 'R');
                    $x += $columnWidths[6];
                    
                    // Cell 8: Keuntungan
                    $this->Rect($x, $y, $columnWidths[7], $height, 'DF');
                    $this->SetXY($x, $y);
                    $this->SetTextColor(0, 128, 0);
                    $this->Cell($columnWidths[7], $height, 'Rp ' . number_format($trx['keuntungan'], 0, ',', '.'), 0, 0, 'R');
                    $this->SetTextColor(0, 0, 0);
                    $x += $columnWidths[7];
                    
                    // Cell 9: Pembayaran
                    $this->Rect($x, $y, $columnWidths[8], $height, 'DF');
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidths[8], $height, ucfirst($trx['metode_pembayaran']), 0, 0, 'C');
                    $x += $columnWidths[8];
                    
                    // Cell 10: Status
                    $this->Rect($x, $y, $columnWidths[9], $height, 'DF');
                    $this->SetXY($x, $y);
                    
                    // Warna status
                    $status = ucfirst($trx['status']);
                    if ($trx['status'] == 'sukses') {
                        $this->SetFillColor(144, 238, 144);
                        $this->SetTextColor(0, 100, 0);
                    } elseif ($trx['status'] == 'gagal') {
                        $this->SetFillColor(255, 182, 193);
                        $this->SetTextColor(139, 0, 0);
                    } else {
                        $this->SetFillColor(255, 255, 224);
                        $this->SetTextColor(218, 165, 32);
                    }
                    
                    $this->Rect($x, $y, $columnWidths[9], $height, 'F');
                    $this->Rect($x, $y, $columnWidths[9], $height, 'D');
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidths[9], $height, $status, 0, 0, 'C');
                    
                    if ($fill) {
                        $this->SetFillColor(245, 245, 245);
                    } else {
                        $this->SetFillColor(255, 255, 255);
                    }
                    $this->SetTextColor(0, 0, 0);
                    
                    $this->SetY($y + $height);
                    $rowNum++;
                    $fill = !$fill;
                }
            } else {
                $this->SetFont('helvetica', '', 12);
                $this->Cell(0, 20, 'Tidak ada data transaksi', 0, 1, 'C');
            }
            
            // Informasi footer tambahan
            $this->SetY(-25);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 5, '* Laporan ini dibuat secara otomatis oleh sistem', 0, 1, 'L');
            $this->Cell(0, 5, '* Data diambil dari database tanggal ' . date('d/m/Y H:i:s'), 0, 1, 'L');
            
            // Output PDF
            $filename = $filename . '_' . date('Ymd_His') . '.pdf';
            $this->Output($filename, 'D');
            exit;

        } catch (\Exception $e) {
            log_message('error', 'Export PDF Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Membuat kotak summary dengan style menarik
     */
    private function createSummaryBox($summary)
    {
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 8, 'RINGKASAN LAPORAN', 0, 1, 'L');
        $this->Ln(2);
        
        // Background untuk box summary
        $this->SetFillColor(240, 248, 255); // Biru muda
        $this->SetDrawColor(100, 149, 237); // Cornflower blue
        $this->SetLineWidth(0.3);
        
        $this->Rect(15, $this->GetY(), $this->getPageWidth() - 30, 25, 'DF');
        
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(0, 0, 0);
        
        // Posisi awal
        $y = $this->GetY() + 5;
        
        // Total Transaksi
        $this->SetXY(20, $y);
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(40, 6, 'Total Transaksi:', 0, 0, 'L');
        $this->SetFont('helvetica', '', 10);
        $this->Cell(40, 6, number_format($summary['total_transaksi'] ?? 0), 0, 0, 'L');
        
        // Total Penjualan
        $this->SetXY(120, $y);
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(40, 6, 'Total Penjualan:', 0, 0, 'L');
        $this->SetFont('helvetica', '', 10);
        $this->Cell(40, 6, 'Rp ' . number_format($summary['total_penjualan'] ?? 0, 0, ',', '.'), 0, 0, 'L');
        
        // Total Keuntungan
        $this->SetXY(20, $y + 8);
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(40, 6, 'Total Keuntungan:', 0, 0, 'L');
        $this->SetFont('helvetica', '', 10);
        $this->Cell(40, 6, 'Rp ' . number_format($summary['total_keuntungan'] ?? 0, 0, ',', '.'), 0, 1, 'L');
        
        $this->SetY($y + 30);
    }

    /**
     * Membuat header tabel dengan style
     */
    private function createTableHeader($headers, $columnWidths)
    {
        $this->SetFillColor(70, 130, 180); // Steel blue
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(70, 130, 180);
        $this->SetLineWidth(0.3);
        $this->SetFont('helvetica', 'B', 9);
        
        $x = 15;
        $y = $this->GetY();
        $height = 7;
        
        // Gambar header dengan rounded corners (simulasi)
        for ($i = 0; $i < count($headers); $i++) {
            $this->Rect($x, $y, $columnWidths[$i], $height, 'DF', array('all' => array('width' => 0.3)));
            
            // Teks header
            $this->SetXY($x, $y + 1);
            $this->Cell($columnWidths[$i], $height - 1, $headers[$i], 0, 0, 'C');
            
            $x += $columnWidths[$i];
        }
        
        $this->SetY($y + $height);
        $this->SetTextColor(0, 0, 0);
    }
}