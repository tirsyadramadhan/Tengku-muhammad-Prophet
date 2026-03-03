import './bootstrap';
import.meta.glob(['../assets/img/**', '../assets/vendor/fonts/**']);
import { CountUp } from 'countup.js';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.css';
import 'datatables.net-responsive-bs5';
import 'datatables.net-responsive-bs5/css/responsive.bootstrap5.css';
import 'datatables.net-buttons-bs5';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import 'datatables.net-buttons-bs5/css/buttons.bootstrap5.css';
import 'remixicon/fonts/remixicon.css';
import Swal from 'sweetalert2';
import jQuery from 'jquery';
import { Tooltip } from 'bootstrap';

window.$ = window.jQuery = jQuery;
window.jQuery = jQuery;
window.Swal = Swal;
window.DataTable = DataTable;
window.CountUp = CountUp;
window.Tooltip = Tooltip;

document.getElementById('logout-btn').addEventListener('click', function () {
    Swal.fire({
        title: 'Konfirmasi Logout',
        text: 'Apakah Anda yakin ingin keluar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('logout-form').submit();
        }
    });
});