<?= $this->extend('layout/default'); ?>

<?= $this->section('title') ?>Form Tambah Nominal<?= $this->endSection() ?>

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
                                <h3 class="card-title">Form Tambah Nominal</h3>
                                <a href="/nominal" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php $validation = session()->get('validation') ?? \Config\Services::validation(); ?>

                            <form action="/nominal/store" method="post" id="nominalForm">
                                <?= csrf_field() ?>

                                <div class="form-group">
                                    <label for="provider_id">Provider</label>
                                    <select class="form-control <?= $validation->hasError('provider_id') ? 'is-invalid' : '' ?>"
                                        id="provider_id"
                                        name="provider_id"
                                        required>
                                        <option value="">Pilih Provider</option>
                                        <?php foreach ($providers as $provider): ?>
                                            <option value="<?= $provider['id'] ?>" <?= old('provider_id') == $provider['id'] ? 'selected' : '' ?>>
                                                <?= esc($provider['nama_provider']) ?> (<?= $provider['kode_provider'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="error invalid-feedback"><?= $validation->getError('provider_id') ?></span>
                                </div>

                                <div class="form-group">
                                    <label for="nominal">Nominal</label>
                                    <input type="number"
                                        class="form-control <?= $validation->hasError('nominal') ? 'is-invalid' : '' ?>"
                                        id="nominal"
                                        name="nominal"
                                        value="<?= old('nominal') ?>"
                                        placeholder="Contoh: 10000"
                                        min="1000"
                                        step="1000"
                                        required>
                                    <small class="form-text text-muted">
                                        Klik untuk menggunakan nominal umum:
                                        <span class="badge badge-light mr-1" style="cursor: pointer;" onclick="fillNominal(5000)">5.000</span>
                                        <span class="badge badge-light mr-1" style="cursor: pointer;" onclick="fillNominal(10000)">10.000</span>
                                        <span class="badge badge-light mr-1" style="cursor: pointer;" onclick="fillNominal(25000)">25.000</span>
                                        <span class="badge badge-light mr-1" style="cursor: pointer;" onclick="fillNominal(50000)">50.000</span>
                                        <span class="badge badge-light mr-1" style="cursor: pointer;" onclick="fillNominal(100000)">100.000</span>
                                    </small>
                                    <span class="error invalid-feedback"><?= $validation->getError('nominal') ?></span>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="harga_modal">Harga Modal</label>
                                            <input type="text"
                                                class="form-control <?= $validation->hasError('harga_modal') ? 'is-invalid' : '' ?>"
                                                id="harga_modal"
                                                name="harga_modal"
                                                value="<?= old('harga_modal') ?>"
                                                placeholder="0"
                                                min="0"
                                                step="1"
                                                required>
                                            <span class="error invalid-feedback"><?= $validation->getError('harga_modal') ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="harga_jual">Harga Jual</label>
                                            <input type="text"
                                                class="form-control <?= $validation->hasError('harga_jual') ? 'is-invalid' : '' ?>"
                                                id="harga_jual"
                                                name="harga_jual"
                                                value="<?= old('harga_jual') ?>"
                                                placeholder="0"
                                                required>
                                            <span class="error invalid-feedback"><?= $validation->getError('harga_jual') ?></span>
                                        </div>
                                    </div>

                                </div>
                        </div>
                    </div>

                    <!-- Keuntungan (Auto Calculate) -->
                    <div class="form-group">
                        <label>Keuntungan</label>
                        <div class="alert alert-info" id="keuntungan-display">
                            <strong>Rp 0</strong>
                            <small class="d-block mt-1">(Otomatis terhitung dari selisih harga jual dan harga modal)</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control <?= $validation->hasError('status') ? 'is-invalid' : '' ?>"
                            id="status"
                            name="status"
                            required>
                            <option value="">Pilih Status</option>
                            <option value="active" <?= old('status') == 'active' ? 'selected' : '' ?>>Aktif</option>
                            <option value="inactive" <?= old('status') == 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                        <span class="error invalid-feedback"><?= $validation->getError('status') ?></span>
                    </div>

                    <div class="d-flex align-items-center justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i> Simpan
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
    // Function to fill nominal with suggestion
    function fillNominal(nominal) {
        document.getElementById('nominal').value = nominal;
        calculateProfit();
    }

    // Auto calculate profit when harga_modal or harga_jual changes
    function calculateProfit() {
        const hargaModal = parseInt(document.getElementById('harga_modal').value) || 0;
        const hargaJual = parseInt(document.getElementById('harga_jual').value) || 0;
        const profit = hargaJual - hargaModal;

        const keuntunganDisplay = document.getElementById('keuntungan-display');

        if (profit > 0) {
            keuntunganDisplay.innerHTML = `<strong>+ Rp ${profit.toLocaleString('id-ID')}</strong>
                                          <small class="d-block mt-1">(Keuntungan positif)</small>`;
            keuntunganDisplay.className = 'alert alert-success';
        } else if (profit < 0) {
            keuntunganDisplay.innerHTML = `<strong>- Rp ${Math.abs(profit).toLocaleString('id-ID')}</strong>
                                          <small class="d-block mt-1">(Kerugian - harga jual lebih rendah)</small>`;
            keuntunganDisplay.className = 'alert alert-danger';
        } else {
            keuntunganDisplay.innerHTML = `<strong>Rp 0</strong>
                                          <small class="d-block mt-1">(Tidak ada keuntungan atau kerugian)</small>`;
            keuntunganDisplay.className = 'alert alert-info';
        }
    }

    // Add event listeners for price calculations
    document.getElementById('harga_modal').addEventListener('input', calculateProfit);
    document.getElementById('harga_jual').addEventListener('input', calculateProfit);

    // Initial calculation
    calculateProfit();

    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Opss..',
            text: '<?= session()->getFlashdata('error') ?>'
        });
    <?php endif; ?>
</script>
<?= $this->endSection(); ?>