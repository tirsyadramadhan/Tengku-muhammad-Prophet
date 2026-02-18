@extends('layouts/contentNavbarLayout')

@section('title', 'Purchase Order Details - #' . $po->no_po)

@section('content')

<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <div>
      <h4 class="fw-bold mb-0">
        <span class="text-muted fw-light">Purchase Orders /</span> {{ $po->no_po }}
      </h4>
      <small class="text-body-secondary">View detailed status and fulfillment history</small>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('po.edit', $po->po_id) }}" class="btn btn-outline-primary">
        <i class="ri-pencil-line me-1"></i> Edit PO
      </a>
      <button class="btn btn-primary">
        <i class="ri-printer-line me-1"></i> Print
      </button>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-xl-8 col-lg-7 col-md-12">

    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom">
        <h5 class="mb-0 card-title">Order Information</h5>

        @php
        $statusLabels = [
        0 => ['label' => 'Incoming', 'class' => 'bg-label-primary'],
        1 => ['label' => 'Open', 'class' => 'bg-label-info'],
        2 => ['label' => 'Partially Delivered', 'class' => 'bg-label-warning'],
        3 => ['label' => 'Fully Delivered', 'class' => 'bg-label-success'],
        4 => ['label' => 'Partially Delivered & Invoiced', 'class' => 'bg-label-warning'],
        5 => ['label' => 'Fully Delivered & Partially Invoiced', 'class' => 'bg-label-info'],
        6 => ['label' => 'Partially Delivered & Fully Invoiced', 'class' => 'bg-label-info'],
        7 => ['label' => 'Closed', 'class' => 'bg-label-secondary'],
        ];
        $currentStatus = $statusLabels[$po->status] ?? ['label' => 'Unknown', 'class' => 'bg-label-dark'];
        @endphp
        <span class="badge {{ $currentStatus['class'] }} rounded-pill">{{ $currentStatus['label'] }}</span>
      </div>

      <div class="card-body pt-4">
        <div class="row g-4">
          <div class="col-md-6 col-12">
            <div class="d-flex align-items-start">
              <div class="avatar avatar-md me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="ri-shopping-bag-3-line fs-4"></i>
                </span>
              </div>
              <div>
                <h6 class="mb-1 text-heading">{{ $po->nama_barang }}</h6>
                <p class="mb-0 text-body-secondary">Total Quantity: <strong>{{ number_format($po->qty) }} Units</strong></p>
                <small class="text-muted">Price per unit: {{ number_format($po->harga, 2) }}</small>
              </div>
            </div>
          </div>

          <div class="col-md-6 col-12">
            <div class="d-flex align-items-start">
              <div class="avatar avatar-md me-3">
                <span class="avatar-initial rounded bg-label-success">
                  <i class="ri-money-dollar-circle-line fs-4"></i>
                </span>
              </div>
              <div>
                <h6 class="mb-1 text-heading">Total Value</h6>
                <h5 class="mb-0 text-success">{{ number_format($po->total, 2) }}</h5>
                <small class="text-muted">Estimated Margin: {{ number_format($po->margin, 2) }}</small>
              </div>
            </div>
          </div>
        </div>

        <hr class="my-4">

        @php
        $totalDelivered = $po->deliveries->sum('qty_delivered');
        $percent = $po->qty > 0 ? ($totalDelivered / $po->qty) * 100 : 0;
        $progressColor = $percent >= 100 ? 'bg-success' : 'bg-primary';
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="fw-medium text-heading">Fulfillment Progress</span>
          <span class="fw-medium {{ $percent >= 100 ? 'text-success' : 'text-primary' }}">
            {{ number_format($percent) }}% ({{ number_format($totalDelivered) }}/{{ number_format($po->qty) }})
          </span>
        </div>
        <div class="progress" style="height: 10px;">
          <div class="progress-bar {{ $progressColor }} progress-bar-striped progress-bar-animated"
            role="progressbar"
            style="width: {{ $percent }}%"
            aria-valuenow="{{ $percent }}"
            aria-valuemin="0"
            aria-valuemax="100">
          </div>
        </div>
      </div>
    </div>

    <h5 class="mb-3">Fulfillment History</h5>

    @if($po->deliveries->isEmpty())
    <div class="card">
      <div class="card-body text-center py-5">
        <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3">
          <span class="avatar-initial rounded-circle">
            <i class="ri-truck-line fs-3"></i>
          </span>
        </div>
        <h6 class="text-muted mb-3">No deliveries recorded yet.</h6>
        <a href="{{ route('delivery.create') }}" class="btn btn-primary">
          <i class="ri-add-line me-1"></i> Create First Delivery
        </a>
      </div>
    </div>
    @else
    <div class="accordion" id="deliveryAccordion">
      @foreach($po->deliveries as $index => $delivery)
      <div class="card accordion-item {{ $index > 0 ? 'mt-3' : '' }} border active">
        <h2 class="accordion-header" id="heading{{ $delivery->delivery_id }}">
          <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $delivery->delivery_id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $delivery->delivery_id }}">
            <div class="d-flex align-items-center w-100 justify-content-between pe-3">
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-3">
                  <span class="avatar-initial rounded-circle bg-label-primary">
                    <i class="ri-truck-fill"></i>
                  </span>
                </div>
                <div class="d-flex flex-column text-start">
                  <span class="fw-bold text-heading">{{ $delivery->delivery_no }}</span>
                  <small class="text-muted">{{ $delivery->delivered_at ? \Carbon\Carbon::parse($delivery->delivered_at)->format('d M Y') : 'Pending' }}</small>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-label-secondary">Qty: {{ number_format($delivery->qty_delivered) }}</span>
                @if($delivery->delivered_status == 1)
                <span class="badge bg-success rounded-pill">Arrived</span>
                @else
                <span class="badge bg-warning rounded-pill">In Transit</span>
                @endif
              </div>
            </div>
          </button>
        </h2>
        <div id="collapse{{ $delivery->delivery_id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $delivery->delivery_id }}" data-bs-parent="#deliveryAccordion">
          <div class="accordion-body border-top">

            <div class="row mb-4">
              <div class="col-12">
                <div class="alert alert-primary d-flex align-items-center" role="alert">
                  <i class="ri-information-line me-2"></i>
                  <div>
                    <strong>Logistics Note:</strong> Est. Arrival: {{ $delivery->delivery_time_estimation ?? 'N/A' }}.
                    <span class="text-xs ms-1 opacity-75">(Entry by User #{{ $delivery->input_by }})</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="mb-0 text-muted text-uppercase fs-xs fw-bold">
                <i class="ri-bill-line me-1"></i> Associated Invoices
              </h6>
              <a href="{{ route('invoice.create', ['delivery_id' => $delivery->delivery_id]) }}" class="btn btn-xs btn-outline-primary">
                <i class="ri-add-line"></i> Generate Invoice
              </a>
            </div>

            @php
            $invoices = \App\Models\Invoice::where('delivery_id', $delivery->delivery_id)->get();
            @endphp

            @if($invoices->isEmpty())
            <div class="text-center p-3 bg-label-secondary rounded border border-dashed">
              <small class="text-muted">No invoices generated for this delivery yet.</small>
            </div>
            @else
            <div class="list-group">
              @foreach($invoices as $inv)
              <a href="{{ route('invoice.show', $inv->invoice_id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <i class="ri-file-text-line fs-3 me-3 text-secondary"></i>
                  <div>
                    <h6 class="mb-0">{{ $inv->nomor_invoice }}</h6>
                    <small class="text-muted">Due: {{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : 'N/A' }}</small>
                  </div>
                </div>
                <div class="text-end">
                  @if($inv->status_invoice == 1)
                  <span class="badge bg-success rounded-pill mb-1">Paid</span>
                  @elseif($inv->status_invoice == 2)
                  <span class="badge bg-dark rounded-pill mb-1">Void</span>
                  @else
                  <span class="badge bg-danger rounded-pill mb-1">Unpaid</span>
                  @endif
                  <div class="small text-muted">{{ number_format($inv->total_amount ?? 0, 2) }}</div>
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

  <div class="col-xl-4 col-lg-5 col-md-12">

    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Customer Details</h5>
      </div>
      <div class="card-body pt-4">
        <div class="d-flex justify-content-start align-items-center mb-4">
          <div class="avatar avatar-lg me-3">
            <span class="avatar-initial rounded-circle bg-label-info fs-3">
              {{ substr($po->customer->name ?? 'C', 0, 1) }}
            </span>
          </div>
          <div class="d-flex flex-column">
            <a href="javascript:void(0)" class="text-heading text-nowrap">
              <h6 class="mb-0">{{ $po->customer->name ?? 'Unknown Customer' }}</h6>
            </a>
            <small class="text-muted">ID: {{ $po->customer_id }}</small>
          </div>
        </div>

        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
          <span class="fw-medium">Contact Person:</span>
          <span class="text-muted">{{ $po->customer->contact_person ?? '-' }}</span>
        </div>
        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
          <span class="fw-medium">Email:</span>
          <span class="text-muted">{{ $po->customer->email ?? '-' }}</span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="fw-medium">Phone:</span>
          <span class="text-muted">{{ $po->customer->phone ?? '-' }}</span>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Quick Actions</h5>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="{{ route('delivery.create') }}" class="btn btn-primary">
            <i class="ri-truck-line me-2"></i> Create Delivery
          </a>
          <button class="btn btn-label-secondary">
            <i class="ri-download-line me-2"></i> Export PDF
          </button>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">System Info</h5>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <div class="d-flex align-items-center">
              <i class="ri-calendar-check-line me-2 text-primary"></i>
              <span>Created Date</span>
            </div>
            <span class="fw-medium">{{ \Carbon\Carbon::parse($po->tgl_po)->format('d M Y') }}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <div class="d-flex align-items-center">
              <i class="ri-user-add-line me-2 text-info"></i>
              <span>Created By</span>
            </div>
            <span class="fw-medium">User #{{ $po->input_by }}</span>
          </li>
          @if($po->edit_by)
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <div class="d-flex align-items-center">
              <i class="ri-edit-box-line me-2 text-warning"></i>
              <span>Last Updated</span>
            </div>
            <div class="text-end">
              <div class="fw-medium">{{ \Carbon\Carbon::parse($po->edit_date)->format('d M Y') }}</div>
              <small class="text-muted">by User #{{ $po->edit_by }}</small>
            </div>
          </li>
          @endif
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection