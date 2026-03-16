@extends('layouts/contentNavbarLayout')

@php
$pageTitle = $po->status === 0 ? 'Incoming Purchase Order Details' : 'Purchase Order Details';

$statusMap = [
0 => ['label' => 'Incoming', 'class' => 'bg-label-primary'],
1 => ['label' => 'Open', 'class' => 'bg-label-info'],
2 => ['label' => 'Partially Delivered', 'class' => 'bg-label-warning'],
3 => ['label' => 'Fully Delivered', 'class' => 'bg-label-success'],
4 => ['label' => 'Partially Delivered & Partially Invoiced', 'class' => 'bg-label-warning'],
5 => ['label' => 'Fully Delivered & Partially Invoiced', 'class' => 'bg-label-info'],
6 => ['label' => 'Partially Delivered & Fully Invoiced', 'class' => 'bg-label-info'],
7 => ['label' => 'Fully Delivered & Fully Invoiced', 'class' => 'bg-label-success'],
8 => ['label' => 'Closed', 'class' => 'bg-label-secondary'],
];

$currentStatus = $statusMap[$po->status] ?? ['label' => 'Unknown', 'class' => 'bg-label-dark'];
$totalDelivered = $po->deliveries->sum('qty_delivered');
$percent = $po->qty > 0 ? min(($totalDelivered / $po->qty) * 100, 100) : 0;
$progressColor = $percent >= 100 ? 'bg-success' : 'bg-primary';
$percentText = $percent >= 100 ? 'text-success' : 'text-primary';
@endphp

@section('title', $pageTitle . ' — #' . $po->no_po)

