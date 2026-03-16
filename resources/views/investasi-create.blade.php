@extends('layouts/contentNavbarLayout')

@section('title', 'Create Investment')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card mb-4">
                <h5 class="card-header">New Investment Recap</h5>
                <div class="card-body">
                    <form id="investasiForm" action="{{ route('investments.store') }}" method="POST" novalidate autocomplete="off">
                        <input type="hidden" name="mode_setor" id="mode_setor" value="auto">
                        <input type="hidden" name="mode_po_baru" id="mode_po_baru" value="auto">
                        <input type="hidden" name="mode_margin" id="mode_margin" value="auto">

                        <div class="mb-4 input-group-wrapper">
                            <label class="form-label mb-0">Modal Setor Awal</label>
                            <input type="hidden" name="sign_setor" id="sign_setor" value="1">
                            <div id="setor_auto_container">
                                <select class="form-select select2-po auto-input" name="ids_setor_awal[]" multiple="multiple"
                                    data-placeholder="Select POs..." data-type="modal" data-target="#disp_setor">
                                    @foreach($closedPos as $po)
                                    <option value="{{ $po->po_id }}" data-modal="{{ $po->modal_awal }}" data-margin="{{ $po->margin }}">
                                        {{ $po->no_po }} - {{ $po->nama_barang }} (Rp {{ number_format($po->modal_awal) }})
                                    </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-end">Sum: <span id="disp_setor" class="fw-bold">0</span></div>
                            </div>
                        </div>

                        <div class="mb-4 input-group-wrapper">
                            <label class="form-label mb-0">Modal PO Baru</label>

                            <input type="hidden" name="sign_po_baru" id="sign_po_baru" value="1">
                            <div id="po_baru_auto_container">
                                <select class="form-select select2-po auto-input" name="ids_po_baru[]" multiple="multiple"
                                    data-placeholder="Select POs..." data-type="modal" data-target="#disp_po_baru">
                                    @foreach($closedPos as $po)
                                    <option value="{{ $po->po_id }}" data-modal="{{ $po->modal_awal }}">
                                        {{ $po->no_po }} - {{ $po->nama_barang }} (Rp {{ number_format($po->modal_awal) }})
                                    </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-end">Sum: <span id="disp_po_baru" class="fw-bold">0</span></div>
                            </div>
                        </div>

                        <div class="mb-4 input-group-wrapper">
                            <label class="form-label mb-0">Margin</label>

                            <input type="hidden" name="sign_margin" id="sign_margin" value="1">
                            <div id="margin_auto_container">
                                <select class="form-select select2-po auto-input" name="ids_margin[]" multiple="multiple"
                                    data-placeholder="Select POs..." data-type="margin" data-target="#disp_margin">
                                    @foreach($closedPos as $po)
                                    <option value="{{ $po->po_id }}" data-margin="{{ $po->margin }}">
                                        {{ $po->no_po }} - {{ $po->nama_barang }} (Rp {{ number_format($po->margin) }})
                                    </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-end">Sum: <span id="disp_margin" class="fw-bold">0</span></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pencairan Modal</label>
                                <input type="number" step="1000" class="form-control calc-trigger" name="pencairan_modal" id="pencairan_modal" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Margin Cair</label>
                                <input type="number" step="1000" class="form-control calc-trigger" name="margin_cair" id="margin_cair" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Investasi Tambahan</label>
                                <input type="number" step="1000" class="form-control calc-trigger" name="investasi_tambahan" id="investasi_tambahan" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">TOTAL DANA DI TRANSFER</label>
                                <select class="form-select select2-po auto-input" id="penarikan" name="penarikan[]" multiple="multiple"
                                    data-placeholder="Pilih Margin Tersedia dan Total Investasi Transfer">
                                    <option value="{{ $dana_ditransfer }}" data-margin="{{ $dana_ditransfer }}">
                                        Margin Tersedia + Total Investasi Transfer: (Rp {{ number_format($dana_ditransfer) }})
                                    </option>
                                </select>
                                <div class="form-text text-end">Sum: <span id="disp_penarikan" class="fw-bold">0</span></div>
                            </div>
                        </div>

                        <div class="card calc-card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between small text-muted mb-2">
                                    <span>Prev. Dana: <strong>{{ number_format($dana_tersedia) }}</strong></span>
                                    <span id="formula_text"></span>
                                </div>
                                <label class="form-label fw-bold">Dana Tersedia</label>
                                <input type="text" class="form-control readonly-input" id="dana_tersedia" readonly>
                                <label class="form-label fw-bold mt-4">Pengembalian Dana</label>
                                <input name="pengembalian_dana" type="text" class="form-control readonly-input" id="pengembalian_dana" readonly>
                                <input type="hidden" id="prev_dana" value="{{ $dana_tersedia }}">
                            </div>
                        </div>

                        <button type="submit" id="btnSubmit" class="btn btn-primary w-100">Save Investment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection