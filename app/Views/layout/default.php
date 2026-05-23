<!DOCTYPE html>
<html lang="en">

<head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?= esc($title) ?></title>

      <link rel="icon" href="<?= base_url('assets/img/favicon_pulsaio.png?v=' . time()); ?>" type="image/png">

      <!-- Google Font -->
      <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

      <!-- Font Awesome -->
      <link rel="stylesheet" href="<?= base_url('assets/plugins/fontawesome-free/css/all.min.css'); ?>">

      <!-- OverlayScrollbars -->
      <link rel="stylesheet" href="<?= base_url('assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css'); ?>">

      <!-- Theme Style -->
      <link rel="stylesheet" href="<?= base_url('assets/css/adminlte.min.css'); ?>">

      <!-- Custom Style -->
      <style>
            :root {
                  --sidebar-width: 230px;
                  --navbar-height: 56px;
            }

            body {
                  font-family: 'Source Sans Pro', sans-serif;
                  background-color: #f4f6f9;
                  overflow-x: hidden;
            }

            /* ===== NAVBAR ===== */
            .main-header {
                  position: fixed;
                  top: 0;
                  left: 0;
                  width: 100%;
                  height: var(--navbar-height);
                  z-index: 1039;
                  background: #fff;
                  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            }

            /* ===== SIDEBAR ===== */
            .main-sidebar {
                  position: fixed;
                  top: var(--navbar-height);
                  left: 0;
                  width: var(--sidebar-width);
                  height: calc(100vh - var(--navbar-height));
                  overflow-y: auto;
                  background-color: #343a40;
            }

            /* ===== CONTENT ===== */
            .content-wrapper {
                  margin-top: var(--navbar-height);
                  margin-left: var(--sidebar-width);
                  padding: 25px 30px;
                  min-height: calc(100vh - var(--navbar-height));
                  background-color: #f9fafb;
            }

            /* ===== FOOTER ===== */
            footer.main-footer {
                  margin-left: var(--sidebar-width);
                  background: #fff;
                  border-top: 1px solid #dee2e6;
                  padding: 10px 20px;
            }

            /* ===== RESPONSIVE FIX ===== */
            @media (max-width: 992px) {
                  .main-sidebar {
                        position: fixed;
                        width: 200px;
                        left: -200px;
                        transition: left 0.3s ease;
                  }

                  .main-sidebar.sidebar-open {
                        left: 0;
                  }

                  .content-wrapper,
                  footer.main-footer {
                        margin-left: 0 !important;
                        padding: 20px 15px;
                  }
            }

            /* ===== FIX DATATABLE RESPONSIVE ===== */
            table.dataTable {
                  width: 100% !important;
            }
      </style>

      <?= $this->renderSection('style'); ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
      <div class="wrapper">

            <!-- Navbar -->
            <?= $this->include('components/navbar'); ?>

            <!-- Sidebar -->
            <?= $this->include('components/sidebar'); ?>

            <!-- Content -->
            <?= $this->renderSection('content'); ?>

            <!-- Footer -->
            <?= $this->include('components/footer'); ?>

      </div>

      <!-- jQuery -->
      <script src="<?= base_url('assets/plugins/jquery/jquery.min.js'); ?>"></script>
      <!-- Bootstrap -->
      <script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
      <!-- OverlayScrollbars -->
      <script src="<?= base_url('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js'); ?>"></script>
      <!-- AdminLTE -->
      <script src="<?= base_url('assets/js/adminlte.min.js'); ?>"></script>

      <?= $this->renderSection('script'); ?>
</body>

</html>