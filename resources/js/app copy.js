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
import JustValidate from 'just-validate';
import { formatDuration, intervalToDuration } from 'date-fns';
import { id as idLocale } from 'date-fns/locale';
import select2 from 'select2';
import 'select2/dist/css/select2.min.css';
import 'select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css';
import * as bootstrap from 'bootstrap';
const baseUrl = window.location.pathname.replace(/\/+$/, '');

window.$ = window.jQuery = jQuery;
window.jQuery = jQuery;
window.Swal = Swal;
window.DataTable = DataTable;
window.CountUp = CountUp;
window.Tooltip = Tooltip;
window.JustValidate = JustValidate;
select2($);

document.addEventListener('DOMContentLoaded', function () {
    const currentPath = window.location.pathname;

    // skip script entirely on /dashboard
    if (currentPath === '/dashboard') return;

    document.querySelectorAll('.menu-item .menu-link').forEach(link => {
        const menuItem = link.closest('.menu-item');
        const linkPath = new URL(link.href).pathname;

        menuItem.classList.remove('active');

        const isRoot = linkPath === '/';
        const isActive = isRoot
            ? currentPath === '/'
            : currentPath === linkPath || currentPath.startsWith(linkPath + '/');

        if (isActive) {
            menuItem.classList.add('active');
        }
    });
});

if (document.getElementById('logout-btn')) {
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
}

if (document.getElementById("formAuthentication")) {
    const validation = new JustValidate('#formAuthentication', {
        successFieldCssClass: 'is-valid',
        errorFieldCssClass: 'is-invalid',
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
    });

    const rememberCheckbox = document.getElementById('form2Example3');

    rememberCheckbox.addEventListener('change', function () {
        this.value = this.checked ? '1' : '0';
    });

    window.onRecaptchaSuccess = function (token) {
        // Sync token into hidden input so JustValidate can read it
        document.getElementById('g-recaptcha-response').value = token;
        validation.revalidateField('#g-recaptcha-response');
    };

    window.onRecaptchaExpired = function () {
        // Clear hidden input when token expires
        document.getElementById('g-recaptcha-response').value = '';
        validation.revalidateField('#g-recaptcha-response');
    };

    validation
        .addField('#user_name', [
            { rule: 'required', errorMessage: 'Isi Username' },
            { rule: 'minLength', value: 3, errorMessage: 'Minimal 3 Karakter' },
        ], {
            successMessage: 'Username sudah benar',
        })
        .addField('#password-input', [
            {
                rule: 'required',
                errorMessage: 'Isi Password',
            },
            {
                rule: 'minLength',
                value: 6,
                errorMessage: 'Password setidaknya 8 karakter',
            },
        ], {
            errorsContainer: '#password-error',
            errorLabelCssClass: 'password-error-msg',
            successMessage: 'Password sudah benar'
        })
        .addField('#g-recaptcha-response', [
            {
                rule: 'custom',
                validator: () => {
                    return grecaptcha.getResponse().length > 0;
                },
                errorMessage: 'Selesaikan verifikasi reCAPTCHA terlebih dahulu.',
            }
        ], {
            successMessage: 'Verifikasi berhasil!',
        })
        .onSuccess(async (event) => {
            const form = event.target;
            const formData = new FormData(form);
            const validateInputs = form.querySelectorAll('.live-validate');
            const btnSave = document.getElementById('btnSave');
            const btnSpinner = document.getElementById('btnSpinner');
            const recaptchaToken = grecaptcha.getResponse();

            btnSave.disabled = true;
            btnSpinner.classList.remove('d-none');

            formData.append('recaptcha_token', recaptchaToken);

            fetch('/login', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
                .then(async response => {
                    const data = await response.json();

                    if (response.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message ?? 'Pengguna berhasil dibuat.',
                            confirmButtonColor: '#696cff'
                        }).then(() => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                document.getElementById('formAuthentication').reset();
                                grecaptcha.reset();
                                for (let i = 0; i < validateInputs.length; i++) {
                                    validateInputs[i].classList.remove('is-valid', 'is-invalid');
                                }
                            }
                        });
                    } else if (response.status === 422) {
                        const errors = data.errors ?? {};
                        const messages = Object.values(errors).flat().join('<br>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Gagal!',
                            html: messages || 'Terjadi kesalahan validasi.',
                            confirmButtonColor: '#d33'
                        });

                        grecaptcha.reset();

                        for (const field in errors) {
                            const el = document.querySelector(`[name="${field}"]`);
                            if (el) {
                                el.classList.remove('is-valid');
                                el.classList.add('is-invalid');
                            }
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan!',
                            text: data.message ?? 'Server error. Coba lagi nanti.',
                            confirmButtonColor: '#d33'
                        });

                        grecaptcha.reset();
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Gagal!',
                        text: 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.',
                        confirmButtonColor: '#d33'
                    });

                    grecaptcha.reset();
                })
                .finally(() => {
                    btnSave.disabled = false;
                    btnSpinner.classList.add('d-none');
                });
        });

}

if (document.getElementById("create-user")) {
    document.getElementById('toggle-password').addEventListener('click', function () {
        const input = document.getElementById('password-input');
        const icon = this.querySelector('i');

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.classList.toggle('ri-eye-off-line', !isPassword);
        icon.classList.toggle('ri-eye-line', isPassword);
    });

    const validation = new JustValidate('#create-user', {
        errorFieldCssClass: 'is-invalid',
    });

    validation
        .addField('#user_name', [
            {
                rule: 'required',
                errorMessage: 'Username is required',
            },
            {
                rule: 'customRegexp',
                value: /^[a-zA-Z0-9_-]{3,16}$/,
                errorMessage: 'Username must be 3–16 chars (letters, numbers, _ or -)',
            },
        ])
        .addField('#role-pengguna', [
            {
                rule: 'required',
                errorMessage: 'Role is required',
            },
        ])
        .addField('#email', [
            {
                rule: 'required',
                errorMessage: 'Email is required',
            },
            {
                rule: 'email',
                errorMessage: 'Enter a valid email address',
            },
        ])
        .addField('#profile_picture', [
            {
                rule: 'files',
                value: {
                    files: {
                        extensions: ['jpg', 'jpeg', 'png', 'webp'],
                        maxSize: 2 * 1024 * 1024, // 2MB
                        type: ['image/jpeg', 'image/png', 'image/webp'],
                    },
                },
                errorMessage: 'Only JPG/PNG/WEBP under 2MB allowed',
            },
        ])
        .addField('#password-input', [
            {
                rule: 'required',
                errorMessage: 'Password is required',
            },
            {
                rule: 'minLength',
                value: 8,
                errorMessage: 'Password must be at least 8 characters',
            },
            {
                rule: 'customRegexp',
                value: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/,
                errorMessage: 'Must include uppercase, lowercase, and a number',
            },
        ], { errorsContainer: '#password-error' })
        .onSuccess((event) => {
            const form = event.target; // FIX #1 — grab form from event
            const formData = new FormData(form); // FIX #2 — build formData
            const validateInputs = form.querySelectorAll('.live-validate'); // FIX #3
            const btnSave = document.getElementById('btnSave');
            const btnSpinner = document.getElementById('btnSpinner');

            btnSave.disabled = true;
            btnSpinner.classList.remove('d-none');

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData
            })
                .then(async response => {
                    const data = await response.json();

                    if (response.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message ?? 'Pengguna berhasil dibuat.',
                            confirmButtonColor: '#696cff'
                        }).then(() => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                document.getElementById('create-user').reset();
                                for (let i = 0; i < validateInputs.length; i++) {
                                    validateInputs[i].classList.remove('is-valid', 'is-invalid');
                                }
                            }
                        });
                    } else if (response.status === 422) {
                        const errors = data.errors ?? {};
                        const messages = Object.values(errors).flat().join('<br>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Gagal!',
                            html: messages || 'Terjadi kesalahan validasi.',
                            confirmButtonColor: '#d33'
                        });

                        for (const field in errors) {
                            const el = document.querySelector(`[name="${field}"]`);
                            if (el) {
                                el.classList.remove('is-valid');
                                el.classList.add('is-invalid');
                            }
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan!',
                            text: data.message ?? 'Server error. Coba lagi nanti.',
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Gagal!',
                        text: 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.',
                        confirmButtonColor: '#d33'
                    });
                })
                .finally(() => {
                    btnSave.disabled = false;
                    btnSpinner.classList.add('d-none');
                });
        });
}

