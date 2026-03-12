@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Invoice')

@section('content')
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Ubah Data Invoice</h5>
  </div>
  <div class="card-body">
    <form id="invoiceForm" action="{{ route('invoice.update', $item->invoice_id) }}" method="POST">
      @method('PUT')
      <div class="row">

        <!-- Pilih Pengiriman (Select2) -->
        <div class="col-md-6 mb-3">
          <label class="form-label">Pilih Catatan Pengiriman <span class="text-danger">*</span></label>
          <select name="delivery_id" id="delivery_select" class="form-select select2 live-validate">
            @foreach($deliveries as $d)
            <option value="{{ $d->delivery_id }}" {{ $d->delivery_id == $currentDelivery->delivery_id ? 'selected' : '' }}>
              Delivery: {{ $d->delivery_no }} | Nomor PO: {{ $d->po->no_po ?? 'N/A' }} | Nama Barang: {{ $d->po->nama_barang ?? 'N/A' }} | Jml: {{ number_format($d->qty_delivered, 0) }}
            </option>
            @endforeach
          </select>
        </div>

        <!-- Tanggal Invoice -->
        <div class="col-md-6 mb-3">
          <label class="form-label">Tanggal Invoice <span class="text-danger">*</span></label>
          <input type="date"
            name="tgl_invoice"
            id="tgl_invoice"
            class="form-control live-validate"
            value="{{ \Carbon\Carbon::parse($item->tgl_invoice)->format('Y-m-d') }}"
            required>
        </div>

        <!-- Tanggal Jatuh Tempo -->
        <div class="col-md-6 mb-3">
          <label class="form-label">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
          <input type="date"
            name="due_date"
            id="due_date"
            class="form-control live-validate"
            value="{{ \Carbon\Carbon::parse($item->due_date)->format('Y-m-d') }}"
            required>
        </div>

      </div>

      <!-- ← ADDED: matches create form, pre-checked if payment already exists -->
      <div class="col-md-6 mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="{{ $item->payment()->exists() ? 1 : 0 }}" name="pay_now" id="pay_now"
            {{ $item->payment()->exists() ? 'checked' : '' }}>
          <label class="form-check-label" for="pay_now">
            Bayar dan lunasi sekarang?
          </label>
        </div>
      </div>

      <div class="mt-2">
        <button type="submit" id="btnSave" class="btn btn-primary me-2">
          <span id="btnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
          Simpan Invoice
        </button>
        <a href="{{ route('invoice.index') }}" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection