@extends('layouts/contentNavbarLayout')

@section('title', 'Customer Management')

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" />
<style>
    /* 1. Base Table Styling */
    .table-responsive {
        min-height: 400px;
        border-radius: 0 0 8px 8px;
    }

    .table thead th {
        background-color: #f8f9fa !important;
        font-weight: 700;
        color: #566a7f;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        border-top: none !important;
    }

    /* 2. Column-Specific Tinting (Strategic Color Palette) */

    /* ID Column - Subtle Neutral */
    .table td:nth-child(1),
    .table th:nth-child(1) {
        background-color: rgba(133, 146, 163, 0.03);
        width: 100px;
    }

    /* Customer Name - Primary Blue Tint */
    .table td:nth-child(2),
    .table th:nth-child(2) {
        background-color: rgba(105, 108, 255, 0.03);
    }

    /* Added Date - Success Green Tint */
    .table td:nth-child(3),
    .table th:nth-child(3) {
        background-color: rgba(113, 221, 55, 0.04);
    }

    /* Actions - Clear/White */
    .table td:nth-child(4),
    .table th:nth-child(4) {
        background-color: #fff;
        width: 120px;
    }

    /* 3. Refined UI Components */
    .cust-avatar {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-weight: 700;
        font-size: 1rem;
        transition: 0.3s;
    }

    .table tbody tr:hover td {
        filter: brightness(0.97);
        transition: 0.2s;
    }

    .table tbody tr:hover .cust-avatar {
        transform: scale(1.1);
    }

    /* Action Buttons styling */
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }
</style>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-md bg-label-primary me-3">
                <i class="ri-user-star-line fs-3"></i>
            </div>
            <div>
                <h5 class="mb-0">Customer Database</h5>
                <small class="text-muted">Direct control over your client records</small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('customer.create') }}" class="btn btn-primary shadow-sm text-nowrap">
                <i class="ri-user-add-line me-1"></i> Add New
            </a>
        </div>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-bordered mb-0">
            <thead>
                <tr>
                    <th class="text-center">UID</th>
                    <th>Customer Details</th>
                    <th>Registration Date</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                @php
                $colors = ['primary', 'success', 'warning', 'info', 'danger'];
                $color = $colors[$row->id_cust % 5];
                @endphp
                <tr>
                    <td class="text-center">
                        <span class="badge bg-label-secondary">ID-{{ $row->id_cust }}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="cust-avatar bg-label-{{ $color }} text-uppercase me-3 shadow-sm">
                                {{ substr($row->cust_name, 0, 1) }}
                            </div>
                            <div>
                                <span class="fw-bold text-heading d-block">{{ $row->cust_name }}</span>
                                <small class="text-muted">Verified Client</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-medium text-dark">
                                <i class="ri-calendar-event-line me-1 text-success"></i>
                                {{ \Carbon\Carbon::parse($row->input_date)->format('d M Y') }}
                            </span>
                            <small class="text-muted ps-4">{{ \Carbon\Carbon::parse($row->input_date)->format('h:i A') }}</small>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ri-more-2-fill fs-5"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="javascript:void(0);"><i class="ri-pencil-line me-2"></i> Edit</a>
                                <a class="dropdown-item text-danger" href="javascript:void(0);"><i class="ri-delete-bin-line me-2"></i> Delete</a>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <div class="avatar avatar-xl bg-label-secondary mb-3">
                                <i class="ri-user-search-line fs-1"></i>
                            </div>
                            <h6 class="mb-1">No customers found</h6>
                            <small class="text-muted">Start growing your database by adding your first client.</small>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer border-top bg-light-subtle d-flex justify-content-between align-items-center py-3">
        <p class="mb-0 small text-muted">Total Active Customers: <span class="fw-bold text-primary">{{ count($data) }}</span></p>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item prev"><a class="page-link" href="javascript:void(0);"><i class="ri-arrow-left-s-line"></i></a></li>
                <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                <li class="page-item next"><a class="page-link" href="javascript:void(0);"><i class="ri-arrow-right-s-line"></i></a></li>
            </ul>
        </nav>
    </div>
</div>
@endsection