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
use App\Controllers\DebugJWT;

/**
 * @var RouteCollection $routes
 */

// ============================================================
// WEB ROUTES
// ============================================================

$routes->get('/login', [Auth::class, 'index']);
$routes->post('/login/attempt', [Auth::class, 'attemptLogin']);

$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->group('', ['filter' => 'admin'], function ($routes) {
        $routes->get('/', fn() => redirect()->to('/dashboard'));
        $routes->get('/dashboard', [Dashboard::class, 'index']);

        // Master Data (web)
        $routes->get('/master-data/produk', [Produk::class, 'index']);
        $routes->get('/master-data/produk/create', [Produk::class, 'create']);
        $routes->post('/master-data/produk/store', [Produk::class, 'store']);
        $routes->get('/master-data/produk/edit/(:hash)', [Produk::class, 'edit']);
        $routes->post('/master-data/produk/update/(:hash)', [Produk::class, 'update']);
        $routes->post('/master-data/produk/delete/(:hash)', [Produk::class, 'destroy']);

        $routes->get('/master-data/kategori', [Kategori::class, 'index']);
        $routes->get('/master-data/kategori/create', [Kategori::class, 'create']);
        $routes->post('/master-data/kategori/store', [Kategori::class, 'store']);
        $routes->get('/master-data/kategori/edit/(:num)', [Kategori::class, 'edit']);
        $routes->post('/master-data/kategori/update/(:num)', [Kategori::class, 'update']);
        $routes->post('/master-data/kategori/delete/(:num)', [Kategori::class, 'destroy']);

        $routes->get('/master-data/satuan', [Satuan::class, 'index']);
        $routes->get('/master-data/satuan/create', [Satuan::class, 'create']);
        $routes->post('/master-data/satuan/store', [Satuan::class, 'store']);
        $routes->get('/master-data/satuan/edit/(:num)', [Satuan::class, 'edit']);
        $routes->post('/master-data/satuan/update/(:num)', [Satuan::class, 'update']);
        $routes->post('/master-data/satuan/delete/(:num)', [Satuan::class, 'destroy']);

        $routes->get('/setting/user', [User::class, 'index']);
        $routes->get('/setting/user/create', [User::class, 'create']);
        $routes->post('/setting/user/store', [User::class, 'store']);
        $routes->get('/setting/user/edit/(:hash)', [User::class, 'edit']);
        $routes->post('/setting/user/update/(:hash)', [User::class, 'update']);
        $routes->post('/setting/user/delete/(:hash)', [User::class, 'destroy']);

        $routes->get('/laporan/penjualan', [Penjualan::class, 'index']);

        $routes->group('nominal', function ($routes) {
            $routes->get('/', [Nominal::class, 'index']);
            $routes->get('create', [Nominal::class, 'create']);
            $routes->post('store', [Nominal::class, 'store']);
            $routes->get('edit/(:num)', [Nominal::class, 'edit']);
            $routes->post('update/(:num)', [Nominal::class, 'update']);
            $routes->post('delete/(:num)', [Nominal::class, 'delete']);
        });

        $routes->get('provider', 'Provider::index');
        $routes->get('provider/create', 'Provider::create');
        $routes->post('provider/store', 'Provider::store');
        $routes->get('provider/edit/(:num)', 'Provider::edit/$1');
        $routes->post('provider/update/(:num)', 'Provider::update/$1');
        $routes->post('provider/delete/(:num)', 'Provider::delete/$1');

        $routes->group('laporan-pulsa', function ($routes) {
            $routes->get('/', [LaporanPulsa::class, 'index']);
            $routes->get('create', [LaporanPulsa::class, 'create']);
            $routes->post('store', [LaporanPulsa::class, 'store']);
            $routes->get('edit/(:num)', [LaporanPulsa::class, 'edit']);
            $routes->post('update/(:num)', [LaporanPulsa::class, 'update']);
            $routes->post('delete/(:num)', [LaporanPulsa::class, 'delete']);
            $routes->get('export', [LaporanPulsa::class, 'export']);
            $routes->get('export-pdf', [LaporanPulsa::class, 'exportPdf']);
            $routes->get('export-excel', [LaporanPulsa::class, 'exportExcel']);
        });
    });

    $routes->get('/menu/kasir', [Kasir::class, 'index']);
    $routes->post('/menu/kasir/add', [Kasir::class, 'add']);
    $routes->post('/menu/kasir/remove', [Kasir::class, 'remove']);
    $routes->post('/menu/kasir/checkout', [Kasir::class, 'checkout']);

    $routes->get('laporan-pulsa/export-excel', 'LaporanPulsa::exportExcel');
    $routes->get('laporan-pulsa/export-pdf', 'LaporanPulsa::exportPDF');
    $routes->get('penjualan', 'Penjualan::index');
    $routes->get('penjualan/export-produk-excel', 'Penjualan::exportProdukExcel');
    $routes->get('penjualan/export-produk-pdf', 'Penjualan::exportProdukPDF');
    $routes->get('penjualan/export-pulsa-excel', 'Penjualan::exportPulsaExcel');
    $routes->get('penjualan/export-pulsa-pdf', 'Penjualan::exportPulsaPDF');

    $routes->post('/logout', [Auth::class, 'attemptLogout']);
});

