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
    <link rel="icon" href="<?= base_url('assets/img/logo.jpg'); ?>" type="image/gif" />

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
                                <h3 class="card-title"><?= $table_name ?></h3>
                                <a href="/master-data/kategori/create" type="button" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i>
                                    Tambah Data
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table
                                id="tableKategori"
                                class="table table-bordered table-striped display nowrap">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Kategori</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Tanggal Diupdate</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kategori as $key => $value) { ?>
                                        <tr>
                                            <td><?= $key + 1; ?></td>
                                            <td><?= $value->nama_kategori; ?></td>
                                            <td><?= $value->created_at ? date('d-m-Y H:i:s', strtotime($value->created_at)) : '-' ?></td>
                                            <td><?= $value->updated_at ? date('d-m-Y H:i:s', strtotime($value->updated_at)) : '-' ?></td>
                                            <td>
                                                <a href="<?= '/master-data/kategori/edit/' . $value->id ?>" type="button" class="btn btn-warning">
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
        $("#tableKategori")
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
                        searchable: false,
                        orderable: false,
                        className: 'text-center'
                    }
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
            url = baseUrl + 'master-data/kategori/delete/' + id;
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


<style>
    /* Biar header kategori dan tombol tambah data sejajar rapi */
    .card-header {
        background-color: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        padding: 1rem 1.5rem;
    }

    .card-header .d-flex {
        align-items: center;
        justify-content: space-between;
    }

    .card-header h3 {
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
        color: #333;
    }

    .btn-primary {
        background-color: #c2410c !important; /* oranye tua elegan */
        border-color: #c2410c !important;
        font-weight: 500;
        border-radius: 6px;
    }

    .btn-primary:hover {
        background-color: #9a3412 !important;
        border-color: #9a3412 !important;
    }

    /* Biar tabel gak mentok ke kiri-kanan */
    .card-body {
        padding: 1.5rem;
    }

    /* Header tabel rapi dan rata tengah vertikal */
    table.dataTable thead th {
        vertical-align: middle;
        text-align: center;
    }
</style>
