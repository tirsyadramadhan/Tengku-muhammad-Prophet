@extends('layouts/contentNavbarLayout')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Create Margin</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('margin.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Select Purchase Order</label>
                <select name="po_id" class="form-select" required>
                    <option value="">-- Select PO --</option>
                    @foreach($pos as $po)
                    <option value="{{ $po->po_id }}">
                        {{ $po->no_po }} - {{ $po->nama_barang }} (Status: {{ $po->status }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Added Margin</label>
                <input type="number" step="0.01" name="added_margin" class="form-control">
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="hold_margin_toggle" id="holdMargin">
                <label class="form-check-label" for="holdMargin">Hold Total Margin from PO?</label>
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="hold_modal_toggle" id="holdModal">
                <label class="form-check-label" for="holdModal">Hold Modal from PO?</label>
            </div>

            <button type="submit" class="btn btn-primary">Save Margin</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const marginToggle = document.getElementById('holdMargin');
        const addedMarginInput = document.getElementById('added_margin');

        marginToggle.addEventListener('change', function() {
            if (this.checked) {
                addedMarginInput.value = ''; // Clear the value
                addedMarginInput.disabled = true; // Disable the input
                addedMarginInput.required = false; // Remove requirement for submission
            } else {
                addedMarginInput.disabled = false;
                addedMarginInput.required = true;
            }
        });
    });
</script>
@endsection