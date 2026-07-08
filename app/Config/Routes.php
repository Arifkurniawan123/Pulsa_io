<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Auth;
use App\Controllers\Dashboard;
use App\Controllers\Kasir;
use App\Controllers\Kategori;
use App\Controllers\Penjualan;
use App\Controllers\Produk;
use App\Controllers\Satuan;
use App\Controllers\User;
use App\Controllers\LaporanPulsa;
use App\Controllers\Nominal;
use App\Controllers\Provider;
use App\Controllers\Digiflazz;
use App\Controllers\TopupSaldo;
use App\Controllers\TopupEwalletBank;
use App\Controllers\DebugJWT;

/**
 * @var RouteCollection $routes
 */

// ============================================================
// WEB ROUTES (tanpa prefix api)
// ============================================================

// Public Web Routes
$routes->get('/login', [Auth::class, 'index']);
$routes->post('/login/attempt', [Auth::class, 'attemptLogin']);

// Protected Web Routes (auth + admin)
$routes->group('', ['filter' => 'auth'], function ($routes) {
    // Admin-only routes
    $routes->group('', ['filter' => 'admin'], function ($routes) {
        // Dashboard
        $routes->get('/', fn() => redirect()->to('/dashboard'));
        $routes->get('/dashboard', [Dashboard::class, 'index']);

        // ========== MASTER DATA ==========
        // Produk
        $routes->group('master-data/produk', function ($routes) {
            $routes->get('/', [Produk::class, 'index']);
            $routes->get('create', [Produk::class, 'create']);
            $routes->post('store', [Produk::class, 'store']);
            $routes->get('edit/(:hash)', [Produk::class, 'edit']);
            $routes->post('update/(:hash)', [Produk::class, 'update']);
            $routes->post('delete/(:hash)', [Produk::class, 'destroy']);
        });

        // Kategori
        $routes->group('master-data/kategori', function ($routes) {
            $routes->get('/', [Kategori::class, 'index']);
            $routes->get('create', [Kategori::class, 'create']);
            $routes->post('store', [Kategori::class, 'store']);
            $routes->get('edit/(:num)', [Kategori::class, 'edit']);
            $routes->post('update/(:num)', [Kategori::class, 'update']);
            $routes->post('delete/(:num)', [Kategori::class, 'destroy']);
        });

        // Satuan
        $routes->group('master-data/satuan', function ($routes) {
            $routes->get('/', [Satuan::class, 'index']);
            $routes->get('create', [Satuan::class, 'create']);
            $routes->post('store', [Satuan::class, 'store']);
            $routes->get('edit/(:num)', [Satuan::class, 'edit']);
            $routes->post('update/(:num)', [Satuan::class, 'update']);
            $routes->post('delete/(:num)', [Satuan::class, 'destroy']);
        });

        // ========== SETTING ==========
        // User
        $routes->group('setting/user', function ($routes) {
            $routes->get('/', [User::class, 'index']);
            $routes->get('create', [User::class, 'create']);
            $routes->post('store', [User::class, 'store']);
            $routes->get('edit/(:hash)', [User::class, 'edit']);
            $routes->post('update/(:hash)', [User::class, 'update']);
            $routes->post('delete/(:hash)', [User::class, 'destroy']);
        });

        // ========== NOMINAL ==========
        $routes->group('nominal', function ($routes) {
            $routes->get('/', [Nominal::class, 'index']);
            $routes->get('create', [Nominal::class, 'create']);
            $routes->post('store', [Nominal::class, 'store']);
            $routes->get('edit/(:num)', [Nominal::class, 'edit']);
            $routes->post('update/(:num)', [Nominal::class, 'update']);
            $routes->post('delete/(:num)', [Nominal::class, 'delete']);
        });

        // ========== PROVIDER ==========
        $routes->group('provider', function ($routes) {
            $routes->get('/', [Provider::class, 'index']);
            $routes->get('create', [Provider::class, 'create']);
            $routes->post('store', [Provider::class, 'store']);
            $routes->get('edit/(:num)', [Provider::class, 'edit']);
            $routes->post('update/(:num)', [Provider::class, 'update']);
            $routes->post('delete/(:num)', [Provider::class, 'delete']);
        });

        // ========== LAPORAN ==========
        // Laporan Pulsa
        $routes->group('laporan-pulsa', function ($routes) {
            $routes->get('/', [LaporanPulsa::class, 'index']);
            $routes->get('create', [LaporanPulsa::class, 'create']);
            $routes->post('store', [LaporanPulsa::class, 'store']);
            $routes->get('edit/(:num)', [LaporanPulsa::class, 'edit']);
            $routes->post('update/(:num)', [LaporanPulsa::class, 'update']);
            $routes->post('delete/(:num)', [LaporanPulsa::class, 'delete']);
            $routes->get('export-excel', [LaporanPulsa::class, 'exportExcel']);
            $routes->get('export-pdf', [LaporanPulsa::class, 'exportPDF']);
        });

        // Laporan Penjualan
        $routes->get('laporan/penjualan', [Penjualan::class, 'index']);
        $routes->get('penjualan', [Penjualan::class, 'index']);
        $routes->get('penjualan/export-produk-excel', [Penjualan::class, 'exportProdukExcel']);
        $routes->get('penjualan/export-produk-pdf', [Penjualan::class, 'exportProdukPDF']);
        $routes->get('penjualan/export-pulsa-excel', [Penjualan::class, 'exportPulsaExcel']);
        $routes->get('penjualan/export-pulsa-pdf', [Penjualan::class, 'exportPulsaPDF']);
    });

    // ========== KASIR (Semua user login bisa akses) ==========
    $routes->group('menu/kasir', function ($routes) {
        $routes->get('/', [Kasir::class, 'index']);
        $routes->post('add', [Kasir::class, 'add']);
        $routes->post('remove', [Kasir::class, 'remove']);
        $routes->post('checkout', [Kasir::class, 'checkout']);
    });

    // Logout
    $routes->post('/logout', [Auth::class, 'attemptLogout']);
});


