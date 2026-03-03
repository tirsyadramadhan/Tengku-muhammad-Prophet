@extends('layouts/contentNavbarLayout')

@section('content')
<form id="import-form" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" id="import-file" accept=".xlsx,.xls,.csv" class="form-control mb-2">
    <button type="button" id="btnImport" class="btn btn-success">Import</button>
</form>


@endsection
@section('page-script')
<script>
    document.getElementById('btnImport').addEventListener('click', function() {
        const file = document.getElementById('import-file').files[0];

        if (!file) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih file terlebih dahulu!',
                confirmButtonColor: '#d33'
            });
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', document.querySelector('input[name="_token"]').value);

        fetch('{{ route("po.import") }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                if (response.ok) {
                    Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonColor: '#696cff'
                        })
                        .then(() => window.location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message,
                        confirmButtonColor: '#d33'
                    });
                }
            });
    });
</script>
@endsection