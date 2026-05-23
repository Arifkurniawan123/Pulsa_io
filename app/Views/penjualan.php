<?= $this->extend('layout/default'); ?>

<?= $this->section('style'); ?>
<!-- DataTables -->
<link rel="stylesheet" href="/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" href="/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
<link rel="stylesheet" href="/assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css" />
<link rel="icon" href="<?= base_url('assets/img/logo.jpg'); ?>" type="image/gif" />

<!-- SweetAlert2 -->
<link rel="stylesheet" href="/assets/plugins/sweetalert2/sweetalert2.min.css" />

<style>
    :root {
        --primary: #6366f1;
        --soft: #eef2ff;
        --light: #f9fafb;
        --border: #e5e7eb;
        --text: #1f2937;
    }

    .content-wrapper {
        background: var(--light);
        padding: 15px;
    }

    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        background: #fff;
        margin-bottom: 15px;
    }

    .card-header {
        border-bottom: 1px solid var(--border);
        background: transparent;
        font-weight: 600;
        color: var(--text);
        padding: 1rem;
    }

    .card-body {
        padding: 1rem;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid var(--border);
        box-shadow: none !important;
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }

    .btn-primary {
        background: var(--primary);
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: #4f46e5;
    }

    .btn-secondary {
        background: #9ca3af;
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: #6b7280;
    }

    .btn-success {
        background: #10b981;
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }

    .btn-success:hover {
        background: #059669;
    }

    .btn-danger {
        background: #ef4444;
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    /* Table Styling */
    table {
        width: 100% !important;
    }

    table thead {
        background: var(--soft);
        color: var(--text);
    }

    table thead th {
        padding: 0.75rem;
        font-weight: 600;
        font-size: 0.9rem;
        border: 1px solid var(--border);
        white-space: nowrap;
    }

    table tbody td {
        padding: 0.75rem;
        font-size: 0.9rem;
        vertical-align: middle;
        border: 1px solid var(--border);
    }

    table tbody tr:hover {
        background-color: #f3f4f6;
    }

    /* DataTables */
    .dataTables_wrapper {
        font-size: 0.9rem;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px;
        padding: 0.25rem 0.5rem;
        margin: 0 2px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--primary) !important;
        color: #fff !important;
        border: none;
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 6px;
        border: 1px solid var(--border);
        padding: 0.4rem 0.7rem;
        font-size: 0.9rem;
    }

    /* Navigation Tabs */
    .nav-tabs {
        border-bottom: 2px solid var(--border);
        gap: 0;
        margin-bottom: 0;
    }

    .nav-tabs .nav-link {
        color: var(--text);
        border: none;
        border-bottom: 3px solid transparent;
        border-radius: 0;
        padding: 0.7rem 1.2rem;
        font-weight: 500;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        background: transparent;
        cursor: pointer;
    }

    .nav-tabs .nav-link:hover {
        border-bottom-color: var(--primary);
        color: var(--primary);
    }

    .nav-tabs .nav-link.active {
        background: transparent;
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    .tab-content {
        padding-top: 0;
    }

    /* Summary Cards */
    .summary-card {
        margin-bottom: 15px;
    }

    .summary-card .card {
        margin-bottom: 0;
    }

    .summary-card h6 {
        font-size: 0.85rem;
        font-weight: 600;
    }

    .summary-card h3 {
        font-size: 1.5rem;
        font-weight: 700;
    }

    /* Filter Form */
    .filter-form {
        display: flex;
        gap: 10px;
        align-items: flex-end;
        flex-wrap: wrap;
    }

    .filter-form label {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .filter-form .form-control {
        min-width: 150px;
    }

    /* Export Buttons */
    .export-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        margin-left: auto;
    }

    .card-header.d-flex {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: wrap;
        gap: 10px;
    }

    .card-header .card-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }

    /* Badges */
    .badge {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-danger {
        background: #fee2e2;
        color: #7f1d1d;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-secondary {
        background: #f3f4f6;
        color: #374151;
    }

    .badge-light {
        background: #f0f0f0;
        color: #333;
    }

    .badge-primary {
        background: #dbeafe;
        color: #1e40af;
    }

    .text-success {
        color: #10b981;
    }

    .text-muted {
        color: #9ca3af;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            width: 100%;
        }

        .filter-form > div {
            width: 100%;
        }

        .filter-form .form-control,
        .filter-form .btn {
            width: 100%;
        }

        .nav-tabs .nav-link {
            padding: 0.6rem 0.9rem;
            font-size: 0.85rem;
        }

        table thead th,
        table tbody td {
            padding: 0.5rem;
            font-size: 0.8rem;
        }

        .export-buttons {
            width: 100%;
            justify-content: flex-start;
            margin-left: 0;
            margin-top: 10px;
        }

        .card-header.d-flex {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .card-header .card-title {
            width: 100%;
        }
    }
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <!-- Filter Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 card-title">
                        <i class="fas fa-chart-line"></i> <?= esc($table_name) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="get" class="filter-form">
                        <div>
                            <label for="start">Dari:</label>
                            <input type="date" id="start" name="start" value="<?= esc($start ?? '') ?>" class="form-control" />
                        </div>

                        <div>
                            <label for="end">Sampai:</label>
                            <input type="date" id="end" name="end" value="<?= esc($end ?? '') ?>" class="form-control" />
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>

                        <a href="<?= current_url() ?>" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> Reset
                        </a>
                    </form>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="produk-tab" data-bs-toggle="tab" data-bs-target="#produk-pane" type="button" role="tab" aria-controls="produk-pane" aria-selected="true">
                        <i class="fas fa-box"></i> Laporan Produk
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pulsa-tab" data-bs-toggle="tab" data-bs-target="#pulsa-pane" type="button" role="tab" aria-controls="pulsa-pane" aria-selected="false">
                        <i class="fas fa-mobile-alt"></i> Laporan Pulsa
                    </button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content">
                <!-- Tab Produk -->
                <div class="tab-pane fade show active" id="produk-pane" role="tabpanel" aria-labelledby="produk-tab" tabindex="0">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 card-title">Data Penjualan Produk</h6>

                            <?php if (!empty($penjualan)): ?>
                                <div class="export-buttons">
                                    <a href="<?= base_url('penjualan/export-produk-excel?' . http_build_query(['start' => $start, 'end' => $end])) ?>"
                                        class="btn btn-success" title="Export Excel">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </a>
                                    <a href="<?= base_url('penjualan/export-produk-pdf?' . http_build_query(['start' => $start, 'end' => $end])) ?>"
                                        class="btn btn-danger" title="Export PDF">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tablePenjualan" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%;">No</th>
                                            <th>Nama Produk</th>
                                            <th style="width: 12%;">Jumlah Terjual</th>
                                            <th style="width: 15%;">Total Pendapatan</th>
                                            <th>Nama Kasir</th>
                                            <th style="width: 12%;">Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($penjualan)) : ?>
                                            <?php foreach ($penjualan as $key => $value) : ?>
                                                <tr>
                                                    <td><?= $key + 1; ?></td>
                                                    <td><?= esc($value->nama_produk); ?></td>
                                                    <td><?= esc($value->total_terjual); ?></td>
                                                    <td>Rp <?= number_format($value->total_pendapatan, 0, ',', '.') ?></td>
                                                    <td><?= esc($value->kasir); ?></td>
                                                    <td><?= date('d/m/Y', strtotime($value->tanggal)) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">Tidak ada data untuk periode ini</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Pulsa -->
                <div class="tab-pane fade" id="pulsa-pane" role="tabpanel" aria-labelledby="pulsa-tab" tabindex="0">
                    <!-- Summary Cards Pulsa -->
                    <?php if ($pulsaSummary): ?>
                        <div class="row g-2 summary-card">
                            <div class="col-md-4 col-sm-6">
                                <div class="card bg-primary text-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-2">Total Transaksi</h6>
                                                <h3 class="mb-0"><?= number_format($pulsaSummary['total_transaksi'] ?? 0) ?></h3>
                                            </div>
                                            <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="card bg-success text-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-2">Total Penjualan</h6>
                                                <h3 class="mb-0">Rp <?= number_format($pulsaSummary['total_penjualan'] ?? 0, 0, ',', '.') ?></h3>
                                            </div>
                                            <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="card bg-info text-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-2">Total Keuntungan</h6>
                                                <h3 class="mb-0">Rp <?= number_format($pulsaSummary['total_keuntungan'] ?? 0, 0, ',', '.') ?></h3>
                                            </div>
                                            <i class="fas fa-chart-line fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Data Table Pulsa -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 card-title">Data Penjualan Pulsa</h6>
                            <!-- Export Buttons Pulsa -->
                            <?php if (!empty($pulsa)): ?>
                                <div class="export-buttons">
                                    <a href="<?= base_url('penjualan/export-pulsa-excel?' . http_build_query(['start' => $start, 'end' => $end])) ?>" class="btn btn-success" title="Export Excel">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </a>
                                    <a href="<?= base_url('penjualan/export-pulsa-pdf?' . http_build_query(['start' => $start, 'end' => $end])) ?>" class="btn btn-danger" title="Export PDF">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%;">No</th>
                                            <th style="width: 13%;">No Transaksi</th>
                                            <th style="width: 13%;">Tanggal</th>
                                            <th style="width: 11%;">Provider</th>
                                            <th style="width: 11%;">Kasir</th>
                                            <th style="width: 10%;">Nominal</th>
                                            <th style="width: 10%;">No Tujuan</th>
                                            <th style="width: 12%;">Harga Jual</th>
                                            <th style="width: 12%;">Keuntungan</th>
                                            <th style="width: 10%;">Pembayaran</th>
                                            <th style="width: 7%;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($pulsa)) : ?>
                                            <?php foreach ($pulsa as $index => $trx) : ?>
                                                <tr>
                                                    <td><?= $index + 1; ?></td>
                                                    <td><span class="badge badge-light"><?= esc($trx['no_transaksi'] ?? $trx->no_transaksi); ?></span></td>
                                                    <td><?= date('d/m/Y H:i', strtotime($trx['created_at'] ?? $trx->created_at)) ?></td>
                                                    <td><?= esc($trx['nama_provider'] ?? $trx->nama_provider ?? '-') ?></td>
                                                    <td><?= esc($trx['nama_user'] ?? $trx->nama_user ?? '-') ?></td>
                                                    <td><?= number_format($trx['nominal_paket'] ?? $trx->nominal ?? 0, 0, ',', '.') ?></td>
                                                    <td><?= esc($trx['no_tujuan'] ?? $trx->no_tujuan) ?></td>
                                                    <td>Rp <?= number_format($trx['harga_jual'] ?? $trx->harga_jual ?? 0, 0, ',', '.') ?></td>
                                                    <td><span class="text-success">Rp <?= number_format($trx['keuntungan'] ?? $trx->keuntungan ?? 0, 0, ',', '.') ?></span></td>
                                                    <td><span class="badge badge-secondary text-capitalize"><?= esc($trx['metode_pembayaran'] ?? $trx->metode_pembayaran ?? '-') ?></span></td>
                                                    <td>
                                                        <?php if (($trx['status'] ?? $trx->status ?? null) == 'sukses'): ?>
                                                            <span class="badge badge-success">Sukses</span>
                                                        <?php elseif (($trx['status'] ?? $trx->status ?? null) == 'gagal'): ?>
                                                            <span class="badge badge-danger">Gagal</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-warning">Proses</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="11" class="text-center text-muted py-4">Tidak ada data untuk periode ini</td>
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
    </section>
