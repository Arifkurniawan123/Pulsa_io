<?= $this->extend('layout/default'); ?>

<?= $this->section('title') ?>Master Provider Pulsa<?= $this->endSection() ?>

<?= $this->section('style'); ?>
<!-- DataTables -->
<link rel="stylesheet" href="/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="icon" href="<?= base_url('assets/img/logo.jpg'); ?>" type="image/gif" />


<!-- SweetAlert2 -->
<link rel="stylesheet" href="/assets/plugins/sweetalert2/sweetalert2.min.css">
<?= $this->endSection(); ?>

<?= $this->section('content') ?>
<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <h3 class="card-title">Data Provider Pulsa</h3>
                                <a href="/provider/create" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i> Tambah Provider
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tableProvider" class="table table-bordered table-striped display nowrap">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Provider</th>
                                        <th>Nama Provider</th>
                                        <th>Status</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($providers as $index => $provider): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= $provider['kode_provider'] ?>
                                                </span>
                                            </td>
                                            <td><?= esc($provider['nama_provider']) ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $provider['status'] == 'active' ? 'badge-success' : 'badge-danger' ?>">
                                                    <?= $provider['status'] == 'active' ? 'Aktif' : 'Nonaktif' ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($provider['created_at'])) ?></td>
                                            <td class="text-center">
                                                <a href="<?= base_url('provider/edit/' . $provider['id']) ?>" 
                                                   class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                    data-id="<?= $provider['id'] ?>"
                                                    data-name="<?= esc($provider['nama_provider']) ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('script'); ?>
<!-- DataTables & Plugins -->
<script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>

<!-- SweetAlert2 -->
<script src="/assets/plugins/sweetalert2/sweetalert2.min.js"></script>

<script>
    $(function() {
        $("#tableProvider").DataTable({
            responsive: false,
            lengthChange: true,
            autoWidth: false,
            scrollX: true,
            pageLength: 10,
            columnDefs: [{
                    targets: 0,
                    searchable: false,
                    width: '50px'
                },
                {
                    targets: 1,
                    searchable: true,
                },
                {
                    targets: 2,
                    searchable: true,
                },
                {
                    targets: 3,
                    searchable: true,
                },
                {
                    targets: 4,
                    searchable: true,
                },
                {
                    targets: 5,
                    searchable: false,
                    orderable: false,
                    width: '100px',
                    className: 'text-center'
                }
            ]
        });

        // SweetAlert delete confirmation
        $('.btn-delete').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const url = '<?= base_url('provider/delete/') ?>' + id;
            const csrfToken = '<?= csrf_token(); ?>';
            const csrfHash = '<?= csrf_hash(); ?>';

            Swal.fire({
                title: "Yakin hapus provider?",
                html: `Provider <strong>"${name}"</strong> akan dihapus permanen!`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                confirmButtonText: "Ya, hapus!",
                cancelButtonColor: "#6c757d",
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            [csrfToken]: csrfHash
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire("Berhasil!", res.message, "success")
                                    .then(() => location.reload());
                            } else {
                                Swal.fire("Gagal!", res.message, "error");
                            }
                        },
                        error: function(xhr) {
                            const errMsg = xhr.responseJSON?.message || "Terjadi kesalahan!";
                            Swal.fire("Opss..", errMsg, "error");
                        }
                    });
                }
            });
        });
    });

    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
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