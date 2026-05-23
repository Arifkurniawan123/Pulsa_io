<?= $this->extend('layout/default'); ?>

<?= $this->section('style'); ?>
<link rel="stylesheet" href="/assets/plugins/sweetalert2/sweetalert2.min.css" />
<link rel="icon" href="<?= base_url('assets/img/logo.jpg'); ?>" type="image/gif" />

<style>
    /* Tambahkan di bagian CSS yang sudah ada */
    .text-success {
        color: #10b981 !important;
    }

    .dashboard-container {
        background-color: #f9fafb;
        min-height: 100vh;
        padding: 2rem;
        border-radius: 12px;
    }

    .summary-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
    }

    .summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    }

    .summary-icon {
        font-size: 2.5rem;
        color: #6366f1;
        margin-bottom: 0.5rem;
    }

    .summary-title {
        font-size: 0.95rem;
        color: #6b7280;
        font-weight: 500;
    }

    .summary-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #111827;
    }

    .content-wrapper {
        background: #f3f4f6;
    }

    .swal2-popup {
        border-radius: 12px !important;
    }

    /* Styles untuk section pulsa */
    .pulsa-section {
        background: #ffffff;
        border-radius: 16px;
        padding: 1.5rem;
        margin-top: 2rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-item {
        text-align: center;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .quick-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
    }

    .quick-action-btn {
        flex: 1;
        min-width: 150px;
        padding: 1rem;
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 12px;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .quick-action-btn:hover {
        background: #5b5cdd;
        transform: translateY(-2px);
        color: white;
        text-decoration: none;
    }

    .quick-action-btn.secondary {
        background: #6b7280;
    }

    .quick-action-btn.secondary:hover {
        background: #4b5563;
    }

    .info-badge {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        color: #0369a1;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        font-size: 0.875rem;
    }

    .profit-positive {
        color: #10b981 !important;
    }

    .profit-negative {
        color: #ef4444 !important;
    }

    @media (max-width: 768px) {
        .quick-actions {
            flex-direction: column;
        }

        .quick-action-btn {
            min-width: 100%;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<div class="content-wrapper">
    <section class="content pt-4">
        <div class="container-fluid dashboard-container">
            <!-- Data Produk (Tetap Sama) -->
            <div class="row g-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="summary-card">
                        <i class="fas fa-receipt summary-icon"></i>
                        <div class="summary-value"><?= $dashboard_summary['transaksiHariIni'] ?></div>
                        <div class="summary-title">Transaksi Hari Ini</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="summary-card">
                        <i class="fas fa-money-bill-wave summary-icon" style="color:#10b981;"></i>
                        <div class="summary-value">Rp <?= number_format($dashboard_summary['pendapatanHariIni'], 0, ',', '.') ?></div>
                        <div class="summary-title">Pendapatan Hari Ini</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="summary-card">
                        <i class="fas fa-boxes summary-icon" style="color:#f59e0b;"></i>
                        <div class="summary-value"><?= $dashboard_summary['produkTerjualHariIni'] ?></div>
                        <div class="summary-title">Produk Terjual</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="summary-card">
                        <i class="fas fa-exclamation-triangle summary-icon" style="color:#ef4444;"></i>
                        <div class="summary-value"><?= $dashboard_summary['StokHabis'] ?></div>
                        <div class="summary-title">Stok Habis</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="summary-card">
                        <i class="fas fa-mobile-alt summary-icon" style="color:#3b82f6;"></i>
                        <div class="summary-value"><?= $dashboard_summary['transaksiPulsaHariIni'] ?></div>
                        <div class="summary-title">Transaksi Pulsa</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="summary-card">
                        <i class="fas fa-coins summary-icon" style="color:#f59e0b;"></i>
                        <div class="summary-value">Rp <?= number_format($dashboard_summary['pendapatanPulsaHariIni'], 0, ',', '.') ?></div>
                        <div class="summary-title">Pendapatan Pulsa</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="summary-card">
                        <i class="fas fa-chart-line summary-icon" style="color:#10b981;"></i>
                        <div class="summary-value profit-positive">Rp <?= number_format($dashboard_summary['keuntunganPulsaHariIni'], 0, ',', '.') ?></div>
                        <div class="summary-title">Keuntungan Pulsa</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="summary-card">
                        <i class="fas fa-chart-pie summary-icon" style="color:#8b5cf6;"></i>
                        <div class="summary-value">Rp <?= number_format($dashboard_summary['totalPendapatanGabungan'], 0, ',', '.') ?></div>
                        <div class="summary-title">Total Pendapatan</div>
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
<script src="/assets/plugins/sweetalert2/sweetalert2.min.js"></script>

<script>
    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '<?= session()->getFlashdata('success') ?>',
            confirmButtonColor: '#6366f1',
            background: '#ffffff',
            color: '#111827'
        });
    <?php endif; ?>

    // Animasi untuk cards
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.summary-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';

            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>
<?= $this->endSection(); ?>