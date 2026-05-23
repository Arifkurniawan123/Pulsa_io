<?php $user = session()->get(); ?>
<?= $this->extend('layout/default'); ?>
<?= $this->section('style'); ?>
<!-- DataTables -->
<link rel="stylesheet" href="/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="/assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<link rel="icon" href="<?= base_url('assets/img/logo.jpg'); ?>" type="image/gif" />

<!-- SweetAlert2 -->
<link rel="stylesheet" href="/assets/plugins/sweetalert2/sweetalert2.min.css">

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

    .form-label {
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--text);
    }

    .btn-primary {
        background: var(--primary);
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
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

    .btn-warning {
        background: #f59e0b;
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }

    .btn-warning:hover {
        background: #d97706;
    }

    table thead {
        background: var(--soft);
        color: var(--text);
    }

    table tbody tr:hover {
        background-color: #f3f4f6;
    }

    table th {
        padding: 0.75rem;
        font-weight: 600;
        font-size: 0.9rem;
        text-align: left;
    }

    table td {
        padding: 0.75rem;
        font-size: 0.9rem;
        vertical-align: middle !important;
    }

    table th.text-end {
        text-align: right;
    }

    table td.text-end {
        text-align: right;
    }

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

    .export-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        margin-left: auto;
    }

    .btn-group-actions {
        display: flex;
        gap: 5px;
        justify-content: flex-end;
    }

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

    .card-header.d-flex {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }

    .card-header .card-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .dropdown-menu {
        border-radius: 8px;
        border: 1px solid var(--border);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }

    .dropdown-item:hover {
        background-color: var(--soft);
        color: var(--primary);
    }

    .alert {
        border: none;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert-danger {
        background: #fee2e2;
        color: #7f1d1d;
        border-left: 4px solid #ef4444;
    }

    .badge {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.85rem;
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

    .text-success {
        color: #10b981;
    }

    .text-muted {
        color: #9ca3af;
    }

    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
        }

        .filter-form .form-control,
        .filter-form .btn {
            width: 100%;
        }

        table th,
        table td {
            padding: 0.5rem;
            font-size: 0.8rem;
        }

        .export-buttons {
            width: 100%;
            justify-content: flex-start;
            margin-left: 0;
            margin-top: 10px;
        }

        .btn-group-actions {
            justify-content: center;
        }

        .card-header.d-flex {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .card-header .card-title {
            margin-bottom: 10px;
        }
    }
</style>
<?= $this->endSection(); ?>

<?= $this->section('content') ?>
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <!-- Filter Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 card-title">
                        <i class="fas fa-mobile-alt"></i> Laporan Penjualan Pulsa
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="get" class="filter-form">
                        <div>
                            <label for="start_date">Dari:</label>
                            <input type="date" id="start_date" name="start_date" value="<?= esc($startDate ?? '') ?>" class="form-control" />
                        </div>

                        <div>
                            <label for="end_date">Sampai:</label>
                            <input type="date" id="end_date" name="end_date" value="<?= esc($endDate ?? '') ?>" class="form-control" />
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

            <!-- Summary Cards -->
            <?php if ($summary): ?>
                <div class="row g-2 summary-card">
                    <div class="col-md-4 col-sm-6">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-2">Total Transaksi</h6>
                                        <h3 class="mb-0"><?= number_format($summary['total_transaksi'] ?? 0) ?></h3>
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
                                        <h3 class="mb-0">Rp <?= number_format($summary['total_penjualan'] ?? 0, 0, ',', '.') ?></h3>
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
                                        <h3 class="mb-0">Rp <?= number_format($summary['total_keuntungan'] ?? 0, 0, ',', '.') ?></h3>
                                    </div>
                                    <i class="fas fa-chart-line fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Data Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 card-title">Data Penjualan Pulsa</h6>
                    <!-- Export Buttons di Kanan -->
                    <?php if (!empty($transactions)): ?>
                        <div class="export-buttons">
                            <a href="<?= base_url('laporan-pulsa/export-excel?' . http_build_query(['start_date' => $startDate, 'end_date' => $endDate])) ?>" class="btn btn-success" title="Export Excel">
                                <i class="fas fa-file-excel"></i> Excel
                            </a>
                            <a href="<?= base_url('laporan-pulsa/export-pdf?' . http_build_query(['start_date' => $startDate, 'end_date' => $endDate])) ?>" class="btn btn-danger" title="Export PDF">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            <a href="<?= base_url('laporan-pulsa/create') ?>" class="btn btn-primary" title="Tambah Transaksi">
                                <i class="fas fa-plus"></i> Tambah
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="export-buttons">
                            <a href="<?= base_url('laporan-pulsa/create') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Transaksi
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($transactions)): ?>
                        <div class="table-responsive">
                            <table id="tableLaporanPulsa" class="table table-bordered table-striped display nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>No Transaksi</th>
                                        <th>Tanggal</th>
                                        <th>Provider</th>
                                        <th>Kasir</th>
                                        <th>Nominal</th>
                                        <th>No Tujuan</th>
                                        <th>Harga Jual</th>
                                        <th>Keuntungan</th>
                                        <th>Pembayaran</th>
                                        <th>Status</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $index => $trx): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <span class="badge badge-light"><?= esc($trx['no_transaksi']) ?></span>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($trx['created_at'])) ?></td>
                                            <td><?= esc($trx['nama_provider'] ?? '-') ?></td>
                                            <!-- Perbaikan: Gunakan $trx['nama_user'] langsung dari array -->
                                            <td><?= esc($trx['nama_user'] ?? '-') ?></td>
                                            <td><?= number_format($trx['nominal_paket'] ?? $trx['nominal'], 0, ',', '.') ?></td>
                                            <td><?= esc($trx['no_tujuan']) ?></td>
                                            <td>Rp <?= number_format($trx['harga_jual'], 0, ',', '.') ?></td>
                                            <td>
                                                <span class="text-success">
                                                    Rp <?= number_format($trx['keuntungan'], 0, ',', '.') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary text-capitalize">
                                                    <?= esc($trx['metode_pembayaran']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($trx['status'] == 'sukses'): ?>
                                                    <span class="badge badge-success">Sukses</span>
                                                <?php elseif ($trx['status'] == 'gagal'): ?>
                                                    <span class="badge badge-danger">Gagal</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Proses</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group-actions">
                                                    <a href="<?= base_url('laporan-pulsa/edit/' . $trx['id']) ?>" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="<?= $trx['id'] ?>" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada data untuk periode ini</h5>
                            <p class="text-muted">Mulai dengan menambahkan transaksi pertama Anda</p>
                            <a href="<?= base_url('laporan-pulsa/create') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Transaksi
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('script'); ?>
<!-- DataTables  & Plugins -->
<script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>

<!-- SweetAlert2 -->
<script src="/assets/plugins/sweetalert2/sweetalert2.min.js"></script>

<script>
    $(function() {
        $("#tableLaporanPulsa").DataTable({
            responsive: false,
            lengthChange: true,
            autoWidth: false,
            scrollX: true,
            pageLength: 10,
            columnDefs: [{
                    targets: 0,
                    searchable: false,
                    width: '50px'
                },
                {
                    targets: -1,
                    searchable: false,
                    orderable: false,
                    width: '100px',
                    className: 'text-center'
                }
            ]
        });

        // SweetAlert delete confirmation
        $('.btn-delete').click(function() {
            const id = $(this).data('id');
            const url = '<?= base_url('laporan-pulsa/delete/') ?>' + id;
            const csrfToken = '<?= csrf_token(); ?>';
            const csrfHash = '<?= csrf_hash(); ?>';

            Swal.fire({
                title: "Yakin hapus data?",
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                confirmButtonText: "Ya, hapus!",
                cancelButtonColor: "#6c757d",
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            [csrfToken]: csrfHash
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire("Berhasil!", res.message, "success")
                                    .then(() => location.reload());
                            } else {
                                Swal.fire("Gagal!", res.message, "error");
                            }
                        },
                        error: function(xhr) {
                            const errMsg = xhr.responseJSON?.message || "Terjadi kesalahan!";
                            Swal.fire("Opss..", errMsg, "error");
                        }
                    });
                }
            });
        });
    });

    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('success') ?>'
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Opss..',
            text: '<?= session()->getFlashdata('error') ?>'
        });
    <?php endif; ?>
</script>
<?= $this->endSection(); ?>