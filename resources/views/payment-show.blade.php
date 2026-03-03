@extends('layouts/contentNavbarLayout')

@section('title', 'Invoice #' . $invoice->nomor_invoice)

@section('page-style')
<style>
    .invoice-preview-card {
        box-shadow: 0 0.25rem 1.125rem rgba(75, 70, 92, 0.1);
    }

    @media print {

        .layout-navbar,
        .layout-menu,
        .footer,
        .btn,
        .no-print {
            display: none !important;
        }

        .content-wrapper {
            padding: 0 !important;
            margin: 0 !important;
        }

        .invoice-preview-card {
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>
@endsection

@section('content')

<div class="row invoice-preview">
    <div class="col-12 d-flex justify-content-between align-items-center mb-4 no-print">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Invoices /</span> {{ $invoice->nomor_invoice }}
        </h4>
        <div class="d-flex gap-2">
            <a href="{{ route('invoice.index') }}" class="btn btn-label-secondary">
                <i class="ri-arrow-left-line me-1"></i> Back
            </a>
            <a href="javascript:window.print()" class="btn btn-secondary">
                <i class="ri-printer-line me-1"></i> Print
            </a>
            <a href="{{ route('invoice.edit', $invoice->invoice_id) }}" class="btn btn-primary">
                <i class="ri-pencil-line me-1"></i> Edit Invoice
            </a>
        </div>
    </div>

    <div class="col-xl-9 col-md-8 col-12 mb-md-0 mb-4">
        <div class="card invoice-preview-card">
            <div class="card-body">

                {{-- ===================== HEADER ===================== --}}
                <div class="d-flex justify-content-between flex-xl-row flex-md-column flex-sm-row flex-column m-sm-3 m-0">
                    <div class="mb-xl-0 mb-4">
                        <div class="d-flex svg-illustration mb-4 gap-2 align-items-center">
                            <span class="app-brand-logo demo">
                                <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="#7367F0" />
                                    <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z" fill="#161616" />
                                    <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z" fill="#161616" />
                                </svg>
                            </span>
                            <span class="app-brand-text fw-bold fs-4"> Company Name</span>
                        </div>
                        <p class="mb-2">123 Business Blvd, Suite 100</p>
                        <p class="mb-2">Jakarta, Indonesia 10110</p>
                        <p class="mb-0">+62 (123) 456 7891</p>
                    </div>
                    <div>
                        <h4 class="fw-medium text-heading mb-2">INVOICE #{{ $invoice->nomor_invoice }}</h4>
                        <div class="mb-2 pt-1">
                            <span class="text-muted">Date Issued:</span>
                            <span class="fw-medium">{{ $invoice->tgl_invoice ? \Carbon\Carbon::parse($invoice->tgl_invoice)->format('d M Y') : 'N/A' }}</span>
                        </div>
                        <div class="pt-1">
                            <span class="text-muted">Date Due:</span>
                            <span class="fw-medium">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <hr class="my-6" />

                {{-- ===================== BILL TO / REFERENCE ===================== --}}
                <div class="d-flex justify-content-between flex-xl-row flex-md-column flex-sm-row flex-column m-sm-3 m-0">

                    <div class="mb-xl-0 mb-4">
                        <h6 class="pb-2 text-heading">Invoice To:</h6>
                        @php $customer = $invoice->delivery->po->customer; @endphp
                        <p class="mb-1 text-heading fw-medium">{{ $customer->name ?? 'Unknown Customer' }}</p>
                        <p class="mb-1">{{ $customer->address ?? 'No Address Provided' }}</p>
                        <p class="mb-1">{{ $customer->phone ?? '' }}</p>
                        <p class="mb-0">{{ $customer->email ?? '' }}</p>
                    </div>

                    <div>
                        <h6 class="pb-2 text-heading">Reference Details:</h6>
                        <table class="table-borderless">
                            <tbody>
                                <tr>
                                    <td class="pe-4 text-muted">PO Number:</td>
                                    <td class="fw-medium text-heading">
                                        <a href="{{ route('po.show', $invoice->delivery->po_id) }}" class="text-body text-decoration-underline">
                                            {{ $invoice->delivery->po->no_po }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="pe-4 text-muted">Delivery Note:</td>
                                    <td class="fw-medium text-heading">
                                        <a href="{{ route('delivery.show', $invoice->delivery->delivery_id) }}" class="text-body text-decoration-underline">
                                            {{ $invoice->delivery->delivery_no }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="pe-4 text-muted">Delivery Date:</td>
                                    <td>{{ $invoice->delivery->delivered_at ? \Carbon\Carbon::parse($invoice->delivery->delivered_at)->format('d M Y') : 'Pending' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ===================== LINE ITEMS ===================== --}}
                @php
                $itemPrice = $invoice->delivery->po->harga;
                $qtyDelivered = $invoice->delivery->qty_delivered;
                $lineTotal = $itemPrice * $qtyDelivered;
                @endphp

                <div class="table-responsive border mt-6">
                    <table class="table table-striped m-0">
                        <thead>
                            <tr>
                                <th class="text-nowrap">Item Description</th>
                                <th class="text-nowrap text-end">PO Price</th>
                                <th class="text-nowrap text-end">Qty Delivered</th>
                                <th class="text-nowrap text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-nowrap">
                                    <p class="mb-0 fw-medium text-heading">{{ $invoice->delivery->po->nama_barang }}</p>
                                    <small class="text-muted">Ref PO: {{ $invoice->delivery->po->no_po }}</small>
                                </td>
                                <td class="text-end">Rp {{ number_format($itemPrice, 2) }}</td>
                                <td class="text-end">{{ number_format($qtyDelivered) }}</td>
                                <td class="text-end">Rp {{ number_format($lineTotal, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ===================== TOTALS ===================== --}}
                <div class="table-responsive">
                    <table class="table table-borderless m-0">
                        <tbody>
                            <tr>
                                <td class="align-top pe-6 py-6 ps-0">
                                    <p class="mb-1">
                                        <span class="me-2 fw-medium text-heading">Salesperson:</span>
                                        <span>{{ $invoice->delivery->po->input_user->user_name ?? 'Admin' }}</span>
                                    </p>
                                    <span>Thanks for your business</span>
                                </td>
                                <td class="px-0 py-6 w-px-150">
                                    <p class="mb-2 text-heading">Subtotal:</p>
                                    <p class="mb-2 text-heading">Tax:</p>
                                    <p class="mb-0 text-heading fw-bold">Grand Total:</p>
                                </td>
                                <td class="text-end py-6 px-4 fw-medium text-heading w-px-150">
                                    <p class="mb-2">Rp {{ number_format($lineTotal, 2) }}</p>
                                    <p class="mb-2">0.00</p>
                                    <p class="mb-0 text-primary fw-bold">Rp {{ number_format($lineTotal, 2) }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr class="my-0" />

                {{-- ===================== PAYMENT HISTORY ===================== --}}
                <div class="card-body">
                    <h6 class="text-heading mb-4"><i class="ri-money-dollar-circle-line me-2"></i>Payment History</h6>

                    @php
                    // Single payment per invoice (hasOne)
                    $totalPaid = $invoice->payment ? $invoice->payment->amount : 0;
                    $remaining = $lineTotal - $totalPaid;
                    @endphp

                    @if(!$invoice->payment)
                    <div class="alert alert-outline-warning mb-4" role="alert">
                        <span class="fw-medium">No payment recorded.</span> This invoice is fully outstanding.
                    </div>
                    @endif

                    <div class="table-responsive mb-4 border rounded">
                        <table class="table table-sm text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Description</th>
                                    <th>Recorded By</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center no-print">Proof</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($invoice->payment)
                                @php $payment = $invoice->payment; @endphp
                                <tr>
                                    <td><span class="fw-medium">#PAY-{{ $payment->payment_id }}</span></td>
                                    <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') : '-' }}</td>
                                    <td>
                                        <span class="badge bg-label-info">{{ $payment->metode_bayar ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 180px;" title="{{ $payment->description }}">
                                            {{ $payment->description ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $payment->input_user->user_name ?? 'User #' . $payment->input_by }}<br>
                                            <span class="text-muted">{{ $payment->input_date ? \Carbon\Carbon::parse($payment->input_date)->format('d M Y, H:i') : '' }}</span>
                                        </small>
                                    </td>
                                    <td class="text-end text-success fw-medium">Rp {{ number_format($payment->amount, 2) }}</td>
                                    <td class="text-center no-print">
                                        @if($payment->bukti_bayar)
                                        <a href="{{ asset('storage/' . $payment->bukti_bayar) }}"
                                            target="_blank"
                                            class="btn btn-sm btn-icon btn-outline-primary"
                                            title="View Proof of Payment">
                                            <i class="ri-file-image-line"></i>
                                        </a>
                                        @else
                                        <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @else
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        No payment record found for this invoice.
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end align-items-center m-3 mb-0 gap-3">
                        <div class="d-flex align-items-center">
                            <span class="text-muted me-2">Total Paid:</span>
                            <span class="fw-bold text-success">Rp {{ number_format($totalPaid, 2) }}</span>
                        </div>
                        <div class="vr"></div>
                        <div class="d-flex align-items-center">
                            <span class="text-muted me-2">Balance Due:</span>
                            <span class="fw-bold fs-5 {{ $remaining <= 0 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format(max($remaining, 0), 2) }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ===================== SIDEBAR ===================== --}}
    <div class="col-xl-3 col-md-4 col-12 no-print">

        <div class="card mb-4">
            <div class="card-body">
                <button class="btn btn-primary d-grid w-100 mb-4" data-bs-toggle="offcanvas" data-bs-target="#sendInvoiceOffcanvas">
                    <span class="d-flex align-items-center justify-content-center text-nowrap">
                        <i class="ri-send-plane-fill me-2"></i>Send Invoice
                    </span>
                </button>
                <button class="btn btn-outline-secondary d-grid w-100 mb-4">
                    Download PDF
                </button>
                <div class="d-flex mb-2">
                    <a href="{{ route('payment.create', ['invoice_id' => $invoice->invoice_id]) }}" class="btn btn-label-success d-grid w-100">
                        <i class="ri-money-dollar-circle-line me-2"></i> Add Payment
                    </a>
                </div>
            </div>
        </div>

        {{-- STATUS --}}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Status</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small uppercase">Invoice Status</label>
                    <div>
                        @if($invoice->status_invoice == 2)
                        <span class="badge bg-dark w-100 py-2">Cancelled</span>
                        @elseif($invoice->payment && $remaining <= 0)
                            <span class="badge bg-success w-100 py-2">Paid</span>
                            @else
                            <span class="badge bg-danger w-100 py-2">Unpaid</span>
                            @endif
                    </div>
                </div>

                {{-- PAYMENT PROGRESS --}}
                @php
                $paidPercent = $lineTotal > 0 ? min(($totalPaid / $lineTotal) * 100, 100) : 0;
                @endphp
                <div class="mb-3">
                    <label class="form-label text-muted">Payment Progress</label>
                    <div class="d-flex justify-content-between mb-1">
                        <small>Rp {{ number_format($totalPaid, 2) }} paid</small>
                        <small>{{ round($paidPercent) }}%</small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $paidPercent }}%"></div>
                    </div>
                </div>

                {{-- FULFILLMENT --}}
                <div class="mb-3">
                    <label class="form-label text-muted">Fulfillment</label>
                    @php
                    $poQty = $invoice->delivery->po->qty;
                    $delQty = $invoice->delivery->qty_delivered;
                    $percent = $poQty > 0 ? ($delQty / $poQty) * 100 : 0;
                    @endphp
                    <div class="d-flex justify-content-between mb-1">
                        <small>{{ number_format($delQty) }} of {{ number_format($poQty) }} Items</small>
                        <small>{{ round($percent) }}%</small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SYSTEM LOG --}}
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">System Log</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <small class="text-muted d-block">Created By:</small>
                        <span class="fw-medium">User ID #{{ $invoice->input_by }}</span>
                    </li>
                    <li class="mb-2">
                        <small class="text-muted d-block">Created At:</small>
                        <span>{{ \Carbon\Carbon::parse($invoice->input_date)->format('d M Y, H:i') }}</span>
                    </li>
                    @if($invoice->edit_date)
                    <li>
                        <small class="text-muted d-block">Last Updated:</small>
                        <span>{{ \Carbon\Carbon::parse($invoice->edit_date)->format('d M Y, H:i') }}</span>
                    </li>
                    @endif
                </ul>
            </div>
        </div>

    </div>
</div>
@endsection