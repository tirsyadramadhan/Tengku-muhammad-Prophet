@extends('layouts/contentNavbarLayout')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Set Margin Diterima</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('margin_diterima.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Select Penarikan</label>
                <select name="margin_diterima" class="form-select" required>
                    <option value="">-- Select Penarikan --</option>
                    @foreach($data as $d)
                    <option value="{{ $d->penarikan }}">
                        {{ $d->penarikan == 0 ? 'No Penarikan (0)' : $d->penarikan }}
                    </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save As Margin Diterima</button>
        </form>
    </div>
</div>
@endsection