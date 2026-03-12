@extends('layouts/contentNavbarLayout')

@section('content')
<div class="row">
    <div class="col-xl-8 col-lg-10 mx-auto">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Buat Purchase Order</h5>
            </div>
            <div class="card-body">
                <div id="createPoForm-meta"
                    data-incoming-details-url="{{ route('po.incoming-details', ':id') }}">
                </div>

                <form id="createPoForm" action="{{ route('po.store') }}" method="POST">
                    <!-- Incoming PO Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih Incoming PO</label>
                        <select name="incoming_po_id" id="incoming_po_id" class="form-select select2 live-validate" required>
                            <option value="">-- Pilih Incoming PO --</option>
                            @foreach($dataIncomingPo as $incoming)
                            <option value="{{ $incoming->po_id }}" data-no-po="{{ $incoming->no_po }}">
                                Nomor PO: {{ $incoming->no_po }} | Nama Barang: {{ $incoming->nama_barang }} | (Jumlah: {{ $incoming->qty }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Editable PO Number -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Nomor PO</label>
                        <input type="text" name="no_po" id="no_po" class="form-control live-validate numeric-only" maxlength="10" placeholder="Masukkan nomor PO" required>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <!-- Customer (read‑only) -->
                        <div class="col-md-6">
                            <label class="form-label">Pelanggan</label>
                            <input type="text" id="customer_name_display" class="form-control bg-light" placeholder="Akan terisi otomatis">
                            <input type="hidden" name="customer_id" id="customer_id">
                        </div>

                        <!-- Product Name -->
                        <div class="col-md-6">
                            <label class="form-label">Nama Barang</label>
                            <input type="text" name="nama_barang" id="nama_barang" class="form-control live-validate" required>
                        </div>

                        <!-- PO Date -->
                        <div class="col-md-6">
                            <label class="form-label">Tanggal PO</label>
                            <input value="{{ now('Asia/Jakarta')->format('Y-m-d') }}" type="date" name="tgl_po" id="tgl_po" class="form-control live-validate" required>
                        </div>

                        <!-- Quantity -->
                        <div class="col-md-6">
                            <label class="form-label">Jumlah</label>
                            <input type="text" name="qty" id="qty" class="form-control live-validate numeric-only" maxlength="3" required>
                        </div>

                        <!-- Price per Unit (currency) -->
                        <div class="col-md-6">
                            <label class="form-label">Harga per Unit (Rp)</label>
                            <input type="text" name="harga_display" id="harga_display" class="form-control currency-input live-validate numeric-only" data-max-raw="11" required placeholder="0">
                            <input type="hidden" name="harga" id="harga">
                        </div>

                        <!-- Total Margin (currency) -->
                        <div class="col-md-6">
                            <label class="form-label">Total Margin (Rp)</label>
                            <input type="text" name="margin_display" id="margin_display" class="form-control currency-input live-validate numeric-only" data-max-raw="13" required placeholder="0">
                            <input type="hidden" name="margin" id="margin">
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end">
                        <button type="submit" id="btnSave" class="btn btn-primary px-5">
                            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                            Simpan & Buka PO
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection