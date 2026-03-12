@extends('layouts/contentNavbarLayout')

@section('content')
<form action="{{ route('investments.import') }}" id="import-form" enctype="multipart/form-data">
    @csrf
    <div class="mb-4">
        <label class="form-label" for="">Pilih File Excel (.xlsx /.xls / .csv)</label>
        <input type="file" name="file" id="import-file" accept=".xlsx,.xls,.csv" class="form-control mb-2">
    </div>
    <button type="submit" id="btnImport" class="btn btn-success mt-4">Import</button>
</form>
@endsection