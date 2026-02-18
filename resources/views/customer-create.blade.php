@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah Customer')

@section('page-style')
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .is-valid {
        border-color: #198754 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .is-invalid {
        border-color: #dc3545 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tambah Customer Baru</h5>
        <small class="text-body-secondary float-end">Form horizontal dengan validasi langsung</small>
    </div>
    <div class="card-body">
        <form id="customerForm">
            @csrf
            <div class="row mb-4">
                <label class="col-sm-2 col-form-label" for="cust_name">Nama Customer</label>
                <div class="col-sm-10">
                    <div class="input-group">
                        <span class="input-group-text"><i class="icon-base ri ri-user-line"></i></span>
                        <input type="text"
                            name="cust_name"
                            id="cust_name"
                            class="form-control"
                            placeholder="Masukkan nama customer"
                            required>
                    </div>
                    <div class="invalid-feedback" id="cust_name-feedback">Nama customer harus diisi.</div>
                </div>
            </div>

            <div class="row justify-content-end">
                <div class="col-sm-10">
                    <button type="submit" id="btnSave" class="btn btn-primary me-2">
                        <span id="btnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        Simpan Customer
                    </button>
                    <a href="{{ route('customer.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-script')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // --- Fungsi validasi langsung ---
        function validateField(element) {
            let $el = $(element);
            let value = $el.val().trim();
            let isValid = value !== '';

            // Directly target the feedback element by its ID
            let $feedback = $('#cust_name-feedback');

            if (isValid) {
                $el.removeClass('is-invalid').addClass('is-valid');
                $feedback.hide();
            } else {
                $el.removeClass('is-valid').addClass('is-invalid');
                $feedback.show();
            }
            return isValid;
        }

        // Event validasi
        $('#cust_name').on('keyup blur change', function() {
            validateField(this);
        });

        // --- Submit form via AJAX ---
        $('#customerForm').on('submit', function(e) {
            e.preventDefault();

            // Validasi semua field
            if (!validateField($('#cust_name'))) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Validasi',
                    text: 'Nama customer harus diisi.'
                });
                return;
            }

            let $btn = $('#btnSave');
            let $spinner = $('#btnSpinner');
            $btn.prop('disabled', true);
            $spinner.removeClass('d-none');

            $.ajax({
                url: '{{ route("customer.store") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = response.redirect_url;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan',
                            text: response.message || 'Terjadi kesalahan.'
                        });
                        $btn.prop('disabled', false);
                        $spinner.addClass('d-none');
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false);
                    $spinner.addClass('d-none');

                    let msg = 'Terjadi kesalahan.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).map(e => e[0]).join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan',
                        html: msg
                    });
                }
            });
        });
    });
</script>
@endsection