if (document.getElementById('paymentForm')) {
    // Inisialisasi Select2
    $('#invoice_id, #metode_bayar').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: '-- Pilih Invoice --',
        allowClear: true
    });

    // Saat invoice dipilih, isi jumlah (read‑only)
    $('#invoice_id').on('change', function () {
        let selected = $(this).find(':selected');
        let total = selected.data('total') || 0;
        $('#amount_display').val(formatRupiah(total));
        $('#amount').val(total);
        validateField($(this));
    });

    const validator = new JustValidate('#paymentForm', {
        errorFieldCssClass: 'is-invalid',
        successFieldCssClass: 'is-valid',
        errorLabelStyle: {},
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
        validateBeforeSubmitting: true,
    });

    validator
        .addField('#invoice_id', [
            {
                rule: 'required',
                errorMessage: 'Invoice wajib dipilih.',
            },
            {
                validator: (value) => value !== '' && value !== null,
                errorMessage: 'Invoice wajib dipilih.',
            },
        ], {
            successMessage: 'Invoice valid ✓',
        })

        .addField('#amount_display', [
            {
                rule: 'required',
                errorMessage: 'Jumlah pembayaran wajib diisi.',
            },
            {
                validator: (value) => {
                    const raw = document.getElementById('amount').value;
                    return raw !== '' && Number(raw) > 0;
                },
                errorMessage: 'Jumlah pembayaran tidak valid.',
            },
        ], {
            successMessage: 'Jumlah valid ✓',
        })

        .addField('#metode_bayar', [
            {
                rule: 'required',
                errorMessage: 'Metode pembayaran wajib dipilih.',
            },
            {
                validator: (value) => value !== '' && value !== null,
                errorMessage: 'Metode pembayaran wajib dipilih.',
            },
        ], {
            successMessage: 'Metode pembayaran valid ✓',
        })

        .addField('#payment_date', [
            {
                rule: 'required',
                errorMessage: 'Tanggal pembayaran wajib diisi.',
            },
            {
                validator: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime());
                },
                errorMessage: 'Format tanggal tidak valid.',
            },
            {
                validator: (value) => {
                    const input = new Date(value);
                    const min = new Date('2000-01-01');
                    const max = new Date('2100-12-31');
                    return input >= min && input <= max;
                },
                errorMessage: 'Tanggal pembayaran di luar rentang yang diizinkan.',
            },
        ], {
            successMessage: 'Tanggal valid ✓',
        })

        .onSuccess(async (event) => {
            event.preventDefault();

            // Trigger select2 fields manually since just-validate doesn't detect them natively
            const invoiceVal = document.getElementById('invoice_id').value;
            const metodeVal = document.getElementById('metode_bayar').value;

            if (!invoiceVal || !metodeVal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Pastikan semua dropdown sudah dipilih.',
                    confirmButtonText: 'Tutup',
                });
                return;
            }

            const confirm = await Swal.fire({
                icon: 'question',
                title: 'Konfirmasi Pembayaran',
                text: 'Apakah data pembayaran sudah benar?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#696cff',
                cancelButtonColor: '#8592a3',
            });

            if (!confirm.isConfirmed) return;

            const btn = document.getElementById('btnSave');
            const spinner = document.getElementById('btnSpinner');
            const form = document.getElementById('paymentForm');
            const formData = new FormData(form);

            btn.disabled = true;
            spinner.classList.remove('d-none');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message ?? 'Pembayaran berhasil disimpan.',
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#696cff',
                    });

                    window.location.href = data.redirect ?? '/payment';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message ?? 'Terjadi kesalahan saat menyimpan pembayaran.',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#ff3e1d',
                    });
                }

            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Jaringan',
                    text: 'Tidak dapat terhubung ke server. Coba lagi.',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#ff3e1d',
                });
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        });


    // Select2 — trigger just-validate recheck on change
    ['#invoice_id', '#metode_bayar'].forEach(id => {
        $(id).on('change', function () {
            validator.revalidateField(id);
        });
    });
}

if (document.getElementById("edit-user")) {
    document.getElementById('toggle-password').addEventListener('click', function () {
        const input = document.getElementById('password-input');
        const icon = this.querySelector('i');

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.classList.toggle('ri-eye-off-line', !isPassword);
        icon.classList.toggle('ri-eye-line', isPassword);
    });

    const validation = new JustValidate('#edit-user', {
        errorFieldCssClass: 'is-invalid',
    });

    validation
        .addField('#user_name', [
            {
                rule: 'required',
                errorMessage: 'Username is required',
            },
            {
                rule: 'customRegexp',
                value: /^[a-zA-Z0-9_-]{3,16}$/,
                errorMessage: 'Username must be 3–16 chars (letters, numbers, _ or -)',
            },
        ])
        .addField('#role-pengguna', [
            {
                rule: 'required',
                errorMessage: 'Role is required',
            },
        ])
        .addField('#email', [
            {
                rule: 'required',
                errorMessage: 'Email is required',
            },
            {
                rule: 'email',
                errorMessage: 'Enter a valid email address',
            },
        ])
        .addField('#profile_picture', [
            {
                rule: 'files',
                value: {
                    files: {
                        extensions: ['jpg', 'jpeg', 'png', 'webp'],
                        maxSize: 2 * 1024 * 1024, // 2MB
                        type: ['image/jpeg', 'image/png', 'image/webp'],
                    },
                },
                errorMessage: 'Only JPG/PNG/WEBP under 2MB allowed',
            },
        ])
        .addField('#password-input', [
            {
                rule: 'minLength',
                value: 8,
                errorMessage: 'Password must be at least 8 characters',
                conditionFunction: () => document.getElementById('password-input').value.length > 0,
            },
            {
                rule: 'customRegexp',
                value: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/,
                errorMessage: 'Must include uppercase, lowercase, and a number',
                conditionFunction: () => document.getElementById('password-input').value.length > 0,
            },
        ], { errorsContainer: '#password-error' })
        .onSuccess((event) => {
            const form = event.target; // FIX #1 — grab form from event
            const formData = new FormData(form); // FIX #2 — build formData
            const validateInputs = form.querySelectorAll('.live-validate'); // FIX #3
            const btnSave = document.getElementById('btnSave');
            const btnSpinner = document.getElementById('btnSpinner');

            btnSave.disabled = true;
            btnSpinner.classList.remove('d-none');

            fetch(form.action, {
                method: 'PUT',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData
            })
                .then(async response => {
                    const data = await response.json();

                    if (response.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message ?? 'Pengguna berhasil dibuat.',
                            confirmButtonColor: '#696cff'
                        }).then(() => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                document.getElementById('edit-user').reset();
                                window.location.reload();
                                for (let i = 0; i < validateInputs.length; i++) {
                                    validateInputs[i].classList.remove('is-valid', 'is-invalid');
                                }
                            }
                        });
                    } else if (response.status === 422) {
                        const errors = data.errors ?? {};
                        const messages = Object.values(errors).flat().join('<br>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Gagal!',
                            html: messages || 'Terjadi kesalahan validasi.',
                            confirmButtonColor: '#d33'
                        });

                        for (const field in errors) {
                            const el = document.querySelector(`[name="${field}"]`);
                            if (el) {
                                el.classList.remove('is-valid');
                                el.classList.add('is-invalid');
                            }
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan!',
                            text: data.message ?? 'Server error. Coba lagi nanti.',
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Gagal!',
                        text: 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.',
                        confirmButtonColor: '#d33'
                    });
                })
                .finally(() => {
                    btnSave.disabled = false;
                    btnSpinner.classList.add('d-none');
                });
        });
}