</div>
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<!-- DataTables -->
<script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>

<!-- Bootstrap Tabs -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="/assets/plugins/sweetalert2/sweetalert2.min.js"></script>

<script>
    $(function() {
        // Initialize DataTable for Produk
        $("#tablePenjualan").DataTable({
            responsive: false,
            lengthChange: false,
            autoWidth: false,
            scrollX: true,
            pageLength: 10,
            language: {
                search: "Cari:",
                zeroRecords: "Tidak ada data ditemukan",
                paginate: {
                    next: "›",
                    previous: "‹"
                }
            },
            columnDefs: [{
                targets: 0,
                searchable: false
            }]
        });

        // Tab switching - ensure tabs work properly
        const pulsaTab = document.getElementById('pulsa-tab');
        const produkTab = document.getElementById('produk-tab');
        
        if (pulsaTab) {
            pulsaTab.addEventListener('click', function(e) {
                e.preventDefault();
                const tab = new bootstrap.Tab(pulsaTab);
                tab.show();
            });
        }

        if (produkTab) {
            produkTab.addEventListener('click', function(e) {
                e.preventDefault();
                const tab = new bootstrap.Tab(produkTab);
                tab.show();
            });
        }
    });

    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '<?= session()->getFlashdata('success') ?>',
            timer: 2500,
            showConfirmButton: false
        });
    <?php endif; ?>
</script>
<?= $this->endSection(); ?>