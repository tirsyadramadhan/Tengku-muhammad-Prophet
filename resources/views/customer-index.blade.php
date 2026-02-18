@extends('layouts/contentNavbarLayout')

@section('title', 'Customer Management')

@section('vendor-style')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<!-- Remixicon -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">
<style>
    /* Custom styling */
    .table-responsive {
        min-height: 400px;
        border-radius: 0 0 8px 8px;
    }

    .table thead th {
        background-color: #f8f9fa !important;
        font-weight: 700;
        color: #566a7f;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        border-top: none !important;
    }

    .cust-avatar {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-weight: 700;
        font-size: 1rem;
        transition: 0.3s;
    }

    .table tbody tr:hover td {
        filter: brightness(0.97);
        transition: 0.2s;
    }

    .table tbody tr:hover .cust-avatar {
        transform: scale(1.1);
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }

    /* Atur lebar kolom agar proporsional */
    th:nth-child(1) {
        width: 60px;
    }

    /* NO */
    th:nth-child(2) {
        width: 100px;
    }

    /* ID */
    th:nth-child(3) {
        width: auto;
    }

    /* Customer Details */
    th:nth-child(4) {
        width: 150px;
    }

    /* Registration Date */
    th:nth-child(5) {
        width: 100px;
    }

    /* Actions */
</style>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-md bg-label-primary me-3">
                <i class="ri-user-star-line fs-3"></i>
            </div>
            <div>
                <h5 class="mb-0">Customer Database</h5>
                <small class="text-muted">Direct control over your client records</small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('customer.create') }}" class="btn btn-primary shadow-sm text-nowrap">
                <i class="ri-user-add-line me-1"></i> Add New
            </a>
        </div>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-bordered mb-0" id="customerTable">
            <thead>
                <tr>
                    <th class="text-center">NO</th>
                    <th class="text-center">UID</th>
                    <th>Customer Details</th>
                    <th>Registration Date</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data diisi via DataTables AJAX -->
            </tbody>
        </table>
    </div>

    <div class="card-footer border-top bg-light-subtle d-flex justify-content-between align-items-center py-3">
        <p class="mb-0 small text-muted">Total Active Customers: <span class="fw-bold text-primary" id="totalRecords">0</span></p>
        <!-- Pagination akan ditangani DataTables -->
    </div>
</div>
@endsection

@section('page-script')
<!-- jQuery (sudah ada di layout? sebaiknya tambahkan jika belum) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $.noConflict();

        // Inisialisasi DataTables
        var table = $('#customerTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('customer.index') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'id_cust',
                    name: 'id_cust',
                    className: 'text-center'
                },
                {
                    data: 'cust_name',
                    name: 'cust_name',
                    render: function(data, type, row) {
                        // Warna avatar berdasarkan id_cust
                        var colors = ['primary', 'success', 'warning', 'info', 'danger'];
                        var color = colors[row.id_cust % 5];
                        return '<div class="d-flex align-items-center">' +
                            '<div class="cust-avatar bg-label-' + color + ' text-uppercase me-3 shadow-sm">' +
                            data.charAt(0) +
                            '</div>' +
                            '<div>' +
                            '<span class="fw-bold text-heading d-block">' + data + '</span>' +
                            '<small class="text-muted">Verified Client</small>' +
                            '</div>' +
                            '</div>';
                    }
                },
                {
                    data: 'input_date',
                    name: 'input_date',
                    render: function(data, type, row) {
                        return '<div class="d-flex flex-column">' +
                            '<span class="fw-medium text-dark">' +
                            '<i class="ri-calendar-event-line me-1 text-success"></i>' +
                            data +
                            '</span>' +
                            '</div>';
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" // Bahasa Indonesia
            },
            order: [
                [1, 'asc']
            ], // Urut berdasarkan ID secara default
            drawCallback: function(settings) {
                // Update total records di footer
                $('#totalRecords').text(settings.fnRecordsTotal());
            }
        });

        // Fungsi global untuk delete (dipanggil dari tombol dropdown)
        window.deleteCustomer = function(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data customer akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("customer.destroy", ":id") }}'.replace(':id', id),
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Terhapus!',
                                    response.message,
                                    'success'
                                );
                                table.ajax.reload(); // reload data
                            } else {
                                Swal.fire('Gagal!', 'Terjadi kesalahan.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Gagal!', 'Terjadi kesalahan server.', 'error');
                        }
                    });
                }
            });
        };
    });
</script>
@endsection