if (document.getElementById("incomingPoForm")) {
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: '-- Pilih Pelanggan --',
        allowClear: true
    });

    // ── Currency Formatter ────────────────────────────────────────────────────────

    function formatCurrency(value) {
        const numeric = value.replace(/[^\d]/g, '');
        if (!numeric) return '';
        return new Intl.NumberFormat('id-ID').format(numeric);
    }

    function getRaw(id) {
        return parseInt(document.getElementById(id)?.value?.replace(/\D/g, '') || 0);
    }

    // ── Currency Input Binding ────────────────────────────────────────────────────

    document.getElementById('harga_display').addEventListener('input', function () {
        const raw = this.value.replace(/\D/g, '');
        this.value = formatCurrency(this.value);
        document.getElementById('harga').value = raw;
        validator.revalidateField('#harga_display');

        // Revalidate tambahan_margin if it has a value
        if (document.getElementById('tambahan_margin_display').value) {
            validator.revalidateField('#tambahan_margin_display');
        }
    });

    document.getElementById('tambahan_margin_display').addEventListener('input', function () {
        const raw = this.value.replace(/\D/g, '');
        this.value = formatCurrency(this.value);
        document.getElementById('tambahan_margin').value = raw;
    });

    // ── Numeric Only ──────────────────────────────────────────────────────────────

    ['qty', 'margin_percentage'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });
    });

    // ── Validator ─────────────────────────────────────────────────────────────────

    const validator = new JustValidate('#incomingPoForm', {
        errorFieldCssClass: 'is-invalid',
        successFieldCssClass: 'is-valid',
        errorLabelStyle: {},
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
        validateBeforeSubmitting: true,
    });

    validator
        .addField('#customer_id', [
            {
                rule: 'required',
                errorMessage: 'Pelanggan wajib dipilih.',
            },
            {
                validator: (value) => value !== '' && value !== null,
                errorMessage: 'Pelanggan wajib dipilih.',
            },
        ], {
            successMessage: 'Pelanggan valid ✓',
        })

        .addField('#nama_barang', [
            {
                rule: 'required',
                errorMessage: 'Nama barang wajib diisi.',
            },
            {
                validator: (value) => value.trim().length >= 2,
                errorMessage: 'Nama barang minimal 2 karakter.',
            },
            {
                validator: (value) => value.trim().length <= 255,
                errorMessage: 'Nama barang maksimal 255 karakter.',
            },
        ], {
            successMessage: 'Nama barang valid ✓',
        })

        .addField('#tgl_po', [
            {
                rule: 'required',
                errorMessage: 'Tanggal PO wajib diisi.',
            },
            {
                validator: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime());
                },
                errorMessage: 'Format tanggal PO tidak valid.',
            },
            {
                validator: (value) => {
                    const date = new Date(value);
                    const min = new Date('2000-01-01');
                    const max = new Date('2100-12-31');
                    return date >= min && date <= max;
                },
                errorMessage: 'Tanggal PO di luar rentang yang diizinkan.',
            },
        ], {
            successMessage: 'Tanggal PO valid ✓',
        })

        .addField('#qty', [
            {
                rule: 'required',
                errorMessage: 'Jumlah wajib diisi.',
            },
            {
                validator: (value) => {
                    const qty = parseInt(value.replace(/\D/g, '')) || 0;
                    return qty > 0;
                },
                errorMessage: 'Jumlah harus lebih dari 0.',
            },
            {
                validator: (value) => {
                    const qty = parseInt(value.replace(/\D/g, '')) || 0;
                    return qty <= 999;
                },
                errorMessage: 'Jumlah maksimal 999.',
            },
        ], {
            successMessage: 'Jumlah valid ✓',
        })

        .addField('#harga_display', [
            {
                rule: 'required',
                errorMessage: 'Harga per unit wajib diisi.',
            },
            {
                validator: () => getRaw('harga') > 0,
                errorMessage: 'Harga per unit harus lebih dari Rp 0.',
            },
            {
                validator: () => getRaw('harga').toString().length <= 16,
                errorMessage: 'Harga per unit melebihi batas maksimum.',
            },
        ], {
            successMessage: 'Harga per unit valid ✓',
        })

        .addField('#margin_percentage', [
            {
                rule: 'required',
                errorMessage: 'Margin wajib diisi.',
            },
            {
                validator: (value) => {
                    const val = parseInt(value.replace(/\D/g, '')) || 0;
                    return val >= 1;
                },
                errorMessage: 'Margin harus lebih dari 0%.',
            },
            {
                validator: (value) => {
                    const val = parseInt(value.replace(/\D/g, '')) || 0;
                    return val <= 99;
                },
                errorMessage: 'Margin tidak boleh lebih dari 99%.',
            },
        ], {
            successMessage: 'Margin valid ✓',
        })

        .onSuccess(async (event) => {
            event.preventDefault();

            const confirm = await Swal.fire({
                icon: 'question',
                title: 'Konfirmasi Purchase Order',
                text: 'Apakah data Incoming PO sudah benar dan siap disimpan?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#696cff',
                cancelButtonColor: '#8592a3',
            });

            if (!confirm.isConfirmed) return;

            const btn = document.getElementById('btnSave');
            const spinner = document.getElementById('btnSpinner');
            const form = document.getElementById('incomingPoForm');
            const formData = new FormData(form);

            btn.disabled = true;
            spinner.classList.remove('d-none');

            if (form.getAttribute('data-method') === "PUT") {
                formData.append('_method', 'PUT');
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message ?? 'Incoming PO berhasil disimpan.',
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#696cff',
                    });

                    window.location.href = data.redirect ?? '/incoming-po';

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message ?? 'Terjadi kesalahan saat menyimpan Incoming PO.',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#ff3e1d',
                    });
                }

            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Jaringan',
                    text: 'Tidak dapat terhubung ke server. Coba lagi.',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#ff3e1d',
                });
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        });

    // ── Select2 Revalidation ──────────────────────────────────────────────────────

    $('#customer_id').on('change', function () {
        validator.revalidateField('#customer_id');
    });
}

if (document.getElementById("createPoForm")) {
    // --- Initialize Select2 ---
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: '-- Pilih Incoming PO --',
        allowClear: true
    });

    const validator = new JustValidate('#createPoForm', {
        errorFieldCssClass: 'is-invalid',
        successFieldCssClass: 'is-valid',
        errorLabelStyle: {},
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
        validateBeforeSubmitting: true,
    });

    const incomingDetailsUrl = document.getElementById('createPoForm-meta')
        .getAttribute('data-incoming-details-url');

    // ── Helpers ───────────────────────────────────────────────────────────────────

    function getRawValue(id) {
        return parseInt(document.getElementById(id)?.value?.replace(/\D/g, '') || 0);
    }

    function formatIDR(amount) {
        if (!amount && amount !== 0) return '';
        let val = Math.floor(amount).toString().replace(/\./g, '');
        return val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // ── Validation ────────────────────────────────────────────────────────────────

    validator
        .addField('#incoming_po_id', [
            {
                rule: 'required',
                errorMessage: 'Incoming PO wajib dipilih.',
            },
            {
                validator: (value) => value !== '' && value !== null,
                errorMessage: 'Incoming PO wajib dipilih.',
            },
        ], {
            successMessage: 'Incoming PO valid ✓',
        })

        .addField('#no_po', [
            {
                rule: 'required',
                errorMessage: 'Nomor PO wajib diisi.',
            },
            {
                validator: (value) => value.trim().length >= 3,
                errorMessage: 'Nomor PO minimal 3 karakter.',
            },
            {
                validator: (value) => /^[0-9]+$/.test(value.trim()),
                errorMessage: 'Nomor PO hanya boleh berisi angka.',
            },
            {
                validator: (value) => value.trim().length <= 10,
                errorMessage: 'Nomor PO maksimal 10 karakter.',
            },
        ], {
            successMessage: 'Nomor PO valid ✓',
        })

        .addField('#nama_barang', [
            {
                rule: 'required',
                errorMessage: 'Nama barang wajib diisi.',
            },
            {
                validator: (value) => value.trim().length >= 2,
                errorMessage: 'Nama barang minimal 2 karakter.',
            },
            {
                validator: (value) => value.trim().length <= 255,
                errorMessage: 'Nama barang maksimal 255 karakter.',
            },
        ], {
            successMessage: 'Nama barang valid ✓',
        })

        .addField('#tgl_po', [
            {
                rule: 'required',
                errorMessage: 'Tanggal PO wajib diisi.',
            },
            {
                validator: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime());
                },
                errorMessage: 'Format tanggal PO tidak valid.',
            },
            {
                validator: (value) => {
                    const date = new Date(value);
                    const min = new Date('2000-01-01');
                    const max = new Date('2100-12-31');
                    return date >= min && date <= max;
                },
                errorMessage: 'Tanggal PO di luar rentang yang diizinkan.',
            },
        ], {
            successMessage: 'Tanggal PO valid ✓',
        })

        .addField('#qty', [
            {
                rule: 'required',
                errorMessage: 'Jumlah wajib diisi.',
            },
            {
                validator: (value) => {
                    const qty = parseInt(value.replace(/\D/g, '')) || 0;
                    return qty > 0;
                },
                errorMessage: 'Jumlah harus lebih dari 0.',
            },
            {
                validator: (value) => {
                    const qty = parseInt(value.replace(/\D/g, '')) || 0;
                    return qty <= 999;
                },
                errorMessage: 'Jumlah maksimal 999.',
            },
        ], {
            successMessage: 'Jumlah valid ✓',
        })

        .addField('#harga_display', [
            {
                rule: 'required',
                errorMessage: 'Harga per unit wajib diisi.',
            },
            {
                validator: () => {
                    const raw = getRawValue('harga');
                    return raw > 0;
                },
                errorMessage: 'Harga per unit harus lebih dari Rp 0.',
            },
            {
                validator: () => {
                    const raw = getRawValue('harga');
                    const maxRaw = parseInt(
                        document.getElementById('harga_display')?.getAttribute('data-max-raw') || 11
                    );
                    return raw.toString().length <= maxRaw;
                },
                errorMessage: 'Harga per unit melebihi batas maksimum.',
            },
        ], {
            successMessage: 'Harga per unit valid ✓',
        })

        .addField('#margin_display', [
            {
                rule: 'required',
                errorMessage: 'Total margin wajib diisi.',
            },
            {
                validator: () => {
                    const raw = getRawValue('margin');
                    return raw > 0;
                },
                errorMessage: 'Total margin harus lebih dari Rp 0.',
            },
            {
                validator: () => {
                    const raw = getRawValue('margin');
                    const maxRaw = parseInt(
                        document.getElementById('margin_display')?.getAttribute('data-max-raw') || 13
                    );
                    return raw.toString().length <= maxRaw;
                },
                errorMessage: 'Total margin melebihi batas maksimum.',
            },
        ], {
            successMessage: 'Total margin valid ✓',
        })

        .onSuccess(async (event) => {
            event.preventDefault();

            // Guard: customer_id must be populated (filled by incoming PO selection)
            const customerId = document.getElementById('customer_id').value;
            if (!customerId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Pilih Incoming PO terlebih dahulu untuk mengisi data pelanggan.',
                    confirmButtonText: 'Tutup',
                });
                return;
            }

            const confirm = await Swal.fire({
                icon: 'question',
                title: 'Konfirmasi Purchase Order',
                text: 'Apakah data PO sudah benar dan siap disimpan?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan & Buka PO',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#696cff',
                cancelButtonColor: '#8592a3',
            });

            if (!confirm.isConfirmed) return;

            const btn = document.getElementById('btnSave');
            const spinner = document.getElementById('btnSpinner');
            const form = document.getElementById('createPoForm');
            const formData = new FormData(form);

            btn.disabled = true;
            spinner.classList.remove('d-none');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message ?? 'Purchase Order berhasil disimpan.',
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#696cff',
                    });

                    window.location.href = data.redirect ?? '/po';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message ?? 'Terjadi kesalahan saat menyimpan PO.',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#ff3e1d',
                    });
                }

            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Jaringan',
                    text: 'Tidak dapat terhubung ke server. Coba lagi.',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#ff3e1d',
                });
            } finally {
                btn.disabled = false;
                spinner.classList.remove('d-none');
            }
        });

    // ── Event Listeners ───────────────────────────────────────────────────────────

    // Select2 — force revalidation on change
    $('#incoming_po_id').on('change', function () {
        validator.revalidateField('#incoming_po_id');

        let id = $(this).val();
        if (!id) return;

        $('#createPoForm :input').not('#incoming_po_id').prop('disabled', true);

        $.ajax({
            url: incomingDetailsUrl.replace(':id', id),
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    let data = response.data;

                    if (data.customer) {
                        $('#customer_name_display').val(data.customer.cust_name);
                        $('#customer_id').val(data.customer_id);
                    } else {
                        $('#customer_name_display').val('Pelanggan Tidak Dikenal');
                        $('#customer_id').val('');
                    }

                    $('#no_po').val(data.no_po);
                    $('#nama_barang').val(data.nama_barang);
                    $('#qty').val(data.qty);

                    $('#harga').val(data.harga);
                    $('#harga_display').val(formatIDR(data.harga));

                    $('#margin').val(data.margin);
                    $('#margin_display').val(formatIDR(data.margin));

                    $('#createPoForm :input').prop('disabled', false);

                    // Revalidate all live-validate fields after population
                    ['#no_po', '#nama_barang', '#tgl_po', '#qty', '#harga_display', '#margin_display']
                        .forEach(id => validator.revalidateField(id));
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal mengambil data Incoming PO.',
                });
                $('#createPoForm :input').prop('disabled', false);
            }
        });
    });

    // Revalidate margin when harga changes — margin depends on harga
    document.getElementById('harga_display').addEventListener('input', () => {
        const margin = document.getElementById('margin_display').value;
        if (margin) validator.revalidateField('#margin_display');
    });

    // Numeric strip on input
    ['qty', 'no_po', 'harga_display', 'margin_display'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });
    });
}

