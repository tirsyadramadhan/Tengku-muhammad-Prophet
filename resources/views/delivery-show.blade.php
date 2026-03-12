@extends('layouts/contentNavbarLayout')

@section('title', 'Delivery Note - #' . $delivery->delivery_no)

@section('content')

<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <div>
      <h4 class="fw-bold mb-0">
        <span class="text-muted fw-light">Pengiriman /</span> {{ $delivery->delivery_no }}
      </h4>
      <small class="text-body-secondary">
        Ref: PO #<a href="{{ route('po.show', $delivery->po_id) }}" class="fw-medium">{{ $delivery->po->no_po }}</a>
      </small>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('delivery.edit', $delivery->delivery_id) }}" class="btn btn-outline-primary">
        <i class="ri-pencil-line me-1"></i> Edit
      </a>

      <button class="btn btn-secondary">
        <i class="ri-printer-line me-1"></i> Cetak Surat Jalan
      </button>

      @if($delivery->invoiced_status == 0)
      <a href="{{ route('invoice.create', ['delivery_id' => $delivery->delivery_id]) }}" class="btn btn-primary">
        <i class="ri-file-add-line me-1"></i> Buat Invoice
      </a>
      @endif
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-xl-8 col-lg-7 col-md-12">

    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom">
        <h5 class="mb-0 card-title">Informasi Pengiriman</h5>
        <div class="d-flex gap-2">
          {{-- Badge Status Pengiriman --}}
          @if($delivery->delivered_status == 0)
          <span class="badge bg-label-secondary">Menunggu</span>
          @elseif($delivery->delivered_status == 1)
          <span class="badge bg-label-info">Dikirim</span>
          @elseif($delivery->delivered_status == 2)
          <span class="badge bg-label-success">Terkirim</span>
          @endif

          {{-- Badge Status Invoice --}}
          @if($delivery->invoiced_status == 1)
          <span class="badge bg-label-primary"><i class="ri-check-double-line me-1"></i> Sudah Ditagih</span>
          @else
          <span class="badge bg-label-warning"><i class="ri-error-warning-line me-1"></i> Belum Ditagih</span>
          @endif
        </div>
      </div>

      <div class="card-body pt-4">
        <div class="row g-4">
          <div class="col-md-6 col-12">
            <div class="d-flex align-items-start">
              <div class="avatar avatar-md me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="ri-truck-line fs-4"></i>
                </span>
              </div>
              <div>
                <h6 class="mb-1 text-heading">Jumlah Terkirim</h6>
                <h4 class="mb-0 text-primary">{{ number_format($delivery->qty_delivered) }} <small class="text-muted fs-6">Unit</small></h4>
                <small class="text-muted">Barang: {{ $delivery->po->nama_barang }}</small>
              </div>
            </div>
          </div>

          <div class="col-md-6 col-12">
            <div class="d-flex align-items-start">
              <div class="avatar avatar-md me-3">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="ri-calendar-event-line fs-4"></i>
                </span>
              </div>
              <div>
                <h6 class="mb-1 text-heading">Jadwal</h6>
                <div class="d-flex flex-column">
                  <small class="mb-1"><strong>Est. Tiba:</strong> {{ $delivery->delivery_time_estimation ? \Carbon\Carbon::parse($delivery->delivery_time_estimation)->format('d M Y, H:i') : 'N/A' }}</small>
                  <small class="text-success"><strong>Tiba Aktual:</strong> {{ $delivery->delivered_at ? \Carbon\Carbon::parse($delivery->delivered_at)->format('d M Y, H:i') : 'Belum tiba' }}</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-bill-line me-2 text-muted"></i>Invoice Terkait</h5>
      </div>

      @php
      // Ambil invoice yang terkait dengan pengiriman ini
      $invoices = \App\Models\Invoice::where('delivery_id', $delivery->delivery_id)->get();
      @endphp

      @if($invoices->isEmpty())
      <div class="card-body">
        <div class="alert alert-outline-warning mb-0" role="alert">
          <span class="fw-medium">Belum Ada Invoice.</span> Pengiriman ini belum ditagihkan.
        </div>
      </div>
      @else
      <div class="list-group list-group-flush">
        @foreach($invoices as $inv)
        <a href="{{ route('invoice.show', $inv->invoice_id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-4">
          <div class="d-flex align-items-center">
            <div class="avatar avatar-sm me-3">
              <span class="avatar-initial rounded-circle bg-label-success">
                <i class="ri-money-dollar-circle-line"></i>
              </span>
            </div>
            <div>
              <h6 class="mb-0 text-heading">{{ $inv->nomor_invoice }}</h6>
              <small class="text-muted">Jatuh Tempo: {{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '-' }}</small>
            </div>
          </div>
          <div class="text-end">
            @if($inv->status_invoice == 1)
            <span class="badge bg-success rounded-pill">Lunas</span>
            @elseif($inv->status_invoice == 2)
            <span class="badge bg-dark rounded-pill">Dibatalkan</span>
            @else
            <span class="badge bg-danger rounded-pill">Belum Lunas</span>
            @endif
          </div>
        </a>
        @endforeach
      </div>
      @endif
    </div>

    <div class="card">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Konteks Pesanan Induk</h5>
      </div>
      <div class="card-body pt-4">
        <div class="row">
          <div class="col-sm-6 mb-3 mb-sm-0">
            <small class="text-uppercase text-muted fw-bold">Sumber PO</small>
            <div class="d-flex align-items-center mt-2">
              <i class="ri-file-list-3-line text-primary me-2"></i>
              <span class="fw-medium text-heading">{{ $delivery->po->no_po }}</span>
            </div>
            <div class="mt-2 text-muted small">
              Tanggal Pesan: {{ \Carbon\Carbon::parse($delivery->po->tgl_po)->format('d M Y') }}
            </div>
          </div>
          <div class="col-sm-6">
            <small class="text-uppercase text-muted fw-bold">Cek Pemenuhan</small>
            @php
            $totalPoQty = $delivery->po->qty;
            $thisDeliveryQty = $delivery->qty_delivered;
            $percentage = $totalPoQty > 0 ? ($thisDeliveryQty / $totalPoQty) * 100 : 0;
            @endphp
            <div class="mt-2">
              <div class="d-flex justify-content-between mb-1">
                <span class="small">Pengiriman ini mencakup</span>
                <span class="small fw-bold">{{ number_format($percentage, 1) }}% dari PO</span>
              </div>
              <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <small class="text-muted mt-1 d-block">{{ number_format($thisDeliveryQty) }} dari {{ number_format($totalPoQty) }} total unit yang dipesan.</small>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="col-xl-4 col-lg-5 col-md-12">

    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Penerima / Pelanggan</h5>
      </div>
      <div class="card-body pt-4">
        <div class="d-flex justify-content-start align-items-center mb-4">
          <div class="avatar avatar-lg me-3">
            <span class="avatar-initial rounded-circle bg-label-primary fs-3">
              {{ substr($delivery->po->customer->name ?? 'C', 0, 1) }}
            </span>
          </div>
          <div class="d-flex flex-column">
            <a href="javascript:void(0)" class="text-heading text-nowrap">
              <h6 class="mb-0">{{ $delivery->po->customer->name ?? 'Pelanggan Tidak Diketahui' }}</h6>
            </a>
            <small class="text-muted">ID: {{ $delivery->po->customer_id }}</small>
          </div>
        </div>

        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
          <span class="fw-medium">Kontak:</span>
          <span class="text-muted text-end">{{ $delivery->po->customer->contact_person ?? '-' }}</span>
        </div>
        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
          <span class="fw-medium">Telepon:</span>
          <span class="text-muted text-end">{{ $delivery->po->customer->phone ?? '-' }}</span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="fw-medium">Email:</span>
          <span class="text-muted text-end">{{ $delivery->po->customer->email ?? '-' }}</span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Log Sistem</h5>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <div class="d-flex align-items-center">
              <i class="ri-user-add-line me-2 text-secondary"></i>
              <span>Dibuat Oleh</span>
            </div>
            <span class="fw-medium">Pengguna #{{ $delivery->input_by }}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <div class="d-flex align-items-center">
              <i class="ri-calendar-line me-2 text-secondary"></i>
              <span>Dibuat Pada</span>
            </div>
            <span class="fw-medium">{{ \Carbon\Carbon::parse($delivery->input_date)->format('d M Y') }}</span>
          </li>
          @if($delivery->edit_by)
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <div class="d-flex align-items-center">
              <i class="ri-edit-box-line me-2 text-warning"></i>
              <span>Terakhir Diedit</span>
            </div>
            <div class="text-end">
              <div class="fw-medium">{{ \Carbon\Carbon::parse($delivery->edit_date)->format('d M Y') }}</div>
              <small class="text-muted">oleh Pengguna #{{ $delivery->edit_by }}</small>
            </div>
          </li>
          @endif
        </ul>
      </div>
    </div>

  </div>
</div>
@endsection