// ============================================================
// API ROUTES (prefix /api)
// ============================================================

$routes->group('api', ['filter' => 'cors'], function ($routes) {
    
    // ========== PUBLIC API (tanpa token) ==========
    $routes->post('login', [Auth::class, 'attemptLogin']);
    $routes->post('logout', [Auth::class, 'attemptLogout']);
    $routes->get('debug-jwt', [DebugJWT::class, 'index']);
    $routes->post('debug-jwt/test-decode', [DebugJWT::class, 'testDecode']);

    // ========== PROTECTED API (wajib token JWT) ==========
    $routes->group('', ['filter' => 'jwt'], function ($routes) {
        
        // Dashboard
        $routes->get('dashboard', [Dashboard::class, 'index']);

        // ========== API PROVIDER ==========
        $routes->group('provider', function ($routes) {
            $routes->get('/', [Provider::class, 'index']);                     // GET ALL + FILTER
            $routes->get('allowed', [Provider::class, 'getAllowed']);         // GET ALLOWED
            $routes->get('(:num)', [Provider::class, 'edit/$1']);             // GET BY ID
            $routes->post('store', [Provider::class, 'store']);               // CREATE
            $routes->put('update/(:num)', [Provider::class, 'update/$1']);    // UPDATE (PUT)
            $routes->delete('delete/(:num)', [Provider::class, 'delete/$1']); // DELETE
        });

        // ========== API NOMINAL ==========
        $routes->group('nominal', function ($routes) {
            $routes->get('/', [Nominal::class, 'index']);                     // GET ALL + FILTER
            $routes->get('provider/(:num)', [Nominal::class, 'getByProvider/$1']); // GET BY PROVIDER
            $routes->get('(:num)', [Nominal::class, 'edit/$1']);              // GET BY ID
            $routes->post('store', [Nominal::class, 'store']);                // CREATE
            $routes->put('update/(:num)', [Nominal::class, 'update/$1']);     // UPDATE (PUT)
            $routes->delete('delete/(:num)', [Nominal::class, 'delete/$1']);  // DELETE
        });

        // ========== API PRODUK ==========
        $routes->group('produk', function ($routes) {
            $routes->get('/', [Produk::class, 'index']);                      // GET ALL
            $routes->get('(:hash)', [Produk::class, 'edit/$1']);              // GET BY ID
            $routes->post('store', [Produk::class, 'store']);                 // CREATE
            $routes->put('update/(:hash)', [Produk::class, 'update/$1']);     // UPDATE (PUT)
            $routes->delete('delete/(:hash)', [Produk::class, 'destroy/$1']); // DELETE
        });

        // ========== API KATEGORI ==========
        $routes->group('kategori', function ($routes) {
            $routes->get('/', [Kategori::class, 'index']);                   // GET ALL
            $routes->get('(:num)', [Kategori::class, 'edit/$1']);            // GET BY ID
            $routes->post('store', [Kategori::class, 'store']);              // CREATE
            $routes->put('update/(:num)', [Kategori::class, 'update/$1']);   // UPDATE (PUT)
            $routes->delete('delete/(:num)', [Kategori::class, 'destroy/$1']);// DELETE
        });

        // ========== API SATUAN ==========
        $routes->group('satuan', function ($routes) {
            $routes->get('/', [Satuan::class, 'index']);                     // GET ALL
            $routes->get('(:num)', [Satuan::class, 'edit/$1']);              // GET BY ID
            $routes->post('store', [Satuan::class, 'store']);                // CREATE
            $routes->put('update/(:num)', [Satuan::class, 'update/$1']);     // UPDATE (PUT)
            $routes->delete('delete/(:num)', [Satuan::class, 'destroy/$1']); // DELETE
        });

        // ========== API USER ==========
        $routes->group('user', function ($routes) {
            $routes->get('/', [User::class, 'index']);                       // GET ALL
            $routes->get('(:hash)', [User::class, 'edit/$1']);               // GET BY ID
            $routes->post('store', [User::class, 'store']);                  // CREATE
            $routes->put('update/(:hash)', [User::class, 'update/$1']);      // UPDATE (PUT)
            $routes->delete('delete/(:hash)', [User::class, 'destroy/$1']);  // DELETE
        });

        // ========== API KASIR ==========
        $routes->group('kasir', function ($routes) {
            $routes->get('/', [Kasir::class, 'index']);                      // GET ALL
            $routes->get('nominals/(:num)', [Kasir::class, 'getNominals/$1']); // GET NOMINAL
            $routes->post('add', [Kasir::class, 'add']);                     // ADD TO CART
            $routes->post('remove', [Kasir::class, 'remove']);               // REMOVE FROM CART
            $routes->post('checkout', [Kasir::class, 'checkout']);           // CHECKOUT
        });

        // ========== API LAPORAN PULSA ==========
        $routes->group('laporan-pulsa', function ($routes) {
            $routes->get('/', [LaporanPulsa::class, 'index']);               // GET ALL + FILTER
            $routes->get('providers', [LaporanPulsa::class, 'getProviders']);// GET PROVIDERS
            $routes->get('nominals/(:num)', [LaporanPulsa::class, 'getNominals/$1']); // GET NOMINAL BY PROVIDER
            $routes->get('(:num)', [LaporanPulsa::class, 'edit/$1']);        // GET BY ID
            $routes->post('store', [LaporanPulsa::class, 'store']);          // CREATE
            $routes->put('update/(:num)', [LaporanPulsa::class, 'update/$1']);// UPDATE (PUT)
            $routes->delete('delete/(:num)', [LaporanPulsa::class, 'delete/$1']);// DELETE
        });

        // ========== API PENJUALAN ==========
        $routes->get('penjualan', [Penjualan::class, 'index']);              // GET ALL + FILTER

        // ========== API DIGIFLAZZ ==========
        $routes->group('digiflazz', function ($routes) {
            $routes->get('pricelist', [Digiflazz::class, 'priceList']);
            $routes->post('sync', [Digiflazz::class, 'syncProducts']);
            $routes->post('topup', [Digiflazz::class, 'topup']);
        });

        // ========== API TOPUP SALDO ==========
        $routes->group('topup-saldo', function ($routes) {
            $routes->get('saldo', [TopupSaldo::class, 'getSaldo']);
            $routes->get('history', [TopupSaldo::class, 'getHistory']);
            $routes->post('simulasi', [TopupSaldo::class, 'simulasi']);
        });

        // ========== API TOPUP E-WALLET ==========
        $routes->group('topup-ewallet', function ($routes) {
            $routes->options('/', [TopupEwalletBank::class, 'options']);
            $routes->post('/', [TopupEwalletBank::class, 'topupEwalletInitiate']);
            $routes->post('confirm/(:any)', [TopupEwalletBank::class, 'topupEwalletConfirm/$1']);
            $routes->get('history', [TopupEwalletBank::class, 'topupEwalletHistory']);
            $routes->get('supported-methods', [TopupEwalletBank::class, 'getSupportedEwallets']);
        });

        // ========== API TOPUP BANK ==========
        $routes->group('topup-bank', function ($routes) {
            $routes->options('/', [TopupEwalletBank::class, 'options']);
            $routes->post('/', [TopupEwalletBank::class, 'topupBankInitiate']);
            $routes->post('confirm/(:any)', [TopupEwalletBank::class, 'topupBankConfirm/$1']);
            $routes->get('history', [TopupEwalletBank::class, 'topupBankHistory']);
            $routes->get('supported-banks', [TopupEwalletBank::class, 'getSupportedBanks']);
        });
    });
});