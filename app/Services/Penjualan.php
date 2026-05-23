<?php

namespace App\Services;

use App\Models\PenjualanDetail;

class Penjualan
{
    protected $penjualanDetailModel;

    public function __construct()
    {
        $this->penjualanDetailModel = new PenjualanDetail();
    }

    public function getSoldProduct(?string $start = null, ?string $end = null): array
    {
        try {
            $data = $this->penjualanDetailModel->findAllSoldProduk($start, $end);

            return [
                'success' => true,
                'data'    => $data ?? [],
            ];
        } catch (\Throwable $th) {
            log_message('error', $th->getMessage());
            return [
                'success' => false,
                'data'    => [],
            ];
        }
    }
}
