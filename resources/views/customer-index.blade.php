@extends('layouts/contentNavbarLayout')

@section('title', 'Customer Management')

@section('vendor-style')
<style>
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
                    <th>Customer Details</th>
                    <th>Registration Date</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('page-script')
<script>
    document.addEventListener("DOMContentLoaded", function() {
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
                    data: 'cust_name',
                    name: 'cust_name',
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
            order: [
                [1, 'asc']
            ],
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