@section('content')
<div id="main-container-index" class="container-fluid px-3 px-md-4 py-4">

  {{-- ── Breadcrumb + Header ───────────────────────────────────── --}}
  <div class="row align-items-center g-3 mb-4">
    <div class="col-12 col-sm">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item">
            <a href="{{ route('po.index') }}">Purchase Order</a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">{{ $po->no_po }}</li>
        </ol>
      </nav>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <h4 class="fw-bold mb-0">{{ $pageTitle }}</h4>
        <span class="badge {{ $currentStatus['class'] }} rounded-pill fs-6">
          {{ $currentStatus['label'] }}
        </span>
      </div>
      <small class="text-muted">{{ $po->no_po }} &bull; {{ \Carbon\Carbon::parse($po->tgl_po)->format('d M Y') }}</small>
    </div>
    <div class="col-12 col-sm-auto d-flex flex-wrap gap-2">
      <a href="{{ route('po.edit', $po->po_id) }}" class="btn btn-outline-primary">
        <i class="ri-pencil-line me-1"></i>
        <span class="d-none d-sm-inline">Edit PO</span>
      </a>
      <button class="btn btn-primary">
        <i class="ri-printer-line me-1"></i>
        <span class="d-none d-sm-inline">Cetak</span>
      </button>
    </div>
  </div>

  {{-- ── Stat Pills Row ─────────────────────────────────────────── --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-none bg-label-primary h-100">
        <div class="card-body p-3">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-sm flex-shrink-0">
              <span class="avatar-initial rounded bg-primary">
                <i class="ri-scales-line"></i>
              </span>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 text-muted small text-truncate">Total Qty</p>
              <h6 class="mb-0 fw-bold">{{ number_format($po->qty) }} Unit</h6>
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
                <i class="ri-money-dollar-circle-line"></i>
              </span>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 text-muted small text-truncate">Total Nilai</p>
              <h6 class="mb-0 fw-bold text-truncate">Rp {{ number_format($po->total, 0, ',', '.') }}</h6>
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
                <i class="ri-percent-line"></i>
              </span>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 text-muted small text-truncate">Margin</p>
              <h6 class="mb-0 fw-bold text-truncate">Rp {{ number_format($po->margin, 0, ',', '.') }}</h6>
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
                <i class="ri-truck-line"></i>
              </span>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 text-muted small text-truncate">Terkirim</p>
              <h6 class="mb-0 fw-bold">{{ number_format($totalDelivered) }} / {{ number_format($po->qty) }}</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Main Layout ─────────────────────────────────────────────── --}}
  <div class="row g-4">

    {{-- Left Column ──────────────────────────────────────────── --}}
    <div class="col-12 col-lg-8">

      {{-- Order Info Card --}}
      <div class="card mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
          <h5 class="card-title mb-0">
            <i class="ri-file-list-3-line me-2 text-primary"></i>Informasi Pesanan
          </h5>
          <span class="badge {{ $currentStatus['class'] }} rounded-pill">{{ $currentStatus['label'] }}</span>
        </div>
        <div class="card-body">
          <div class="row g-4">
            <div class="col-12 col-sm-6">
              <div class="d-flex align-items-start gap-3">
                <div class="avatar avatar-md flex-shrink-0">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class="ri-shopping-bag-3-line fs-5"></i>
                  </span>
                </div>
                <div class="overflow-hidden">
                  <p class="mb-1 text-muted small">Nama Barang</p>
                  <h6 class="mb-1 fw-bold text-heading">{{ $po->nama_barang }}</h6>
                  <p class="mb-0 small text-body-secondary">
                    <strong>{{ number_format($po->qty) }} Unit</strong>
                    &bull; Rp {{ number_format($po->harga, 0, ',', '.') }} / unit
                  </p>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6">
              <div class="row g-2">
                <div class="col-6">
                  <p class="mb-1 text-muted small">Harga / Unit</p>
                  <p class="mb-0 fw-semibold">Rp {{ number_format($po->harga, 0, ',', '.') }}</p>
                </div>
                <div class="col-6">
                  <p class="mb-1 text-muted small">Modal Awal</p>
                  <p class="mb-0 fw-semibold">Rp {{ number_format($po->modal_awal, 0, ',', '.') }}</p>
                </div>
                <div class="col-6">
                  <p class="mb-1 text-muted small">Margin / Unit</p>
                  <p class="mb-0 fw-semibold">Rp {{ number_format($po->margin_unit, 0, ',', '.') }}</p>
                </div>
                <div class="col-6">
                  <p class="mb-1 text-muted small">Tambahan Margin</p>
                  <p class="mb-0 fw-semibold">
                    {{ $po->tambahan_margin ? 'Rp ' . number_format($po->tambahan_margin, 0, ',', '.') : '-' }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <hr class="my-4">

          <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-2">
            <span class="fw-semibold text-heading">Progres Pemenuhan</span>
            <span class="fw-semibold {{ $percentText }}">
              {{ number_format($percent, 1) }}%
              <span class="text-muted fw-normal small">
                ({{ number_format($totalDelivered) }} / {{ number_format($po->qty) }} unit)
              </span>
            </span>
          </div>
          <div class="progress rounded-pill" style="height:10px;">
            <div class="progress-bar {{ $progressColor }} progress-bar-striped progress-bar-animated"
              role="progressbar"
              style="width:{{ $percent }}%"
              aria-valuenow="{{ $percent }}"
              aria-valuemin="0"
              aria-valuemax="100">
            </div>
          </div>
        </div>
      </div>

      {{-- Delivery History --}}
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="mb-0">
          <i class="ri-truck-line me-2 text-primary"></i>Riwayat Pengiriman
        </h5>
        <a href="{{ route('delivery.create') }}" class="btn btn-sm btn-primary">
          <i class="ri-add-line me-1"></i> Tambah Pengiriman
        </a>
      </div>

      @if($po->deliveries->isEmpty())
      <div class="card">
        <div class="card-body text-center py-5">
          <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3">
            <span class="avatar-initial rounded-circle">
              <i class="ri-truck-line fs-3"></i>
            </span>
          </div>
          <h6 class="text-muted mb-1">Belum ada pengiriman tercatat</h6>
          <p class="text-muted small mb-3">Buat pengiriman pertama untuk PO ini.</p>
          <a href="{{ route('delivery.create') }}" class="btn btn-primary btn-sm">
            <i class="ri-add-line me-1"></i> Buat Pengiriman Pertama
          </a>
        </div>
      </div>
      @else
      <div class="accordion accordion-flush" id="deliveryAccordion">
        @foreach($po->deliveries as $index => $delivery)
        @php
        $invoices = \App\Models\Invoice::where('delivery_id', $delivery->delivery_id)->get();
        $isFirst = $index === 0;
        @endphp
        <div class="card mb-3 border">
          <h2 class="accordion-header" id="heading{{ $delivery->delivery_id }}">
            <button
              class="accordion-button rounded {{ !$isFirst ? 'collapsed' : '' }}"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#collapse{{ $delivery->delivery_id }}"
              aria-expanded="{{ $isFirst ? 'true' : 'false' }}"
              aria-controls="collapse{{ $delivery->delivery_id }}">
              <div class="d-flex align-items-center justify-content-between w-100 pe-2 gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-3">
                  <div class="avatar avatar-sm flex-shrink-0">
                    <span class="avatar-initial rounded-circle bg-label-primary">
                      <i class="ri-truck-fill"></i>
                    </span>
                  </div>
                  <div>
                    <p class="mb-0 fw-bold text-heading">{{ $delivery->delivery_no }}</p>
                    <small class="text-muted">
                      {{ $delivery->delivered_at
                                                ? \Carbon\Carbon::parse($delivery->delivered_at)->format('d M Y')
                                                : 'Menunggu pengiriman' }}
                    </small>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                  <span class="badge bg-label-secondary">
                    {{ number_format($delivery->qty_delivered) }} unit
                  </span>
                  @if($delivery->delivered_status == 1)
                  <span class="badge bg-success rounded-pill">Tiba</span>
                  @else
                  <span class="badge bg-warning rounded-pill">Dalam Perjalanan</span>
                  @endif
                </div>
              </div>
            </button>
          </h2>

          <div id="collapse{{ $delivery->delivery_id }}"
            class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}"
            aria-labelledby="heading{{ $delivery->delivery_id }}"
            data-bs-parent="#deliveryAccordion">
            <div class="accordion-body border-top">

              <div class="alert alert-primary d-flex align-items-start gap-2 mb-4" role="alert">
                <i class="ri-information-line mt-1 flex-shrink-0"></i>
                <div>
                  <strong>Catatan Logistik:</strong>
                  Est. Tiba: {{ $delivery->delivery_time_estimation ?? 'N/A' }}.
                  <span class="opacity-75 small ms-1">
                    (Diinput oleh Pengguna #{{ $delivery->input_by }})
                  </span>
                </div>
              </div>

              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h6 class="mb-0 text-uppercase small fw-bold text-muted">
                  <i class="ri-bill-line me-1"></i> Invoice Terkait
                </h6>
                <a href="{{ route('invoice.create', ['delivery_id' => $delivery->delivery_id]) }}"
                  class="btn btn-sm btn-outline-primary">
                  <i class="ri-add-line"></i> Buat Invoice
                </a>
              </div>

              @if($invoices->isEmpty())
              <div class="text-center p-4 bg-light rounded border border-dashed">
                <i class="ri-file-text-line fs-3 text-muted d-block mb-2"></i>
                <small class="text-muted">Belum ada invoice untuk pengiriman ini.</small>
              </div>
              @else
              <div class="list-group list-group-flush rounded border">
                @foreach($invoices as $inv)
                <a href="{{ route('invoice.show', $inv->invoice_id) }}"
                  class="list-group-item list-group-item-action">
                  <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-3">
                      <i class="ri-file-text-line fs-4 text-secondary flex-shrink-0"></i>
                      <div>
                        <p class="mb-0 fw-semibold">{{ $inv->nomor_invoice }}</p>
                        <small class="text-muted">
                          Jatuh Tempo:
                          {{ $inv->due_date
                                                        ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y')
                                                        : 'N/A' }}
                        </small>
                      </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                      @if($inv->status_invoice == 1)
                      <span class="badge bg-success rounded-pill d-block mb-1">Lunas</span>
                      @elseif($inv->status_invoice == 2)
                      <span class="badge bg-dark rounded-pill d-block mb-1">Batal</span>
                      @else
                      <span class="badge bg-danger rounded-pill d-block mb-1">Belum Lunas</span>
                      @endif
                      <small class="text-muted">
                        Rp {{ number_format($inv->total_amount ?? 0, 0, ',', '.') }}
                      </small>
                    </div>
                  </div>
                </a>
                @endforeach
              </div>
              @endif

            </div>
          </div>
        </div>
        @endforeach
      </div>
      @endif
    </div>

    {{-- Right Column ──────────────────────────────────────────── --}}
    <div class="col-12 col-lg-4">

      {{-- Customer Card --}}
      <div class="card mb-4">
        <div class="card-header border-bottom">
          <h5 class="card-title mb-0">
            <i class="ri-user-line me-2 text-info"></i>Detail Pelanggan
          </h5>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-4">
            <div class="avatar avatar-lg flex-shrink-0">
              <span class="avatar-initial rounded-circle bg-label-info fs-4">
                {{ strtoupper(substr($po->customer->name ?? 'C', 0, 1)) }}
              </span>
            </div>
            <div class="overflow-hidden">
              <h6 class="mb-0 fw-bold text-truncate">
                {{ $po->customer->name ?? 'Pelanggan Tidak Diketahui' }}
              </h6>
              <small class="text-muted">ID: {{ $po->customer_id }}</small>
            </div>
          </div>

          <ul class="list-group list-group-flush">
            <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
              <span class="text-muted small d-flex align-items-center gap-1">
                <i class="ri-contacts-line"></i> Narahubung
              </span>
              <span class="fw-medium text-end small">{{ $po->customer->contact_person ?? '-' }}</span>
            </li>
            <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
              <span class="text-muted small d-flex align-items-center gap-1">
                <i class="ri-mail-line"></i> Email
              </span>
              <span class="fw-medium text-end small text-truncate" style="max-width:60%">
                {{ $po->customer->email ?? '-' }}
              </span>
            </li>
            <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
              <span class="text-muted small d-flex align-items-center gap-1">
                <i class="ri-phone-line"></i> Telepon
              </span>
              <span class="fw-medium text-end small">{{ $po->customer->phone ?? '-' }}</span>
            </li>
          </ul>
        </div>
      </div>

      {{-- Quick Actions --}}
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0">
            <i class="ri-flashlight-line me-2 text-warning"></i>Aksi Cepat
          </h5>
        </div>
        <div class="card-body d-grid gap-2">
          <a href="{{ route('delivery.create') }}" class="btn btn-primary">
            <i class="ri-truck-line me-2"></i> Buat Pengiriman
          </a>
          <a href="{{ route('po.edit', $po->po_id) }}" class="btn btn-outline-warning">
            <i class="ri-pencil-line me-2"></i> Edit PO
          </a>
          <button class="btn btn-outline-secondary">
            <i class="ri-download-line me-2"></i> Ekspor PDF
          </button>
        </div>
      </div>

      {{-- System Info --}}
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">
            <i class="ri-information-line me-2 text-secondary"></i>Info Sistem
          </h5>
        </div>
        <div class="card-body p-0">
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
              <span class="text-muted small d-flex align-items-center gap-2">
                <i class="ri-calendar-check-line text-primary"></i> Tanggal PO
              </span>
              <span class="fw-semibold small">
                {{ \Carbon\Carbon::parse($po->tgl_po)->format('d M Y') }}
              </span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
              <span class="text-muted small d-flex align-items-center gap-2">
                <i class="ri-time-line text-secondary"></i> Diinput
              </span>
              <span class="fw-semibold small">
                {{ \Carbon\Carbon::parse($po->input_date)->format('d M Y, H:i') }}
              </span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
              <span class="text-muted small d-flex align-items-center gap-2">
                <i class="ri-user-add-line text-info"></i> Dibuat Oleh
              </span>
              <span class="fw-semibold small">Pengguna #{{ $po->input_by }}</span>
            </li>
            @if($po->edit_by)
            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
              <span class="text-muted small d-flex align-items-center gap-2">
                <i class="ri-edit-box-line text-warning"></i> Diperbarui
              </span>
              <div class="text-end">
                <p class="mb-0 fw-semibold small">
                  {{ \Carbon\Carbon::parse($po->edit_date)->format('d M Y') }}
                </p>
                <small class="text-muted">oleh Pengguna #{{ $po->edit_by }}</small>
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