if (document.getElementById("invoiceForm")) {
    $('#delivery_select').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: '-- Pilih Delivery --',
        allowClear: true
    });

    const validator = new JustValidate('#invoiceForm', {
        errorFieldCssClass: 'is-invalid',
        successFieldCssClass: 'is-valid',
        errorLabelStyle: {},
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
        validateBeforeSubmitting: true,
    });

    validator
        .addField('#delivery_select', [
            {
                rule: 'required',
                errorMessage: 'Catatan pengiriman wajib dipilih.',
            },
            {
                validator: (value) => value !== '' && value !== null,
                errorMessage: 'Catatan pengiriman wajib dipilih.',
            },
        ], {
            successMessage: 'Pengiriman valid ✓',
        })

        .addField('#tgl_invoice', [
            {
                rule: 'required',
                errorMessage: 'Tanggal invoice wajib diisi.',
            },
            {
                validator: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime());
                },
                errorMessage: 'Format tanggal invoice tidak valid.',
            },
            {
                validator: (value) => {
                    const date = new Date(value);
                    const min = new Date('2000-01-01');
                    const max = new Date('2100-12-31');
                    return date >= min && date <= max;
                },
                errorMessage: 'Tanggal invoice di luar rentang yang diizinkan.',
            },
        ], {
            successMessage: 'Tanggal invoice valid ✓',
        })

        .addField('#due_date', [
            {
                rule: 'required',
                errorMessage: 'Tanggal jatuh tempo wajib diisi.',
            },
            {
                validator: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime());
                },
                errorMessage: 'Format tanggal jatuh tempo tidak valid.',
            },
            {
                validator: (value) => {
                    const dueDate = new Date(value);
                    const invoiceDate = new Date(document.getElementById('tgl_invoice').value);
                    if (isNaN(invoiceDate.getTime())) return true; // skip if invoice date not set yet
                    return dueDate > invoiceDate;
                },
                errorMessage: 'Tanggal jatuh tempo harus setelah tanggal invoice.',
            },
            {
                validator: (value) => {
                    const date = new Date(value);
                    const min = new Date('2000-01-01');
                    const max = new Date('2100-12-31');
                    return date >= min && date <= max;
                },
                errorMessage: 'Tanggal jatuh tempo di luar rentang yang diizinkan.',
            },
        ], {
            successMessage: 'Tanggal jatuh tempo valid ✓',
        })

        .onSuccess(async (event) => {
            event.preventDefault();

            const deliveryVal = document.getElementById('delivery_select').value;
            if (!deliveryVal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Pastikan semua dropdown sudah dipilih.',
                    confirmButtonText: 'Tutup',
                });
                return;
            }

            const confirm = await Swal.fire({
                icon: 'question',
                title: 'Konfirmasi Invoice',
                text: 'Apakah data invoice sudah benar?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#696cff',
                cancelButtonColor: '#8592a3',
            });

            if (!confirm.isConfirmed) return;

            const btn = document.getElementById('btnSave');
            const spinner = document.getElementById('btnSpinner');
            const form = document.getElementById('invoiceForm');
            const formData = new FormData(form);

            btn.disabled = true;
            spinner.classList.remove('d-none');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message ?? 'Invoice berhasil disimpan.',
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#696cff',
                    });

                    window.location.href = data.redirect ?? '/invoice';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message ?? 'Terjadi kesalahan saat menyimpan invoice.',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#ff3e1d',
                    });
                }

            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Jaringan',
                    text: 'Tidak dapat terhubung ke server. Coba lagi.',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#ff3e1d',
                });
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        });

    // Select2 — force revalidation on change since Select2 bypasses native events
    $('#delivery_select').on('change', function () {
        validator.revalidateField('#delivery_select');
    });

    // Revalidate due_date when tgl_invoice changes — due_date depends on it
    document.getElementById('tgl_invoice').addEventListener('change', () => {
        const dueDate = document.getElementById('due_date').value;
        if (dueDate) validator.revalidateField('#due_date');
    });
}

