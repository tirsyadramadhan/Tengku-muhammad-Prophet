@extends('layouts/contentNavbarLayout')

@section('title', 'Users')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Users</h4>
            <small class="text-muted">Kelola Pengguna</small>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3 bg-white">
            <div class="d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path fill="#E1B530" d="M3.783 2.826L12 1l8.217 1.826a1 1 0 0 1 .783.976v9.987a6 6 0 0 1-2.672 4.992L12 23l-6.328-4.219A6 6 0 0 1 3 13.79V3.802a1 1 0 0 1 .783-.976M5 4.604v9.185a4 4 0 0 0 1.781 3.328L12 20.597l5.219-3.48A4 4 0 0 0 19 13.79V4.604L12 3.05zM12 11a2.5 2.5 0 1 1 0-5a2.5 2.5 0 0 1 0 5m-4.473 5a4.5 4.5 0 0 1 8.946 0z" />
                </svg>
                <div class="ms-3">
                    <h5 class="mb-0 fw-bold">Data Users</h5>
                </div>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm px-3">
                <i class="ri-add-line me-1"></i> Buat User Baru
            </a>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0" id="table-users">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th class="text-center">Username</th>
                        <th class="text-center">Role</th>
                        <th class="text-center">Email</th>
                        <th class="text-center">Status Akun</th>
                        <th class="text-center">Terakhir Login</th>
                        <th class="text-end">Dibuat Pada</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection
@section('page-script')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var dt_table = $('#table-users');

        if (dt_table.length) {
            var table = dt_table.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('users.index') }}",
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'fw-medium'
                    },
                    {
                        data: 'user_name',
                        name: 'user_name',
                        className: 'fw-medium'
                    },
                    {
                        data: 'role_name',
                        name: 'role_name',
                        className: 'fw-medium'
                    },
                    {
                        data: 'email',
                        name: 'email',
                        className: 'fw-medium'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        className: 'fw-medium'
                    },
                    {
                        data: 'last_login',
                        name: 'last_login',
                        className: 'fw-medium'
                    },
                    {
                        data: 'input_date',
                        name: 'input_date',
                        className: 'fw-medium'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        className: 'fw-medium',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [2, 'desc']
                ],
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
            });

            // ── Delete Handler ─────────────────────────────────────────
            $(document).on('click', '.btn-delete', function() {
                const deleteUrl = $(this).data('url');
                const poNo = $(this).data('po');

                Swal.fire({
                    title: 'Hapus User?',
                    text: `Apakah Anda yakin ingin menghapus User ini?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e6381a',
                    cancelButtonColor: '#6e7d88',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return $.ajax({
                            url: deleteUrl,
                            method: 'DELETE',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                // FIX: read from meta tag since there's no @csrf form on the index page
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        }).catch(error => {
                            Swal.showValidationMessage(
                                `Request failed: ${error.responseJSON?.message ?? error.statusText}`
                            );
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Dihapus!',
                            text: 'Data User berhasil dihapus.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        table.ajax.reload(null, false);
                    }
                });
            });
        }
    });
</script>
@endsection