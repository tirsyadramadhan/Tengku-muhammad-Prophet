@extends('layouts/contentNavbarLayout')

@php
$deliveryStatusMap = [
0 => ['label' => 'Menunggu', 'class' => 'bg-label-secondary', 'icon' => 'ri-time-line'],
1 => ['label' => 'Dikirim', 'class' => 'bg-label-info', 'icon' => 'ri-truck-line'],
2 => ['label' => 'Terkirim', 'class' => 'bg-label-success', 'icon' => 'ri-checkbox-circle-line'],
];
$invoicedStatusMap = [
0 => ['label' => 'Belum Ditagih', 'class' => 'bg-label-warning', 'icon' => 'ri-error-warning-line'],
1 => ['label' => 'Sudah Ditagih', 'class' => 'bg-label-primary', 'icon' => 'ri-check-double-line'],
];

$dStatus = $deliveryStatusMap[$delivery->delivered_status] ?? $deliveryStatusMap[0];
$iStatus = $invoicedStatusMap[$delivery->invoiced_status] ?? $invoicedStatusMap[0];

$invoices = \App\Models\Invoice::where('delivery_id', $delivery->delivery_id)->get();
$totalPoQty = $delivery->po->qty;
$thisQty = $delivery->qty_delivered;
$percentage = $totalPoQty > 0 ? min(($thisQty / $totalPoQty) * 100, 100) : 0;
$pColor = $percentage >= 100 ? 'bg-success' : 'bg-info';
@endphp

@section('title', 'Detail Pengiriman — ' . $delivery->delivery_no)

