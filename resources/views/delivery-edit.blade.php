@extends('layouts/contentNavbarLayout')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Edit Pengiriman: #{{ $delivery->delivery_id }}</h5>
    <a href="{{ route('delivery.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
  </div>
  <div class="card-body">
    <form id="deliveryForm" action="{{ route('delivery.update', $delivery->delivery_id) }}" method="POST">
      @method('PUT')
      <div class="row">

        <!-- PO Selection (Select2) -->
        <div class="col-md-6 mb-3">
          <label class="form-label">Tautkan ke Purchase Order <span class="text-danger">*</span></label>
          <input type="hidden" name="po_id" value="{{ $delivery->po_id }}">
          <select id="po_id" class="form-select select2" disabled readonly>
            @foreach($purchaseOrders as $po)
            <option value="{{ $po->po_id }}"
              data-qty="{{ $po->available_for_edit }}"
              {{ $delivery->po_id == $po->po_id ? 'selected' : '' }}>
              Nomor PO: {{ $po->display_text }} | Nama Barang: {{ $po->nama_barang }}
            </option>
            @endforeach
          </select>
        </div>

        <!-- Quantity to Deliver -->
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
        </div>

        <!-- Estimated Delivery Date -->
        <div class="col-md-6 mb-3">
          <label class="form-label">Estimasi Tanggal Pengiriman <span class="text-danger">*</span></label>
          <input type="date"
            name="delivery_time_estimation"
            id="delivery_time_estimation"
            class="form-control"
            value="{{ \Carbon\Carbon::parse($delivery->delivery_time_estimation)->format('Y-m-d') }}"
            min="{{ now('Asia/Jakarta')->format('Y-m-d') }}"
            required>
        </div>

      </div>

      <!-- ← ADDED: matches create form, pre-checked if already delivered -->
      <div class="col-md-6 mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="1" name="deliver_now" id="deliver_now"
            {{ $delivery->delivered_status == 1 ? 'checked' : '' }}>
          <label class="form-check-label" for="deliver_now">
            Deliver Sekarang?
          </label>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" id="submit_btn">Perbarui Pengiriman</button>
    </form>
  </div>
</div>
@endsection