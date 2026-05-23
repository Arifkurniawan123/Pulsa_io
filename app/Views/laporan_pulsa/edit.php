<?= $this->extend('layout/default'); ?>

<?= $this->section('title') ?>Edit Transaksi Pulsa<?= $this->endSection() ?>

<?= $this->section('style'); ?>
<!-- SweetAlert2 -->
<link rel="stylesheet" href="/assets/plugins/sweetalert2/sweetalert2.min.css">
<?= $this->endSection(); ?>

<?= $this->section('content') ?>
<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <h3 class="card-title">Form Edit Transaksi Pulsa</h3>
                                <a href="/laporan-pulsa" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php $validation = session()->get('validation') ?? \Config\Services::validation(); ?>

                            <form action="/laporan-pulsa/update/<?= $transaksi['id'] ?>" method="post" id="transaksiForm">
                                <?= csrf_field() ?>

                                <!-- No Transaksi (Readonly) -->
                                <div class="form-group">
                                    <label>No Transaksi</label>
                                    <input type="text"
                                        class="form-control bg-light"
                                        value="<?= esc($transaksi['no_transaksi']) ?>"
                                        readonly>
                                </div>

                                <!-- No Tujuan -->
                                <div class="form-group">
                                    <label for="no_tujuan">No Tujuan</label>
                                    <input type="text"
                                        class="form-control <?= $validation->hasError('no_tujuan') ? 'is-invalid' : '' ?>"
                                        id="no_tujuan"
                                        name="no_tujuan"
                                        value="<?= old('no_tujuan', $transaksi['no_tujuan']) ?>"
                                        placeholder="Contoh: 081234567890"
                                        required>
                                    <span class="error invalid-feedback"><?= $validation->getError('no_tujuan') ?></span>
                                </div>

                                <!-- Provider -->
                                <div class="form-group">
                                    <label for="provider_id">Provider</label>
                                    <select class="form-control <?= $validation->hasError('provider_id') ? 'is-invalid' : '' ?>"
                                        id="provider_id"
                                        name="provider_id"
                                        required>
                                        <option value="">Pilih Provider</option>
                                        <?php foreach ($providers as $provider): ?>
                                            <option value="<?= $provider['id'] ?>" <?= old('provider_id', $transaksi['provider_id']) == $provider['id'] ? 'selected' : '' ?>>
                                                <?= esc($provider['nama_provider']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="error invalid-feedback"><?= $validation->getError('provider_id') ?></span>
                                </div>

                                <!-- Nominal Pulsa -->
                                <div class="form-group">
                                    <label for="nominal_id">Nominal Pulsa</label>
                                    <select class="form-control <?= $validation->hasError('nominal_id') ? 'is-invalid' : '' ?>"
                                        id="nominal_id"
                                        name="nominal_id"
                                        required>
                                        <option value="">Pilih Nominal</option>
                                        <?php foreach ($nominals as $nominal): ?>
                                            <option value="<?= $nominal['id'] ?>"
                                                <?= old('nominal_id', $transaksi['nominal_id']) == $nominal['id'] ? 'selected' : '' ?>>
                                                <?= number_format($nominal['nominal'], 0, ',', '.') ?>
                                                (Rp <?= number_format($nominal['harga_jual'], 0, ',', '.') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="error invalid-feedback"><?= $validation->getError('nominal_id') ?></span>
                                </div>

                                <!-- Metode Pembayaran -->
                                <div class="form-group">
                                    <label for="metode_pembayaran">Metode Pembayaran</label>
                                    <select class="form-control <?= $validation->hasError('metode_pembayaran') ? 'is-invalid' : '' ?>"
                                        id="metode_pembayaran"
                                        name="metode_pembayaran"
                                        required>
                                        <option value="tunai" <?= old('metode_pembayaran', $transaksi['metode_pembayaran']) == 'tunai' ? 'selected' : '' ?>>Tunai</option>
                                        <option value="saldo" <?= old('metode_pembayaran', $transaksi['metode_pembayaran']) == 'saldo' ? 'selected' : '' ?>>Saldo</option>
                                        <option value="transfer" <?= old('metode_pembayaran', $transaksi['metode_pembayaran']) == 'transfer' ? 'selected' : '' ?>>Transfer</option>
                                        <option value="grip" <?= old('metode_pembayaran', $transaksi['metode_pembayaran']) == 'grip' ? 'selected' : '' ?>>Grip</option>
                                    </select>
                                    <span class="error invalid-feedback"><?= $validation->getError('metode_pembayaran') ?></span>
                                </div>

                                <!-- Status -->
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control <?= $validation->hasError('status') ? 'is-invalid' : '' ?>"
                                        id="status"
                                        name="status"
                                        required>
                                        <option value="proses" <?= old('status', $transaksi['status']) == 'proses' ? 'selected' : '' ?>>Proses</option>
                                        <option value="sukses" <?= old('status', $transaksi['status']) == 'sukses' ? 'selected' : '' ?>>Sukses</option>
                                        <option value="gagal" <?= old('status', $transaksi['status']) == 'gagal' ? 'selected' : '' ?>>Gagal</option>
                                    </select>
                                    <span class="error invalid-feedback"><?= $validation->getError('status') ?></span>
                                </div>

                                <div class="d-flex align-items-center justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-save mr-1"></i> Update Transaksi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('script'); ?>
<!-- SweetAlert2 -->
<script src="/assets/plugins/sweetalert2/sweetalert2.min.js"></script>

<script>
    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Opss..',
            text: '<?= session()->getFlashdata('error') ?>'
        });
    <?php endif; ?>
</script>
<?= $this->endSection(); ?>