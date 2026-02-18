@extends('layouts/contentNavbarLayout')

@section('title', 'Incoming PO Details - Transaction')

@section('page-style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
  /* Custom spacing for the timeline effect */
  .accordion-button:not(.collapsed) {
    background-color: #fcfdfe;
    color: #696cff;
  }

  .invoice-section {
    border-left: 4px solid #e7e7ff;
    background-color: #f8f9fa;
  }
</style>
@endsection

@section('content')

<div class="row">
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0 text-primary">
            <i class="ri-file-list-3-line me-2"></i>Purchase Order #{{ $po->no_po }}
          </h5>
          <small class="text-muted">Dibuat Pada {{ \Carbon\Carbon::parse($po->tgl_po)->format('d M Y') }}</small>
        </div>
        <div>
          @switch($po->status)
          @case(0) <span class="badge bg-label-primary rounded-pill">Incoming</span> @break
          @case(1) <span class="badge bg-label-info rounded-pill">Open</span> @break
          @case(2) <span class="badge bg-label-success rounded-pill">Partially Delivered</span> @break
          @case(3) <span class="badge bg-label-success rounded-pill">Fully Delivered</span> @break
          @case(4) <span class="badge bg-label-success rounded-pill">Partially Delivered & Partially Invoiced</span> @break
          @case(5) <span class="badge bg-label-success rounded-pill">Fully Delivered & Partially Invoiced</span> @break
          @case(6) <span class="badge bg-label-success rounded-pill">Partially Delivered & Fully Invoiced</span> @break
          @case(7) <span class="badge bg-label-success rounded-pill">Close</span> @break
          @endswitch

          <a href="{{ route('po.edit', $po->po_id) }}" class="btn btn-sm btn-outline-primary ms-2">
            <i class="ri-pencil-line me-1"></i> Edit
          </a>
        </div>
      </div>

      <div class="card-body">
        <div class="row g-4">
          <div class="col-md-3">
            <small class="text-light fw-semibold d-block mb-1">Pelanggan</small>
            <div class="d-flex align-items-center">
              <div class="avatar avatar-sm me-2">
                <span class="avatar-initial rounded-circle bg-label-primary">
                  {{ substr($po->customer->name ?? 'C', 0, 1) }}
                </span>
              </div>
              <div>
                <h6 class="mb-0">{{ $po->customer->name ?? 'Pelanggan Tidak Diketahui' }}</h6>
                <small class="text-muted">ID: {{ $po->customer_id }}</small>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <small class="text-light fw-semibold d-block mb-1">Detail Produk</small>
            <h6 class="mb-0">{{ $po->nama_barang }}</h6>
            <small class="text-muted">{{ number_format($po->qty) }} Units @ {{ number_format($po->harga, 2) }}</small>
          </div>

          <div class="col-md-3">
            <small class="text-light fw-semibold d-block mb-1">Total Harga</small>
            <h6 class="mb-0 text-success">{{ number_format($po->total, 2) }}</h6>
            <small class="text-muted">Margin: {{ number_format($po->margin, 2) }}</small>
          </div>

          <div class="col-md-3">
            @php
            $totalDelivered = $po->deliveries->sum('qty_delivered');
            $percent = $po->qty > 0 ? ($totalDelivered / $po->qty) * 100 : 0;
            @endphp
            <small class="text-light fw-semibold d-block mb-1">Pengantaran ({{ number_format($percent) }}%)</small>
            <div class="progress mt-1" style="height: 6px;">
              <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <small class="text-muted">{{ number_format($totalDelivered) }} / {{ number_format($po->qty) }} Diantar</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xl-8 col-12">
    <div class="card">
      <h5 class="card-header border-bottom mb-0">
        <i class="ri-truck-line me-2"></i> Pengantaran & Faktur
      </h5>

      @if($po->deliveries->isEmpty())
      <div class="card-body text-center py-5">
        <div class="mb-3">
          <span class="badge bg-label-secondary p-3 rounded-circle">
            <i class="ri-archive-line fs-3"></i>
          </span>
        </div>
        <h6 class="text-muted">Belum Ada Pengantaran.</h6>
      </div>
      @else
      <div class="accordion accordion-flush" id="deliveryAccordion">
        @foreach($po->deliveries as $index => $delivery)
        <div class="accordion-item border-bottom">
          <h2 class="accordion-header" id="headingDelivery{{ $delivery->delivery_id }}">
            <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDelivery{{ $delivery->delivery_id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapseDelivery{{ $delivery->delivery_id }}">
              <div class="d-flex justify-content-between w-100 align-items-center pe-3">
                <div class="d-flex align-items-center">
                  <i class="ri-map-pin-time-line me-3 fs-4 text-primary"></i>
                  <div>
                    <span class="fw-bold text-heading">{{ $delivery->delivery_no }}</span>
                    <div class="small text-muted">
                      {{ $delivery->delivered_at ? \Carbon\Carbon::parse($delivery->delivered_at)->format('d M Y, H:i') : 'Belum Tiba' }}
                    </div>
                  </div>
                </div>
                <div class="d-flex gap-2">
                  <span class="badge rounded-pill bg-label-primary">Qty: {{ number_format($delivery->qty_delivered) }}</span>
                  @if($delivery->delivered_status == 0)
                  <span class="badge bg-warning">Dalam Perjalanan</span>
                  @elseif($delivery->delivered_status == 1)
                  <span class="badge bg-info">Sudah Tiba</span>
                  @else
                  <span class="badge bg-success">Sudah Diantar</span>
                  @endif
                </div>
              </div>
            </button>
          </h2>
          <div id="collapseDelivery{{ $delivery->delivery_id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="headingDelivery{{ $delivery->delivery_id }}" data-bs-parent="#deliveryAccordion">
            <div class="accordion-body pt-0">

              <div class="alert alert-outline-secondary d-flex align-items-center my-3" role="alert">
                <i class="ri-information-line me-2"></i>
                <div class="d-flex flex-column ps-1">
                  <span class="fw-medium">Catatan Pengiriman:</span>
                  <span class="small">Perkiraan Waktu Tiba: {{ $delivery->delivery_time_estimation ?? 'N/A' }}.
                    Di Input Oleh: User #{{ $delivery->input_by ?? 'Sys' }}</span>
                </div>
              </div>

              <h6 class="fw-medium text-muted mt-4 mb-3 text-uppercase fs-xs">
                <i class="ri-bill-line me-1"></i> Faktur yang terhubung
              </h6>

              @php
              // Assuming relationship is defined in model as 'invoices' (plural) or 'invoice' (single)
              // Adjust based on your Model. I am assuming a generic $delivery->invoices collection based on schema
              $invoices = \App\Models\Invoice::where('delivery_id', $delivery->delivery_id)->get();
              @endphp

              @if($invoices->isEmpty())
              <div class="list-group list-group-flush border rounded-2">
                <div class="list-group-item d-flex justify-content-between align-items-center bg-lighter">
                  <span class="text-muted fst-italic">Belum ada faktur untuk pengiriman ini</span>
                  <a href="{{ route('invoice.create') }}" class="btn btn-xs btn-primary">
                    <i class="ri-add-line"></i> Buat Faktur
                  </a>
                </div>
              </div>
              @else
              <div class="list-group">
                @foreach($invoices as $inv)
                <a href="{{ route('invoice.show', $inv->invoice_id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center invoice-section rounded mb-2">
                  <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-3">
                      <span class="avatar-initial rounded bg-label-success">
                        <i class="ri-money-dollar-circle-line"></i>
                      </span>
                    </div>
                    <div>
                      <h6 class="mb-0 text-body">{{ $inv->nomor_invoice }}</h6>
                      <small class="text-muted">Tenggat Waktu: {{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : 'N/A' }}</small>
                    </div>
                  </div>
                  <div class="text-end">
                    @if($inv->status_invoice == 1)
                    <span class="badge bg-success rounded-pill">Sudah Dibayar</span>
                    @elseif($inv->status_invoice == 2)
                    <span class="badge bg-dark rounded-pill">Dibatalkan</span>
                    @else
                    <span class="badge bg-danger rounded-pill">Belum Dibayar</span>
                    @endif
                    <i class="ri-arrow-right-s-line ms-2 text-muted"></i>
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
  </div>

  <div class="col-xl-4 col-12 mt-4 mt-xl-0">
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Aksi</h5>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="{{ route('delivery.create') }}" class="btn btn-primary">
            <i class="ri-add-line"></i> Buat Pengiriman
          </a>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <small class="text-uppercase text-muted fw-bold">Info Sistem</small>
        <ul class="list-unstyled mt-3 mb-0">
          <li class="d-flex align-items-center mb-3">
            <i class="ri-user-line me-2 text-primary"></i>
            <span class="fw-medium mx-2">Dibuat Oleh:</span>
            <span>User #{{ $po->input_by }}</span>
          </li>
          <li class="d-flex align-items-center mb-3">
            <i class="ri-calendar-line me-2 text-primary"></i>
            <span class="fw-medium mx-2">Dibuat Pada:</span>
            <span>{{ $po->input_date }}</span>
          </li>
          @if($po->edit_by)
          <li class="d-flex align-items-center">
            <i class="ri-edit-line me-2 text-warning"></i>
            <span class="fw-medium mx-2">Last Edit:</span>
            <span>{{ $po->edit_date }}</span>
          </li>
          @endif
        </ul>
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script>
  // Initialize any tooltips or interactive elements here
</script>
@endsection