if (document.getElementById('deliveryForm')) {
    // --- Initialize Select2 ---
    $('#po_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: '-- Pilih PO --',
        allowClear: true
    });

    const validator = new JustValidate('#deliveryForm', {
        errorFieldCssClass: 'is-invalid',
        successFieldCssClass: 'is-valid',
        errorLabelStyle: {},
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
        validateBeforeSubmitting: true,
    });

    // ── Helpers ──────────────────────────────────────────────────────────────────

    function getAvailableQty() {
        const select = document.getElementById('po_id');
        const opt = select.options[select.selectedIndex];
        return parseInt(opt?.getAttribute('data-qty') || 0);
    }

    function getInputQty() {
        const val = document.getElementById('qty_delivered').value;
        return parseInt(val.replace(/\D/g, '')) || 0;
    }

    function checkOverDelivery() {
        const availableQty = getAvailableQty();
        const inputQty = getInputQty();
        const warningDiv = document.getElementById('qty_warning');
        const submitBtn = document.getElementById('submit_btn');

        if (inputQty > availableQty && availableQty > 0) {
            warningDiv.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            warningDiv.style.display = 'none';
            submitBtn.disabled = false;
        }
    }

    // ── Validation ───────────────────────────────────────────────────────────────

    validator
        .addField('#po_id', [
            {
                rule: 'required',
                errorMessage: 'Purchase Order wajib dipilih.',
            },
            {
                validator: (value) => value !== '' && value !== null,
                errorMessage: 'Purchase Order wajib dipilih.',
            },
        ], {
            successMessage: 'Purchase Order valid ✓',
        })

        .addField('#qty_delivered', [
            {
                rule: 'required',
                errorMessage: 'Jumlah yang dikirim wajib diisi.',
            },
            {
                rule: 'number',
                errorMessage: 'Jumlah harus berupa angka.',
            },
            {
                validator: (value) => {
                    const qty = parseInt(value.replace(/\D/g, '')) || 0;
                    return qty > 0;
                },
                errorMessage: 'Jumlah yang dikirim harus lebih dari 0.',
            },
            {
                validator: (value) => {
                    const inputQty = parseInt(value.replace(/\D/g, '')) || 0;
                    const availableQty = getAvailableQty();
                    if (availableQty === 0) return true; // skip if no PO selected yet
                    return inputQty <= availableQty;
                },
                errorMessage: 'Jumlah melebihi sisa kuantitas PO yang tersedia.',
            },
        ], {
            successMessage: 'Jumlah valid ✓',
        })

        .addField('#delivery_time_estimation', [
            {
                rule: 'required',
                errorMessage: 'Estimasi tanggal pengiriman wajib diisi.',
            },
            {
                validator: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime());
                },
                errorMessage: 'Format tanggal tidak valid.',
            },
            {
                validator: (value) => {
                    const input = new Date(value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    return input >= today;
                },
                errorMessage: 'Tanggal pengiriman tidak boleh sebelum hari ini.',
            },
            {
                validator: (value) => {
                    const date = new Date(value);
                    const max = new Date('2100-12-31');
                    return date <= max;
                },
                errorMessage: 'Tanggal pengiriman di luar rentang yang diizinkan.',
            },
        ], {
            successMessage: 'Tanggal pengiriman valid ✓',
        })

        .onSuccess(async (event) => {
            event.preventDefault();

            // Final over-qty guard before submit
            const inputQty = getInputQty();
            const availableQty = getAvailableQty();

            if (inputQty > availableQty && availableQty > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Jumlah Melebihi Batas',
                    text: `Jumlah yang diinput (${inputQty}) melebihi sisa PO (${availableQty}).`,
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#ff3e1d',
                });
                return;
            }

            const confirm = await Swal.fire({
                icon: 'question',
                title: 'Konfirmasi Pengiriman',
                text: 'Apakah data pengiriman sudah benar?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#696cff',
                cancelButtonColor: '#8592a3',
            });

            if (!confirm.isConfirmed) return;

            const btn = document.getElementById('submit_btn');
            const form = document.getElementById('deliveryForm');
            const formData = new FormData(form);

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message ?? 'Data pengiriman berhasil disimpan.',
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#696cff',
                    });

                    window.location.href = data.redirect ?? '/delivery';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message ?? 'Terjadi kesalahan saat menyimpan data pengiriman.',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#ff3e1d',
                    });
                }

            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Jaringan',
                    text: 'Tidak dapat terhubung ke server. Coba lagi.',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#ff3e1d',
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Simpan Pengiriman';
            }
        });

    // ── Event Listeners ───────────────────────────────────────────────────────────

    // Select2 — force revalidation on change
    $('#po_id').on('change', function () {
        validator.revalidateField('#po_id');
        // Revalidate qty too since available qty just changed
        const qty = document.getElementById('qty_delivered').value;
        if (qty) validator.revalidateField('#qty_delivered');
        checkOverDelivery();
    });

    // Numeric only — strip non-digits on input
    document.getElementById('qty_delivered').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
        checkOverDelivery();
        validator.revalidateField('#qty_delivered');
    });
}

if (document.getElementById('delivery-table')) {
    const tableEl = document.getElementById('delivery-table');
    const ajaxUrl = tableEl.getAttribute('data-url');   // FIX #1
    const csrfToken = tableEl.getAttribute('data-csrf'); // FIX #2

    var table = $('#delivery-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: ajaxUrl, // no more {{ route() }}

        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
                className: 'text-center fw-medium text-muted'
            },
            {
                data: 'delivery_no',
                name: 'delivery_no',
                className: 'ps-3'
            },
            {
                data: 'po_tracking',
                name: 'po_tracking',
                className: 'ps-3'
            },
            {
                data: 'qty_delivered',
                name: 'qty_delivered',
                className: 'text-center'
            },
            {
                data: 'delivered_at',
                name: 'delivered_at',
                className: 'ps-3'
            },
            { data: 'status', name: 'status' },
            { data: 'invoiced_status', name: 'invoiced_status' },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center'
            }
        ],

        order: [[4, 'desc']],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],

        drawCallback: function () {
            var elements = document.getElementsByClassName("delivery-timer");
            var tableEl = document.getElementById('delivery-table');
            var csrfToken = tableEl.getAttribute('data-csrf');

            for (let i = 0; i < elements.length; i++) {
                var wrapper = elements[i].closest('.timer-wrapper');
                var target = wrapper ? wrapper.getAttribute('data-target') : null;
                var deliveryId = wrapper ? wrapper.getAttribute('data-id') : null;
                if (!target || !deliveryId) continue;

                (function (el, targetDate, id) {
                    var triggered = false; // prevent multiple requests

                    function updateCountdown() {
                        var now = new Date();
                        var end = new Date(targetDate);

                        if (end <= now) {
                            el.textContent = '0 days 0 hours 0 minutes';

                            if (!triggered) {
                                triggered = true; // lock so it only fires once
                                fetch(`/delivery/${id}/auto-deliver`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                        'X-Requested-With': 'XMLHttpRequest',
                                    },
                                    body: JSON.stringify({ delivery_id: id })
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        table.ajax.reload(null, false); // refresh datatable row
                                    })
                                    .catch(err => {
                                        console.error('Auto deliver failed:', err);
                                    });
                            }
                            return;
                        }

                        var duration = intervalToDuration({ start: now, end: end });
                        el.textContent = formatDuration(duration, {
                            format: ['days', 'hours', 'minutes'],
                            zero: true,
                            delimiter: ' ',
                            locale: idLocale
                        });
                    }

                    updateCountdown();
                    setInterval(updateCountdown, 1000);
                })(elements[i], target, deliveryId);
            }
        }
    });

    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();

        const url = $(this).data('url');
        const deliveryNo = $(this).data('po');

        Swal.fire({
            title: 'Hapus Pengiriman?',
            text: `Hapus Pengiriman: ${deliveryNo}? Tindakan ini tidak dapat dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: csrfToken },
                    dataType: 'json'
                })
                    .done(response => response)
                    .fail(xhr => {
                        const errorMsg = xhr.responseJSON?.message ?? 'Terjadi kesalahan';
                        Swal.fire({
                            title: 'Gagal!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonColor: '#3085d6'
                        });
                    });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value?.success) {
                Swal.fire({
                    title: 'Terhapus!',
                    text: result.value.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
            }
        });
    });
}

if (document.getElementById('invoice-table')) {
    const tableEl = document.getElementById('invoice-table');
    const ajaxUrl = tableEl.getAttribute('data-url');   // FIX #1
    const csrfToken = tableEl.getAttribute('data-csrf'); // FIX #2

    var table = $('#invoice-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: ajaxUrl,

        order: [
            [4, 'asc']
        ],

        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
        },
        {
            data: 'invoice_details',
            name: 'invoice_details'
        },
        {
            data: 'linked_references',
            name: 'linked_references'
        },
        {
            data: 'status_section',
            name: 'status_section',
            className: 'text-end'
        },
        {
            data: 'due_date_timer',
            name: 'due_date_timer',
            className: 'text-center'
        },
        {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            className: 'text-center'
        }
        ],
        pageLength: 10,

        drawCallback: function () {
            // Convert to static array so DOM changes don't affect the loop
            var elements = Array.from(document.getElementsByClassName("invoice-timer"));

            elements.forEach(function (el) {
                var wrapper = el.closest('.timer-wrapper');
                var target = wrapper ? wrapper.getAttribute('data-target') : null;
                if (!target) return;

                (function (el, targetDate) {
                    function updateCountdown() {
                        var now = new Date();
                        var end = new Date(targetDate);
                        var currentWrapper = el.closest('.timer-wrapper');

                        if (!currentWrapper) return;

                        if (end <= now) {
                            currentWrapper.classList.remove('bg-label-info');
                            currentWrapper.classList.add('bg-label-danger');
                            currentWrapper.innerHTML = '<i class="ri-error-warning-line me-1"></i> Sudah Telat!';
                            clearInterval(interval);
                            return;
                        }

                        var duration = intervalToDuration({ start: now, end: end });
                        el.textContent = formatDuration(duration, {
                            format: ['days', 'hours', 'minutes'],
                            zero: true,
                            delimiter: ' ',
                            locale: idLocale
                        });
                    }

                    updateCountdown();
                    var interval = setInterval(updateCountdown, 1000);
                })(el, target);
            });
        }
    });

    $('#invoice-table').on('click', '.btn-delete-ajax', function () {
        const url = $(this).data('url');
        const noPo = $(this).data('po');

        Swal.fire({
            title: 'Hapus Invoice?',
            html: `Invoice terkait <strong>${noPo}</strong> akan dihapus secara permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: csrfToken,
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false,
                        }).then(() => table.ajax.reload(null, false));
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Terjadi kesalahan.'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Dapat Dihapus',
                        text: 'Invoice ini sudah memiliki payment dan tidak bisa dihapus!',
                    });
                }
            });
        });
    });
}

