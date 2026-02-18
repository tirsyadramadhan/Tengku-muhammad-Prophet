@extends('layouts/contentNavbarLayout')

@section('title', 'Create Invoice')

@section('page-style')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
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
    <div class="card-header">
        <h5 class="mb-0">Create New Invoice</h5>
    </div>
    <div class="card-body">
        <form id="invoiceForm" action="{{ route('invoice.store') }}" method="POST">
            @csrf

            <div class="row">
                <!-- Select Delivery (Select2) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Select Delivery Record <span class="text-danger">*</span></label>
                    <select name="delivery_id" id="delivery_select" class="form-select select2" required>
                        <option value="">-- Choose a Delivery --</option>
                        @foreach($deliveries as $d)
                        <option value="{{ $d->delivery_id }}" data-po="{{ $d->po->no_po ?? 'N/A' }}" data-customer="{{ $d->po->customer->cust_name ?? 'N/A' }}" data-qty="{{ $d->qty_delivered }}">
                            Delivery #{{ $d->delivery_id }} | PO: {{ $d->po->no_po ?? 'N/A' }} | Cust: {{ $d->po->customer->cust_name ?? 'N/A' }} | Qty: {{ number_format($d->qty_delivered, 0) }}
                        </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Please select a delivery record.</div>
                </div>

                <!-- Invoice Date (Asia/Jakarta) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                    <input type="datetime-local"
                        name="tgl_invoice"
                        id="tgl_invoice"
                        class="form-control"
                        value="{{ now('Asia/Jakarta')->format('Y-m-d\TH:i') }}"
                        min="{{ now('Asia/Jakarta')->format('Y-m-d\TH:i') }}"
                        required>
                    <div class="invalid-feedback">Invoice date is required and must be today or later.</div>
                </div>

                <!-- Due Date (must be after invoice date) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Due Date <span class="text-danger">*</span></label>
                    <input type="datetime-local"
                        name="due_date"
                        id="due_date"
                        class="form-control"
                        min="{{ now('Asia/Jakarta')->addDay()->format('Y-m-d\TH:i') }}"
                        required>
                    <div class="invalid-feedback">Due date must be after invoice date.</div>
                </div>
            </div>

            <div class="mt-2">
                <button type="submit" id="btnSave" class="btn btn-primary me-2">
                    <span id="btnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                    Save Invoice
                </button>
                <a href="{{ route('invoice.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-script')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $.noConflict();

        // Initialize Select2
        $('#delivery_select').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Choose a Delivery --',
            allowClear: true
        });

        // Set minimum for due date based on invoice date
        function updateDueDateMin() {
            let invoiceDate = $('#tgl_invoice').val();
            if (invoiceDate) {
                // Add one second to invoice date to ensure due date is after
                let date = new Date(invoiceDate);
                date.setSeconds(date.getSeconds() + 1);
                let year = date.getFullYear();
                let month = String(date.getMonth() + 1).padStart(2, '0');
                let day = String(date.getDate()).padStart(2, '0');
                let hours = String(date.getHours()).padStart(2, '0');
                let minutes = String(date.getMinutes()).padStart(2, '0');
                let minDue = `${year}-${month}-${day}T${hours}:${minutes}`;
                $('#due_date').attr('min', minDue);
            }
        }

        $('#tgl_invoice').on('change', function() {
            updateDueDateMin();
            // If current due date is less than new min, clear it
            let dueDate = $('#due_date').val();
            let minDue = $('#due_date').attr('min');
            if (dueDate && minDue && dueDate < minDue) {
                $('#due_date').val('');
            }
            validateField($('#due_date'));
        });

        // --- Live validation function (Bootstrap styling) ---
        function validateField(element) {
            let $el = $(element);
            let fieldId = $el.attr('id');
            let value = $el.val();
            let isValid = false;

            function setValidity(valid) {
                isValid = valid;
                if (valid) {
                    $el.removeClass('is-invalid').addClass('is-valid');
                    $el.siblings('.invalid-feedback').hide();
                } else {
                    $el.removeClass('is-valid').addClass('is-invalid');
                    $el.siblings('.invalid-feedback').show();
                }
            }

            // Delivery select
            if (fieldId === 'delivery_select') {
                isValid = value !== '';
                setValidity(isValid);
            }
            // Invoice Date
            else if (fieldId === 'tgl_invoice') {
                isValid = value !== '' && new Date(value) >= new Date(new Date().toISOString().slice(0, 16));
                setValidity(isValid);
            }
            // Due Date
            else if (fieldId === 'due_date') {
                let invoiceDate = $('#tgl_invoice').val();
                isValid = value !== '' && (!invoiceDate || new Date(value) > new Date(invoiceDate));
                setValidity(isValid);
            }

            return isValid;
        }

        // Attach validation events
        $('#delivery_select, #tgl_invoice, #due_date').on('change keyup blur', function() {
            validateField($(this));
        });

        // --- AJAX form submission with SweetAlert ---
        $('#invoiceForm').on('submit', function(e) {
            e.preventDefault();

            // Validate all fields
            let allValid = true;
            $('#delivery_select, #tgl_invoice, #due_date').each(function() {
                if (!validateField($(this))) allValid = false;
            });

            if (!allValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please correct the highlighted fields.'
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
                            title: 'Success!',
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
                            text: response.message || 'Something went wrong.'
                        });
                        $btn.prop('disabled', false);
                        $spinner.addClass('d-none');
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false);
                    $spinner.addClass('d-none');

                    let msg = 'An error occurred.';
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