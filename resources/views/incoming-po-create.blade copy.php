@extends('layouts/contentNavbarLayout')

@section('page-style')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
  .currency-input:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  }

  .is-valid.currency-input {
    border-color: #198754;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
  }

  .is-invalid.currency-input {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
  }
</style>
@endsection

@section('content')
<div class="row">
  <div class="col-xl-8 col-lg-10 mx-auto">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Create Incoming Purchase Order</h5>
        <small class="text-muted">Status will be <span class="badge bg-label-info">0 (Incoming)</span></small>
      </div>
      <div class="card-body">
        <form id="incomingPoForm" action="{{ route('incoming-po.store') }}" method="POST">
          @csrf

          <!-- Customer -->
          <div class="mb-4">
            <label class="form-label fw-bold">Customer <span class="text-danger">*</span></label>
            <select name="customer_id" id="customer_id" class="form-select select2 live-validate" required>
              <option value="">-- Select Customer --</option>
              @foreach($customers as $cust)
              <option value="{{ $cust->id_cust }}">{{ $cust->cust_name }}</option>
              @endforeach
            </select>
            <div class="invalid-feedback">Please select a customer.</div>
          </div>

          <div class="row g-4">
            <!-- Nama Barang -->
            <div class="col-md-6">
              <label class="form-label fw-bold">Nama Barang <span class="text-danger">*</span></label>
              <input type="text" name="nama_barang" id="nama_barang" class="form-control live-validate" placeholder="e.g. Laptop, Meja, etc." required>
              <div class="invalid-feedback">Item name is required.</div>
            </div>

            <!-- PO Date -->
            <div class="col-md-6">
              <label class="form-label fw-bold">PO Date <span class="text-danger">*</span></label>
              <input type="datetime-local" name="tgl_po" id="tgl_po"
                class="form-control live-validate"
                value="{{ now('Asia/Jakarta')->format('Y-m-d\TH:i') }}" required>
              <div class="invalid-feedback">Date is required.</div>
            </div>

            <!-- Quantity -->
            <div class="col-md-4">
              <label class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
              <input maxlength="3" type="text" name="qty" id="qty" class="form-control live-validate numeric-only" min="1" placeholder="e.g. 10" required>
              <div class="invalid-feedback" id="qty-error">Quantity must be at least 1.</div>
            </div>

            <!-- Price per Unit -->
            <div class="col-md-4">
              <label class="form-label fw-bold">Price per Unit (Rp) <span class="text-danger">*</span></label>
              <input maxlength="16" type="text" id="harga_display" class="form-control currency-input live-validate numeric-only" placeholder="0" required>
              <input type="hidden" name="harga" id="harga">
              <div class="invalid-feedback" id="harga-error">Price is required.</div>
            </div>

            <!-- Margin Percentage -->
            <div class="col-md-4">
              <label class="form-label fw-bold">Margin (%) <span class="text-danger">*</span></label>
              <input maxlength="2" type="text" name="margin_percentage" id="margin_percentage" class="form-control live-validate numeric-only" placeholder="20" min="0" step="0.1" required>
              <div class="invalid-feedback" id="margin-error">Margin percentage is required.</div>
            </div>

            <!-- Tambahan Margin (optional) -->
            <div class="col-md-12">
              <label class="form-label fw-bold">Tambahan Margin (Rp) <span class="text-muted">(Optional)</span></label>
              <input maxlength="14" type="text" id="tambahan_margin_display" class="form-control currency-input live-validate numeric-only" placeholder="0">
              <input type="hidden" name="tambahan_margin" id="tambahan_margin tambahan-error">
            </div>
          </div>

          <hr class="my-4">

          <div class="d-flex justify-content-end">
            <button type="submit" id="btnSave" class="btn btn-primary px-5">
              <span id="btnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
              Save Purchase Order
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<!-- jQuery (already loaded in layout, but for safety) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  $(document).ready(function() {
    $.noConflict();

    // Select all inputs with class 'numeric-only'
    const numericInputs = document.querySelectorAll('.numeric-only');

    numericInputs.forEach(input => {
      // 1. Block non-digit keys on keydown
      input.addEventListener('keydown', function(e) {
        const key = e.key;
        const isCtrlKey = e.ctrlKey || e.metaKey;
        const allowedSpecialKeys = [
          'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
          'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
          'Home', 'End'
        ];

        if (allowedSpecialKeys.includes(key) || isCtrlKey) {
          return; // allow navigation & control combinations
        }

        // Allow only digits 0-9
        if (!/^[0-9]$/.test(key)) {
          e.preventDefault();
        }
      });

      // 2. Clean input on any change (paste, drag, etc.) and enforce maxlength
      input.addEventListener('input', function() {
        // Remove any non-digit characters
        let sanitized = this.value.replace(/\D/g, '');

        // Apply maxlength if attribute exists
        const maxLen = this.getAttribute('maxlength');
        if (maxLen && sanitized.length > parseInt(maxLen, 10)) {
          sanitized = sanitized.slice(0, maxLen);
        }

        // Update the field only if changed
        if (this.value !== sanitized) {
          this.value = sanitized;
        }

        // Special handling for price & tambahan margin: sync hidden fields
        if (this.id === 'harga_display') {
          document.getElementById('harga').value = sanitized;
        }
        if (this.id === 'tambahan_margin_display') {
          document.getElementById('tambahan_margin').value = sanitized;
        }
      });

      // 3. Intercept paste to sanitize before insertion
      input.addEventListener('paste', function(e) {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData('text');
        // Keep only digits
        let sanitized = paste.replace(/\D/g, '');

        // Apply maxlength
        const maxLen = this.getAttribute('maxlength');
        if (maxLen) {
          sanitized = sanitized.slice(0, parseInt(maxLen, 10));
        }

        // Insert at cursor position
        const start = this.selectionStart;
        const end = this.selectionEnd;
        const currentValue = this.value;
        const newValue = currentValue.substring(0, start) + sanitized + currentValue.substring(end);
        this.value = newValue;

        // Trigger input event to run further cleaning & sync hidden fields
        this.dispatchEvent(new Event('input', {
          bubbles: true
        }));
      });
    });

    // 4. On form submit, ensure hidden fields are up‑to‑date (extra safety)
    document.getElementById('incomingPoForm').addEventListener('submit', function() {
      const hargaDisplay = document.getElementById('harga_display');
      if (hargaDisplay) {
        document.getElementById('harga').value = hargaDisplay.value.replace(/\D/g, '');
      }
      const tambahanDisplay = document.getElementById('tambahan_margin_display');
      if (tambahanDisplay) {
        document.getElementById('tambahan_margin').value = tambahanDisplay.value.replace(/\D/g, '');
      }
    });

    // Initialize Select2 for customer dropdown
    $('#customer_id').select2({
      theme: 'bootstrap-5',
      width: '100%',
      placeholder: '-- Select Customer --',
      allowClear: true
    });

    // --- Currency formatting functions ---
    function formatRupiah(angka, prefix = 'Rp') {
      if (!angka) return '';
      let number_string = angka.replace(/[^,\d]/g, '').toString(),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

      if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
      }
      rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
      return prefix ? (rupiah ? 'Rp ' + rupiah : '') : rupiah;
    }

    function unformatRupiah(val) {
      if (!val) return '0';
      return val.replace(/[^0-9]/g, '');
    }

    // Apply currency formatting on input and blur
    $(document).on('input', '.currency-input', function() {
      let raw = unformatRupiah($(this).val());
      $(this).val(formatRupiah(raw));
      let targetId = $(this).attr('id').replace('_display', '');
      $('#' + targetId).val(raw);
    }).on('blur', '.currency-input', function() {
      let raw = unformatRupiah($(this).val());
      if (raw === '') raw = '0';
      $(this).val(formatRupiah(raw));
      let targetId = $(this).attr('id').replace('_display', '');
      $('#' + targetId).val(raw);
    });

    // --- Live validation function ---
    function validateField(element) {
      let isValid = false;
      let fieldId = element.attr('id');
      let value = element.val();

      // --- Tambahan Margin (optional) ---
      if (fieldId === 'tambahan_margin_display') {
        if (value === '') {
          element.removeClass('is-invalid is-valid');
          return true;
        }
        let raw = unformatRupiah(value);
        isValid = !isNaN(raw) && raw !== '';
      }
      // --- Required currency inputs (Price per Unit) ---
      else if (element.hasClass('currency-input') && element.prop('required')) {
        let raw = unformatRupiah(value);
        isValid = raw !== '' && parseFloat(raw) > 0;
        // Update error message
        $('#harga-error').text(isValid ? 'Price is required.' : 'Price must be greater than zero.');
      }
      // --- Quantity and Margin Percentage (numeric‑only, required, >0) ---
      else if (fieldId === 'qty' || fieldId === 'margin_percentage') {
        // Value is already digits due to numeric‑only filtering
        let num = parseInt(value, 10);
        isValid = value !== '' && !isNaN(num) && num > 0;
        // Update the corresponding error message
        if (fieldId === 'qty') {
          $('#qty-error').text(isValid ? 'Quantity must be at least 1.' : 'Quantity must be greater than zero.');
        } else if (fieldId === 'margin_percentage') {
          $('#margin-error').text(isValid ? 'Margin percentage is required.' : 'Margin percentage must be greater than zero.');
        }
      }
      // --- All other inputs (text, select, etc.) ---
      else {
        isValid = element[0].checkValidity();
      }

      // Apply Bootstrap validation classes and show/hide feedback
      if (isValid) {
        element.removeClass('is-invalid').addClass('is-valid');
        element.siblings('.invalid-feedback').hide();
      } else {
        element.removeClass('is-valid').addClass('is-invalid');
        element.siblings('.invalid-feedback').show();
      }
      return isValid;
    }

    // Attach validation events
    $('.live-validate').on('change keyup blur', function() {
      validateField($(this));
    });

    // Special handling for select2 changes (trigger validation)
    $('#customer_id').on('change', function() {
      validateField($(this));
    });

    // --- AJAX form submission with SweetAlert ---
    $('#incomingPoForm').on('submit', function(e) {
      e.preventDefault();

      // Ensure all fields are validated
      let allValid = true;
      $('.live-validate').each(function() {
        if (!validateField($(this))) allValid = false;
      });

      if (!allValid) {
        Swal.fire({
          icon: 'error',
          title: 'Validation Error',
          text: 'Please fill all required fields correctly.'
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
            // Fallback if success:false but no errors
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
            // Validation errors from Laravel
            let errors = xhr.responseJSON.errors;
            msg = '<ul>';
            $.each(errors, function(field, messages) {
              msg += '<li>' + messages[0] + '</li>';
            });
            msg += '</ul>';
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