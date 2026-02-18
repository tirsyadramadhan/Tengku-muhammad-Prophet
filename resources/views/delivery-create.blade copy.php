@extends('layouts/contentNavbarLayout')

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
        <h5>Record New Delivery</h5>
    </div>
    <div class="card-body">
        <form id="deliveryForm" action="{{ route('delivery.store') }}" method="POST">
            @csrf
            <div class="row">
                <!-- PO Selection (Select2) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Link to Purchase Order <span class="text-danger">*</span></label>
                    <select name="po_id" id="po_id" class="form-select select2" required>
                        @if($pos->isEmpty())
                        <option value="">No pending POs available for delivery</option>
                        @else
                        <option value="">Select PO</option>
                        @foreach($pos as $po)
                        <option value="{{ $po->po_id }}" data-qty="{{ $po->remaining }}">
                            {{ $po->display_text }}
                        </option>
                        @endforeach
                        @endif
                    </select>
                    <div class="invalid-feedback">Please select a PO.</div>
                </div>

                <!-- Quantity to Deliver (text, numeric-only) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Quantity to Deliver <span class="text-danger">*</span></label>
                    <input type="text"
                        name="qty_delivered"
                        id="qty_delivered"
                        class="form-control numeric-only"
                        maxlength="3"
                        placeholder="Enter quantity"
                        required>
                    <div class="invalid-feedback" id="qty-error">Quantity must be greater than zero.</div>
                    <div id="qty_warning" class="text-danger small mt-1" style="display:none;">
                        Quantity exceeds available PO amount!
                    </div>
                </div>

                <!-- Estimated Delivery Date (min = now) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estimated Delivery Date <span class="text-danger">*</span></label>
                    <input type="datetime-local"
                        name="delivery_time_estimation"
                        id="delivery_time_estimation"
                        class="form-control"
                        min="{{ now('Asia/Jakarta')->format('Y-m-d\TH:i') }}"
                        required>
                    <div class="invalid-feedback">Delivery date must be today or later.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="submit_btn">Save Delivery</button>
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

        // --- Initialize Select2 ---
        $('#po_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Select PO --',
            allowClear: true
        });

        // --- Numeric-only enforcement for quantity input ---
        const qtyInput = document.getElementById('qty_delivered');

        // Block non-digit keys
        qtyInput.addEventListener('keydown', function(e) {
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

        // Clean pasted content
        qtyInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            let sanitized = paste.replace(/\D/g, ''); // keep only digits
            const maxLen = this.getAttribute('maxlength');
            if (maxLen && sanitized.length > parseInt(maxLen, 10)) {
                sanitized = sanitized.slice(0, parseInt(maxLen, 10));
            }
            // Insert at cursor
            const start = this.selectionStart;
            const end = this.selectionEnd;
            const currentValue = this.value;
            const newValue = currentValue.substring(0, start) + sanitized + currentValue.substring(end);
            this.value = newValue;
            // Trigger validation
            this.dispatchEvent(new Event('input', {
                bubbles: true
            }));
        });

        // On input, enforce maxlength and validate
        qtyInput.addEventListener('input', function() {
            let raw = this.value.replace(/\D/g, '');
            const maxLen = this.getAttribute('maxlength');
            if (maxLen && raw.length > parseInt(maxLen, 10)) {
                raw = raw.slice(0, parseInt(maxLen, 10));
            }
            if (this.value !== raw) this.value = raw;

            // Validate this field (separate function)
            validateField($(this));
        });

        // --- Over-delivery warning ---
        function checkOverDelivery() {
            const select = document.getElementById('po_id');
            const selectedOption = select.options[select.selectedIndex];
            const availableQty = parseInt(selectedOption?.getAttribute('data-qty') || 0);
            const inputQty = parseInt(qtyInput.value.replace(/\D/g, '')) || 0;

            const warningDiv = document.getElementById('qty_warning');
            const submitBtn = document.getElementById('submit_btn');

            if (inputQty > availableQty) {
                warningDiv.style.display = 'block';
                submitBtn.disabled = true;
            } else {
                warningDiv.style.display = 'none';
                submitBtn.disabled = false;
            }
        }

        // Attach over-delivery check to both select change and quantity input
        $('#po_id').on('change', function() {
            checkOverDelivery();
            validateField($(this));
        });

        qtyInput.addEventListener('input', function() {
            checkOverDelivery();
        });

        // --- Live validation function (Bootstrap styling) ---
        function validateField(element) {
            let $el = $(element);
            let fieldId = $el.attr('id');
            let value = $el.val();
            let isValid = false;
            let errorMsg = '';

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

            // PO Select
            if (fieldId === 'po_id') {
                isValid = value !== '';
                setValidity(isValid, 'Please select a PO.');
            }
            // Quantity
            else if (fieldId === 'qty_delivered') {
                let raw = value.replace(/\D/g, '');
                if (raw === '' || parseInt(raw, 10) === 0) {
                    setValidity(false, 'Quantity must be greater than zero.');
                } else {
                    setValidity(true, '');
                }
            }
            // Date
            else if (fieldId === 'delivery_time_estimation') {
                // Basic required check; min attribute handled by browser
                isValid = value !== '';
                setValidity(isValid, 'Delivery date is required.');
            }

            return isValid;
        }

        // Attach validation events
        $('#po_id, #qty_delivered, #delivery_time_estimation').on('change keyup blur', function() {
            validateField($(this));
        });

        // --- AJAX Form Submission with SweetAlert ---
        $('#deliveryForm').on('submit', function(e) {
            e.preventDefault();

            // Validate all fields
            let allValid = true;
            $('#po_id, #qty_delivered, #delivery_time_estimation').each(function() {
                if (!validateField($(this))) allValid = false;
            });

            // Also ensure no over-delivery
            if (!allValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please correct the highlighted fields.'
                });
                return;
            }

            if (document.getElementById('qty_warning').style.display === 'block') {
                Swal.fire({
                    icon: 'error',
                    title: 'Quantity Exceeds Available',
                    text: 'You cannot deliver more than the remaining PO quantity.'
                });
                return;
            }

            let $btn = $('#submit_btn');
            let originalText = $btn.text();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

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
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).text(originalText);

                    let msg = 'An error occurred.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        // Validation errors from Laravel
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