<template id="truncate-form">
    <swal-title>
        Peringatan: Apakah anda yakin menghapus seluruh PO?
    </swal-title>
    <swal-html>
        <form action="{{ route('po.truncate') }}" id="truncate-po-form" novalidate autocomplete="off">
            <div class="mb-4">
                <label for="swal-reason" class="form-label fw-semibold">
                    Alasan <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    id="swal-reason"
                    class="form-control"
                    placeholder="Masukkan alasan truncate...">
            </div>

            <div class="mb-4">
                <label for="swal-confirm" class="form-label fw-semibold">
                    Ketik untuk mengkonfirmasi <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    id="swal-confirm"
                    class="form-control"
                    placeholder="SAYA YAKIN ATAS TINDAKAN INI">
            </div>
        </form>
    </swal-html>
    <swal-icon type="warning" color="red"></swal-icon>
    <swal-button type="confirm" id="confirm-form-po-truncate">
        Yakin
    </swal-button>
    <swal-button type="cancel">
        Cancel
    </swal-button>
    <swal-param name="allowEscapeKey" value="false" />
    <swal-param name="allowOutsideClick" value="false" />
</template>