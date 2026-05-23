<?= $this->extend('layout/default'); ?>

<?= $this->section('title') ?>Tambah Transaksi Pulsa<?= $this->endSection() ?>

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
                                <h3 class="card-title">Form Tambah Transaksi Pulsa</h3>
                                <a href="/laporan-pulsa" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php $validation = session()->get('validation') ?? \Config\Services::validation(); ?>

                            <form action="/laporan-pulsa/store" method="post" id="transaksiForm">
                                <?= csrf_field() ?>

                                <div class="form-group">
                                    <label for="no_tujuan">No Tujuan</label>
                                    <input type="text"
                                        class="form-control <?= $validation->hasError('no_tujuan') ? 'is-invalid' : '' ?>"
                                        id="no_tujuan"
                                        name="no_tujuan"
                                        value="<?= old('no_tujuan') ?>"
                                        placeholder="Contoh: 081234567890"
                                        required>
                                    <span class="error invalid-feedback"><?= $validation->getError('no_tujuan') ?></span>
                                </div>

                                <div class="form-group">
                                    <label for="metode_pembayaran">Metode Pembayaran</label>
                                    <select class="form-control <?= $validation->hasError('metode_pembayaran') ? 'is-invalid' : '' ?>"
                                        id="metode_pembayaran"
                                        name="metode_pembayaran"
                                        required>
                                        <option value="">Pilih Metode</option>
                                        <option value="tunai" <?= old('metode_pembayaran') == 'tunai' ? 'selected' : '' ?>>Tunai</option>
                                        <option value="saldo" <?= old('metode_pembayaran') == 'saldo' ? 'selected' : '' ?>>Saldo</option>
                                        <option value="transfer" <?= old('metode_pembayaran') == 'transfer' ? 'selected' : '' ?>>Transfer</option>
                                        <option value="grip" <?= old('metode_pembayaran') == 'grip' ? 'selected' : '' ?>>Grip</option>
                                    </select>
                                    <span class="error invalid-feedback"><?= $validation->getError('metode_pembayaran') ?></span>
                                </div>

                                <div class="form-group">
                                    <label for="provider_id">Provider</label>
                                    <select class="form-control <?= $validation->hasError('provider_id') ? 'is-invalid' : '' ?>"
                                        id="provider_id"
                                        name="provider_id"
                                        required>
                                        <option value="">Pilih Provider</option>
                                        <?php foreach ($providers as $provider): ?>
                                            <option value="<?= $provider['id'] ?>" <?= old('provider_id') == $provider['id'] ? 'selected' : '' ?>>
                                                <?= esc($provider['nama_provider']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="error invalid-feedback"><?= $validation->getError('provider_id') ?></span>
                                </div>

                                <div class="form-group">
                                    <label for="nominal_id">Nominal Pulsa</label>
                                    <select class="form-control <?= $validation->hasError('nominal_id') ? 'is-invalid' : '' ?>"
                                        id="nominal_id"
                                        name="nominal_id"
                                        required>
                                        <option value="">Pilih Nominal</option>
                                        <?php foreach ($nominals as $nominal): ?>
                                            <option value="<?= $nominal['id'] ?>"
                                                data-nominal="<?= $nominal['nominal'] ?>"
                                                data-harga-modal="<?= $nominal['harga_modal'] ?>"
                                                data-harga-jual="<?= $nominal['harga_jual'] ?>"
                                                data-keuntungan="<?= $nominal['harga_jual'] - $nominal['harga_modal'] ?>"
                                                <?= old('nominal_id') == $nominal['id'] ? 'selected' : '' ?>>
                                                <?= number_format($nominal['nominal'], 0, ',', '.') ?>
                                                (Rp <?= number_format($nominal['harga_jual'], 0, ',', '.') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="error invalid-feedback"><?= $validation->getError('nominal_id') ?></span>
                                </div>

                                <!-- Preview Harga -->
                                <div class="form-group" id="preview_harga" style="display: none;">
                                    <label>Detail Harga</label>
                                    <div class="alert alert-info">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>Nominal</strong>
                                                <div id="preview_nominal">-</div>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Harga Modal</strong>
                                                <div class="text-danger" id="preview_harga_modal">-</div>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Harga Jual</strong>
                                                <div class="text-primary" id="preview_harga_jual">-</div>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Keuntungan</strong>
                                                <div class="text-success" id="preview_keuntungan">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-save mr-1"></i> Simpan Transaksi
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
    document.addEventListener('DOMContentLoaded', function() {
        const nominalSelect = document.getElementById('nominal_id');
        const previewSection = document.getElementById('preview_harga');
        const previewNominal = document.getElementById('preview_nominal');
        const previewHargaModal = document.getElementById('preview_harga_modal');
        const previewHargaJual = document.getElementById('preview_harga_jual');
        const previewKeuntungan = document.getElementById('preview_keuntungan');

        function formatRupiah(angka) {
            return 'Rp ' + parseInt(angka).toLocaleString('id-ID');
        }

        function updatePreview() {
            const selectedOption = nominalSelect.options[nominalSelect.selectedIndex];

            if (selectedOption.value) {
                const nominal = selectedOption.getAttribute('data-nominal');
                const hargaModal = selectedOption.getAttribute('data-harga-modal');
                const hargaJual = selectedOption.getAttribute('data-harga-jual');
                const keuntungan = selectedOption.getAttribute('data-keuntungan');

                previewNominal.textContent = parseInt(nominal).toLocaleString('id-ID');
                previewHargaModal.textContent = formatRupiah(hargaModal);
                previewHargaJual.textContent = formatRupiah(hargaJual);
                previewKeuntungan.textContent = formatRupiah(keuntungan);

                previewSection.style.display = 'block';
            } else {
                previewSection.style.display = 'none';
            }
        }

        nominalSelect.addEventListener('change', updatePreview);

        // Initial update
        updatePreview();
    });

    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Opss..',
            text: '<?= session()->getFlashdata('error') ?>'
        });
    <?php endif; ?>
</script>
<?= $this->endSection(); ?>