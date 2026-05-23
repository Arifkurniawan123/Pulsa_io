<?= $this->extend('layout/default'); ?>

<?= $this->section('title') ?>Edit Provider Pulsa<?= $this->endSection() ?>

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
                                <h3 class="card-title">Form Edit Provider</h3>
                                <a href="/provider" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php $validation = session()->get('validation') ?? \Config\Services::validation(); ?>

                            <form action="/provider/update/<?= $provider['id'] ?>" method="post" id="providerForm">
                                <?= csrf_field() ?>

                                <div class="form-group">
                                    <label for="nama_provider">Nama Provider</label>
                                    <input type="text"
                                        class="form-control <?= $validation->hasError('nama_provider') ? 'is-invalid' : '' ?>"
                                        id="nama_provider"
                                        name="nama_provider"
                                        value="<?= old('nama_provider', $provider['nama_provider']) ?>"
                                        placeholder="Contoh: Telkomsel, Indosat, XL"
                                        required>
                                    <span class="error invalid-feedback"><?= $validation->getError('nama_provider') ?></span>
                                </div>

                                <div class="form-group">
                                    <label for="kode_provider">Kode Provider</label>
                                    <input type="text"
                                        class="form-control <?= $validation->hasError('kode_provider') ? 'is-invalid' : '' ?>"
                                        id="kode_provider"
                                        name="kode_provider"
                                        value="<?= old('kode_provider', $provider['kode_provider']) ?>"
                                        placeholder="Contoh: TSEL, ISAT, XL"
                                        maxlength="100"
                                        style="text-transform: uppercase;"
                                        required>
                                    <span class="error invalid-feedback"><?= $validation->getError('kode_provider') ?></span>
                                </div>

                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control <?= $validation->hasError('status') ? 'is-invalid' : '' ?>"
                                        id="status"
                                        name="status"
                                        required>
                                        <option value="active" <?= old('status', $provider['status']) == 'active' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="inactive" <?= old('status', $provider['status']) == 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                                    </select>
                                    <span class="error invalid-feedback"><?= $validation->getError('status') ?></span>
                                </div>

                                <div class="d-flex align-items-center justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-save mr-1"></i> Update
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
    // Auto uppercase for kode provider
    document.getElementById('kode_provider').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Opss..',
            text: '<?= session()->getFlashdata('error') ?>'
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('success') ?>'
        });
    <?php endif; ?>
</script>
<?= $this->endSection(); ?>