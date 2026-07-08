<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\Dashboard as DashboardService;

class Dashboard extends BaseController
{
    protected $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    public function index()
    {
        $dashboardSummary = $this->dashboardService->getDashboardSummary();

        $isApi = str_contains($this->request->getUri()->getPath(), 'api/');

        if ($isApi) {
            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'message' => 'Data dashboard berhasil diambil',
                'code'    => 200,
                'data'    => $dashboardSummary
            ]);
        }

        $data = [
            'page'              => 'dashboard',
            'title'             => 'Pulsa Io - Dashboard',
            'dashboard_summary' => $dashboardSummary,
        ];
        return view('dashboard', $data);
    }
}