<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title); ?></title>

  <!-- Favicon -->
  <link rel="icon" href="<?= base_url('assets/img/logo.jpg'); ?>" type="image/jpeg">

  <!-- Google Font -->
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600&display=fallback">

  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="<?= base_url('assets/plugins/fontawesome-free/css/all.min.css'); ?>">

  <!-- overlayScrollbars -->
  <link rel="stylesheet"
        href="<?= base_url('assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css'); ?>">

  <!-- AdminLTE -->
  <link rel="stylesheet"
        href="<?= base_url('assets/css/adminlte.min.css'); ?>">

  <!-- Custom Pulsa IO Style -->
  <style>
    :root {
        --primary-color: #6366f1;
        --bg-light: #f9fafb;
        --text-dark: #1f2937;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: var(--bg-light);
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        color: var(--text-dark);
        margin: 0;
    }

    .auth-wrapper {
        background: #ffffff;
        border-radius: 16px;
        padding: 2rem 2.5rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        width: 100%;
        max-width: 420px;
        text-align: center;
        animation: fadeIn 0.6s ease-in-out;
    }

    .auth-wrapper img {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 0.8rem;
    }

    .auth-wrapper h1 {
        font-size: 1.6rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .auth-wrapper p {
        color: #6b7280;
        margin-bottom: 1.5rem;
    }

    .form-control {
        border-radius: 10px;
        border: 1px solid #d1d5db;
        padding: 0.75rem 1rem;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.2);
    }

    .btn-primary {
        background: var(--primary-color);
        border: none;
        border-radius: 10px;
        font-weight: 600;
        padding: 0.7rem;
        transition: 0.3s ease;
    }

    .btn-primary:hover {
        background: #4f46e5;
        transform: translateY(-1px);
    }

    .footer-text {
        font-size: 0.9rem;
        color: #9ca3af;
        margin-top: 1rem;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
  </style>

  <?= $this->renderSection('style'); ?>
</head>

<body>
  <?= $this->renderSection('content'); ?>

  <script src="<?= base_url('assets/plugins/jquery/jquery.min.js'); ?>"></script>
  <script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
  <script src="<?= base_url('assets/js/adminlte.min.js'); ?>"></script>

  <?= $this->renderSection('script'); ?>
</body>
</html>
