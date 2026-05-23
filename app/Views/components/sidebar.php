<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulsa IO - Sidebar</title>
    <link rel="icon" href="<?= base_url('assets/img/logo.jpg'); ?>" type="image/gif" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        /* === Sidebar Styling Modern === */
        .main-sidebar {
            background: linear-gradient(135deg, #f8f9fb, #ffffff) !important;
            border-right: 1px solid #e5e7eb !important;
            min-height: 100vh;
        }

        /* Brand/Logo Section */
        .brand-link {
            border-bottom: 1px solid #f0f0f0 !important;
            padding: 1rem 0.8rem !important;
            text-decoration: none;
            display: flex !important;
            align-items: center;
            justify-content: flex-start !important;
        }

        .brand-image {
            opacity: .95;
            width: 42px;
            height: 42px;
            object-fit: cover;
        }

        .brand-text {
            font-weight: 700 !important;
            color: #4f46e5 !important;
            font-size: 1.1rem !important;
            margin-left: 8px;
        }

        /* Sidebar Content */
        .sidebar {
            padding: 1rem 0.8rem !important;
            color: #4c4c6d;
        }

        /* Navigation Items */
        .nav-sidebar {
            width: 100% !important;
        }

        .nav-sidebar .nav-link {
            border-radius: 10px;
            color: #4b5563;
            font-weight: 500;
            transition: all 0.25s ease-in-out;
            display: flex;
            align-items: center;
            padding: 10px 15px;
            margin-bottom: 4px;
            width: 100%;
            text-align: left;
        }

        .nav-sidebar .nav-link i {
            color: #6366f1;
            width: 22px;
            text-align: center;
            transition: color 0.2s ease, transform 0.2s ease;
            margin-right: 10px;
            font-size: 1rem;
        }

        .nav-sidebar .nav-link p {
            margin: 0;
            flex: 1;
            text-align: left;
        }

        .nav-sidebar .nav-link:hover {
            background: rgba(99, 102, 241, 0.08);
            color: #4338ca;
            transform: translateX(3px);
        }

        .nav-sidebar .nav-link:hover i {
            color: #7c3aed;
            transform: scale(1.1);
        }

        .nav-sidebar .nav-link.active {
            background: linear-gradient(135deg, #6366f1, #7c3aed);
            color: #fff !important;
            box-shadow: 0 3px 10px rgba(99, 102, 241, 0.25);
        }

        .nav-sidebar .nav-link.active i {
            color: #fff !important;
        }

        /* Section Headers */
        .nav-header {
            font-size: 0.75rem;
            color: #9ca3af;
            letter-spacing: 0.05em;
            font-weight: 600;
            margin-top: 20px;
            margin-bottom: 8px !important;
            padding-left: 15px;
            text-align: left;
        }

        /* Remove default padding/margin yang tidak diperlukan */
        .nav {
            padding-left: 0 !important;
        }

        .nav-item {
            width: 100%;
        }

        /* Ensure everything is left-aligned */
        .nav-pills {
            align-items: flex-start !important;
        }

        .flex-column {
            align-items: flex-start !important;
        }
    </style>
</head>
<body>
    <?php $user = session()->get(); ?>
    
    <aside class="main-sidebar elevation-4">
        <!-- Logo / Brand -->
        <a href="/dashboard" class="brand-link">
            <img src="/assets/img/logo.jpg"
                alt="Pulsa IO Logo"
                class="brand-image img-circle elevation-2">
            <span class="brand-text">
                Puput <span style="color:#7c3aed;"> Cell </span>
            </span>
        </a>

        <!-- Sidebar Menu -->
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="/dashboard" class="nav-link <?= $page == 'dashboard' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <!-- Master Data -->
                    <li class="nav-header text-uppercase">Master Data</li>

                    <li class="nav-item">
                        <a href="/master-data/produk" class="nav-link <?= $page == 'produk' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-box-open"></i>
                            <p>Produk</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/master-data/kategori" class="nav-link <?= $page == 'kategori' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-layer-group"></i>
                            <p>Kategori</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/master-data/satuan" class="nav-link <?= $page == 'satuan' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-ruler-combined"></i>
                            <p>Satuan</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/provider" class="nav-link <?= $page == 'provider' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-network-wired"></i>
                            <p>Provider</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/nominal" class="nav-link <?= $page == 'nominal' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-money-bill-wave"></i>
                            <p>Nominal Pulsa</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= base_url('laporan-pulsa') ?>"
                            class="nav-link <?= $page == 'laporan_pulsa' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-file-invoice"></i>
                            <p>Pencatatan Pulsa</p>
                        </a>
                    </li>

                    <!-- Menu -->
                    <li class="nav-header text-uppercase">Menu</li>

                    <!-- Kasir -->
                    <li class="nav-item">
                        <a href="/menu/kasir" target="_blank" class="nav-link">
                            <i class="nav-icon fas fa-cash-register"></i>
                            <p>Kasir</p>
                        </a>
                    </li>

                    <!-- Laporan -->
                    <li class="nav-header text-uppercase">Laporan</li>

                    <li class="nav-item">
                        <a href="/laporan/penjualan" class="nav-link <?= $page == 'penjualan' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-chart-line"></i>
                            <p>Laporan Penjualan</p>
                        </a>
                    </li>

                    <!-- Setting -->
                    <li class="nav-header text-uppercase">Settingan</li>

                    <li class="nav-item">
                        <a href="/setting/user" class="nav-link <?= $page == 'user' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <p>User</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>