if (document.getElementById('table-users')) {
    const tableEl = document.getElementById('table-users');
    const ajaxUrl = tableEl.getAttribute('data-url');   // FIX #1
    var dt_table = $('#table-users');

    if (dt_table.length) {
        var table = dt_table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: ajaxUrl,
            },
            columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
                className: 'fw-medium'
            },
            {
                data: 'user_name',
                name: 'user_name',
                className: 'fw-medium'
            },
            {
                data: 'role_name',
                name: 'role_name',
                className: 'fw-medium'
            },
            {
                data: 'email',
                name: 'email',
                className: 'fw-medium'
            },
            {
                data: 'is_active',
                name: 'is_active',
                className: 'fw-medium text-center'
            },
            {
                data: 'last_login',
                name: 'last_login',
                className: 'fw-medium text-center'
            },
            {
                data: 'input_date',
                name: 'input_date',
                className: 'fw-medium text-center'
            },
            {
                data: 'actions',
                name: 'actions',
                className: 'fw-medium',
                orderable: false,
                searchable: false
            },
            ],
            order: [
                [5, 'desc']
            ],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
        });

        // ── Delete Handler ─────────────────────────────────────────
        $(document).on('click', '.btn-delete', function () {
            const deleteUrl = $(this).data('url');
            const poNo = $(this).data('po');

            Swal.fire({
                title: 'Hapus User?',
                text: `Apakah Anda yakin ingin menghapus User ini?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e6381a',
                cancelButtonColor: '#6e7d88',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: deleteUrl,
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            // FIX: read from meta tag since there's no @csrf form on the index page
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    }).catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error.responseJSON?.message ?? error.statusText}`
                        );
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Dihapus!',
                        text: 'Data User berhasil dihapus.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    table.ajax.reload(null, false);
                }
            });
        });
    }
}

