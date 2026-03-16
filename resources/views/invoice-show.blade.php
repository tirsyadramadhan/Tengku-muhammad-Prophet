@extends('layouts/contentNavbarLayout')

@php
$customer = $invoice->delivery->po->customer;
$itemPrice = $invoice->delivery->po->harga;
$qtyDelivered = $invoice->delivery->qty_delivered;
$lineTotal = $itemPrice * $qtyDelivered;
$totalPaid = $invoice->payment ? $invoice->payment->amount : 0;
$remaining = $lineTotal - $totalPaid;
$poQty = $invoice->delivery->po->qty;
$percent = $poQty > 0 ? min(($qtyDelivered / $poQty) * 100, 100) : 0;

$statusMap = [
0 => ['label' => 'Belum Lunas', 'class' => 'bg-danger', 'text' => 'text-danger'],
1 => ['label' => 'Lunas', 'class' => 'bg-success', 'text' => 'text-success'],
2 => ['label' => 'Dibatalkan', 'class' => 'bg-dark', 'text' => 'text-secondary'],
];
$invStatus = $statusMap[$invoice->status_invoice] ?? $statusMap[0];
@endphp

@section('title', 'Invoice — ' . $invoice->nomor_invoice)

@section('content')
<div id="main-container-index" class="container-fluid px-3 px-md-4 py-4">

  {{-- ── Breadcrumb + Header ──────────────────────────────── --}}
  <div class="row align-items-center g-3 mb-4 no-print">
    <div class="col-12 col-sm">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item">
            <a href="{{ route('invoice.index') }}">Invoice</a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            {{ $invoice->nomor_invoice }}
          </li>
        </ol>
      </nav>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <h4 class="fw-bold mb-0">Detail Invoice</h4>
        <span class="badge {{ $invStatus['class'] }} rounded-pill">
          {{ $invStatus['label'] }}
        </span>
      </div>
      <small class="text-muted">{{ $invoice->nomor_invoice }}</small>
    </div>
    <div class="col-12 col-sm-auto d-flex flex-wrap gap-2">
      <a href="{{ route('invoice.index') }}" class="btn btn-outline-secondary">
        <i class="ri-arrow-left-line me-1"></i>
        <span class="d-none d-sm-inline">Kembali</span>
      </a>
      <a href="javascript:window.print()" class="btn btn-outline-secondary">
        <i class="ri-printer-line me-1"></i>
        <span class="d-none d-sm-inline">Cetak</span>
      </a>
      <a href="{{ route('invoice.edit', $invoice->invoice_id) }}" class="btn btn-primary">
        <i class="ri-pencil-line me-1"></i>
        <span class="d-none d-sm-inline">Edit Invoice</span>
      </a>
    </div>
  </div>

  {{-- ── Stat Pills ───────────────────────────────────────── --}}
  <div class="row g-3 mb-4 no-print">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-none bg-label-primary h-100">
        <div class="card-body p-3">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-sm flex-shrink-0">
              <span class="avatar-initial rounded bg-primary">
                <i class="ri-file-text-line"></i>
              </span>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 text-muted small text-truncate">No. Invoice</p>
              <h6 class="mb-0 fw-bold text-truncate">{{ $invoice->nomor_invoice }}</h6>
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
                <i class="ri-calendar-line"></i>
              </span>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 text-muted small text-truncate">Jatuh Tempo</p>
              <h6 class="mb-0 fw-bold small">
                {{ $invoice->due_date
                                    ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y')
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
                <i class="ri-money-dollar-circle-line"></i>
              </span>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 text-muted small text-truncate">Total Tagihan</p>
              <h6 class="mb-0 fw-bold text-truncate">
                Rp {{ number_format($lineTotal, 0, ',', '.') }}
              </h6>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-none bg-label-danger h-100">
        <div class="card-body p-3">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-sm flex-shrink-0">
              <span class="avatar-initial rounded bg-danger">
                <i class="ri-error-warning-line"></i>
              </span>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 text-muted small text-truncate">Sisa Tagihan</p>
              <h6 class="mb-0 fw-bold text-truncate">
                Rp {{ number_format($remaining, 0, ',', '.') }}
              </h6>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Main Layout ──────────────────────────────────────── --}}
  <div class="row g-4">

    {{-- ── Left: Invoice Card ──────────────────────────── --}}
    <div class="col-12 col-lg-9">
      <div class="card mb-4">
        <div class="card-body p-4 p-sm-5">

          {{-- Company + Invoice Number --}}
          <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6">
              <div class="d-flex align-items-center gap-2 mb-3">
                <span class="app-brand-logo">
                  <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="#7367F0" />
                    <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z" fill="#161616" />
                    <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z" fill="#161616" />
                  </svg>
                </span>
                <span class="fw-bold fs-5">Nama Perusahaan</span>
              </div>
              <p class="mb-1 text-muted small">Jl. Bisnis Raya No. 123, Suite 100</p>
              <p class="mb-1 text-muted small">Jakarta, Indonesia 10110</p>
              <p class="mb-0 text-muted small">+62 (123) 456 7891</p>
            </div>
            <div class="col-12 col-sm-6 text-sm-end">
              <h4 class="fw-bold text-heading mb-3">
                INVOICE #{{ $invoice->nomor_invoice }}
              </h4>
              <div class="d-flex flex-column gap-1">
                <div class="d-flex justify-content-sm-end gap-2">
                  <span class="text-muted small">Tanggal Terbit:</span>
                  <span class="fw-medium small">
                    {{ $invoice->tgl_invoice
                                            ? \Carbon\Carbon::parse($invoice->tgl_invoice)->format('d M Y')
                                            : 'N/A' }}
                  </span>
                </div>
                <div class="d-flex justify-content-sm-end gap-2">
                  <span class="text-muted small">Jatuh Tempo:</span>
                  <span class="fw-medium small">
                    {{ $invoice->due_date
                                            ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y')
                                            : 'N/A' }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <hr class="my-4">

          {{-- Bill To + Reference --}}
          <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6">
              <p class="text-uppercase text-muted small fw-bold mb-2">Tagihan Kepada</p>
              <h6 class="fw-bold text-heading mb-1">
                {{ $customer->name ?? 'Pelanggan Tidak Diketahui' }}
              </h6>
              <p class="mb-1 text-muted small">{{ $customer->address ?? 'Alamat tidak tersedia' }}</p>
              <p class="mb-1 text-muted small">{{ $customer->phone ?? '' }}</p>
              <p class="mb-0 text-muted small">{{ $customer->email ?? '' }}</p>
            </div>
            <div class="col-12 col-sm-6">
              <p class="text-uppercase text-muted small fw-bold mb-2">Detail Referensi</p>
              <ul class="list-group list-group-flush">
                <li class="list-group-item px-0 py-1 d-flex justify-content-between gap-3">
                  <span class="text-muted small">Nomor PO</span>
                  <a href="{{ route('po.show', $invoice->delivery->po_id) }}"
                    class="fw-medium small text-decoration-underline text-body">
                    {{ $invoice->delivery->po->no_po }}
                  </a>
                </li>
                <li class="list-group-item px-0 py-1 d-flex justify-content-between gap-3">
                  <span class="text-muted small">Surat Jalan</span>
                  <a href="{{ route('delivery.show', $invoice->delivery->delivery_id) }}"
                    class="fw-medium small text-decoration-underline text-body">
                    {{ $invoice->delivery->delivery_no }}
                  </a>
                </li>
                <li class="list-group-item px-0 py-1 d-flex justify-content-between gap-3">
                  <span class="text-muted small">Tgl. Pengiriman</span>
                  <span class="fw-medium small">
                    {{ $invoice->delivery->delivered_at
                                            ? \Carbon\Carbon::parse($invoice->delivery->delivered_at)->format('d M Y')
                                            : 'Menunggu' }}
                  </span>
                </li>
              </ul>
            </div>
          </div>

          {{-- Line Items Table --}}
          <div class="table-responsive rounded border mb-4">
            <table class="table table-striped mb-0">
              <thead class="table-light">
                <tr>
                  <th>Deskripsi Barang</th>
                  <th class="text-end text-nowrap">Harga PO</th>
                  <th class="text-end text-nowrap">Qty Terkirim</th>
                  <th class="text-end text-nowrap">Total</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <p class="mb-0 fw-semibold text-heading">
                      {{ $invoice->delivery->po->nama_barang }}
                    </p>
                    <small class="text-muted">
                      Ref PO: {{ $invoice->delivery->po->no_po }}
                    </small>
                  </td>
                  <td class="text-end text-nowrap">
                    Rp {{ number_format($itemPrice, 0, ',', '.') }}
                  </td>
                  <td class="text-end">{{ number_format($qtyDelivered) }}</td>
                  <td class="text-end text-nowrap fw-semibold">
                    Rp {{ number_format($lineTotal, 0, ',', '.') }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          {{-- Totals Row --}}
          <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6">
              <p class="mb-1">
                <span class="fw-semibold text-heading me-2">Tenaga Penjual:</span>
                <span class="text-muted">
                  {{ $invoice->delivery->po->input_user->name ?? 'Admin' }}
                </span>
              </p>
              <p class="mb-0 text-muted small">Terima kasih atas kepercayaan Anda.</p>
            </div>
            <div class="col-12 col-sm-6">
              <ul class="list-group list-group-flush">
                <li class="list-group-item px-0 d-flex justify-content-between py-1">
                  <span class="text-muted">Subtotal</span>
                  <span class="fw-medium">
                    Rp {{ number_format($lineTotal, 0, ',', '.') }}
                  </span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between py-1">
                  <span class="text-muted">Pajak</span>
                  <span class="fw-medium">Rp 0</span>
                </li>
                <li class="list-group-item px-0 d-flex justify-content-between py-1 border-top">
                  <span class="fw-bold text-heading fs-6">Total</span>
                  <span class="fw-bold text-primary fs-6">
                    Rp {{ number_format($lineTotal, 0, ',', '.') }}
                  </span>
                </li>
              </ul>
            </div>
          </div>

          <hr class="my-4">

          {{-- Payment History --}}
          <div>
            <h6 class="fw-bold text-heading mb-3">
              <i class="ri-money-dollar-circle-line me-2 text-success"></i>Riwayat Pembayaran
            </h6>

            @if(!$invoice->payment)
            <div class="text-center py-4 bg-light rounded border mb-3">
              <i class="ri-file-warning-line fs-3 text-muted d-block mb-2"></i>
              <p class="text-muted small mb-0">
                Belum ada pembayaran tercatat untuk invoice ini.
              </p>
            </div>
            @else
            <div class="table-responsive rounded border mb-3">
              <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>ID Referensi</th>
                    <th class="text-nowrap">Tanggal</th>
                    <th>Metode</th>
                    <th class="text-end">Jumlah</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <span class="badge bg-label-primary rounded-pill">
                        #PAY-{{ $invoice->payment->payment_id }}
                      </span>
                    </td>
                    <td class="text-nowrap">
                      {{ \Carbon\Carbon::parse($invoice->payment->payment_date)->format('d M Y') }}
                    </td>
                    <td>{{ $invoice->payment->metode_bayar }}</td>
                    <td class="text-end text-success fw-semibold text-nowrap">
                      Rp {{ number_format($invoice->payment->amount, 0, ',', '.') }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            @endif

            <div class="row g-3 justify-content-end">
              <div class="col-12 col-sm-auto">
                <div class="card border-0 bg-label-success h-100">
                  <div class="card-body p-3 text-center">
                    <p class="mb-1 text-muted small">Total Dibayar</p>
                    <h6 class="mb-0 fw-bold text-success">
                      Rp {{ number_format($totalPaid, 0, ',', '.') }}
                    </h6>
                  </div>
                </div>
              </div>
              <div class="col-12 col-sm-auto">
                <div class="card border-0 bg-label-danger h-100">
                  <div class="card-body p-3 text-center">
                    <p class="mb-1 text-muted small">Sisa Tagihan</p>
                    <h6 class="mb-0 fw-bold text-danger">
                      Rp {{ number_format($remaining, 0, ',', '.') }}
                    </h6>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    {{-- ── Right: Actions + Status + Log ──────────────── --}}
    <div class="col-12 col-lg-3 no-print">

      {{-- Actions --}}
      <div class="card mb-4">
        <div class="card-header border-bottom">
          <h6 class="card-title mb-0">
            <i class="ri-flashlight-line me-2 text-warning"></i>Aksi
          </h6>
        </div>
        <div class="card-body d-grid gap-2">
          <button class="btn btn-primary"
            data-bs-toggle="offcanvas"
            data-bs-target="#sendInvoiceOffcanvas">
            <i class="ri-send-plane-fill me-2"></i> Kirim Invoice
          </button>
          <button class="btn btn-outline-secondary">
            <i class="ri-download-line me-2"></i> Unduh PDF
          </button>
          <a href="{{ route('payment.create', ['invoice_id' => $invoice->invoice_id]) }}"
            class="btn btn-outline-success">
            <i class="ri-money-dollar-circle-line me-2"></i> Tambah Pembayaran
          </a>
        </div>
      </div>

      {{-- Status --}}
      <div class="card mb-4">
        <div class="card-header border-bottom">
          <h6 class="card-title mb-0">
            <i class="ri-shield-check-line me-2 text-info"></i>Status
          </h6>
        </div>
        <div class="card-body">
          <p class="text-muted small text-uppercase fw-bold mb-2">Status Invoice</p>
          <span class="badge {{ $invStatus['class'] }} d-block py-2 mb-4 rounded-pill fs-6">
            {{ $invStatus['label'] }}
          </span>

          <p class="text-muted small text-uppercase fw-bold mb-2">Pemenuhan</p>
          <div class="d-flex justify-content-between mb-1">
            <small class="text-muted">{{ number_format($qtyDelivered) }} / {{ number_format($poQty) }} unit</small>
            <small class="fw-semibold {{ $percent >= 100 ? 'text-success' : 'text-info' }}">
              {{ round($percent) }}%
            </small>
          </div>
          <div class="progress rounded-pill" style="height:6px;">
            <div class="progress-bar {{ $percent >= 100 ? 'bg-success' : 'bg-info' }}"
              role="progressbar"
              style="width:{{ $percent }}%"
              aria-valuenow="{{ $percent }}"
              aria-valuemin="0"
              aria-valuemax="100">
            </div>
          </div>
        </div>
      </div>

      {{-- System Log --}}
      <div class="card">
        <div class="card-header border-bottom">
          <h6 class="card-title mb-0">
            <i class="ri-information-line me-2 text-secondary"></i>Log Sistem
          </h6>
        </div>
        <div class="card-body p-0">
          <ul class="list-group list-group-flush">
            <li class="list-group-item px-4 py-3">
              <p class="mb-0 text-muted small">Dibuat Oleh</p>
              <p class="mb-0 fw-semibold small">User #{{ $invoice->input_by }}</p>
            </li>
            <li class="list-group-item px-4 py-3">
              <p class="mb-0 text-muted small">Dibuat Pada</p>
              <p class="mb-0 fw-semibold small">
                {{ \Carbon\Carbon::parse($invoice->input_date)->format('d M Y, H:i') }}
              </p>
            </li>
            @if($invoice->edit_date)
            <li class="list-group-item px-4 py-3">
              <p class="mb-0 text-muted small">Terakhir Diperbarui</p>
              <p class="mb-0 fw-semibold small">
                {{ \Carbon\Carbon::parse($invoice->edit_date)->format('d M Y, H:i') }}
              </p>
              @if($invoice->edit_by)
              <small class="text-muted">oleh User #{{ $invoice->edit_by }}</small>
              @endif
            </li>
            @endif
          </ul>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection