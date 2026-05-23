<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\Penjualan as ServicesPenjualan;
use App\Services\LaporanPulsa as ServicesLaporanPulsa;
use App\Services\ExcelExportService;
use App\Services\PDFExportService;

class Penjualan extends BaseController
{
    protected $penjualanService;
    protected $laporanPulsaService;
    protected $excelExportService;
    protected $pdfExportService;

    public function __construct()
    {
        $this->penjualanService    = new ServicesPenjualan();
        $this->laporanPulsaService = new ServicesLaporanPulsa();
        $this->excelExportService  = new ExcelExportService();
        $this->pdfExportService    = new PDFExportService();
    }

    private function isApi(): bool
    {
        return str_contains($this->request->getUri()->getPath(), 'api/');
    }

    public function index()
    {
        $start = $this->request->getGet('start');
        $end   = $this->request->getGet('end');

        $dataPenjualanDetail = $this->penjualanService->getSoldProduct($start, $end);
        $penjualan           = $dataPenjualanDetail['success'] ? $dataPenjualanDetail['data'] : [];

        $dataPulsaDetail = $this->laporanPulsaService->getData($start, $end);
        $pulsa           = $dataPulsaDetail['success'] ? $dataPulsaDetail['data'] : [];

        $summaryPulsa = $this->laporanPulsaService->getSummaryReport($start, $end);
        $pulsaSummary = $summaryPulsa['success'] ? $summaryPulsa['data'] : [];

        if ($this->isApi()) {
            return $this->response->setStatusCode(200)->setJSON([
                'success'      => true,
                'message'      => 'Data laporan penjualan berhasil diambil',
                'code'         => 200,
                'data'         => [
                    'penjualan'    => $penjualan,
                    'pulsa'        => $pulsa,
                    'pulsaSummary' => $pulsaSummary,
                ],
                'start'        => $start,
                'end'          => $end,
            ]);
        }

        return view('penjualan', [
            'page'         => 'penjualan',
            'title'        => 'Pulsa Io - Laporan Penjualan',
            'table_name'   => 'Laporan Penjualan Produk dan Pulsa per Tanggal',
            'penjualan'    => $penjualan,
            'pulsa'        => $pulsa,
            'pulsaSummary' => $pulsaSummary,
            'start'        => $start,
            'end'          => $end,
        ]);
    }

    public function exportProdukExcel()
    {
        $start         = $this->request->getGet('start');
        $end           = $this->request->getGet('end');
        $dataPenjualan = $this->penjualanService->getSoldProduct($start, $end);
        $data          = $dataPenjualan['success'] ? $dataPenjualan['data'] : [];

        if (empty($data)) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diekspor');
        }
        return $this->excelExportService->exportLaporanProduk($data, $start, $end);
    }

    public function exportProdukPDF()
    {
        $start         = $this->request->getGet('start');
        $end           = $this->request->getGet('end');
        $dataPenjualan = $this->penjualanService->getSoldProduct($start, $end);
        $data          = $dataPenjualan['success'] ? $dataPenjualan['data'] : [];

        if (empty($data)) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diekspor');
        }
        return $this->pdfExportService->exportLaporanProduk($data, $start, $end);
    }

    public function exportPulsaExcel()
    {
        $start         = $this->request->getGet('start');
        $end           = $this->request->getGet('end');
        $dataResult    = $this->laporanPulsaService->getData($start, $end);
        $summaryResult = $this->laporanPulsaService->getSummaryReport($start, $end);

        if (!$dataResult['success'] || !$summaryResult['success']) {
            return redirect()->back()->with('error', 'Gagal mengambil data untuk export');
        }
        return $this->excelExportService->exportLaporanPulsa(
            $dataResult['data'], $summaryResult['data'], $start, $end
        );
    }

    public function exportPulsaPDF()
    {
        $start         = $this->request->getGet('start');
        $end           = $this->request->getGet('end');
        $dataResult    = $this->laporanPulsaService->getData($start, $end);
        $summaryResult = $this->laporanPulsaService->getSummaryReport($start, $end);

        if (!$dataResult['success'] || !$summaryResult['success']) {
            return redirect()->back()->with('error', 'Gagal mengambil data untuk export');
        }
        return $this->pdfExportService->exportLaporanPulsa(
            $dataResult['data'], $summaryResult['data'], $start, $end
        );
    }
}