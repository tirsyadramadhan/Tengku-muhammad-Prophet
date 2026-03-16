@extends('layouts/contentNavbarLayout')

@section('title', 'Buat Invoice')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Buat Invoice Baru</h5>
    </div>
    <div class="card-body">
        <form id="invoiceForm" action="{{ route('invoice.store') }}" method="POST" novalidate autocomplete="off">
            <div class="row">
                <!-- Pilih Pengiriman (Select2) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pilih Catatan Pengiriman <span class="text-danger">*</span></label>
                    <select name="delivery_id" id="delivery_select" class="form-select select2 live-validate" required>
                        <option value="">-- Pilih Pengiriman --</option>
                        @foreach($deliveries as $d)
                        <option value="{{ $d->delivery_id }}" data-po="{{ $d->po->no_po ?? 'N/A' }}" data-customer="{{ $d->po->customer->cust_name ?? 'N/A' }}" data-qty="{{ $d->qty_delivered }}">
                            Delivery: {{ $d->delivery_no }} | Nomor PO: {{ $d->po->no_po ?? 'N/A' }} | Nama Barang: {{ $d->po->nama_barang }} | Jml: {{ number_format($d->qty_delivered, 0) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal Invoice (Asia/Jakarta) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Invoice <span class="text-danger">*</span></label>
                    <input type="date"
                        name="tgl_invoice"
                        id="tgl_invoice"
                        class="form-control live-validate"
                        required>
                </div>

                <!-- Tanggal Jatuh Tempo (harus setelah tanggal invoice) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                    <input type="date"
                        name="due_date"
                        id="due_date"
                        class="form-control live-validate"
                        required>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" name="pay_now" id="pay_now">
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