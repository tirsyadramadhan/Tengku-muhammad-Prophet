@extends('layouts/contentNavbarLayout')

@section('content')
<div class="row">
  <div class="col-xl-8 col-lg-10 mx-auto">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Ubah Incoming PO</h5>
      </div>
      <div class="card-body">
        <form
          data-method="PUT"
          id="incomingPoForm" action="{{ route('incomingPo.update', $po->po_id) }}" method="POST">
          <div class="mb-4">
            <label class="form-label fw-bold">Pelanggan <span class="text-danger">*</span></label>
            <select name="customer_id" id="customer_id" class="form-select select2 live-validate" required>
              @foreach($customers as $cust)
              <option value="{{ $cust->id_cust }}" {{ $po->customer_id == $cust->id_cust ? 'selected' : '' }}>
                {{ $cust->cust_name }}
              </option>
              @endforeach
            </select>
          </div>

          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label fw-bold">Nama Barang <span class="text-danger">*</span></label>
              <input value="{{ $po->nama_barang }}" type="text" name="nama_barang" id="nama_barang" class="form-control live-validate" placeholder="Contoh: Laptop, Meja, dll." required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">Tanggal PO <span class="text-danger">*</span></label>
              <input type="date" name="tgl_po" id="tgl_po"
                class="form-control live-validate"
                value="{{ date('Y-m-d', strtotime($po->tgl_po)) }}" required>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-bold">Jumlah <span class="text-danger">*</span></label>
              <input value="{{ $po->qty }}" maxlength="3" type="text" name="qty" id="qty" class="form-control live-validate numeric-only" min="1" placeholder="Contoh: 10" required>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-bold">Harga per Unit (Rp) <span class="text-danger">*</span></label>
              <input value="{{ (int) $po->harga }}" maxlength="16" type="text" id="harga_display" class="form-control currency-input live-validate numeric-only" placeholder="0" required>
              <input type="hidden" name="harga" id="harga" value="{{ (int) $po->harga }}">
            </div>

            <div class="col-md-4">
              <label for="margin_percentage" class="form-label fw-bold d-block">
                Margin <span class="text-muted small fw-normal"></span>
              </label>

              <input type="text"
                maxlength="2"
                name="margin_percentage"
                id="margin_percentage"
                class="form-control numeric-only live-validate"
                placeholder="0"
                step="0.1"
                value="{{ $marginPercentage }}">
            </div>

            <div class="col-md-12">
              <label class="form-label fw-bold">Tambahan Margin (Rp) <span class="text-muted">(Opsional)</span></label>
              <input value="{{ (int) $po->tambahan_margin }}" maxlength="14" type="text" id="tambahan_margin_display" class="form-control currency-input numeric-only" placeholder="0">
              <input type="hidden" name="tambahan_margin" id="tambahan_margin" value="{{ (int) $po->tambahan_margin }}">
            </div>
          </div>

          <hr class="my-4">

          <div class="d-flex justify-content-end">
            <button type="submit" id="btnSave" class="btn btn-primary px-5">
              <span id="btnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
              Perbarui Purchase Order
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection