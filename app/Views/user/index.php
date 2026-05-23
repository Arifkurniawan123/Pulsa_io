<?= $this->extend('layout/default'); ?>

<?= $this->section('style'); ?>
<!-- DataTables -->
<link
    rel="stylesheet"
    href="/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
<link
    rel="stylesheet"
    href="/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
<link
    rel="stylesheet"
    href="/assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css" />

<!-- SweetAlert2 -->
<link
    rel="stylesheet"
    href="/assets/plugins/sweetalert2/sweetalert2.min.css" />
    <link rel="icon" href="<?= base_url('assets/img/logo.jpg'); ?>" type="image/gif" />
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
                                <h3 class="card-title"><?= $table_name ?></h3>
                                <a href="/setting/user/create" type="button" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i>
                                    Tambah Data
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table
                                id="tableUser"
                                class="table table-bordered table-striped display nowrap">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Image</th>
                                        <th>Nama</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>No Tlp</th>
                                        <th>Alamat</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Tanggal Diupdate</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($user as $key => $value) { ?>
                                        <tr>
                                            <td><?= $key + 1; ?></td>
                                            <td>
                                                <img src="
                                                <?php if ($value->image !== 'default-profile.png') { ?>
                                                    <?= '/assets/img/user/' . $value->image ?>
                                                <?php } else { ?>
                                                    <?= '/assets/img/' . $value->image ?>
                                                <?php } ?>"
                                                    class="rounded-circle"
                                                    style="width: 2.5rem; height: 2.5rem; object-fit: cover;" alt="User Image">
                                            </td>
                                            <td><?= $value->nama_lengkap; ?></td>
                                            <td><?= $value->username; ?></td>
                                            <td><?= $value->email; ?></td>
                                            <td><?= $value->no_tlp; ?></td>
                                            <td style="white-space: normal !important; word-wrap: break-word; min-width: 200px;">
                                                <?= $value->alamat; ?>
                                            </td>
                                            <td><?= $value->nama_role; ?></td>
                                            <td>
                                                <?= $value->status == '1'
                                                    ? '<span class="badge badge-success">Aktif</span>'
                                                    : '<span class="badge badge-danger">Tidak Aktif</span>'
                                                ?>
                                            </td>
                                            <td><?= $value->created_at ? date('d-m-Y H:i:s', strtotime($value->created_at)) : '-' ?></td>
                                            <td><?= $value->updated_at ? date('d-m-Y H:i:s', strtotime($value->updated_at)) : '-' ?></td>
                                            <td>
                                                <a type="button" href="<?= '/setting/user/edit/' . $value->id ?>" class="btn btn-warning">
                                                    <i class="nav-icon fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-delete"
                                                    data-id="<?= $value->id; ?>">
                                                    <i class="nav-icon fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<!-- DataTables  & Plugins -->
<script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>

<!-- SweetAlert2 -->
<script src="/assets/plugins/sweetalert2/sweetalert2.min.js"></script>

<script>
    $(function() {
        $("#tableUser")
            .DataTable({
                responsive: false,
                lengthChange: false,
                autoWidth: false,
                scrollX: true,
                pageLength: 5,
                columnDefs: [{
                        targets: 0,
                        searchable: false,
                        width: '25px'
                    },
                    {
                        targets: 1,
                        searchable: false,
                        orderable: false,
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
                        searchable: true,
                    },
                    {
                        targets: 6,
                        searchable: true,
                    },
                    {
                        targets: 7,
                        searchable: true,
                    },
                    {
                        targets: 8,
                        searchable: true,
                    },
                    {
                        targets: 9,
                        searchable: true,
                    },
                    {
                        targets: 10,
                        searchable: true,
                    },
                    {
                        targets: 11,
                        searchable: false,
                        orderable: false,
                        className: 'text-center'
                    },
                ]
            })
    });

    // sweet alert delete
    $(function() {
        $('.btn-delete').click(function() {
            const baseUrl = '<?= base_url(); ?>'
            const csrfToken = '<?= csrf_token(); ?>'
            const csrfHash = '<?= csrf_hash(); ?>'

            const id = $(this).data('id')
            url = baseUrl + 'setting/user/delete/' + id;
            method = 'POST';

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
                        method: method,
                        data: {
                            [csrfToken]: csrfHash,
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    title: "Dihapus",
                                    text: res.message,
                                    icon: "success"
                                }).then(() => {
                                    location.reload();
                                })
                            }
                        },
                        error: function(xhr) {
                            const errMsg = xhr.responseJSON.message;
                            Swal.fire({
                                title: 'Opsss..',
                                text: errMsg,
                                icon: "error"
                            })
                        }
                    })
                }
            });
        });
    });

    <?php if (session()->getFlashdata('success')) { ?>
        Swal.fire({
            icon: 'success',
            title: 'Sukses',
            text: '<?= session()->getFlashdata('success') ?>'
        });
    <?php } ?>

    <?php if (session()->getFlashdata('error')) { ?>
        Swal.fire({
            icon: 'error',
            title: 'Opss..',
            text: '<?= session()->getFlashdata('error') ?>'
        });
    <?php } ?>
</script>
<?= $this->endSection(); ?>