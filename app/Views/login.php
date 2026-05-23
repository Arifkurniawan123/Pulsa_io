<?php
echo $this->extend('layout/auth');
echo $this->section('content');
?>

<style>
    body {
        background-color: #f4f6f9;
        font-family: 'Poppins', sans-serif;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #333;
    }

    .login-box {
        width: 100%;
        max-width: 400px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        padding: 2.5rem 2rem;
        text-align: center;
    }

    .logo-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: #eef1ff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        box-shadow: 0 4px 10px rgba(102, 126, 234, 0.2);
    }

    .logo-circle img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 50%;
    }

    .login-box h1 {
        font-weight: 700;
        font-size: 1.8rem;
        color: #4c4c6d;
        margin-bottom: 0.5rem;
    }

    .login-box p {
        font-size: 0.95rem;
        color: #666;
        margin-bottom: 1.5rem;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 10px 14px;
        font-size: 0.95rem;
        color: #333;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 4px rgba(102, 126, 234, 0.4);
    }

    .input-group-text {
        background-color: #f0f0f5;
        border: 1px solid #ddd;
        border-left: none;
        color: #777;
    }

    .btn-primary {
        background-color: #667eea;
        border: none;
        padding: 10px;
        border-radius: 8px;
        font-weight: 500;
        width: 100%;
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        background-color: #5a67d8;
    }

    .footer-text {
        margin-top: 20px;
        font-size: 0.85rem;
        color: #888;
    }
</style>

<div class="login-box">
    <div class="logo-circle">
        <img src="<?= base_url('assets/img/logo.jpg') ?>" alt="Pulsa IO Logo">
    </div>

    <h1> Puput <span style="color:#667eea;">Cell</span></h1>
    <p>Masuk untuk mengelola transaksi dan data</p>

    <form action="/login/attempt" method="post">
        <?= csrf_field(); ?>

        <div class="input-group mb-3">
            <input type="text" class="form-control" name="username" placeholder="Username" required>
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-user"></span></div>
            </div>
        </div>

        <div class="input-group mb-4">
            <input type="password" class="form-control" name="password" placeholder="Password" required>
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-lock"></span></div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-sign-in-alt"></i> Login
        </button>
    </form>

    <p class="footer-text">&copy; <?= date('Y') ?> Pulsa IO</p>
</div>

<?php echo $this->endSection(); ?>
