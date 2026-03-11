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
</div>
@endsection