<?= $this->extend('layout/default'); ?>

<?= $this->section('style'); ?>
<link rel="stylesheet" href="/assets/plugins/sweetalert2/sweetalert2.min.css" />
<link rel="icon" href="<?= base_url('assets/img/logo.jpg'); ?>" type="image/gif" />

<style>
    /* ... semua style yang sudah ada ... */
    
    /* 🔥 TAMBAHKAN INI UNTUK WEBSOCKET */
    .fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .flash {
        animation: flash 0.5s ease-in-out 2;
    }
    @keyframes flash {
        0%, 100% { background-color: white; }
        50% { background-color: #fef3c7; }
    }
    .status-online {
        color: #22c55e;
    }
    .status-offline {
        color: #ef4444;
    }
    #live-notif {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
        width: 100%;
    }
    .notif-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        padding: 12px 16px;
        margin-bottom: 8px;
        border-left: 4px solid #6366f1;
        animation: slideInRight 0.5s ease;
    }
    @keyframes slideInRight {
        from { transform: translateX(100px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .notif-card.produk { border-left-color: #8b5cf6; }
    .notif-card.pulsa { border-left-color: #f59e0b; }
    .notif-card .notif-title { font-weight: 600; font-size: 0.9rem; }
    .notif-card .notif-detail { font-size: 0.8rem; color: #6b7280; }
    .notif-card .notif-time { font-size: 0.7rem; color: #9ca3af; }
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<div class="content-wrapper">
    <section class="content pt-4">
        <div class="container-fluid dashboard-container">
            
            <!-- 🔥 WEBSOCKET STATUS -->
            <div class="flex justify-end mb-3">
                <div class="flex items-center gap-2 bg-white px-3 py-1 rounded-full shadow-sm text-sm">
                    <span id="ws-status" class="flex items-center gap-1">
                        <i class="fas fa-circle text-gray-400 text-xs"></i>
                        <span class="text-gray-400">Connecting...</span>
                    </span>
                    <span id="ws-time" class="text-gray-400 text-xs"></span>
                </div>
            </div>

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

            <!-- 🔥 TRANSAKSI TERBARU (WEBSOCKET) -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex justify-between items-center mb-3 border-b pb-2">
                            <h5 class="font-bold text-gray-700">
                                <i class="fas fa-clock text-gray-400"></i> Transaksi Terbaru
                            </h5>
                            <span id="total-transaksi-live" class="text-sm text-gray-400">0 transaksi</span>
                        </div>
                        <div id="list-transaksi-live" class="max-h-64 overflow-y-auto">
                            <p class="text-gray-400 text-sm text-center py-4">Tunggu transaksi pertama...</p>
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

    // ================================================================
    // 🔥 WEBSOCKET UNTUK NOTIFIKASI LIVE
    // ================================================================
    let totalLive = 0;
    const listContainer = document.getElementById('list-transaksi-live');
    const totalSpan = document.getElementById('total-transaksi-live');

    function formatRupiah(angka) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);
    }

    function addTransaction(data) {
        // Update total
        totalLive++;
        totalSpan.textContent = totalLive + ' transaksi';

        // Buat card
        const card = document.createElement('div');
        card.className = 'border-b border-gray-100 last:border-0 py-2 fade-in flash';
        
        let icon = data.type === 'produk' 
            ? '<i class="fas fa-box text-purple-500"></i>' 
            : '<i class="fas fa-mobile-alt text-orange-500"></i>';
        
        let title = data.type === 'produk' 
            ? data.no_invoice 
            : data.no_transaksi;
        
        let detail = data.type === 'produk'
            ? (data.items || []).join(', ')
            : `${data.provider} - ${formatRupiah(data.nominal)} ke ${data.no_tujuan}`;
        
        let totalHarga = data.type === 'produk'
            ? formatRupiah(data.total)
            : formatRupiah(data.harga_jual);

        card.innerHTML = `
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-2">
                        ${icon}
                        <span class="font-bold text-sm">${title}</span>
                        <span class="text-xs text-gray-400">${data.created_at || ''}</span>
                    </div>
                    <div class="text-xs text-gray-600">${detail}</div>
                    <div class="text-xs text-gray-400">Kasir: ${data.kasir || '-'}</div>
                </div>
                <div class="font-bold text-green-600">${totalHarga}</div>
            </div>
        `;

        listContainer.prepend(card);
        
        // Batasi max 20 item
        while (listContainer.children.length > 20) {
            listContainer.removeChild(listContainer.lastChild);
        }
    }

    function connectWebSocket() {
        const ws = new WebSocket('ws://localhost:8081');
        const statusEl = document.getElementById('ws-status');
        const timeEl = document.getElementById('ws-time');
        
        ws.onopen = function() {
            console.log('WebSocket connected');
            statusEl.innerHTML = `
                <i class="fas fa-circle text-green-500 text-xs"></i>
                <span class="text-green-500">Live</span>
            `;
            timeEl.textContent = new Date().toLocaleTimeString();
        };

        ws.onmessage = function(event) {
            const data = JSON.parse(event.data);
            console.log('Received:', data);
            
            if (data.event === 'new_transaction' || data.event === 'initial_data') {
                const items = Array.isArray(data.data) ? data.data : [data.data];
                items.forEach(item => {
                    if (item && item.no_invoice) {
                        item.type = 'produk';
                        addTransaction(item);
                    } else if (item && item.no_transaksi) {
                        item.type = 'pulsa';
                        addTransaction(item);
                    }
                });
            }
        };

        ws.onclose = function() {
            console.log('WebSocket disconnected');
            statusEl.innerHTML = `
                <i class="fas fa-circle text-red-500 text-xs"></i>
                <span class="text-red-500">Offline</span>
            `;
            // Reconnect after 3 seconds
            setTimeout(connectWebSocket, 3000);
        };

        ws.onerror = function(error) {
            console.error('WebSocket error:', error);
            ws.close();
        };
    }

    // Start WebSocket
    connectWebSocket();

    // Update time setiap detik
    setInterval(() => {
        const timeEl = document.getElementById('ws-time');
        if (timeEl && !timeEl.textContent.includes('Offline')) {
            timeEl.textContent = new Date().toLocaleTimeString();
        }
    }, 1000);
</script>
<?= $this->endSection(); ?>