@section('content')
<div id="main-container-index" class="container-fluid px-3 px-md-4 py-4">

  {{-- ── Breadcrumb + Header ──────────────────────────────── --}}
  <div class="row align-items-center g-3 mb-4">
    <div class="col-12 col-sm">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item">
            <a href="{{ route('po.index') }}">Purchase Order</a>
          </li>
          <li class="breadcrumb-item">
            <a href="{{ route('po.show', $delivery->po_id) }}">{{ $delivery->po->no_po }}</a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">{{ $delivery->delivery_no }}</li>
        </ol>
      </nav>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <h4 class="fw-bold mb-0">Detail Pengiriman</h4>
        <span class="badge {{ $dStatus['class'] }} rounded-pill">
          <i class="{{ $dStatus['icon'] }} me-1"></i>{{ $dStatus['label'] }}
        </span>
        <span class="badge {{ $iStatus['class'] }} rounded-pill">
          <i class="{{ $iStatus['icon'] }} me-1"></i>{{ $iStatus['label'] }}
        </span>
      </div>
      <small class="text-muted">
        {{ $delivery->delivery_no }} &bull; Ref PO:
        <a href="{{ route('po.show', $delivery->po_id) }}" class="fw-medium">
          {{ $delivery->po->no_po }}
        </a>
      </small>
    </div>
    <div class="col-12 col-sm-auto d-flex flex-wrap gap-2">
      <a href="{{ route('delivery.edit', $delivery->delivery_id) }}" class="btn btn-outline-primary">
        <i class="ri-pencil-line me-1"></i>
        <span class="d-none d-sm-inline">Edit</span>
      </a>
      <button class="btn btn-outline-secondary">
        <i class="ri-printer-line me-1"></i>
        <span class="d-none d-sm-inline">Cetak Surat Jalan</span>
      </button>
      @if($delivery->invoiced_status == 0)
      <a href="{{ route('invoice.create', ['delivery_id' => $delivery->delivery_id]) }}" class="btn btn-primary">
        <i class="ri-file-add-line me-1"></i>
        <span class="d-none d-sm-inline">Buat Invoice</span>
      </a>
      @endif
    </div>
  </div>

  {{-- ── Stat Pills ───────────────────────────────────────── --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-none bg-label-primary h-100">
        <div class="card-body p-3">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-sm flex-shrink-0">
              <span class="avatar-initial rounded bg-primary">
                <i class="ri-truck-line"></i>
              </span>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 text-muted small text-truncate">Qty Dikirim</p>
              <h6 class="mb-0 fw-bold">{{ number_format($delivery->qty_delivered) }} Unit</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-none bg-label-info h-100">
        <div class="card-body p-3">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-sm flex-shrink-0">
              <span class="avatar-initial rounded bg-info">
                <i class="ri-calendar-event-line"></i>
              </span>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 text-muted small text-truncate">Est. Tiba</p>
              <h6 class="mb-0 fw-bold small">
                {{ $delivery->delivery_time_estimation
                                    ? \Carbon\Carbon::parse($delivery->delivery_time_estimation)->format('d M Y')
                                    : 'N/A' }}
              </h6>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-none bg-label-success h-100">
        <div class="card-body p-3">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-sm flex-shrink-0">
              <span class="avatar-initial rounded bg-success">
                <i class="ri-checkbox-circle-line"></i>
              </span>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 text-muted small text-truncate">Tiba Aktual</p>
              <h6 class="mb-0 fw-bold small">
                {{ $delivery->delivered_at
                                    ? \Carbon\Carbon::parse($delivery->delivered_at)->format('d M Y')
                                    : 'Belum tiba' }}
              </h6>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-none bg-label-warning h-100">
        <div class="card-body p-3">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-sm flex-shrink-0">
              <span class="avatar-initial rounded bg-warning">
                <i class="ri-percent-line"></i>
              </span>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 text-muted small text-truncate">Porsi PO</p>
              <h6 class="mb-0 fw-bold">{{ number_format($percentage, 1) }}%</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Main Layout ──────────────────────────────────────── --}}
  <div class="row g-4">

    {{-- Left Column ─────────────────────────────────────── --}}
    <div class="col-12 col-lg-8">

      {{-- Delivery Info --}}
      <div class="card mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
          <h5 class="card-title mb-0">
            <i class="ri-truck-line me-2 text-primary"></i>Informasi Pengiriman
          </h5>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge {{ $dStatus['class'] }} rounded-pill">
              <i class="{{ $dStatus['icon'] }} me-1"></i>{{ $dStatus['label'] }}
            </span>
            <span class="badge {{ $iStatus['class'] }} rounded-pill">
              <i class="{{ $iStatus['icon'] }} me-1"></i>{{ $iStatus['label'] }}
            </span>
          </div>
        </div>
        <div class="card-body">
          <div class="row g-4">
            <div class="col-12 col-sm-6">
              <div class="d-flex align-items-start gap-3">
                <div class="avatar avatar-md flex-shrink-0">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class="ri-truck-line fs-5"></i>
                  </span>
                </div>
                <div>
                  <p class="mb-1 text-muted small">Jumlah Terkirim</p>
                  <h4 class="mb-0 text-primary fw-bold">
                    {{ number_format($delivery->qty_delivered) }}
                    <small class="text-muted fs-6 fw-normal">Unit</small>
                  </h4>
                  <small class="text-muted">
                    Barang: <span class="fw-medium">{{ $delivery->po->nama_barang }}</span>
                  </small>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6">
              <div class="d-flex align-items-start gap-3">
                <div class="avatar avatar-md flex-shrink-0">
                  <span class="avatar-initial rounded bg-label-info">
                    <i class="ri-calendar-event-line fs-5"></i>
                  </span>
                </div>
                <div>
                  <p class="mb-2 text-muted small">Jadwal Pengiriman</p>
                  <div class="d-flex flex-column gap-1">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-label-secondary rounded-pill small">Est.</span>
                      <span class="small fw-medium">
                        {{ $delivery->delivery_time_estimation
                                                    ? \Carbon\Carbon::parse($delivery->delivery_time_estimation)->format('d M Y')
                                                    : 'N/A' }}
                      </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-label-success rounded-pill small">Aktual</span>
                      <span class="small fw-medium text-success">
                        {{ $delivery->delivered_at
                                                    ? \Carbon\Carbon::parse($delivery->delivered_at)->format('d M Y')
                                                    : 'Belum tiba' }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- PO Context --}}
      <div class="card mb-4">
        <div class="card-header border-bottom">
          <h5 class="card-title mb-0">
            <i class="ri-file-list-3-line me-2 text-info"></i>Konteks Pesanan Induk
          </h5>
        </div>
        <div class="card-body">
          <div class="row g-4 align-items-center">
            <div class="col-12 col-sm-6">
              <p class="text-uppercase text-muted small fw-bold mb-2">Sumber PO</p>
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="ri-file-list-3-line text-primary fs-5"></i>
                <a href="{{ route('po.show', $delivery->po_id) }}" class="fw-bold text-heading fs-6">
                  {{ $delivery->po->no_po }}
                </a>
              </div>
              <div class="row g-2 mt-1">
                <div class="col-6">
                  <p class="mb-0 text-muted small">Nama Barang</p>
                  <p class="mb-0 fw-semibold small">{{ $delivery->po->nama_barang }}</p>
                </div>
                <div class="col-6">
                  <p class="mb-0 text-muted small">Tanggal PO</p>
                  <p class="mb-0 fw-semibold small">
                    {{ \Carbon\Carbon::parse($delivery->po->tgl_po)->format('d M Y') }}
                  </p>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6">
              <p class="text-uppercase text-muted small fw-bold mb-2">Porsi Pemenuhan</p>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small text-muted">Pengiriman ini</span>
                <span class="small fw-bold {{ $percentage >= 100 ? 'text-success' : 'text-info' }}">
                  {{ number_format($percentage, 1) }}% dari PO
                </span>
              </div>
              <div class="progress rounded-pill mb-2" style="height:8px;">
                <div class="progress-bar {{ $pColor }} progress-bar-striped"
                  role="progressbar"
                  style="width:{{ $percentage }}%"
                  aria-valuenow="{{ $percentage }}"
                  aria-valuemin="0"
                  aria-valuemax="100">
                </div>
              </div>
              <small class="text-muted">
                {{ number_format($thisQty) }} dari {{ number_format($totalPoQty) }} unit dipesan
              </small>
            </div>
          </div>
        </div>
      </div>

      {{-- Invoice List --}}
      <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
          <h5 class="card-title mb-0">
            <i class="ri-bill-line me-2 text-warning"></i>Invoice Terkait
          </h5>
          @if($delivery->invoiced_status == 0)
          <a href="{{ route('invoice.create', ['delivery_id' => $delivery->delivery_id]) }}"
            class="btn btn-sm btn-outline-primary">
            <i class="ri-add-line me-1"></i> Buat Invoice
          </a>
          @endif
        </div>

        @if($invoices->isEmpty())
        <div class="card-body">
          <div class="text-center py-4">
            <div class="avatar avatar-lg bg-label-warning mx-auto mb-3">
              <span class="avatar-initial rounded-circle">
                <i class="ri-file-warning-line fs-4"></i>
              </span>
            </div>
            <h6 class="text-muted mb-1">Belum Ada Invoice</h6>
            <p class="text-muted small mb-3">Pengiriman ini belum ditagihkan.</p>
            @if($delivery->invoiced_status == 0)
            <a href="{{ route('invoice.create', ['delivery_id' => $delivery->delivery_id]) }}"
              class="btn btn-sm btn-primary">
              <i class="ri-add-line me-1"></i> Buat Invoice Sekarang
            </a>
            @endif
          </div>
        </div>
        @else
        <div class="list-group list-group-flush rounded-bottom">
          @foreach($invoices as $inv)
          <a href="{{ route('invoice.show', $inv->invoice_id) }}"
            class="list-group-item list-group-item-action px-4 py-3">
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
              <div class="d-flex align-items-center gap-3">
                <div class="avatar avatar-sm flex-shrink-0">
                  <span class="avatar-initial rounded-circle bg-label-success">
                    <i class="ri-money-dollar-circle-line"></i>
                  </span>
                </div>
                <div>
                  <p class="mb-0 fw-semibold text-heading">{{ $inv->nomor_invoice }}</p>
                  <small class="text-muted">
                    Jatuh Tempo:
                    {{ $inv->due_date
                                            ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y')
                                            : '-' }}
                  </small>
                </div>
              </div>
              <div class="flex-shrink-0">
                @if($inv->status_invoice == 1)
                <span class="badge bg-success rounded-pill">Lunas</span>
                @elseif($inv->status_invoice == 2)
                <span class="badge bg-dark rounded-pill">Dibatalkan</span>
                @else
                <span class="badge bg-danger rounded-pill">Belum Lunas</span>
                @endif
              </div>
            </div>
          </a>
          @endforeach
        </div>
        @endif
      </div>

    </div>

    {{-- Right Column ─────────────────────────────────────── --}}
    <div class="col-12 col-lg-4">

      {{-- Customer Card --}}
      <div class="card mb-4">
        <div class="card-header border-bottom">
          <h5 class="card-title mb-0">
            <i class="ri-user-line me-2 text-info"></i>Penerima / Pelanggan
          </h5>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-4">
            <div class="avatar avatar-lg flex-shrink-0">
              <span class="avatar-initial rounded-circle bg-label-primary fs-4">
                {{ strtoupper(substr($delivery->po->customer->name ?? 'C', 0, 1)) }}
              </span>
            </div>
            <div class="overflow-hidden">
              <h6 class="mb-0 fw-bold text-truncate">
                {{ $delivery->po->customer->name ?? 'Pelanggan Tidak Diketahui' }}
              </h6>
              <small class="text-muted">ID: {{ $delivery->po->customer_id }}</small>
            </div>
          </div>

          <ul class="list-group list-group-flush">
            <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
              <span class="text-muted small d-flex align-items-center gap-1">
                <i class="ri-contacts-line"></i> Kontak
              </span>
              <span class="fw-medium small text-end">
                {{ $delivery->po->customer->contact_person ?? '-' }}
              </span>
            </li>
            <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
              <span class="text-muted small d-flex align-items-center gap-1">
                <i class="ri-phone-line"></i> Telepon
              </span>
              <span class="fw-medium small text-end">
                {{ $delivery->po->customer->phone ?? '-' }}
              </span>
            </li>
            <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
              <span class="text-muted small d-flex align-items-center gap-1">
                <i class="ri-mail-line"></i> Email
              </span>
              <span class="fw-medium small text-end text-truncate" style="max-width:60%">
                {{ $delivery->po->customer->email ?? '-' }}
              </span>
            </li>
          </ul>
        </div>
      </div>

      {{-- System Log --}}
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">
            <i class="ri-information-line me-2 text-secondary"></i>Log Sistem
          </h5>
        </div>
        <div class="card-body p-0">
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
              <span class="text-muted small d-flex align-items-center gap-2">
                <i class="ri-user-add-line text-info"></i> Dibuat Oleh
              </span>
              <span class="fw-semibold small">Pengguna #{{ $delivery->input_by }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
              <span class="text-muted small d-flex align-items-center gap-2">
                <i class="ri-calendar-line text-secondary"></i> Dibuat Pada
              </span>
              <span class="fw-semibold small">
                {{ \Carbon\Carbon::parse($delivery->input_date)->format('d M Y, H:i') }}
              </span>
            </li>
            @if($delivery->edit_by)
            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
              <span class="text-muted small d-flex align-items-center gap-2">
                <i class="ri-edit-box-line text-warning"></i> Diperbarui
              </span>
              <div class="text-end">
                <p class="mb-0 fw-semibold small">
                  {{ \Carbon\Carbon::parse($delivery->edit_date)->format('d M Y, H:i') }}
                </p>
                <small class="text-muted">oleh Pengguna #{{ $delivery->edit_by }}</small>
              </div>
            </li>
            @endif
          </ul>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection