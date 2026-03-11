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

                <form id="createPoForm" action="{{ route('po.store') }}">
                    <!-- Incoming PO Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih Incoming PO <span class="text-danger">*</span></label>
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
                        <label class="form-label fw-bold">Nomor PO <span class="text-danger">*</span></label>
                        <input type="text"
                            name="no_po"
                            id="no_po"
                            class="form-control live-validate numeric-only"
                            maxlength="10"
                            placeholder="Masukkan nomor PO"
                            required>
                    </div>

                    <hr class="my-4">

                    <div class="row g-4">

                        <!-- Pelanggan -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Pelanggan <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-select select2 live-validate" required>
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach($customers as $cust)
                                <option value="{{ $cust->id_cust }}">{{ $cust->cust_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Nama Barang -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" id="nama_barang" class="form-control live-validate" placeholder="Contoh: Laptop, Meja, dll." required>
                        </div>

                        <!-- Tanggal PO -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal PO <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_po" id="tgl_po"
                                class="form-control live-validate"
                                value="{{ now('Asia/Jakarta')->format('Y-m-d') }}" required>
                        </div>

                        <!-- Jumlah -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Jumlah <span class="text-danger">*</span></label>
                            <input maxlength="3" type="text" name="qty" id="qty" class="form-control live-validate numeric-only" min="1" placeholder="Contoh: 10" required>
                        </div>

                        <!-- Harga per Unit -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Harga per Unit (Rp) <span class="text-danger">*</span></label>
                            <input maxlength="16" type="text" id="harga_display" class="form-control currency-input live-validate numeric-only" placeholder="0" required>
                            <input type="hidden" name="harga" id="harga">
                        </div>

                        <!-- Margin -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Margin (%) <span class="text-danger">*</span></label>
                            <input type="text"
                                maxlength="2"
                                name="margin_percentage"
                                id="margin_percentage"
                                class="form-control numeric-only live-validate"
                                placeholder="0"
                                step="0.1">
                        </div>

                        <!-- Tambahan Margin (Optional) -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">
                                Tambahan Margin (Rp) <span class="text-muted fw-normal">(Opsional)</span>
                            </label>
                            <input maxlength="14" type="text" id="tambahan_margin_display" class="form-control currency-input numeric-only" placeholder="0">
                            <input type="hidden" name="tambahan_margin" id="tambahan_margin">
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