// ============================================================
// API ROUTES (untuk Flutter)
// ============================================================

// Public API (tanpa token)
$routes->post('api/login', 'Auth::attemptLogin');
$routes->post('api/logout', 'Auth::attemptLogout');

// Debug JWT (development)
$routes->get('api/debug-jwt', 'DebugJWT::index');
$routes->post('api/debug-jwt/test-decode', 'DebugJWT::testDecode');

// Protected API (wajib token)
$routes->group('api', ['filter' => 'jwt'], function ($routes) {

    $routes->get('dashboard', 'Dashboard::index');

    // Provider API
    $routes->get('provider', 'Provider::index');
    $routes->get('provider/allowed', 'Provider::getAllowed');           // <-- TAMBAHKAN
    $routes->get('provider/(:num)', 'Provider::edit/$1');
    $routes->post('provider/store', 'Provider::store');
    $routes->post('provider/update/(:num)', 'Provider::update/$1');
    $routes->post('provider/delete/(:num)', 'Provider::delete/$1');

    // Nominal API
    $routes->get('nominal', 'Nominal::index');
    $routes->get('nominal/provider/(:num)', 'Nominal::getByProvider/$1'); // <-- TAMBAHKAN
    $routes->get('nominal/(:num)', 'Nominal::edit/$1');
    $routes->post('nominal/store', 'Nominal::store');
    $routes->post('nominal/update/(:num)', 'Nominal::update/$1');
    $routes->post('nominal/delete/(:num)', 'Nominal::delete/$1');

    // Produk API (jika masih perlu)
    $routes->get('produk', 'Produk::index');
    $routes->post('produk/store', 'Produk::store');
    $routes->get('produk/(:hash)', 'Produk::edit/$1');
    $routes->post('produk/update/(:hash)', 'Produk::update/$1');
    $routes->post('produk/delete/(:hash)', 'Produk::destroy/$1');

    // Kategori API
    $routes->get('kategori', 'Kategori::index');
    $routes->post('kategori/store', 'Kategori::store');
    $routes->get('kategori/(:num)', 'Kategori::edit/$1');
    $routes->post('kategori/update/(:num)', 'Kategori::update/$1');
    $routes->post('kategori/delete/(:num)', 'Kategori::destroy/$1');

    // Satuan API
    $routes->get('satuan', 'Satuan::index');
    $routes->post('satuan/store', 'Satuan::store');
    $routes->get('satuan/(:num)', 'Satuan::edit/$1');
    $routes->post('satuan/update/(:num)', 'Satuan::update/$1');
    $routes->post('satuan/delete/(:num)', 'Satuan::destroy/$1');

    // Kasir API
    $routes->get('kasir', 'Kasir::index');
    $routes->post('kasir/add', 'Kasir::add');
    $routes->post('kasir/remove', 'Kasir::remove');
    $routes->post('kasir/checkout', 'Kasir::checkout');
    $routes->get('kasir/nominals/(:num)', 'Kasir::getNominals/$1');

    // Laporan Pulsa API
    $routes->get('laporan-pulsa', 'LaporanPulsa::index');
    $routes->post('laporan-pulsa/store', 'LaporanPulsa::store');
    $routes->get('laporan-pulsa/(:num)', 'LaporanPulsa::edit/$1');
    $routes->post('laporan-pulsa/update/(:num)', 'LaporanPulsa::update/$1');
    $routes->post('laporan-pulsa/delete/(:num)', 'LaporanPulsa::delete/$1');

    // Laporan Penjualan API
    $routes->get('penjualan', 'Penjualan::index');

    // User API
    $routes->get('user', 'User::index');
    $routes->post('user/store', 'User::store');
    $routes->get('user/(:hash)', 'User::edit/$1');
    $routes->post('user/update/(:hash)', 'User::update/$1');
    $routes->post('user/delete/(:hash)', 'User::destroy/$1');

    // DIGIFLAZZ API
    $routes->group('digiflazz', function ($routes) {
        $routes->get('pricelist', 'Digiflazz::priceList');
        $routes->post('sync', 'Digiflazz::syncProducts');
        $routes->post('topup', 'Digiflazz::topup');
    });
});