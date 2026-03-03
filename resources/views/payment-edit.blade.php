@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Pembayaran')

@section('page-style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
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
    <div class="card-header">
        <h5 class="mb-0">Edit Pembayaran</h5>
    </div>
    <div class="card-body">

        {{-- PUT spoofing for Laravel resource routing --}}
        <form id="paymentForm" action="{{ route('payment.update', $payment->payment_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">

                {{-- Invoice Select --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pilih Invoice <span class="text-danger">*</span></label>
                    <select name="invoice_id" id="invoice_id" class="form-select select2" required>
                        <option value="">-- Pilih Invoice --</option>
                        @foreach($invoices as $inv)
                        <option
                            value="{{ $inv->invoice_id }}"
                            data-total="{{ $inv->total_display }}"
                            {{ $inv->invoice_id == $payment->invoice_id ? 'selected' : '' }}>
                            {{ $inv->nomor_invoice }} | {{ $inv->customer_name }} | Rp {{ number_format($inv->total_display, 0, ',', '.') }}
                        </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Silakan pilih invoice.</div>
                </div>

                {{-- Amount (read-only, auto-filled from invoice) --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
                    <input type="text"
                        id="amount_display"
                        class="form-control"
                        readonly
                        value="Rp {{ number_format($payment->amount, 0, ',', '.') }}">
                    <input type="hidden" name="amount" id="amount" value="{{ $payment->amount }}">
                    <div class="invalid-feedback">Jumlah tidak boleh kosong.</div>
                </div>

                {{-- Payment Method --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                    <select name="metode_bayar" id="metode_bayar" class="form-select select2" required>
                        <option value="">-- Pilih Metode --</option>
                        @foreach(['Tunai','Transfer Bank','Kartu Kredit','Kartu Debit','QRIS','OVO','GoPay','DANA','LinkAja','ShopeePay'] as $metode)
                        <option value="{{ $metode }}" {{ $payment->metode_bayar === $metode ? 'selected' : '' }}>
                            {{ $metode }}
                        </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Silakan pilih metode pembayaran.</div>
                </div>

                {{-- Payment Date --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Pembayaran <span class="text-danger">*</span></label>
                    <input type="datetime-local"
                        name="payment_date"
                        id="payment_date"
                        class="form-control"
                        value="{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d\TH:i') }}"
                        required>
                    <div class="invalid-feedback">Tanggal pembayaran harus diisi.</div>
                </div>

            </div>

            <div class="mt-2">
                <button type="submit" id="btnSave" class="btn btn-warning me-2">
                    <span id="btnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                    <i class="ri-save-line me-1"></i> Update Pembayaran
                </button>
                <a href="{{ route('payment.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>

    </div>
</div>
@endsection

@section('page-script')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $.noConflict();

        // ── 1. INIT SELECT2 ───────────────────────────────────────────────
        $('#invoice_id, #metode_bayar').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Pilih --',
            allowClear: true
        });

        // ── 2. FORMAT RUPIAH ──────────────────────────────────────────────
        function formatRupiah(angka) {
            if (!angka) return '0';
            return 'Rp ' + Number(angka).toLocaleString('id-ID');
        }

        // ── 3. AUTO-FILL AMOUNT WHEN INVOICE CHANGES ──────────────────────
        $('#invoice_id').on('change', function() {
            var selected = $(this).find(':selected');
            var total = selected.data('total') || 0;
            $('#amount_display').val(total ? formatRupiah(total) : '');
            $('#amount').val(total);
            validateField($(this));
        });

        // ── 4. LIVE VALIDATION ────────────────────────────────────────────
        function validateField($el) {
            var val = $el.val();
            var isValid = val !== '' && val !== null;

            if (isValid) {
                $el.removeClass('is-invalid').addClass('is-valid');
                $el.siblings('.invalid-feedback').hide();
            } else {
                $el.removeClass('is-valid').addClass('is-invalid');
                $el.siblings('.invalid-feedback').show();
            }
            return isValid;
        }

        $('#invoice_id, #metode_bayar, #payment_date').on('change keyup blur', function() {
            validateField($(this));
        });

        // ── 5. AJAX SUBMIT ────────────────────────────────────────────────
        $('#paymentForm').on('submit', function(e) {
            e.preventDefault();

            var allValid = true;
            $('#invoice_id, #metode_bayar, #payment_date').each(function() {
                if (!validateField($(this))) allValid = false;
            });

            if (!allValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Validasi',
                    text: 'Harap perbaiki field yang disorot.'
                });
                return;
            }

            var $btn = $('#btnSave');
            var $spinner = $('#btnSpinner');
            $btn.prop('disabled', true);
            $spinner.removeClass('d-none');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST', // Laravel _method spoofing handles PUT
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
                            title: 'Error',
                            text: response.message || 'Terjadi kesalahan.'
                        });
                        $btn.prop('disabled', false);
                        $spinner.addClass('d-none');
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false);
                    $spinner.addClass('d-none');

                    var msg = 'Terjadi kesalahan.';
                    if (xhr.responseJSON?.errors) {
                        msg = Object.values(xhr.responseJSON.errors).map(e => e[0]).join('<br>');
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: msg
                    });
                }
            });
        });
    });
</script>
@endsection