@extends('layouts/contentNavbarLayout')

@section('title', 'Dana Tersedia')

@section('vendor-style')
<style>
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- ── Page Title ────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="card">
            <h4 class="fw-bold mb-1">
                Dana Tersedia:
                <span style="color: {{ $danaTersedia < 0 ? '#dc3545' : '#90ee90' }}">
                    Rp {{ number_format($danaTersedia, 0, ',', '.') }}
                </span>
            </h4>
            <table class="table table-bordered table-dark mt-4">
                <tbody>
                    <tr>
                        <th colspan="2" class="text-center">Rumus Dana Tersedia:</th>
                    </tr>
                    <tr>
                        <td>Total margin seluruh PO (Kecuali Incoming PO): </td>
                        <td>
                            <span style="color: {{ $totalMargin < 0 ? '#dc3545' : '#90ee90' }}">
                                {{ number_format($totalMargin) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Margin Diterima: </td>
                        <td>
                            <span style="color: {{ $marginDiterima < 0 ? '#dc3545' : '#90ee90' }}">
                                {{ number_format($marginDiterima) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Total margin seluruh PO (Kecuali Incoming PO dan PO yang sudah Close): </td>
                        <td>
                            <span style="color: {{ $marginDitahan < 0 ? '#dc3545' : '#90ee90' }}">
                                {{ number_format($marginDitahan) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Total modal setor awal dari Investasi: </td>
                        <td>
                            <span style="color: {{ $modalSetorAwal < 0 ? '#dc3545' : '#90ee90' }}">
                                {{ number_format($modalSetorAwal) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Investasi Dikembalikan: </td>
                        <td>
                            <span style="color: {{ $investasiDikembalikan < 0 ? '#dc3545' : '#90ee90' }}">
                                {{ number_format($investasiDikembalikan) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Total modal seluruh PO (Kecuali Incoming PO dan PO yang sudah Close): </td>
                        <td>
                            <span style="color: {{ $investasiDitahan < 0 ? '#dc3545' : '#90ee90' }}">
                                {{ number_format(num: $investasiDitahan) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Dana Tersedia: (<span style="color: {{ $totalMargin < 0 ? '#dc3545' : '#90ee90' }}">
                                {{ number_format($totalMargin) }}
                            </span> - <span style="color: {{ $marginDiterima < 0 ? '#dc3545' : '#90ee90' }}">
                                {{ number_format($marginDiterima) }}
                            </span> - <span style="color: {{ $marginDitahan < 0 ? '#dc3545' : '#90ee90' }}">
                                {{ number_format($marginDitahan) }}
                            </span> + <span style="color: {{ $modalSetorAwal < 0 ? '#dc3545' : '#90ee90' }}">
                                {{ number_format($modalSetorAwal) }}
                            </span> + <span style="color: {{ $investasiDikembalikan < 0 ? '#dc3545' : '#90ee90' }}">
                                {{ number_format($investasiDikembalikan) }}
                            </span> - <span style="color: {{ $investasiDitahan < 0 ? '#dc3545' : '#90ee90' }}">
                                {{ number_format(num: $investasiDitahan) }}
                            </span>)</td>
                        <td>
                            <span style="color: {{ $danaTersedia < 0 ? '#dc3545' : '#90ee90' }}">
                                {{ number_format($danaTersedia) }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    @php
    $statusMap = [
    0 => 'Incoming',
    1 => 'Open',
    2 => 'Partially Delivered',
    3 => 'Fully Delivered',
    4 => 'Partially Delivered & Partially Invoiced',
    5 => 'Fully Delivered & Partially Invoiced',
    6 => 'Partially Delivered & Fully Invoiced',
    7 => 'Fully Delivered & Fully Invoiced',
    8 => 'Closed',
    ];
    @endphp

    {{-- INVESTASI TABLE --}}
    <h4>Jumlahkan total modal setor awal dari seluruh Investasi</h4>
    <table class="table table-bordered table-dark">
        <thead>
            <tr>
                <th>Modal Setor Awal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($investasiRows as $row)
            <tr>
                <td>{{ number_format($row->modal_setor_awal) }}</td>
            </tr>
            @endforeach
            <tr>
                <td>Total: {{ number_format($invSums->total_awal) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- PO MARGIN TABLE (status != 0) --}}
    <h4>Diambil total margin dari semua PO (Kecuali Incoming PO)</h4>
    <table class="table table-bordered table-dark">
        <thead>
            <tr>
                <th>Status</th>
                <th>Margin</th>
            </tr>
        </thead>
        <tbody>
            @foreach($poMarginRows as $row)
            <tr>
                <td>{{ $statusMap[$row->status] ?? 'Unknown' }}</td>
                <td>{{ number_format($row->margin) }}</td>
            </tr>
            @endforeach
            <tr>
                <td>Status</td>
                <td>Total: {{ number_format($totalMargin) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- PO DITAHAN TABLE (status != 0 AND != 8) --}}
    <h4>Diambil margin yang ditahan dan modal yang ditahan dari seluruh PO (Kecuali Incoming PO dan PO yang sudah Close)</h4>
    <table class="table table-bordered table-dark">
        <thead>
            <tr>
                <th>Status</th>
                <th>Margin</th>
                <th>Modal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($poDitahanRows as $row)
            <tr>
                <td>{{ $statusMap[$row->status] ?? 'Unknown' }}</td>
                <td>{{ number_format($row->margin) }}</td>
                <td>{{ number_format($row->modal_awal) }}</td>
            </tr>
            @endforeach
            <tr>
                <td>Status</td>
                <td>Total: {{ number_format($marginDitahan) }}</td>
                <td>Total: {{ number_format($investasiDitahan) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- MARGIN TABLE --}}
    <h4>Diambil Investasi Dikembalikan, Margin Diterima dan Margin Tersedia</h4>
    <table class="table table-bordered table-dark">
        <thead>
            <tr>
                <th>Investasi Dikembalikan</th>
                <th>Margin Diterima</th>
                <th>Margin Tersedia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($marginRows as $row)
            <tr>
                <td>{{ number_format($row->investasi_dikembalikan) }}</td>
                <td>{{ number_format($row->margin_diterima) }}</td>
                <td>{{ number_format($row->margin_tersedia) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection