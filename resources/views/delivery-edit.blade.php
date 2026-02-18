@extends('layouts/contentNavbarLayout')

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
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Edit Pengiriman: #{{ $delivery->delivery_id }}</h5>
    <a href="{{ route('delivery.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
  </div>
  <div class="card-body">
    <form id="deliveryForm" action="{{ route('delivery.update', $delivery->delivery_id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="row">
        <input type="hidden" value="{{ (int) $delivery->qty_delivered }}" id="qtyInput2">
        <div class="col-md-6 mb-3">
          <label class="form-label">Purchase Order</span></label>

          <!-- Hidden field to submit the actual po_id -->
          <input type="hidden" name="po_id" value="{{ $delivery->po_id }}">

          <!-- Disabled select for display only -->
          <select id="po_id" class="form-select select2" disabled readonly>
            @foreach($purchaseOrders as $po)
            <option value="{{ $po->po_id }}"
              data-qty="{{ $po->available_for_edit }}"
              {{ $delivery->po_id == $po->po_id ? 'selected' : '' }}>
              {{ $po->display_text }}
            </option>
            @endforeach
          </select>

          <div class="invalid-feedback">Silakan pilih PO.</div>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Jumlah yang Dikirim <span class="text-danger">*</span></label>
          <input type="text"
            name="qty_delivered"
            id="qty_delivered"
            class="form-control numeric-only"
            maxlength="3"
            placeholder="Masukkan jumlah"
            value="{{ (int) $delivery->qty_delivered }}"
            required>
          <div class="invalid-feedback" id="qty-error">Jumlah harus lebih dari 0.</div>
          <div id="qty_warning" class="text-danger small mt-1" style="display:none;">
            Jumlah melebihi sisa PO yang tersedia!
          </div>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Estimasi Tanggal Pengiriman <span class="text-danger">*</span></label>
          <input type="datetime-local"
            name="delivery_time_estimation"
            id="delivery_time_estimation"
            class="form-control"
            value="{{ \Carbon\Carbon::parse($delivery->delivery_time_estimation)->format('Y-m-d\TH:i') }}"
            min="{{ now('Asia/Jakarta')->format('Y-m-d\TH:i') }}"
            required>
          <div class="invalid-feedback">Tanggal pengiriman harus hari ini atau setelahnya.</div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" id="submit_btn">Perbarui Pengiriman</button>
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

    // Initialize Select2
    $('#po_id').select2({
      theme: 'bootstrap-5',
      width: '100%',
      placeholder: '-- Pilih PO --',
      allowClear: true
    });

    const qtyInput = document.getElementById('qty_delivered');
    const qtyInput2 = document.getElementById('qtyInput2');
    const select3 = document.getElementById('po_id');
    const selectedOption2 = select3.options[select3.selectedIndex];
    const availableQty2 = parseInt(selectedOption2?.getAttribute('data-qty') || 0);
    const maxQty = Math.round(qtyInput2.value) + Math.round(availableQty2)

    // Logic for numeric-only enforcement (Same as your original)
    qtyInput.addEventListener('keydown', function(e) {
      const key = e.key;
      if (['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight'].includes(key) || e.ctrlKey || e.metaKey) return;
      if (!/^[0-9]$/.test(key)) e.preventDefault();
    });

    // Over-delivery warning logic
    function checkOverDelivery() {
      const select = document.getElementById('po_id');
      const selectedOption = select.options[select.selectedIndex];

      // The controller pre-calculates "remaining" including the current delivery's share
      const availableQty = parseInt(selectedOption?.getAttribute('data-qty') || 0);
      const inputQty = parseInt(qtyInput.value) || 0;

      const warningDiv = document.getElementById('qty_warning');
      const submitBtn = document.getElementById('submit_btn');

      if (inputQty > maxQty) {
        console.log(maxQty)
        warningDiv.style.display = 'block';
        warningDiv.innerText = `Maksimal yang dapat dikirim untuk PO ini adalah ${maxQty}`;
        $(qtyInput).addClass('is-invalid');
        submitBtn.disabled = true;
      } else {
        warningDiv.style.display = 'none';
        $(qtyInput).removeClass('is-invalid');
        submitBtn.disabled = false;
      }
    }

    // Run check on page load to validate initial data
    checkOverDelivery();

    $('#po_id').on('change', function() {
      checkOverDelivery();
      validateField($(this));
    });

    qtyInput.addEventListener('input', function() {
      checkOverDelivery();
      validateField($(this));
    });

    // Validation styling function (Same as your original)
    function validateField(element) {
      let $el = $(element);
      let fieldId = $el.attr('id');
      let value = $el.val();

      if (fieldId === 'qty_delivered') {
        if (!value || parseInt(value) <= 0) {
          $el.addClass('is-invalid').removeClass('is-valid');
          return false;
        }
      }
      $el.addClass('is-valid').removeClass('is-invalid');
      return true;
    }

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });
    // Form Submit handling (Same as your original but with PUT logic)
    $('#deliveryForm').on('submit', function(e) {
      e.preventDefault();

      // Clear previous error states
      $('.form-control').removeClass('is-invalid');
      $('.invalid-feedback').hide();

      let $form = $(this);
      let $btn = $('#submit_btn');
      let originalText = $btn.text();

      // Show loading state
      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize(),
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: response.message,
              timer: 2000,
              showConfirmButton: false
            }).then(() => {
              window.location.href = response.redirect_url;
            });
          }
        },
        error: function(xhr) {
          $btn.prop('disabled', false).text(originalText);

          if (xhr.status === 422) {
            // Validation Errors
            let errors = xhr.responseJSON.errors;
            Object.keys(errors).forEach(key => {
              let input = $(`[name="${key}"]`);
              input.addClass('is-invalid');
              // Find or create feedback div
              let feedback = input.siblings('.invalid-feedback');
              if (feedback.length) {
                feedback.text(errors[key][0]).show();
              }
            });

            Swal.fire({
              icon: 'error',
              title: 'Validasi Gagal',
              text: 'Periksa kembali data yang Anda masukkan.'
            });
          } else {
            // System Errors
            Swal.fire({
              icon: 'error',
              title: 'Error ' + xhr.status,
              text: xhr.responseJSON?.message || 'Terjadi kesalahan pada server.'
            });
          }
        }
      });
    });
  });
</script>
@endsection