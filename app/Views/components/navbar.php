<?php $user = session()->get(); ?>
<link rel="icon" href="<?= base_url('assets/img/logo.jpg'); ?>" type="image/gif" />

<nav class="main-header navbar navbar-expand navbar-light sticky-top shadow-sm"
    style="background-color: #ffffff; border-bottom: 1px solid #e5e7eb; padding: 0.7rem 1rem;">

    <!-- Tombol sidebar -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link text-secondary" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>

    <!-- Bagian kanan (profil user) -->
    <ul class="navbar-nav ml-auto align-items-center">

        <li class="nav-item dropdown mr-3">
            <a href="#"
                class="nav-link dropdown-toggle d-flex align-items-center"
                id="userDropdown"
                role="button"
                data-toggle="dropdown"
                data-bs-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
                style="color: #4c4c6d; font-weight: 500;">
                <img src="/assets/img/logo.jpg"
                    class="img-circle elevation-2 border"
                    alt="User"
                    width="38" height="38"
                    style="object-fit: cover; border: 2px solid #667eea;">
                <span class="ml-2"><?= esc($user['name']); ?></span>
            </a>

            <div class="dropdown-menu dropdown-menu-right mt-2 shadow-sm"
                aria-labelledby="userDropdown"
                style="border-radius: 10px; border: 1px solid #f0f0f0; min-width: 160px;">
                <form action="/logout" method="post" class="px-3 py-2 mb-0">
                    <?= csrf_field(); ?>
                    <button type="submit" class="btn btn-block text-white"
                        style="background: #667eea; border-radius: 8px; font-weight: 500;">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>

<style>
    /* === Navbar Styling Modern & Responsive === */
    .navbar {
        justify-content: space-between;
        padding: 0.6rem 1.5rem !important;
    }

    /* Tombol sidebar */
    .navbar-nav:first-child {
        margin-left: 0.3rem;
    }

    .navbar-nav.ml-auto {
        margin-right: 210px !important;
        /* kasih jarak dari tepi kanan */
        display: flex;
        align-items: center;
    }

    /* Tombol profil */
    .navbar-nav .nav-item.dropdown>a {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #374151;
        font-weight: 500;
    }

    .navbar-nav .nav-item.dropdown>a:hover {
        color: #6366f1;
    }

    /* Gaya foto profil */
    .navbar .img-circle {
        border: 2px solid #6366f1;
        width: 38px;
        height: 38px;
        object-fit: cover;
        margin-left: 8px;
    }

    /* === Dropdown fix agar tidak keluar layar kanan === */
    .navbar-nav .dropdown-menu {
        right: 0 !important;
        left: auto !important;
        transform: none !important;
        margin-top: 8px !important;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        min-width: 160px;
    }

    /* Responsif (layar kecil) */
    @media (max-width: 992px) {
        .navbar-nav .dropdown-menu {
            right: 0 !important;
            left: auto !important;
            margin-right: 10px;
        }
    }
</style>

<!-- Script wajib agar dropdown berfungsi -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>