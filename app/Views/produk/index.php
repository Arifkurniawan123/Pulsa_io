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
                                <h3 class="card-title"><?= esc($table_name); ?></h3>
                                <a href="/master-data/produk/create" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i>
                                    Tambah Produk
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tableProduk" class="table table-bordered table-striped display nowrap">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Produk</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Kategori</th>
                                        <th>Satuan</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produk as $i => $p): ?>
                                        <tr>
                                            <td><?= $i + 1; ?></td>
                                            <td><?= esc($p->nama_produk); ?></td>
                                            <td>Rp <?= number_format($p->harga, 0, ',', '.'); ?></td>
                                            <td><?= esc($p->stok); ?></td>
                                            <td><?= esc($p->nama_kategori); ?></td>
                                            <td><?= esc($p->nama_satuan); ?></td>
                                            <td><?= $p->created_at ? date('d/m/Y H:i', strtotime($p->created_at)) : '-'; ?></td>
                                            <td class="text-center">
                                                <a href="<?= '/master-data/produk/edit/' . $p->id ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="<?= $p->id; ?>">
                                                    <i class="fas fa-trash-alt"></i>
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
        $("#tableProduk").DataTable({
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
                    searchable: true,
                },
                {
                    targets: 6,
                    searchable: true,
                },
                {
                    targets: 7,
                    searchable: false,
                    orderable: false,
                    width: '100px',
                    className: 'text-center'
                }
            ]
        });
    });

    // sweet alert delete
    $(function() {
        $('.btn-delete').click(function() {
            const baseUrl = '<?= base_url(); ?>'
            const csrfToken = '<?= csrf_token(); ?>'
            const csrfHash = '<?= csrf_hash(); ?>'

            const id = $(this).data('id')
            url = baseUrl + 'master-data/produk/delete/' + id;
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