if (document.getElementById('table-incoming')) {
    const tableEl = document.getElementById('table-incoming');
    const ajaxUrl = tableEl.getAttribute('data-url');   // FIX #1

    // ── CountUp Stats ─────────────────────────────────────────────
    function updateCardStats() {
        const moneyOpts = {
            startVal: 0,
            prefix: 'Rp ',
            separator: '.',
            decimal: ',',
            duration: 3
        };
        const numOpts = {
            startVal: 0,
            duration: 3
        };

        const statsConfig = [{
            id: 'card-incoming',
            key: 'incoming',
            opts: numOpts
        },
        {
            id: 'card-price',
            key: 'price',
            opts: moneyOpts
        },
        {
            id: 'card-capital',
            key: 'capital',
            opts: moneyOpts
        },
        {
            id: 'card-margin',
            key: 'margin',
            opts: moneyOpts
        },
        ];

        $.getJSON('/api/incomingPo-stats')
            .done(data => {
                statsConfig.forEach(({
                    id,
                    key,
                    opts
                }) => {
                    const anim = new CountUp(id, data[key] || 0, opts);
                    if (!anim.error) anim.start();
                    else console.error(`CountUp error for ${id}:`, anim.error);
                });
            })
            .fail(err => console.error('Failed to fetch stats:', err));
    }

    updateCardStats();

    // ── Rupiah Formatter ──────────────────────────────────────────
    function rupiah(val) {
        return 'Rp ' + parseFloat(val || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // ── DataTable Init ────────────────────────────────────────────
    var dt_table = $('#table-incoming');

    if (dt_table.length) {
        var table = dt_table.DataTable({
            processing: true,
            serverSide: true,

            // dataSrc callback to capture totals from server response
            ajax: {
                url: ajaxUrl,
                dataSrc: function (json) {
                    // Expects server to return: { data: [...], totals: { qty, total, modal_awal, margin } }
                    if (json.totals) {
                        $('#ft-qty').text(Number(json.totals.qty || 0).toLocaleString('id-ID'));
                        $('#ft-total').text(rupiah(json.totals.total));
                        $('#ft-modal').text(rupiah(json.totals.modal_awal));
                        $('#ft-margin').text(rupiah(json.totals.margin));
                    }
                    return json.data;
                }
            },

            columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
                className: 'text-center fw-medium'
            },
            {
                data: 'detail_po',
                name: 'detail_po',
                className: 'text-center fw-medium'
            },
            {
                data: 'tgl_po',
                name: 'tgl_po',
                className: 'text-center fw-medium'
            },
            {
                data: 'price_references',
                name: 'price_references',
                className: 'text-center fw-medium'
            },
            {
                data: 'modal_awal',
                name: 'modal_awal',
                className: 'text-center fw-medium'
            },
            {
                data: 'margin_references',
                name: 'margin_references',
                className: 'text-center fw-medium'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center fw-medium'
            }
            ],

            order: [
                [2, 'desc']
            ],

            pageLength: 10, // Fix: original used wrong key "displayLength"
        });

        // ── Delete Handler ─────────────────────────────────────────
        $(document).on('click', '.btn-delete-ajax', function () {
            const deleteUrl = $(this).data('url');
            const poNo = $(this).data('po');

            Swal.fire({
                title: 'Hapus Data?',
                text: `Apakah Anda yakin ingin menghapus PO #${poNo}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: deleteUrl,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: document.querySelector('meta[name="csrf-token"]').content
                        },
                        error: function (xhr) {
                            const msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan.';
                            Swal.showValidationMessage(`Request failed: ${msg}`);
                        }
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Terhapus!', result.value.message, 'success');
                    table.ajax.reload(null, false);
                    updateCardStats();
                }
            });
        });
    }
}

if (document.getElementById('table-po')) {
    const tableEl = document.getElementById('table-po');
    const ajaxUrl = tableEl.getAttribute('data-url');   // FIX #1

    function updateCardStats() {
        const moneyOpts = {
            startVal: 0,
            duration: 3,
            prefix: 'Rp ',
            separator: '.',
            decimal: ','
        };
        const numOpts = {
            startVal: 0,
            duration: 3
        };

        const statsMap = [{
            id: 'card-incoming',
            key: 'incoming',
            opts: numOpts
        },
        {
            id: 'card-price',
            key: 'price',
            opts: moneyOpts
        },
        {
            id: 'card-capital',
            key: 'capital',
            opts: moneyOpts
        },
        {
            id: 'card-margin',
            key: 'margin',
            opts: moneyOpts
        },
        ];

        $.getJSON('/api/po-stats')
            .done(data => {
                statsMap.forEach(({
                    id,
                    key,
                    opts
                }) => {
                    const anim = new CountUp(id, data[key] || 0, opts);
                    if (!anim.error) anim.start();
                    else console.warn(`CountUp error for ${id}:`, anim.error);
                });
            })
            .fail(err => console.error('Failed to fetch PO stats:', err));
    }

    updateCardStats();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function rupiah(val) {
        return 'Rp ' + parseFloat(val || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    var dt_table = $('#table-po');

    if (dt_table.length) {
        var table = dt_table.DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: ajaxUrl,
                dataSrc: function (json) {
                    if (json.totals) {
                        $('#ft-qty').text(Number(json.totals.qty || 0).toLocaleString('id-ID'));
                        $('#ft-total').text(rupiah(json.totals.total));
                        $('#ft-modal').text(rupiah(json.totals.modal_awal));
                        $('#ft-margin').text(rupiah(json.totals.margin));
                    }
                    return json.data;
                }
            },
            columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
                className: 'fw-medium'
            },
            {
                data: 'detail_po',
                name: 'detail_po',
                className: 'fw-medium'
            },
            {
                data: 'tgl_po',
                name: 'tgl_po',
                className: 'fw-medium text-center'
            },
            {
                data: 'qty',
                name: 'qty',
                className: 'fw-medium text-center'
            },
            {
                data: 'price_references',
                name: 'price_references',
                className: 'fw-medium'
            },
            {
                data: 'modal_awal',
                name: 'modal_awal',
                className: 'fw-medium'
            },
            {
                data: 'margin_references',
                name: 'margin_references',
                className: 'fw-medium'
            },
            {
                data: 'status_badge',
                name: 'status',
                className: 'fw-medium text-center'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'fw-medium'
            }
            ],
            order: [
                [2, 'desc']
            ],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],

            responsive: {
                details: {
                    type: 'column',
                    target: 0,
                    display: $.fn.dataTable.Responsive.display.childRow,
                    renderer: function (api, rowIdx, columns) {
                        let hidden = columns.filter(col => col.hidden);
                        if (!hidden.length) return false;
                        return $('<span class="dt-child-placeholder"></span>');
                    }
                }
            },

            drawCallback: function () {
                // Destroy existing tooltips first to avoid duplicates on redraw
                dt_table.find('.dt-type-numeric').each(function () {
                    let existing = bootstrap.Tooltip.getInstance(this);
                    if (existing) existing.dispose();
                });

                // ✅ Now init fresh tooltips on newly rendered cells
                dt_table.find('.dt-type-numeric').each(function () {
                    $(this).attr('data-bs-toggle', 'tooltip');
                    $(this).attr('data-bs-placement', 'left');
                    $(this).attr('title', 'Klik untuk melihat lebih banyak');
                    new bootstrap.Tooltip(this);
                });
            },
        });

        table.on('responsive-display', function (e, datatable, row, showHide) {
            if (!showHide) return;

            let rowIdx = row.index();
            let rowData = row.data();
            let columns = datatable.responsive.hasHidden()
                ? datatable.columns().responsiveHidden()
                : [];

            let marginData = rowData.margin_references || '—';
            let statusData = rowData.status_badge || '—';
            let actionData = rowData.action || '—';

            let $childTr = $(row.node()).next('tr.child');

            $childTr.html(`
                <td colspan="9" style="padding: 0 !important; border: none !important;">
                    <table class="w-100" style="table-layout: fixed;">
                        <thead>
                            <tr class="table-light">
                                <th class="text-center" colspan="2">Referensi Margin PO</th>
                                <th class="text-center">Status PO</th>
                                <th class="text-center" colspan="3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-medium" colspan="2">${marginData}</td>
                                <td class="fw-medium text-center">${statusData}</td>
                                <td class="fw-medium text-center" colspan="3">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        ${actionData}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            `);
        });
        // ── Delete Handler ─────────────────────────────────────────
        $(document).on('click', '.btn-delete', function () {
            const deleteUrl = $(this).data('url');
            const poNo = $(this).data('po');

            Swal.fire({
                title: 'Hapus PO?',
                text: `Apakah Anda yakin ingin menghapus PO #${poNo}? Data yang sudah ada pengirimannya tidak bisa dihapus.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e6381a',
                cancelButtonColor: '#6e7d88',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: deleteUrl,
                        type: 'DELETE',
                    }).catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error.responseJSON?.message ?? error.statusText}`
                        );
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Dihapus!',
                        text: 'Data PO berhasil dihapus.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    table.ajax.reload(null, false);
                    updateCardStats();
                }
            });
        });
    }
}

if (document.getElementById('paymentTable')) {
    const tableEl = document.getElementById('paymentTable');
    const ajaxUrl = tableEl.getAttribute('data-url');   // FIX #1

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var table = $('#paymentTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: ajaxUrl,
        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false,
            className: 'col-no'
        },
        {
            data: 'referensi',
            name: 'referensi',
            className: 'col-ref ps-4'
        },
        {
            data: 'amount',
            name: 'amount',
            className: 'col-money'
        },
        {
            data: 'payment_date_estimation',
            name: 'payment_date_estimation',
            className: 'text-center'
        },
        {
            data: 'tanggal_metode',
            name: 'payment_date',
            className: 'ps-5'
        },
        {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            className: 'text-center'
        }
        ],
        order: [
            [4, 'desc']
        ],
        drawCallback: function () {
            var elements = Array.from(document.getElementsByClassName("payment-timer"));

            elements.forEach(function (el) {
                var wrapper = el.closest('.timer-wrapper');
                var target = wrapper ? wrapper.getAttribute('data-target') : null;
                if (!target) return;

                (function (el, targetDate) {
                    function formatCountdown(duration, suffix) {
                        var parts = [];
                        if (duration.days) parts.push(duration.days + ' Hari');
                        if (duration.hours) parts.push(duration.hours + ' Jam');
                        if (duration.minutes) parts.push(duration.minutes + ' Menit');
                        if (parts.length === 0) parts.push('0 Menit');
                        return parts.join(' ') + ' ' + suffix;
                    }

                    function updateCountdown() {
                        var now = new Date();
                        var end = new Date(targetDate);
                        var wrapper = el.closest('.timer-wrapper');
                        if (!wrapper) return;

                        var badgeEl = wrapper.querySelector('.timer-badge');
                        var labelEl = wrapper.querySelector('.small');
                        if (!badgeEl || !labelEl) return;

                        var isPast = now >= end;
                        var duration = intervalToDuration(
                            isPast ? { start: end, end: now } : { start: now, end: end }
                        );

                        if (isPast) {
                            badgeEl.classList.remove('bg-label-warning');
                            badgeEl.classList.add('bg-danger');
                            el.classList.remove('text-warning');
                            el.classList.add('text-white');
                            el.textContent = formatCountdown(duration, 'yang lalu');

                            labelEl.classList.remove('text-muted');
                            labelEl.classList.add('text-danger', 'fw-bold');
                            labelEl.textContent = 'Sudah Telat';

                            clearInterval(interval);
                            return;
                        }

                        badgeEl.classList.remove('bg-danger');
                        badgeEl.classList.add('bg-label-warning');
                        el.classList.remove('text-white');
                        el.classList.add('text-warning');
                        el.textContent = formatCountdown(duration, 'lagi');

                        labelEl.classList.remove('text-danger', 'fw-bold');
                        labelEl.classList.add('text-muted');
                        labelEl.textContent = 'Sedang menunggu pembayaran';
                    }

                    updateCountdown();
                    var interval = setInterval(updateCountdown, 60000);
                })(el, target);
            });
        }
    });

    $('#paymentTable tbody').on('click', '.btn-delete-ajax', function () {
        var deleteUrl = $(this).data('url'); // e.g. /payment/5
        var $btn = $(this);

        Swal.fire({
            title: 'Hapus Pembayaran?',
            text: 'Data pembayaran ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff3e1d',
            cancelButtonColor: '#8592a3',
            confirmButtonText: '<i class="ri-delete-bin-line me-1"></i> Ya, Hapus!',
            cancelButtonText: 'Batal',
            focusCancel: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;

            // Show loading state on the button
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                success: function (response) {
                    Swal.fire({
                        title: 'Terhapus!',
                        text: response.message ?? 'Pembayaran berhasil dihapus.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                    });

                    // Reload DataTable to reflect the deletion
                    table.ajax.reload(null, false);
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan. Coba lagi.';
                    Swal.fire({
                        title: 'Gagal!',
                        text: msg,
                        icon: 'error',
                        confirmButtonColor: '#696cff',
                    });

                    // Restore button on failure
                    $btn.prop('disabled', false).html('<i class="ri-delete-bin-line"></i>');
                }
            });
        });
    });
}

if (document.getElementById('investment-table')) {
    const tableEl = document.getElementById('investment-table');
    const ajaxUrl = tableEl.getAttribute('data-url');   // FIX #1

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    // ── HELPERS ───────────────────────────────────────────────
    function toNum(str) {
        return parseFloat(String(str).replace(/<[^>]*>/g, '').replace(/[^0-9.\-]/g, '')) || 0;
    }

    function formatRp(val) {
        return 'Rp ' + Math.round(val).toLocaleString('id-ID');
    }

    // ── COUNTUP STAT CARDS ────────────────────────────────────
    // Same pattern as po-index.blade.php — hits /api/investasi-stats
    function updateCardStats() {
        const moneyOpts = {
            startVal: 0,
            duration: 2.5,
            prefix: 'Rp ',
            separator: '.',
            decimal: ','
        };

        const statsMap = [{
            id: 'inv-card-margin',
            key: 'totalMargin'
        },
        {
            id: 'inv-card-modal-setor',
            key: 'totalModalSetor'
        },
        {
            id: 'inv-card-modal-po',
            key: 'totalModalPoBaru'
        },
        {
            id: 'inv-card-penarikan',
            key: 'totalPenarikan'
        },
        {
            id: 'inv-card-dana',
            key: 'danaTersedia'
        },
        ];

        // Remove skeleton shimmer before animating
        statsMap.forEach(function ({
            id
        }) {
            document.getElementById(id)?.classList.remove('loading');
        });

        $.getJSON('/api/investasi-stats')
            .done(function (data) {
                statsMap.forEach(function ({
                    id,
                    key
                }) {
                    var val = parseFloat(data[key] || 0);
                    var anim = new CountUp(id, val, moneyOpts);
                    if (!anim.error) anim.start();
                    else console.warn('CountUp error [' + id + ']:', anim.error);
                });
            })
            .fail(function (err) {
                console.error('Failed to fetch investasi stats:', err);
                statsMap.forEach(function ({
                    id
                }) {
                    var el = document.getElementById(id);
                    if (el) el.textContent = '—';
                });
            });
    }

    // Fire on page load
    updateCardStats();

    // ── DATATABLE INIT ────────────────────────────────────────
    var table = $('#investment-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: ajaxUrl,
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Data',
                    text: xhr.responseJSON?.message ?? 'Terjadi kesalahan saat mengambil data.',
                    confirmButtonColor: '#696cff'
                });
            }
        },
        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false,
            className: 'text-center',
        },
        {
            data: 'tgl_investasi',
            name: 'tgl_investasi',
            orderable: true,
            searchable: true,
            className: 'text-center',
        },
        {
            data: 'modal_setor_awal',
            name: 'modal_setor_awal',
            className: 'money-cell money-positive',
            orderable: true
        },
        {
            data: 'modal_po_baru',
            name: 'modal_po_baru',
            className: 'money-cell',
            orderable: true
        },
        {
            data: 'margin',
            name: 'margin',
            className: 'money-cell money-positive',
            orderable: true
        },
        {
            data: 'pencairan_modal',
            name: 'pencairan_modal',
            className: 'money-cell',
            orderable: true
        },
        {
            data: 'margin_cair',
            name: 'margin_cair',
            className: 'money-cell money-negative',
            orderable: true
        },
        {
            data: 'pengembalian_dana',
            name: 'pengembalian_dana',
            className: 'money-cell fw-bold',
            orderable: true
        },
        {
            data: 'dana_tersedia',
            name: 'dana_tersedia',
            className: 'money-cell fw-bold',
            orderable: true
        },
        {
            data: 'action',
            name: 'action',
            className: 'text-center'
        }
        ],
        order: [
            [1, 'desc']
        ],
    });
    $('#investment-table tbody').on('click', '.btn-delete-inv', function () {
        var url = $(this).data('url');
        var name = $(this).data('name') || 'record ini';
        var $btn = $(this);

        Swal.fire({
            title: 'Hapus Investasi?',
            html: 'Data <strong>' + name + '</strong> akan dihapus permanen.<br>Tindakan ini <u>tidak dapat dibatalkan</u>.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff3e1d',
            cancelButtonColor: '#8592a3',
            confirmButtonText: '<i class="ri-delete-bin-line me-1"></i>Ya, Hapus!',
            cancelButtonText: 'Batal',
            focusCancel: true,
            customClass: {
                confirmButton: 'btn btn-danger px-4',
                cancelButton: 'btn btn-secondary px-4 ms-2'
            },
            buttonsStyling: false
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: url,
                type: 'DELETE',
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: res.message ?? 'Data berhasil dihapus.',
                        timer: 1800,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                    table.ajax.reload(null, false);
                    updateCardStats(); // Re-animate after delete
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menghapus!',
                        text: xhr.responseJSON?.message ?? 'Terjadi kesalahan.',
                        confirmButtonColor: '#696cff'
                    });
                    $btn.prop('disabled', false).html('<i class="ri-delete-bin-line"></i>');
                }
            });
        });
    });

    // ── Delete Handler ─────────────────────────────────────────
    $(document).on('click', '.btn-delete', function () {
        const deleteUrl = $(this).data('url');

        Swal.fire({
            title: 'Hapus Investasi?',
            text: `Apakah Anda yakin ingin menghapus investasi ini?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e6381a',
            cancelButtonColor: '#6e7d88',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    data: {
                        _token: document.querySelector('meta[name="csrf-token"]').content
                    }
                }).catch(error => {
                    Swal.showValidationMessage(
                        `Request failed: ${error.responseJSON?.message ?? error.statusText}`
                    );
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Dihapus!',
                    text: 'Data Investasi berhasil dihapus.',
                    timer: 1500,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
                updateCardStats();
            }
        });
    });
}

