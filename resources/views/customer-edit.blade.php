@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah Customer')

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Ubah Customer</h5>
    </div>
    <div class="card-body">
        <form id="customerForm" data-method="PUT" action="{{ route('customers.update', $item->id_cust) }}">
            @method('PUT')
            <div class="row mb-4">
                <label class="col-sm-2 col-form-label" for="cust_name">Nama Customer</label>
                <div class="col-sm-10">
                    <label class="col-sm-2 col-form-label" for="cust_name">Nama Customer</label>
                    <input type="text"
                        name="cust_name"
                        id="cust_name"
                        class="form-control"
                        placeholder="Masukkan nama customer"
                        value="{{ $item->cust_name }}"
                        required>
                </div>
            </div>

            <div class="row justify-content-end">
                <div class="col-sm-10">
                    <button type="submit" id="btnSave" class="btn btn-primary me-2">
                        <span id="btnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        Simpan Customer
                    </button>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection