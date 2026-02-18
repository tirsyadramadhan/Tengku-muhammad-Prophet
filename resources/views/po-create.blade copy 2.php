@extends('layouts/contentNavbarLayout')

@section('page-style')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8 col-lg-10 mx-auto">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Buat Purchase Order (Finalisasi Incoming)</h5>
                <small class="text-muted">Ubah PO Incoming menjadi PO Open</small>
            </div>
            <div class="card-body">
                <form id="createPoForm" action="{{ route('po.store') }}" method="POST">
                    @csrf

                    <!-- Incoming PO Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-primary">PILIH INCOMING PO (Status 0)</label>
                        <select name="incoming_po_id" id="incoming_po_id" class="form-select select2" required>
                            <option value="">-- Pilih Incoming PO --</option>
                            @foreach($dataIncomingPo as $incoming)
                            <option value="{{ $incoming->po_id }}" data-no-po="{{ $incoming->no_po }}">
                                {{ $incoming->no_po }} – {{ $incoming->nama_barang }} (Jml: {{ $incoming->qty }})
                            </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Silakan pilih incoming PO.</div>
                    </div>

                    <!-- Editable PO Number -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Nomor PO</label>
                        <input type="text" name="no_po" id="no_po" class="form-control live-validate numeric-only" maxlength="10" placeholder="Masukkan nomor PO" required>
                        <div class="invalid-feedback">Nomor PO wajib diisi.</div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <!-- Customer (read‑only) -->
                        <div class="col-md-6">
                            <label class="form-label">Pelanggan</label>
                            <input type="text" id="customer_name_display" class="form-control bg-light" readonly placeholder="Akan terisi otomatis">
                            <input type="hidden" name="customer_id" id="customer_id">
                            <div class="invalid-feedback">Data pelanggan tidak ditemukan.</div>
                        </div>

                        <!-- Product Name -->
                        <div class="col-md-6">
                            <label class="form-label">Nama Barang</label>
                            <input type="text" name="nama_barang" id="nama_barang" class="form-control live-validate" required>
                            <div class="invalid-feedback">Nama barang wajib diisi.</div>
                        </div>

                        <!-- PO Date -->
                        <div class="col-md-6">
                            <label class="form-label">Tanggal PO</label>
                            <input value="{{ now('Asia/Jakarta')->format('Y-m-d\TH:i') }}" type="datetime-local" name="tgl_po" id="tgl_po" class="form-control live-validate" required>
                            <div class="invalid-feedback">Tanggal wajib diisi.</div>
                        </div>

                        <!-- Quantity -->
                        <div class="col-md-6">
                            <label class="form-label">Jumlah</label>
                            <input type="text" name="qty" id="qty" class="form-control live-validate numeric-only" maxlength="3" required>
                            <div class="invalid-feedback">Jumlah tidak valid.</div>
                        </div>

                        <!-- Price per Unit (currency) -->
                        <div class="col-md-6">
                            <label class="form-label">Harga per Unit (Rp)</label>
                            <input type="text" name="harga_display" id="harga_display" class="form-control currency-input live-validate numeric-only" data-max-raw="11" required placeholder="0">
                            <input type="hidden" name="harga" id="harga">
                            <div class="invalid-feedback">Harga wajib diisi.</div>
                        </div>

                        <!-- Total Margin (currency) -->
                        <div class="col-md-6">
                            <label class="form-label">Total Margin (Rp)</label>
                            <input type="text" name="margin_display" id="margin_display" class="form-control currency-input live-validate numeric-only" data-max-raw="13" required placeholder="0">
                            <input type="hidden" name="margin" id="margin">
                            <div class="invalid-feedback">Margin wajib diisi.</div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end">
                        <button type="submit" id="btnSave" class="btn btn-primary px-5">
                            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                            Simpan & Buka PO
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page-script')
<!-- jQuery (already loaded in layout, but included for safety) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $.noConflict();

        // --- Initialize Select2 ---
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Pilih Incoming PO --',
            allowClear: true
        });

        // --- Currency formatter helpers ---
        function formatIDR(amount) {
            if (!amount && amount !== 0) return '';
            let val = Math.floor(amount).toString().replace(/\./g, '');
            return val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function unformatIDR(val) {
            return val.replace(/\./g, '') || '0';
        }

        // --- Global handlers for numeric-only inputs ---
        const numericInputs = document.querySelectorAll('.numeric-only');

        // 1. Block non‑digit keys (including for currency fields)
        numericInputs.forEach(input => {
            input.addEventListener('keydown', function(e) {
                const key = e.key;
                const isCtrlKey = e.ctrlKey || e.metaKey;
                const allowedSpecialKeys = [
                    'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
                    'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                    'Home', 'End'
                ];

                if (allowedSpecialKeys.includes(key) || isCtrlKey) return;

                if (!/^[0-9]$/.test(key)) {
                    e.preventDefault();
                }
            });

            // 2. Sanitize paste for all numeric fields
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                let sanitized = paste.replace(/\D/g, ''); // keep only digits

                // Apply max raw digits if the field has data-max-raw (for currency)
                const maxRaw = this.dataset.maxRaw;
                if (maxRaw && sanitized.length > parseInt(maxRaw, 10)) {
                    sanitized = sanitized.slice(0, parseInt(maxRaw, 10));
                }

                // Insert at cursor
                const start = this.selectionStart;
                const end = this.selectionEnd;
                const currentValue = this.value;
                const newValue = currentValue.substring(0, start) + sanitized + currentValue.substring(end);
                this.value = newValue;

                // Trigger input event to run further processing (formatting / validation)
                this.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            });
        });

        // 3. Input handler – different logic for currency vs. plain numeric
        $(document).on('input', '.numeric-only', function() {
            let $this = $(this);
            let raw = $this.val().replace(/\D/g, ''); // start with digits only

            if ($this.hasClass('currency-input')) {
                // Currency field: enforce max raw digits, then format
                const maxRaw = $this.data('max-raw');
                if (maxRaw && raw.length > parseInt(maxRaw, 10)) {
                    raw = raw.slice(0, parseInt(maxRaw, 10));
                }
                // Update display with formatting
                $this.val(formatIDR(raw));
                // Sync hidden field (strip _display from id)
                let targetId = $this.attr('id').replace('_display', '');
                $('#' + targetId).val(raw);
            } else {
                // Plain numeric field (PO Number, Quantity): enforce maxlength attribute
                const maxLen = $this.attr('maxlength');
                if (maxLen && raw.length > parseInt(maxLen, 10)) {
                    raw = raw.slice(0, parseInt(maxLen, 10));
                }
                $this.val(raw);
            }

            // Trigger validation after updating value
            validateField($this);
        });

        // --- Live validation function (enhanced) ---
        function validateField(element) {
            let $el = $(element);
            let fieldId = $el.attr('id');
            let value = $el.val();
            let isValid = false;
            let errorMsg = '';

            // Helper to set validity and update error div
            function setValidity(valid, message) {
                isValid = valid;
                errorMsg = message;
                if (valid) {
                    $el.removeClass('is-invalid').addClass('is-valid');
                    $el.siblings('.invalid-feedback').text(message).hide();
                } else {
                    $el.removeClass('is-valid').addClass('is-invalid');
                    $el.siblings('.invalid-feedback').text(message).show();
                }
            }

            // PO Number: must be exactly 10 digits and >0
            if (fieldId === 'no_po') {
                let raw = value.replace(/\D/g, '');
                if (raw.length !== 10) {
                    setValidity(false, 'Nomor PO harus terdiri dari 10 digit.');
                } else if (parseInt(raw, 10) === 0) {
                    setValidity(false, 'Nomor PO tidak boleh nol.');
                } else {
                    setValidity(true, '');
                }
            }
            // Quantity: numeric, max 3 digits, >0
            else if (fieldId === 'qty') {
                let raw = value.replace(/\D/g, '');
                if (raw === '' || parseInt(raw, 10) === 0) {
                    setValidity(false, 'Jumlah harus lebih dari 0.');
                } else {
                    setValidity(true, '');
                }
            }
            // Price per Unit (currency)
            else if (fieldId === 'harga_display') {
                let raw = unformatIDR(value);
                if (raw === '' || parseInt(raw, 10) === 0) {
                    setValidity(false, 'Harga harus lebih dari 0.');
                } else {
                    setValidity(true, '');
                }
            }
            // Total Margin (currency)
            else if (fieldId === 'margin_display') {
                let raw = unformatIDR(value);
                if (raw === '' || parseInt(raw, 10) === 0) {
                    setValidity(false, 'Margin harus lebih dari 0.');
                } else {
                    setValidity(true, '');
                }
            }
            // All other fields (customer name, date, etc.) use browser built‑in
            else {
                isValid = $el[0].checkValidity();
                if (isValid) {
                    $el.removeClass('is-invalid').addClass('is-valid');
                    $el.siblings('.invalid-feedback').hide();
                } else {
                    $el.removeClass('is-valid').addClass('is-invalid');
                    $el.siblings('.invalid-feedback').show();
                }
                return isValid; // early return because we don't change message
            }

            return isValid;
        }

        // Attach validation to live-validate fields
        $('.live-validate').on('change keyup blur', function() {
            validateField($(this));
        });

        // --- Populate fields when an Incoming PO is selected ---
        $('#incoming_po_id').on('change', function() {
            let id = $(this).val();
            if (!id) return;

            // Disable all inputs while loading
            $('#createPoForm :input').not('#incoming_po_id').prop('disabled', true);

            $.ajax({
                url: "{{ route('po.incoming-details', ':id') }}".replace(':id', id),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        let data = response.data;

                        // Customer
                        if (data.customer) {
                            $('#customer_name_display').val(data.customer.cust_name);
                            $('#customer_id').val(data.customer_id);
                        } else {
                            $('#customer_name_display').val('Pelanggan Tidak Dikenal');
                            $('#customer_id').val('');
                        }

                        // PO Number – use the one from incoming PO (will be validated later)
                        $('#no_po').val(data.no_po);

                        // Basic fields
                        $('#nama_barang').val(data.nama_barang);
                        $('#qty').val(data.qty);

                        // Price & Margin (raw values)
                        $('#harga').val(data.harga);
                        $('#harga_display').val(formatIDR(data.harga));

                        $('#margin').val(data.margin);
                        $('#margin_display').val(formatIDR(data.margin));

                        // Re-enable inputs and run validation
                        $('#createPoForm :input').prop('disabled', false);
                        $('.live-validate').each(function() {
                            validateField($(this));
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data Incoming PO.'
                    });
                    $('#createPoForm :input').prop('disabled', false);
                }
            });
        });

        // --- AJAX form submission with SweetAlert ---
        $('#createPoForm').on('submit', function(e) {
            e.preventDefault();

            // Ensure hidden fields have raw digits
            $('#harga').val(unformatIDR($('#harga_display').val()));
            $('#margin').val(unformatIDR($('#margin_display').val()));

            // Validate all live-validate fields
            let allValid = true;
            $('.live-validate').each(function() {
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

            let $btn = $('#btnSave');
            let $spinner = $('#btnSpinner');
            $btn.prop('disabled', true);
            $spinner.removeClass('d-none');

            $.ajax({
                url: $(this).attr('action'),
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

                    let msg = 'Terjadi kesalahan.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;
                        msg = Object.values(errors).map(e => e[0]).join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
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