@extends('layouts/contentNavbarLayout')

@section('title', 'Rekam Pembayaran Baru')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Rekam Pembayaran Baru</h5>
    </div>
    <div class="card-body">
        <form id="paymentForm" action="{{ route('payment.store') }}" method="POST">
            <div class="row">
                <!-- Pilih Invoice (Select2) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pilih Invoice <span class="text-danger">*</span></label>
                    <select name="invoice_id" id="invoice_id" class="form-select select2" required>
                        <option value="">-- Pilih Invoice --</option>
                        @foreach($invoices as $inv)
                        <option value="{{ $inv->invoice_id }}" data-total="{{ $inv->total_display }}">
                            Invoice: {{ $inv->nomor_invoice }} | Delivery: {{ $inv->delivery->delivery_no }} | Nomor PO: {{ $inv->delivery->po->no_po }} | Nama Barang: {{ $inv->delivery->po->nama_barang }} | Tagihan: Rp {{ number_format($inv->total_display, 0, ',', '.') }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Jumlah (read‑only, akan terisi otomatis) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
                    <input type="text"
                        name="amount"
                        id="amount_display"
                        class="form-control"
                        readonly
                        placeholder="0"
                        data-raw="0">
                </div>

                <!-- Metode Pembayaran (Select2) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                    <select name="metode_bayar" id="metode_bayar" class="form-select select2" required>
                        <option value="">-- Pilih Metode --</option>
                        <option value="Tunai">Tunai</option>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="Kartu Kredit">Kartu Kredit</option>
                        <option value="Kartu Debit">Kartu Debit</option>
                        <option value="QRIS">QRIS</option>
                        <option value="OVO">OVO</option>
                        <option value="GoPay">GoPay</option>
                        <option value="DANA">DANA</option>
                        <option value="LinkAja">LinkAja</option>
                        <option value="ShopeePay">ShopeePay</option>
                    </select>
                </div>

                <!-- Tanggal Pembayaran (datetime‑local) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estimasi Pembayaran <span class="text-danger">*</span></label>
                    <input type="date"
                        name="payment_date"
                        id="payment_date_estimation"
                        class="form-control"
                        required>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="0" id="pay_now">
                        <label class="form-check-label" for="pay_now">
                            Lunasi Sekarang?
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-2">
                <button type="submit" id="btnSave" class="btn btn-primary me-2">
                    <span id="btnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                    Simpan Pembayaran
                </button>
                <a href="{{ route('payment.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection