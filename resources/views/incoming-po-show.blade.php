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
            <i class="ri-file-list-3-line me-2"></i>Incoming Purchase Order #{{ $po->no_po }}
          </h5>
          <small class="text-muted">Created on {{ \Carbon\Carbon::parse($po->tgl_po)->format('d M Y') }}</small>
        </div>
        <div>
          @switch($po->status)
          @case(0) <span class="badge bg-label-primary rounded-pill">Incoming</span> @break
          @case(1) <span class="badge bg-label-info rounded-pill">Open</span> @break
          @endswitch

          <a href="{{ route('incoming-po.edit', $po->po_id) }}" class="btn btn-sm btn-outline-primary ms-2">
            <i class="ri-pencil-line me-1"></i> Edit
          </a>
        </div>
      </div>

      <div class="card-body">
        <div class="row g-4">
          <div class="col-md-3">
            <small class="text-light fw-semibold d-block mb-1">Customer</small>
            <div class="d-flex align-items-center">
              <div class="avatar avatar-sm me-2">
                <span class="avatar-initial rounded-circle bg-label-primary">
                  {{ substr($po->customer->name ?? 'C', 0, 1) }}
                </span>
              </div>
              <div>
                <h6 class="mb-0">{{ $po->customer->name ?? 'Unknown Customer' }}</h6>
                <small class="text-muted">ID: {{ $po->customer_id }}</small>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <small class="text-light fw-semibold d-block mb-1">Product Details</small>
            <h6 class="mb-0">{{ $po->nama_barang }}</h6>
            <small class="text-muted">{{ number_format($po->qty) }} Units @ {{ number_format($po->harga, 2) }}</small>
          </div>

          <div class="col-md-3">
            <small class="text-light fw-semibold d-block mb-1">Total Value</small>
            <h6 class="mb-0 text-success">{{ number_format($po->total, 2) }}</h6>
            <small class="text-muted">Margin: {{ number_format($po->margin, 2) }}</small>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xl-4 col-12 mt-4 mt-xl-0">
    <div class="card">
      <div class="card-body">
        <small class="text-uppercase text-muted fw-bold">System Info</small>
        <ul class="list-unstyled mt-3 mb-0">
          <li class="d-flex align-items-center mb-3">
            <i class="ri-user-line me-2 text-primary"></i>
            <span class="fw-medium mx-2">Created By:</span>
            <span>User #{{ $po->input_by }}</span>
          </li>
          <li class="d-flex align-items-center mb-3">
            <i class="ri-calendar-line me-2 text-primary"></i>
            <span class="fw-medium mx-2">Created At:</span>
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