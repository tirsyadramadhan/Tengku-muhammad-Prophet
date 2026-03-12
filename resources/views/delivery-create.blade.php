@extends('layouts/contentNavbarLayout')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Catat Pengiriman Baru</h5>
    </div>
    <div class="card-body">
        <form id="deliveryForm" action="{{ route('delivery.store') }}" method="POST">
            <div class="row">
                <!-- PO Selection (Select2) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tautkan ke Purchase Order <span class="text-danger">*</span></label>
                    <select name="po_id" id="po_id" class="form-select select2" required>
                        @if($pos->isEmpty())
                        <option value="">Tidak ada PO tertunda yang tersedia untuk pengiriman</option>
                        @else
                        <option value="">Pilih PO</option>
                        @foreach($pos as $po)
                        <option value="{{ $po->po_id }}" data-qty="{{ $po->remaining }}">
                            Nomor PO: {{ $po->display_text }} | Nama Barang: {{ $po->nama_barang }}
                        </option>
                        @endforeach
                        @endif
                    </select>
                </div>

                <!-- Quantity to Deliver (text, numeric-only) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jumlah yang Dikirim <span class="text-danger">*</span></label>
                    <input type="text"
                        name="qty_delivered"
                        id="qty_delivered"
                        class="form-control numeric-only"
                        maxlength="3"
                        placeholder="Masukkan jumlah"
                        required>
                </div>

                <!-- Estimated Delivery Date (min = now) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estimasi Tanggal Pengiriman <span class="text-danger">*</span></label>
                    <input type="date"
                        name="delivery_time_estimation"
                        id="delivery_time_estimation"
                        class="form-control"
                        min="{{ now('Asia/Jakarta')->format('Y-m-d') }}"
                        required>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="0" id="deliver_now">
                    <label class="form-check-label" for="deliver_now">
                        Deliver Sekarang?
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="submit_btn">Simpan Pengiriman</button>
        </form>
    </div>
</div>
@endsection