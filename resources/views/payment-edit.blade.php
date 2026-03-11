@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Pembayaran')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Pembayaran</h5>
    </div>
    <div class="card-body">
        <form id="paymentForm" action="{{ route('payment.update', $payment->payment_id) }}" method="POST">
            @method('PUT')
            <div class="row">

                <!-- Pilih Invoice (Select2) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pilih Invoice <span class="text-danger">*</span></label>
                    <select name="invoice_id" id="invoice_id" class="form-select select2" required>
                        <option value="">-- Pilih Invoice --</option>
                        @foreach($invoices as $inv)
                        <option
                            value="{{ $inv->invoice_id }}"
                            data-total="{{ $inv->total_display }}"
                            {{ $inv->invoice_id == $payment->invoice_id ? 'selected' : '' }}>
                            Invoice: {{ $inv->nomor_invoice }} | Delivery: {{ $inv->delivery->delivery_no }} | Nomor PO: {{ $inv->delivery->po->no_po }} | Nama Barang: {{ $inv->delivery->po->nama_barang }} | Tagihan: Rp {{ number_format($inv->total_display, 0, ',', '.') }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Jumlah (read-only, auto-filled) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
                    <input type="text"
                        name="amount"
                        id="amount_display"
                        class="form-control"
                        readonly
                        placeholder="0"
                        data-raw="{{ $payment->amount }}"
                        value="Rp {{ number_format($payment->amount, 0, ',', '.') }}">
                </div>

                <!-- Metode Pembayaran (Select2) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                    <select name="metode_bayar" id="metode_bayar" class="form-select select2" required>
                        <option value="">-- Pilih Metode --</option>
                        @foreach(['Tunai','Transfer Bank','Kartu Kredit','Kartu Debit','QRIS','OVO','GoPay','DANA','LinkAja','ShopeePay'] as $metode)
                        <option value="{{ $metode }}" {{ $payment->metode_bayar === $metode ? 'selected' : '' }}>
                            {{ $metode }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Estimasi Pembayaran -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estimasi Pembayaran <span class="text-danger">*</span></label>
                    <input type="date"
                        name="payment_date"
                        id="payment_date_estimation"
                        class="form-control"
                        value="{{ $payment->payment_date_estimation ? \Carbon\Carbon::parse($payment->payment_date_estimation)->format('Y-m-d') : '' }}"
                        required>
                </div>

                <!-- Bayar Sekarang -->
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" name="pay_now" id="pay_now"
                            {{ $payment->payment_status == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="pay_now">
                            Lunasi Sekarang?
                        </label>
                    </div>
                </div>

            </div>

            <div class="mt-2">
                <button type="submit" id="btnSave" class="btn btn-warning me-2">
                    <span id="btnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                    <i class="ri-save-line me-1"></i> Update Pembayaran
                </button>
                <a href="{{ route('payment.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection