@extends('layouts/contentNavbarLayout')

@section('title', 'Create Investment')

@section('page-style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .calc-card {
        border-left: 5px solid #696cff;
        background-color: #f8f9fa;
    }

    .readonly-input {
        background-color: #e9ecef !important;
        font-weight: bold;
    }

    .input-mode-toggle {
        cursor: pointer;
        text-decoration: none;
        font-size: 0.8rem;
    }

    .hidden-input {
        display: none;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card mb-4">
                <h5 class="card-header">New Investment Recap</h5>
                <div class="card-body">
                    <form id="investasiForm" action="{{ route('investments.store') }}" method="POST">
                        @csrf

                        <input type="hidden" name="mode_setor" id="mode_setor" value="auto">
                        <input type="hidden" name="mode_po_baru" id="mode_po_baru" value="auto">
                        <input type="hidden" name="mode_margin" id="mode_margin" value="auto">

                        <div class="mb-4 input-group-wrapper">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">Modal Setor Awal</label>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2 toggle-sign"
                                    data-target="setor"
                                    style="padding: 0.1rem 0.5rem; font-size: 0.75rem;">
                                    Make Negative
                                </button>
                                <a href="javascript:void(0);" class="input-mode-toggle text-primary" data-target="setor">
                                    <i class="ri-edit-line"></i> Switch to Manual
                                </a>
                            </div>
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

                            <div id="setor_manual_container" class="hidden-input">
                                <input type="number" class="form-control manual-input" name="manual_setor_awal">
                            </div>
                        </div>

                        <div class="mb-4 input-group-wrapper">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">Modal PO Baru</label>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2 toggle-sign"
                                    data-target="po_baru"
                                    style="padding: 0.1rem 0.5rem; font-size: 0.75rem;">
                                    Make Negative
                                </button>
                                <a href="javascript:void(0);" class="input-mode-toggle text-primary" data-target="po_baru">
                                    <i class="ri-edit-line"></i> Switch to Manual
                                </a>
                            </div>

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

                            <div id="po_baru_manual_container" class="hidden-input">
                                <input type="number" class="form-control manual-input" name="manual_po_baru">
                            </div>
                        </div>

                        <div class="mb-4 input-group-wrapper">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">Total Margin</label>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2 toggle-sign"
                                    data-target="margin"
                                    style="padding: 0.1rem 0.5rem; font-size: 0.75rem;">
                                    Make Negative
                                </button>
                                <a href="javascript:void(0);" class="input-mode-toggle text-primary" data-target="margin">
                                    <i class="ri-edit-line"></i> Switch to Manual
                                </a>
                            </div>

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

                            <div id="margin_manual_container" class="hidden-input">
                                <input type="number" class="form-control manual-input" name="manual_total_margin" value="0" step="1000">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pencairan Modal</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary toggle-simple" type="button" onclick="flipValue('#pencairan_modal')">(-)</button>
                                    <input type="number" step="1000" class="form-control calc-trigger" name="pencairan_modal" id="pencairan_modal" value="0">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">TOTAL DANA DI TRANSFER</label>
                                <select class="form-select select2-po auto-input" id="penarikan" name="penarikan[]" multiple="multiple"
                                    data-placeholder="Pilih Margin Tersedia dan Total Investasi Transfer">
                                    <option value="{{ $marginTersedia }}" data-margin="{{ $marginTersedia }}">
                                        Margin Tersedia: (Rp {{ number_format($marginTersedia) }})
                                    </option>
                                    <option value="{{ $totalInvestasiTransfer }}" data-margin="{{ $totalInvestasiTransfer }}">
                                        Total Investasi Transfer: (Rp {{ number_format($totalInvestasiTransfer) }})
                                    </option>
                                </select>
                                <div class="form-text text-end">Sum: <span id="disp_penarikan" class="fw-bold">0</span></div>
                            </div>
                        </div>

                        <div class="card calc-card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between small text-muted mb-2">
                                    <span>Prev. Dana: <strong>{{ number_format($prevDana) }}</strong></span>
                                    <span id="formula_text"></span>
                                </div>
                                <label class="form-label fw-bold">Dana Tersedia (Automatic Result)</label>
                                <input type="text" class="form-control readonly-input" id="dana_tersedia_display" readonly>
                                <input type="hidden" id="prev_dana" value="{{ $prevDana }}">
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

@section('page-script')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $.noConflict();

        // Initialize Select2
        $('.select2-po').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        // State objects
        var modes = {
            setor: 'auto',
            po_baru: 'auto',
            margin: 'auto'
        };

        // Signs object, initialized from hidden inputs
        var signs = {
            setor: parseFloat($('#sign_setor').val()) || 1,
            po_baru: parseFloat($('#sign_po_baru').val()) || 1,
            margin: parseFloat($('#sign_margin').val()) || 1
        };

        // Update sign button text based on current sign
        function updateSignButton(target) {
            var btn = $('.toggle-sign[data-target="' + target + '"]');
            if (signs[target] === -1) {
                btn.text('Make Positive');
            } else {
                btn.text('Make Negative');
            }
        }
        // Initialize button texts
        updateSignButton('setor');
        updateSignButton('po_baru');
        updateSignButton('margin');

        // Toggle sign handler
        $('.toggle-sign').on('click', function() {
            var target = $(this).data('target');
            // Toggle sign
            signs[target] = signs[target] === 1 ? -1 : 1;
            // Update hidden input
            $('#sign_' + target).val(signs[target]);
            // Update button text
            updateSignButton(target);
            // Recalculate
            recalculate();
        });

        // Mode toggle handler
        $('.input-mode-toggle').on('click', function() {
            var target = $(this).data('target');
            var isAuto = modes[target] === 'auto';
            if (isAuto) {
                modes[target] = 'manual';
                $('#mode_' + target).val('manual');
                $('#' + target + '_auto_container').addClass('hidden-input');
                $('#' + target + '_manual_container').removeClass('hidden-input');
                $(this).html('<i class="ri-list-check"></i> Switch to Select POs');
            } else {
                modes[target] = 'auto';
                $('#mode_' + target).val('auto');
                $('#' + target + '_auto_container').removeClass('hidden-input');
                $('#' + target + '_manual_container').addClass('hidden-input');
                $(this).html('<i class="ri-edit-line"></i> Switch to Manual');
            }
            recalculate();
        });

        // Prevent non-numeric input
        $(document).on('keypress', '.manual-input, .calc-trigger', function(e) {
            var charCode = e.which || e.keyCode;
            var val = $(this).val();
            if (charCode >= 48 && charCode <= 57) return true;
            if (charCode === 46 && val.indexOf('.') === -1) return true;
            e.preventDefault();
            return false;
        });

        $(document).on('input', '.manual-input, .calc-trigger', function() {
            var val = $(this).val();
            var cleanVal = val.replace(/[^0-9.]/g, '');
            var parts = cleanVal.split('.');
            if (parts.length > 2) cleanVal = parts[0] + '.' + parts.slice(1).join('');
            if (val !== cleanVal) $(this).val(cleanVal);
        });

        // Recalculation function
        function recalculate() {
            var prev = parseFloat($('#prev_dana').val()) || 0;

            // Setor Awal
            var setor = 0;
            if (modes.setor === 'auto') {
                $('[name="ids_setor_awal[]"] option:selected').each(function() {
                    setor += parseFloat($(this).data('modal')) || 0;
                });
                setor = setor * signs.setor;
                $('#disp_setor').text(new Intl.NumberFormat('id-ID').format(setor));
                if (signs.setor === -1) $('#disp_setor').addClass('text-danger');
                else $('#disp_setor').removeClass('text-danger');
            } else {
                setor = parseFloat($('[name="manual_setor_awal"]').val()) || 0;
                setor = setor * signs.setor;
            }

            // PO Baru
            var poBaru = 0;
            if (modes.po_baru === 'auto') {
                $('[name="ids_po_baru[]"] option:selected').each(function() {
                    poBaru += parseFloat($(this).data('modal')) || 0;
                });
                poBaru = poBaru * signs.po_baru;
                $('#disp_po_baru').text(new Intl.NumberFormat('id-ID').format(poBaru));
                if (signs.po_baru === -1) $('#disp_po_baru').addClass('text-danger');
                else $('#disp_po_baru').removeClass('text-danger');
            } else {
                poBaru = parseFloat($('[name="manual_po_baru"]').val()) || 0;
                poBaru = poBaru * signs.po_baru;
            }

            // Margin
            var margin = 0;
            if (modes.margin === 'auto') {
                $('[name="ids_margin[]"] option:selected').each(function() {
                    margin += parseFloat($(this).data('margin')) || 0;
                });
                margin = margin * signs.margin;
                $('#disp_margin').text(new Intl.NumberFormat('id-ID').format(margin));
                if (signs.margin === -1) $('#disp_margin').addClass('text-danger');
                else $('#disp_margin').removeClass('text-danger');
            } else {
                margin = parseFloat($('[name="manual_total_margin"]').val()) || 0;
                margin = margin * signs.margin;
            }

            var pencairan = parseFloat($('#pencairan_modal').val()) || 0;
            var penarikan = 0;
            $('#penarikan option:selected').each(function() {
                // Sum up the value of each selected option
                penarikan += parseFloat($(this).val()) || 0;
            });

            // Optional: Update the display text below the select box
            $('#disp_penarikan').text(new Intl.NumberFormat('id-ID').format(penarikan));
            // Dana tersedia formula: (prev + setor + margin + pencairan) - (poBaru + penarikan)
            var total = (prev + setor + margin + pencairan) - (poBaru + penarikan);

            $('#dana_tersedia_display').val(new Intl.NumberFormat('id-ID').format(total));
            $('#formula_text').text('(' + prev.toLocaleString() + ' + ' + setor.toLocaleString() + ' + ' + margin.toLocaleString() + ' + ' + pencairan.toLocaleString() + ') - (' + poBaru.toLocaleString() + ' + ' + penarikan.toLocaleString() + ')');
        }

        // Attach recalculate to relevant events
        $('.select2-po').on('change', recalculate);
        $('.manual-input, .calc-trigger').on('input', recalculate);
        recalculate(); // initial calculation

        // Flip value function for simple toggle buttons
        window.flipValue = function(selector) {
            var input = $(selector);
            var val = parseFloat(input.val()) || 0;
            input.val(-val);
            recalculate();
        };

        // AJAX submit
        $('#investasiForm').on('submit', function(e) {
            e.preventDefault();
            var btn = $('#btnSubmit');
            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = response.redirect_url;
                    });
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Save Investment');
                    var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error saving data';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });
    });
</script>
@endsection