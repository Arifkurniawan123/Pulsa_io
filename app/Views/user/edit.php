<?= $this->extend('layout/default'); ?>

<?= $this->section('style'); ?>
<!-- SweetAlert2 -->
<link
    rel="stylesheet"
    href="/assets/plugins/sweetalert2/sweetalert2.min.css" />
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<div class="content-wrapper">
    <!-- Main content -->
    <section class="content pt-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <h3 class="card-title"><?= $form_name; ?></h3>
                                <a href="/setting/user" type="button" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i>
                                    Kembali
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php $validation = session()->getFlashdata('validation') ?? [] ?>

                            <form action="<?= '/setting/user/update/' . $user->id ?>" method="post" enctype="multipart/form-data">
                                <?= csrf_field(); ?>

                                <input type="hidden" id="old-img" name="old-img" value="<?= $user->image ?>">

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="nama_lengkap">Nama Lengkap</label>
                                        <input type="text" class="form-control <?= isset($validation['nama_lengkap']) ? 'is-invalid' : '' ?>" id="nama_lengkap" name="nama_lengkap" placeholder="Nama lengkap" value="<?= old('nama_lengkap', $user->nama_lengkap) ?>">
                                        <span class="invalid-feedback"><?= $validation['nama_lengkap'] ?? '' ?></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="role">Role</label>
                                        <select name="role" id="role" class="form-control <?= isset($validation['role']) ? 'is-invalid' : '' ?>">
                                            <option value="" selected disabled>Pilih Role</option>
                                            <?php foreach ($roles as $role) { ?>
                                                <option value="<?= $role->id; ?>" <?= old('role', $user->role_id) == $role->id ? 'selected' : '' ?>>
                                                    <?= $role->nama_role; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <span class="invalid-feedback"><?= $validation['role'] ?? '' ?></span>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="username">Username</label>
                                        <input type="text" class="form-control <?= isset($validation['username']) ? 'is-invalid' : '' ?>" id="username" name="username" placeholder="Username" value="<?= old('username', $user->username) ?>">
                                        <span class="invalid-feedback"><?= $validation['username'] ?? '' ?></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="password">Password</label>
                                        <input type="password" class="form-control <?= isset($validation['password']) ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="******">
                                        <span class="invalid-feedback"><?= $validation['password'] ?? '' ?></span>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="email">Email</label>
                                        <input type="text" class="form-control <?= isset($validation['email']) ? 'is-invalid' : '' ?>" id="email" name="email" placeholder="user@email.com" value="<?= old('email', $user->email) ?>">
                                        <span class="invalid-feedback"><?= $validation['email'] ?? '' ?></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="no_tlp">No Tlp</label>
                                        <input type="text" class="form-control <?= isset($validation['no_tlp']) ? 'is-invalid' : '' ?>" id="no_tlp" name="no_tlp" placeholder="08123456789" value="<?= old('no_tlp', $user->no_tlp) ?>">
                                        <span class="invalid-feedback"><?= $validation['no_tlp'] ?? '' ?></span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="alamat">Alamat</label>
                                    <textarea name="alamat" id="alamat" rows="3" class="form-control <?= isset($validation['alamat']) ? 'is-invalid' : '' ?>"><?= trim(old('alamat', $user->alamat)) ?></textarea>
                                    <span class="invalid-feedback"><?= $validation['alamat'] ?? '' ?></span>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="image">Pilih Gambar</label>
                                    <div class="custom-file">
                                        <label class="custom-file-label" for="image"><?= $user->image ?></label>
                                        <input type="file" class="custom-file-input <?= isset($validation['image']) ? 'is-invalid' : '' ?>" id="image" name="image" onchange="imgPreview()">
                                        <span class="invalid-feedback"><?= $validation['image'] ?? '' ?></span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-end">
                                    <button type="submit" id="submitModal" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </div>
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
    function imgPreview() {
        const image = $('#image').get(0);
        const label = $('.custom-file-label');

        const file = image.files[0];
        file ? label.text(file.name) : label.text('Pilih Gambar');
    }

    <?php if (session()->getFlashdata('error')) { ?>
        Swal.fire({
            icon: 'error',
            title: 'Opss..',
            text: '<?= session()->getFlashdata('error') ?>'
        });
    <?php } ?>
</script>
<?= $this->endSection(); ?>