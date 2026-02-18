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

import JSZip from 'jszip';
import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';

import 'remixicon/fonts/remixicon.css';
import Swal from 'sweetalert2';

window.Swal = Swal;
window.DataTable = DataTable;
window.JSZip = JSZip;
pdfMake.vfs = pdfFonts.pdfMake.vfs;
window.pdfMake = pdfMake;
window.CountUp = CountUp;
