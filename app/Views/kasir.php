<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $title ?></title>
    <link rel="icon" href="<?= base_url('assets/img/logo.jpg'); ?>" type="image/gif" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" />
    <link rel="stylesheet" href="/assets/plugins/fontawesome-free/css/all.min.css" />
    <link rel="stylesheet" href="/assets/plugins/select2/css/select2.min.css" />
    <link rel="stylesheet" href="/assets/plugins/sweetalert2/sweetalert2.min.css" />
    <link rel="stylesheet" href="/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
    <link rel="stylesheet" href="/assets/css/adminlte.min.css" />

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
        }

        .kasir-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .kasir-body {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        .kasir-left,
        .kasir-right {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .kasir-left {
            flex: 2;
            padding: 1rem;
        }

        .kasir-right {
            flex: 1;
            padding: 1rem 1rem 1rem 0;
        }

        .card {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .card-body {
            overflow-y: auto;
        }

        .navbar {
            background-color: #343a40;
        }

        .brand-link img {
            height: 35px;
        }

        .jenis-badge-fisik {
            background-color: #28a745;
        }

        .jenis-badge-digital {
            background-color: #17a2b8;
        }

        .preview-card {
            border-radius: 8px;
            padding: 1rem;
            margin-top: 0.5rem;
            background-color: #f8fafc;
            border: 1px solid #e5e7eb;
        }

        .form-jenis {
            display: none;
        }

        .required-field::after {
            content: " *";
            color: #dc3545;
        }

        .tab-content {
            flex: 1;
            overflow-y: auto;
        }

        .tab-pane {
            height: 100%;
        }

        .nav-tabs .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid #007bff;
        }

        /* Status badges */
        .badge-status-sukses {
            background-color: #28a745;
        }

        .badge-status-proses {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-status-gagal {
            background-color: #dc3545;
        }

        /* Filter styles - updated to match laporan pulsa */
        .filter-card {
            margin-bottom: 1rem;
        }

        .filter-card .card-body {
            padding: 1rem;
        }

        .filter-form .row {
            align-items: end;
        }

        .filter-form .form-label {
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .filter-actions .btn {
            margin-right: 0.5rem;
        }

        /* Styling untuk semua dropdown Select2 */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            display: flex !important;
            align-items: center !important;
            height: 100% !important;
            padding-left: 12px !important;
        }

        /* Tinggi box Select2 */
        .select2-container .select2-selection--single {
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
        }

        /* Tinggi arrow disamakan */
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            display: flex;
            align-items: center;
        }

        /* Styling khusus untuk dropdown dengan minimumResultsForSearch */
        .select2-container--default.select2-container--disabled {
            width: 100% !important;
        }

        /* Styling untuk form group agar konsisten */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .kasir-body {
                flex-direction: column;
            }

            .kasir-left,
            .kasir-right {
                padding: 0.5rem;
            }

            .kasir-right {
                padding-top: 0;
            }

            .filter-form .col-md-4 {
                margin-bottom: 1rem;
            }

            .filter-actions {
                display: flex;
                justify-content: space-between;
            }
        }
    </style>
</head>

<body>
    <div class="kasir-wrapper">
        <!-- Navbar -->
        <?php $user = session()->get(); ?>
        <nav class="navbar navbar-expand navbar-dark">
            <ul class="navbar-nav">
                <li class="nav-item d-flex align-items-center">
                    <div class="brand-link">
                        <img src="/assets/img/logo.jpg" alt="Logo" class="img-circle elevation-2 mr-2" style="width: 35px" />
                        <span class="text-white">Pulsa - io</span>
                    </div>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown">
                        <img src="<?php if ($user['image'] !== 'default-profile.png') { ?>
            <?= '/assets/img/user/' . $user['image'] ?>
        <?php } else { ?>
            <?= '/assets/img/default-profile.png' ?>
        <?php } ?>"
                            class="img-circle elevation-2"
                            alt="User Image"
                            width="30"
                            height="30"
                            style="object-fit: cover;">

                        <span class="ml-2"><?= $user['name'] ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right mt-1">
                        <form action="/logout" method="post" class="px-4 py-2">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Konten Kasir -->
        <div class="kasir-body">
            <!-- Kiri: Keranjang & Laporan -->
            <div class="kasir-left">
                <div class="card">
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs" id="leftTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="cart-tab" data-toggle="tab" href="#cart-content" role="tab">
                                    <i class="fas fa-shopping-cart mr-1"></i> Keranjang Belanja
                                    <?php $cart = session()->get('kasir_cart') ?? []; ?>
                                    <?php if (!empty($cart)): ?>
                                        <span class="badge badge-primary ml-1"><?= count($cart) ?></span>
                                    <?php endif; ?>
                                </a>
                        </ul>
                    </div>

                    <div class="card-body p-0">
                        <div class="tab-content" id="leftTabsContent">
                            <!-- Tab Keranjang -->
                            <div class="tab-pane fade show active" id="cart-content" role="tabpanel">
                                <?php if (!empty($cart)): ?>
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th>Jenis</th>
                                                <th>Harga</th>
                                                <th>Qty</th>
                                                <th>Subtotal</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cart as $itemId => $item): ?>
                                                <tr>
                                                    <td><?= esc($item['nama']) ?></td>
                                                    <td>
                                                        <span class="badge jenis-badge-<?= $item['jenis'] ?>">
                                                            <?= ucfirst($item['jenis']) ?>
                                                        </span>
                                                    </td>
                                                    <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                                    <td><?= $item['jumlah'] ?></td>
                                                    <td>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                                                    <td>
                                                        <form action="/menu/kasir/remove" method="post" style="display:inline">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="item_id" value="<?= $itemId ?>">
                                                            <button class="btn btn-danger btn-sm" type="submit" title="Hapus dari keranjang">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Keranjang belanja kosong</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab Laporan Pulsa -->
                            <div class="tab-pane fade" id="report-content" role="tabpanel">
                                <!-- Filter Tanggal - Updated to match laporan pulsa -->
                                <div class="card filter-card bg-light">
                                    <div class="card-body">
                                        <form method="get" class="filter-form">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                                                    <input type="date" class="form-control"
                                                        name="start_date" id="start_date"
                                                        value="<?= $start_date ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                                                    <input type="date" class="form-control"
                                                        name="end_date" id="end_date"
                                                        value="<?= $end_date ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label d-block">Aksi</label>
                                                    <div class="filter-actions">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-filter mr-1"></i> Filter
                                                        </button>
                                                        <?php if ($start_date || $end_date): ?>
                                                            <a href="/menu/kasir" class="btn btn-secondary">
                                                                <i class="fas fa-sync-alt mr-1"></i> Reset
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Summary Laporan -->
                                <?php if (!empty($summary_pulsa)): ?>
                                    <div class="p-3 border-bottom">
                                        <div class="row text-center">
                                            <div class="col-3">
                                                <small class="text-muted">Total Transaksi</small>
                                                <div class="h5 font-weight-bold text-primary">
                                                    <?= number_format($summary_pulsa['total_transaksi'] ?? 0, 0, ',', '.') ?>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <small class="text-muted">Total Penjualan</small>
                                                <div class="h5 font-weight-bold text-success">
                                                    Rp <?= number_format($summary_pulsa['total_penjualan'] ?? 0, 0, ',', '.') ?>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <small class="text-muted">Total Keuntungan</small>
                                                <div class="h5 font-weight-bold text-info">
                                                    Rp <?= number_format($summary_pulsa['total_keuntungan'] ?? 0, 0, ',', '.') ?>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <small class="text-muted">Rata-rata Keuntungan</small>
                                                <div class="h5 font-weight-bold text-warning">
                                                    Rp <?= number_format($summary_pulsa['rata_rata_keuntungan'] ?? 0, 0, ',', '.') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Tabel Laporan -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm" id="tableLaporanPulsa">
                                        <thead>
                                            <tr>
                                                <th>No Transaksi</th>
                                                <th>Tanggal</th>
                                                <th>No Tujuan</th>
                                                <th>Provider</th>
                                                <th>Nominal</th>
                                                <th>Harga Jual</th>
                                                <th>Keuntungan</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($laporan_pulsa)): ?>
                                                <?php foreach ($laporan_pulsa as $transaksi): ?>
                                                    <tr>
                                                        <td><?= esc($transaksi['no_transaksi']) ?></td>
                                                        <td><?= date('d/m/Y H:i', strtotime($transaksi['created_at'])) ?></td>
                                                        <td><?= esc($transaksi['no_tujuan']) ?></td>
                                                        <td><?= esc($transaksi['nama_provider'] ?? '-') ?></td>
                                                        <td><?= number_format($transaksi['nominal'], 0, ',', '.') ?></td>
                                                        <td>Rp <?= number_format($transaksi['harga_jual'], 0, ',', '.') ?></td>
                                                        <td>Rp <?= number_format($transaksi['keuntungan'], 0, ',', '.') ?></td>
                                                        <td>
                                                            <span class="badge badge-status-<?= $transaksi['status'] ?>">
                                                                <?= ucfirst($transaksi['status']) ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="8" class="text-center py-4 text-muted">
                                                        <i class="fas fa-chart-bar fa-2x mb-2"></i><br>
                                                        Tidak ada data transaksi pulsa
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kanan: Form Input -->
            <div class="kasir-right">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Tambah Produk</h3>
                    </div>
                    <div class="card-body">
                        <!-- Error Messages -->
                        <?php if (session()->get('validation_errors')): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach (session()->get('validation_errors') as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->get('error')): ?>
                            <div class="alert alert-danger">
                                <?= session()->get('error') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->get('success')): ?>
                            <div class="alert alert-success">
                                <?= session()->get('success') ?>
                            </div>
                        <?php endif; ?>

                        <form action="/menu/kasir/add" method="post" id="formTambahProduk" novalidate class="form-vertical">
                            <?= csrf_field(); ?>

                            <div class="form-group">
                                <label for="jenis_produk" class="required-field">Jenis Produk</label>
                                <select class="form-control" name="jenis_produk" id="jenis_produk" required>
                                    <option value="">Pilih Jenis Produk</option>
                                    <option value="fisik" <?= old('jenis_produk') == 'fisik' ? 'selected' : '' ?>>Produk Fisik</option>
                                    <option value="digital" <?= old('jenis_produk') == 'digital' ? 'selected' : '' ?>>Produk Digital (Pulsa)</option>
                                </select>
                            </div>

                            <!-- Form Produk Fisik -->
                            <div id="form-fisik" class="form-jenis">
                                <div class="form-group">
                                    <label for="produk_id" class="required-field">Pilih Produk</label>
                                    <select class="form-control select2" name="produk_id" id="produk_id">
                                        <option value="">Pilih Produk</option>
                                        <?php foreach ($produk_fisik as $produk): ?>
                                            <option value="<?= $produk->id ?>"
                                                data-harga="<?= $produk->harga ?>"
                                                data-stok="<?= $produk->stok ?>"
                                                <?= old('produk_id') == $produk->id ? 'selected' : '' ?>>
                                                <?= esc($produk->nama_produk) ?> -
                                                Rp <?= number_format($produk->harga, 0, ',', '.') ?>
                                                (Stok: <?= $produk->stok ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="jumlah" class="required-field">Jumlah</label>
                                    <input type="number" class="form-control" name="jumlah" id="jumlah"
                                        value="<?= old('jumlah', 1) ?>" min="1" required>
                                </div>

                                <!-- Preview Produk Fisik -->
                                <div id="preview-fisik" class="preview-card" style="display: none;">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <small class="text-muted">Nama Produk</small>
                                            <div class="font-weight-bold" id="preview-nama">-</div>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted">Harga Satuan</small>
                                            <div class="text-primary font-weight-bold" id="preview-harga">-</div>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted">Stok Tersedia</small>
                                            <div class="text-info font-weight-bold" id="preview-stok">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Produk Digital -->
                            <div id="form-digital" class="form-jenis">
                                <div class="form-group">
                                    <label for="no_tujuan_pulsa" class="required-field">No Tujuan</label>
                                    <input type="text" class="form-control" name="no_tujuan_pulsa" id="no_tujuan_pulsa"
                                        value="<?= old('no_tujuan_pulsa') ?>" placeholder="081234567890" required>
                                </div>

                                <div class="form-group">
                                    <label for="provider_id" class="required-field">Provider</label>
                                    <select class="form-control select2" name="provider_id" id="provider_id" required>
                                        <option value="">Pilih Provider</option>
                                        <?php foreach ($produk_digital['providers'] ?? [] as $provider): ?>
                                            <option value="<?= $provider['id'] ?>" <?= old('provider_id') == $provider['id'] ? 'selected' : '' ?>>
                                                <?= esc($provider['nama_provider']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="nominal_id" class="required-field">Nominal Pulsa</label>
                                    <select class="form-control select2" name="nominal_id" id="nominal_id" required>
                                        <option value="">Pilih Nominal</option>
                                        <?php foreach ($produk_digital['nominals'] ?? [] as $nominal): ?>
                                            <option value="<?= $nominal['id'] ?>"
                                                data-harga-jual="<?= $nominal['harga_jual'] ?>"
                                                data-harga-modal="<?= $nominal['harga_modal'] ?>"
                                                data-nominal="<?= $nominal['nominal'] ?>"
                                                <?= old('nominal_id') == $nominal['id'] ? 'selected' : '' ?>>
                                                <?= number_format($nominal['nominal'], 0, ',', '.') ?>
                                                (Rp <?= number_format($nominal['harga_jual'], 0, ',', '.') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="metode_pembayaran_pulsa" class="required-field">Metode Pembayaran</label>
                                    <select class="form-control" name="metode_pembayaran_pulsa" id="metode_pembayaran_pulsa" required>
                                        <option value="">Pilih Metode</option>
                                        <option value="tunai" <?= old('metode_pembayaran_pulsa') == 'tunai' ? 'selected' : '' ?>>Tunai</option>
                                        <option value="saldo" <?= old('metode_pembayaran_pulsa') == 'saldo' ? 'selected' : '' ?>>Saldo</option>
                                        <option value="transfer" <?= old('metode_pembayaran_pulsa') == 'transfer' ? 'selected' : '' ?>>Transfer</option>
                                        <option value="grip" <?= old('metode_pembayaran_pulsa') == 'grip' ? 'selected' : '' ?>>Grip</option>
                                    </select>
                                </div>

                                <!-- Preview Produk Digital -->
                                <div id="preview-digital" class="preview-card" style="display: none;">
                                    <div class="row text-center">
                                        <div class="col-3">
                                            <small class="text-muted">Nominal</small>
                                            <div class="font-weight-bold" id="preview-nominal">-</div>
                                        </div>
                                        <div class="col-3">
                                            <small class="text-muted">Harga Modal</small>
                                            <div class="text-danger font-weight-bold" id="preview-harga-modal">-</div>
                                        </div>
                                        <div class="col-3">
                                            <small class="text-muted">Harga Jual</small>
                                            <div class="text-primary font-weight-bold" id="preview-harga-jual">-</div>
                                        </div>
                                        <div class="col-3">
                                            <small class="text-muted">Keuntungan</small>
                                            <div class="text-success font-weight-bold" id="preview-keuntungan">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-block mt-3" id="btnTambahKeranjang">
                                <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                            </button>
                        </form>

                        <hr>

                        <!-- Form Checkout -->
                        <?php
                        $total = 0;
                        $cart = session()->get('kasir_cart') ?? [];
                        foreach ($cart as $item) {
                            $total += $item['subtotal'];
                        }
                        $ppnPercent = 0;
                        $ppn = ($ppnPercent / 100) * $total;
                        $diskon = 0;
                        $grandTotal = $total + $ppn - $diskon;
                        ?>

                        <form action="/menu/kasir/checkout" method="post" id="formCheckout" class="form-vertical">
                            <?= csrf_field(); ?>

                            <div class="container-fluid px-0">
                                <dl class="row mb-0">
                                    <dt class="col-6">Total</dt>
                                    <dd class="col-6 text-right">Rp <?= number_format($total, 0, ',', '.') ?></dd>

                                    <dt class="col-6">PPN (%)</dt>
                                    <dd class="col-6 text-right">
                                        <input type="number" id="ppn_percent" name="ppn_percent"
                                            class="form-control form-control-sm text-right"
                                            min="0" max="100" value="<?= $ppnPercent ?>" step="0.1">
                                    </dd>

                                    <dt class="col-6">Diskon (Rp)</dt>
                                    <dd class="col-6 text-right">
                                        <input type="number" id="diskon" name="diskon"
                                            class="form-control form-control-sm text-right"
                                            min="0" value="0" max="<?= $total ?>">
                                    </dd>

                                    <hr class="col-12 my-2" />

                                    <dt class="col-6 font-weight-bold">Grand Total</dt>
                                    <dd class="col-6 text-right font-weight-bold text-primary" id="grandTotalDisplay">
                                        Rp <?= number_format($grandTotal, 0, ',', '.') ?>
                                    </dd>
                                </dl>

                                <input type="hidden" name="grand_total" id="inputGrandTotal" value="<?= $grandTotal ?>">
                                <input type="hidden" name="ppn" id="inputPPN" value="<?= $ppn ?>">
                                <input type="hidden" name="metode_pembayaran" id="metodePembayaranInput">

                                <button type="button" id="btnCheckout" class="btn btn-primary btn-block mt-3"
                                    <?= empty($cart) ? 'disabled title="Keranjang masih kosong"' : '' ?>>
                                    <i class="fas fa-money-bill-wave"></i> Checkout
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="/assets/plugins/jquery/jquery.min.js"></script>
    <script src="/assets/plugins/select2/js/select2.min.js"></script>
    <script src="/assets/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/assets/js/adminlte.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi select2 untuk semua dropdown
            $('.select2').select2({
                placeholder: 'Pilih...',
                allowClear: true,
                width: '100%', // Tambahkan ini untuk memastikan lebar 100%
                dropdownAutoWidth: true
            });

            // Inisialisasi select2 untuk dropdown biasa dengan lebar yang sama
            $('#jenis_produk, #metode_pembayaran_pulsa').select2({
                minimumResultsForSearch: -1,
                width: '100%', // Tambahkan ini
                dropdownAutoWidth: true
            });

            // Fungsi untuk toggle form berdasarkan jenis produk
            function toggleFormJenis() {
                const jenis = $('#jenis_produk').val();
                console.log('Jenis dipilih:', jenis);

                // Sembunyikan semua form terlebih dahulu
                $('#form-fisik').hide();
                $('#form-digital').hide();
                $('#btnTambahKeranjang').prop('disabled', true);

                // Tampilkan form sesuai jenis
                if (jenis === 'fisik') {
                    $('#form-fisik').show();
                    $('#btnTambahKeranjang').prop('disabled', false);
                } else if (jenis === 'digital') {
                    $('#form-digital').show();
                    $('#btnTambahKeranjang').prop('disabled', false);
                }
            }

            // Panggil fungsi saat halaman dimuat
            toggleFormJenis();

            // Event listener untuk perubahan jenis produk
            $('#jenis_produk').on('change', function() {
                toggleFormJenis();
            });

            // Preview untuk produk fisik
            $('#produk_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');

                if (selectedOption.val()) {
                    $('#preview-nama').text(selectedOption.text().split(' - ')[0]);
                    $('#preview-harga').text('Rp ' + parseInt(selectedOption.data('harga')).toLocaleString('id-ID'));
                    $('#preview-stok').text(selectedOption.data('stok'));
                    $('#preview-fisik').show();
                } else {
                    $('#preview-fisik').hide();
                }
            });

            // Preview untuk produk digital
            $('#nominal_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');

                if (selectedOption.val()) {
                    const nominal = parseInt(selectedOption.data('nominal'));
                    const hargaModal = parseInt(selectedOption.data('harga-modal'));
                    const hargaJual = parseInt(selectedOption.data('harga-jual'));
                    const keuntungan = hargaJual - hargaModal;

                    $('#preview-nominal').text(nominal.toLocaleString('id-ID'));
                    $('#preview-harga-modal').text('Rp ' + hargaModal.toLocaleString('id-ID'));
                    $('#preview-harga-jual').text('Rp ' + hargaJual.toLocaleString('id-ID'));
                    $('#preview-keuntungan').text('Rp ' + keuntungan.toLocaleString('id-ID'));
                    $('#preview-digital').show();
                } else {
                    $('#preview-digital').hide();
                }
            });

            // Grand total calculation
            let total = <?= $total ?>;
            const ppnPercentInput = $("#ppn_percent");
            const diskonInput = $("#diskon");
            const grandTotalDisplay = $("#grandTotalDisplay");
            const inputGrandTotal = $("#inputGrandTotal");
            const inputPPN = $("#inputPPN");

            function updateGrandTotal() {
                const ppnPercent = parseFloat(ppnPercentInput.val()) || 0;
                let diskon = parseFloat(diskonInput.val()) || 0;

                // Pastikan diskon tidak melebihi total
                if (diskon > total) {
                    diskon = total;
                    diskonInput.val(diskon);
                }

                const ppn = (ppnPercent / 100) * total;
                const grandTotal = total + ppn - diskon;

                grandTotalDisplay.text(formatRupiah(grandTotal));
                inputGrandTotal.val(grandTotal);
                inputPPN.val(ppn);
            }

            function formatRupiah(number) {
                return 'Rp ' + Math.round(number).toLocaleString('id-ID');
            }

            ppnPercentInput.on("input", updateGrandTotal);
            diskonInput.on("input", updateGrandTotal);
            updateGrandTotal();

            // Checkout button
            $("#btnCheckout").on("click", function() {
                Swal.fire({
                    title: 'Pilih Metode Pembayaran',
                    input: 'select',
                    inputOptions: {
                        tunai: 'Tunai',
                        saldo: 'Saldo',
                        transfer: 'Transfer',
                    },
                    inputPlaceholder: 'Pilih metode',
                    showCancelButton: true,
                    confirmButtonColor: "#28a745",
                    confirmButtonText: 'Lanjut Bayar',
                    cancelButtonColor: "#dc3545",
                    cancelButtonText: 'Batal',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Silakan pilih metode pembayaran!'
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#metodePembayaranInput').val(result.value);
                        $('#formCheckout').submit();
                    }
                });
            });

            // Validasi form sebelum submit
            $('#formTambahProduk').on('submit', function(e) {
                const jenis = $('#jenis_produk').val();

                if (!jenis) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Silakan pilih jenis produk terlebih dahulu!'
                    });
                    return;
                }

                let isValid = true;
                let message = '';

                if (jenis === 'fisik') {
                    const produkId = $('#produk_id').val();
                    const jumlah = $('#jumlah').val();

                    if (!produkId) {
                        isValid = false;
                        message = 'Silakan pilih produk!';
                    } else if (!jumlah || jumlah < 1) {
                        isValid = false;
                        message = 'Silakan isi jumlah dengan angka yang valid!';
                    }
                } else if (jenis === 'digital') {
                    const noTujuan = $('#no_tujuan_pulsa').val();
                    const provider = $('#provider_id').val();
                    const nominal = $('#nominal_id').val();
                    const metode = $('#metode_pembayaran_pulsa').val();

                    if (!noTujuan) {
                        isValid = false;
                        message = 'Silakan isi nomor tujuan!';
                    } else if (!provider) {
                        isValid = false;
                        message = 'Silakan pilih provider!';
                    } else if (!nominal) {
                        isValid = false;
                        message = 'Silakan pilih nominal pulsa!';
                    } else if (!metode) {
                        isValid = false;
                        message = 'Silakan pilih metode pembayaran!';
                    }
                }

                if (!isValid) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: message
                    });
                }
            });

            // Inisialisasi DataTables untuk laporan pulsa
            $('#tableLaporanPulsa').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "pageLength": 10,
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Berikutnya",
                        "previous": "Sebelumnya"
                    }
                }
            });

            // Auto refresh ketika ada perubahan di keranjang
            setInterval(function() {
                const currentTotal = <?= $total ?>;
                if (currentTotal !== total) {
                    location.reload();
                }
            }, 3000);
        });
    </script>
</body>

</html>