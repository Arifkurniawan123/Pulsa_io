<?= $this->extend('layout/default'); ?>

<?= $this->section('style'); ?>
<!-- DataTables -->
<link rel="stylesheet" href="/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" href="/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
<link rel="stylesheet" href="/assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css" />
<link rel="icon" href="<?= base_url('assets/img/logo.jpg'); ?>" type="image/gif" />

<!-- SweetAlert2 -->
<link rel="stylesheet" href="/assets/plugins/sweetalert2/sweetalert2.min.css" />
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <h3 class="card-title">Data Nominal Pulsa</h3>
                                <a href="/nominal/create" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i>
                                    Tambah Nominal
                                </a>
                            </div>
                        </div>
                        <div class="card-body">

                            <!-- Filter Provider -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <form method="get" class="mb-0">
                                        <div class="row align-items-end">
                                            <div class="col-md-4">
                                                <label for="provider_id" class="form-label">Filter Berdasarkan Provider</label>
                                                <select class="form-control" id="provider_id" name="provider_id" onchange="this.form.submit()">
                                                    <option value="">Semua Provider</option>
                                                    <?php foreach ($providers as $provider): ?>
                                                        <option value="<?= esc($provider['id']) ?>"
                                                            <?= ($selectedProvider ?? '') == $provider['id'] ? 'selected' : '' ?>>
                                                            <?= esc($provider['nama_provider']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <a href="/nominal" class="btn btn-secondary w-100">Reset</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Data Table -->
                            <?php if (!empty($nominals)): ?>
                                <table id="tableNominal" class="table table-bordered table-striped display nowrap">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Provider</th>
                                            <th>Nominal</th>
                                            <th>Harga Modal</th>
                                            <th>Harga Jual</th>
                                            <th>Keuntungan</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($nominals as $i => $n): ?>
                                            <tr>
                                                <td><?= $i + 1; ?></td>
                                                <td><?= esc($n['nama_provider'] ?? '-'); ?></td>
                                                <td class="text-right"><?= number_format($n['nominal'], 0, ',', '.'); ?></td>
                                                <td class="text-right">Rp <?= number_format($n['harga_modal'], 0, ',', '.'); ?></td>
                                                <td class="text-right">Rp <?= number_format($n['harga_jual'], 0, ',', '.'); ?></td>
                                                <td class="text-right">
                                                    <?php
                                                        $profit = $n['harga_jual'] - $n['harga_modal'];
                                                        $color = $profit > 0 ? 'text-success' : ($profit < 0 ? 'text-danger' : 'text-muted');
                                                    ?>
                                                    <span class="<?= $color; ?>">Rp <?= number_format($profit, 0, ',', '.'); ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge <?= $n['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                                        <?= $n['status'] === 'active' ? 'Aktif' : 'Nonaktif'; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="<?= base_url('nominal/edit/' . $n['id']); ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="<?= $n['id']; ?>">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Belum ada data nominal</h5>
                                    <p class="text-muted">Mulai dengan menambahkan nominal pertama Anda</p>
                                    <a href="/nominal/create" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Tambah Nominal Pertama
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection(); ?>


<?= $this->section('script'); ?>
<!-- DataTables & SweetAlert -->
<script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="/assets/plugins/sweetalert2/sweetalert2.min.js"></script>

<script>
    $(function() {
        $("#tableNominal").DataTable({
            responsive: false,
            lengthChange: true,
            autoWidth: false,
            scrollX: true,
            pageLength: 10,
            columnDefs: [
                { targets: 0, searchable: false, width: '50px' },
                { targets: [1,2,3,4,5,6], searchable: true },
                { targets: 7, searchable: false, orderable: false, width: '100px', className: 'text-center' }
            ]
        });

        // SweetAlert delete
        $('.btn-delete').click(function() {
            const id = $(this).data('id');
            const url = '<?= base_url('nominal/delete/') ?>' + id;
            const csrfToken = '<?= csrf_token(); ?>';
            const csrfHash = '<?= csrf_hash(); ?>';

            Swal.fire({
                title: "Yakin hapus data?",
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#C82333",
                confirmButtonText: "Ya, hapus!",
                cancelButtonColor: "#5A6268",
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: { [csrfToken]: csrfHash },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    title: "Dihapus",
                                    text: res.message,
                                    icon: "success"
                                }).then(() => location.reload());
                            }
                        },
                        error: function(xhr) {
                            const errMsg = xhr.responseJSON?.message || "Terjadi kesalahan!";
                            Swal.fire({
                                title: 'Opss..',
                                text: errMsg,
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });
    });

    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Sukses',
            text: '<?= session()->getFlashdata('success') ?>'
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Opss..',
            text: '<?= session()->getFlashdata('error') ?>'
        });
    <?php endif; ?>
</script>
<?= $this->endSection(); ?>
