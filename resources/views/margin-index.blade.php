@extends('layouts/contentNavbarLayout')

@section('title', 'Margin Management')

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" />
<style>
    /* Dashboard & Table Styling */
    .card-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
    }

    .table-container {
        padding: 0 1.5rem 1.5rem 1.5rem;
    }

    /* Column Sizing and Financial Formatting */
    .col-po {
        min-width: 220px;
    }

    .currency-text {
        font-family: 'Public Sans', sans-serif;
        font-weight: 600;
        text-align: right;
    }

    /* Column Tinting for Contextual Readability */
    .bg-modal {
        background-color: rgba(3, 195, 236, 0.03);
    }

    /* Cyan */
    .bg-acquired {
        background-color: rgba(113, 221, 55, 0.03);
    }

    /* Green */
    .bg-held {
        background-color: rgba(255, 62, 29, 0.03);
    }

    /* Red */
    .bg-total {
        background-color: rgba(105, 108, 255, 0.05);
    }

    /* Primary */

    .table tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.03) !important;
        transition: 0.2s;
    }

    /* Stat Cards */
    .stat-card {
        border: none;
        border-left: 4px solid #696cff;
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #03c3ec;">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-info me-3">
                        <i class="ri-database-2-line fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Base Modal</small>
                        <h5 class="mb-0 fw-bold">Rp {{ number_format($data->sum('modal')) }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #71dd37;">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-success me-3">
                        <i class="ri-line-chart-line fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Margin Acquired</small>
                        <h5 class="mb-0 fw-bold">Rp {{ number_format($data->sum('margin_acquired')) }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm" style="border-left-color: #ff3e1d;">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-danger me-3">
                        <i class="ri-lock-2-line fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Held Funds</small>
                        <h5 class="mb-0 fw-bold">Rp {{ number_format($data->sum('held_margin') + $data->sum('held_modal')) }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-label-primary me-3">
                        <i class="ri-money-dollar-circle-line fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Net Total Margin</small>
                        <h5 class="mb-0 fw-bold">Rp {{ number_format($data->sum('total_margin')) }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header-actions border-bottom">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-md bg-label-primary me-3">
                    <i class="ri-percent-line fs-3"></i>
                </div>
                <div>
                    <h5 class="mb-0">Margin Ledger</h5>
                    <small class="text-muted">Tracking profit distribution and retained capital</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('margin.create') }}" class="btn btn-primary">
                    <i class="ri-add-circle-line me-1"></i> New Margin
                </a>
            </div>
        </div>

        <div class="table-responsive table-container text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="col-po py-3">PO Reference</th>
                        <th class="text-end bg-modal">Base Modal</th>
                        <th class="text-end bg-acquired">Acquired</th>
                        <th class="text-end">Added</th>
                        <th class="text-end bg-held">Held Margin</th>
                        <th class="text-end bg-held">Held Modal</th>
                        <th class="text-end bg-total fw-bold text-primary">Total Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                    <tr>
                        <td class="col-po">
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark">{{ $item->po->no_po }}</span>
                                <small class="text-muted">
                                    <i class="ri-user-smile-line me-1"></i>{{ $item->po->customer->cust_name ?? 'N/A' }}
                                </small>
                            </div>
                        </td>

                        <td class="currency-text text-info bg-modal">
                            Rp {{ number_format($item->modal) }}
                        </td>

                        <td class="currency-text text-success bg-acquired">
                            Rp {{ number_format($item->margin_acquired) }}
                        </td>

                        <td class="currency-text text-warning">
                            @if($item->added_margin > 0)
                            +{{ number_format($item->added_margin) }}
                            @else
                            <span class="text-muted small">0</span>
                            @endif
                        </td>

                        <td class="currency-text text-danger bg-held">
                            Rp {{ number_format($item->held_margin) }}
                        </td>

                        <td class="currency-text text-danger bg-held">
                            Rp {{ number_format($item->held_modal) }}
                        </td>

                        <td class="currency-text fw-bold text-primary bg-total">
                            <span class="badge bg-label-primary fs-6">
                                Rp {{ number_format($item->total_margin) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="my-3">
                                <i class="ri-money-dollar-circle-line fs-1 text-muted"></i>
                                <p class="mt-2 text-muted">No margin records found.</p>
                                <a href="{{ route('margin.create') }}" class="btn btn-sm btn-primary">Create Your First Record</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer border-top bg-light p-3">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Total Records: <strong>{{ $data->count() }}</strong>
                </small>
            </div>
        </div>
    </div>
</div>
@endsection