if (document.getElementById('customerTable')) {
    const tableEl = document.getElementById('customerTable');
    const ajaxUrl = tableEl.getAttribute('data-url');
    const ajaxDeleteUrl = tableEl.getAttribute('data-url-delete');

    var table = $('#customerTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: ajaxUrl,
        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false,
            className: 'text-center'
        },
        {
            data: 'cust_name',
            name: 'cust_name',
        },
        {
            data: 'input_date',
            name: 'input_date',
            render: function (data, type, row) {
                return '<div class="d-flex flex-column">' +
                    '<span class="fw-medium text-dark">' +
                    '<i class="ri-calendar-event-line me-1 text-success"></i>' +
                    data +
                    '</span>' +
                    '</div>';
            }
        },
        {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            className: 'text-center'
        }
        ],
        order: [
            [2, 'desc']
        ],
    });

    // Fungsi global untuk delete (dipanggil dari tombol dropdown)
    window.deleteCustomer = function (id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data customer akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: ajaxDeleteUrl.replace(':id', id),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire(
                                'Terhapus!',
                                response.message,
                                'success'
                            );
                            table.ajax.reload(); // reload data
                        } else {
                            Swal.fire('Gagal!', 'Terjadi kesalahan.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Gagal!', 'Terjadi kesalahan server.', 'error');
                    }
                });
            }
        });
    };
}

if (baseUrl === "/dashboard") {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });

    if (document.getElementById("dana-tersedia-card")) {
        document.getElementById("dana-tersedia-card").addEventListener("click", function () {
            window.location.href = "dashboard/dana-tersedia";
        });
    }

    const rupiah = (val) =>
        'Rp ' + Number(val).toLocaleString('id-ID', {
            maximumFractionDigits: 0
        });

    const setText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };

    const showRow = (id) => {
        const el = document.getElementById(id);
        if (el) el.style.display = '';
    };

    const hideRow = (id) => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    };

    const statusConfig = {
        2: {
            label: 'Sebagian di Deliver',
        },
        3: {
            label: 'Sepenuhnya di Deliver',
        },
        4: {
            label: 'Sebagian di Deliver dan sebagian di Invoice',
        },
        5: {
            label: 'Sepenuhnya di Deliver dan sebagian di Invoice',
        },
        6: {
            label: 'Sebagian di Deliver dan sepenuhnya di Invoice',
        },
        7: {
            label: 'Semuanya di deliver dan semuanya di Invoice (Menunggu Pembayaran)',
        },
    };

    function renderBreakdown(breakdown) {
        const tbody = document.getElementById('status-breakdown-tbody');
        if (!tbody) return;

        const maxCount = Math.max(1, ...breakdown.map(r => r.count));
        tbody.innerHTML = '';

        breakdown.forEach(row => {
            const cfg = statusConfig[row.status] || {
                label: row.label,
                color: 'secondary'
            };
            const isEmpty = row.count === 0;

            tbody.insertAdjacentHTML('beforeend', `
                <tr class="${isEmpty ? 'opacity-50' : ''}">
                    <td class="text-center">
                        <span class="badge bg-label-${cfg.color} px-2">${row.status}</span>
                    </td>
                    <td class="fw-semibold">${cfg.label}</td>
                    <td class="text-center fw-bold ${isEmpty ? 'text-muted' : ''}">${row.count}</td>
                </tr>
            `);
        });
    }

    function fetchFilteredStats() {
        const startDate = document.getElementById('filter-start-date')?.value;
        const endDate = document.getElementById('filter-end-date')?.value;

        const params = new URLSearchParams();
        if (startDate) params.append('startDate', startDate);
        if (endDate) params.append('endDate', endDate);

        ['f-total-po', 'f-total-nilai-po', 'f-total-modal', 'f-total-margin',
            'f-total-invoice', 'f-invoice-unpaid', 'f-invoice-paid'
        ]
            .forEach(id => setText(id, '…'));

        ['filtered-stats-section', 'filtered-invoice-section', 'filtered-breakdown-section']
            .forEach(showRow);

        $.getJSON(`/api/dashboard-filtered-stats?${params.toString()}`)
            .done(data => {
                setText('f-total-po', data.totalPo.toLocaleString('id-ID'));
                setText('f-total-nilai-po', rupiah(data.totalNilaiPo));
                setText('f-total-modal', rupiah(data.totalModal));
                setText('f-total-margin', rupiah(data.totalMargin));

                setText('f-total-invoice', data.totalInvoice);
                setText('f-invoice-unpaid', data.invoiceUnpaid);
                setText('f-invoice-paid', data.invoicePaid);

                renderBreakdown(data.statusBreakdown);

                // Active filter badge
                const badge = document.getElementById('active-filter-badge');
                const wrap = document.getElementById('active-filter-badge-wrap');
                if (badge && wrap) {
                    const from = data.filter.startDate || 'Awal';
                    const to = data.filter.endDate || 'Hari ini';
                    badge.textContent = `Filter aktif: ${from} → ${to}`;
                    wrap.style.display = 'block';
                }
            })
            .fail(err => {
                console.error('Filtered stats fetch failed:', err);
            });
    }

    document.getElementById('btn-filter-apply')
        ?.addEventListener('click', fetchFilteredStats);

    document.getElementById('btn-filter-reset')
        ?.addEventListener('click', () => {
            document.getElementById('filter-start-date').value = '';
            document.getElementById('filter-end-date').value = '';
            ['filtered-stats-section', 'filtered-invoice-section', 'filtered-breakdown-section']
                .forEach(hideRow);
            document.getElementById('active-filter-badge-wrap').style.display = 'none';
        });

    ['filter-start-date', 'filter-end-date'].forEach(id => {
        document.getElementById(id)
            ?.addEventListener('keydown', e => {
                if (e.key === 'Enter') fetchFilteredStats();
            });
    });

    const numberOptions = {
        startVal: 0,
        duration: 3
    };

    const statsMap = [
        'dana-tersedia',
        'total-dana-ditf',
        'investasi-dikembalikan',
        'total-tf-investasi',
        'margin-diterima',
        'total-margin',
        'sisa-margin',
        'margin-tersedia',
        'investasi-ditahan',
        'margin-ditahan',
        'totalInvestasiTransfer',
    ];

    function updateCardStats() {
        $.getJSON('/api/dashboard-stats')
            .done(data => {
                statsMap.forEach(id => {
                    const dataKey = id.replace(/-([a-z])/g, g => g[1].toUpperCase());
                    if (data[dataKey] !== undefined) {
                        new CountUp(id, data[dataKey], numberOptions).start();
                    }
                });
            })
            .fail(err => console.error('Failed to fetch dashboard stats:', err));
    }

    updateCardStats();

    if (typeof Echo !== 'undefined') {
        Echo.channel('global-updates')
            .listen('CrudActionOccurred', (e) => {
                updateCardStats();
            });
    }
}