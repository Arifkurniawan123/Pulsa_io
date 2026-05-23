<?= $this->extend('layout/default'); ?>

<?= $this->section('style'); ?>
<!-- SweetAlert2 -->
<link rel="stylesheet" href="/assets/plugins/sweetalert2/sweetalert2.min.css" />
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <h3 class="card-title"><?= $form_name; ?></h3>
                                <a href="/master-data/produk" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <?php $validation = session()->get('validation') ?? \Config\Services::validation(); ?>

                            <form action="<?= '/master-data/produk/update/' . $produk->id; ?>" method="post" id="produkForm">
                                <?= csrf_field(); ?>

                                <!-- Nama Produk -->
                                <div class="form-group">
                                    <label for="produk">Nama Produk</label>
                                    <input 
                                        type="text" 
                                        class="form-control <?= $validation->hasError('produk') ? 'is-invalid' : '' ?>" 
                                        id="produk" 
                                        name="produk" 
                                        placeholder="Nama produk"
                                        value="<?= old('produk', $produk->nama_produk) ?>">
                                    <span class="error invalid-feedback"><?= $validation->getError('produk') ?></span>
                                </div>

                                <!-- Harga -->
                                <div class="form-group">
                                    <label for="harga">Harga</label>
                                    <input 
                                        type="text" 
                                        class="form-control <?= $validation->hasError('harga') ? 'is-invalid' : '' ?>" 
                                        id="harga" 
                                        name="harga" 
                                        placeholder="Contoh: 10000 atau 10000.50"
                                        value="<?= old('harga', $produk->harga) ?>">
                                    <span class="error invalid-feedback"><?= $validation->getError('harga') ?></span>
                                </div>

                                <!-- Stok -->
                                <div class="form-group">
                                    <label for="stok">Stok</label>
                                    <input 
                                        type="number" 
                                        class="form-control <?= $validation->hasError('stok') ? 'is-invalid' : '' ?>" 
                                        id="stok" 
                                        name="stok" 
                                        placeholder="Masukkan jumlah stok produk"
                                        value="<?= old('stok', $produk->stok) ?>">
                                    <span class="error invalid-feedback"><?= $validation->getError('stok') ?></span>
                                </div>

                                <!-- Kategori -->
                                <div class="form-group">
                                    <label for="kategori">Kategori</label>
                                    <select 
                                        class="form-control <?= $validation->hasError('kategori') ? 'is-invalid' : '' ?>" 
                                        name="kategori" 
                                        id="kategori">
                                        <?php foreach ($kategori as $k): ?>
                                            <option value="<?= $k->id; ?>" <?= old('kategori', $produk->kategori_id) == $k->id ? 'selected' : '' ?>>
                                                <?= $k->nama_kategori; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="error invalid-feedback"><?= $validation->getError('kategori') ?></span>
                                </div>

                                <!-- Satuan -->
                                <div class="form-group">
                                    <label for="satuan">Satuan</label>
                                    <select 
                                        class="form-control <?= $validation->hasError('satuan') ? 'is-invalid' : '' ?>" 
                                        name="satuan" 
                                        id="satuan">
                                        <?php foreach ($satuan as $s): ?>
                                            <option value="<?= $s->id; ?>" <?= old('satuan', $produk->satuan_id) == $s->id ? 'selected' : '' ?>>
                                                <?= $s->nama_satuan; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="error invalid-feedback"><?= $validation->getError('satuan') ?></span>
                                </div>

                                <div class="d-flex align-items-center justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-save mr-1"></i> Update
                                    </button>
                                </div>
                            </form>
                        </div> <!-- end card body -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection(); ?>

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