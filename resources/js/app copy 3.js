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
import 'datatables.net-fixedheader'
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

if (document.getElementById("activate-user")) {
    $('.activate-user').on('click', function () {
        const url = $(this).data('url');
        const token = $(this).data('token');

        Swal.fire({
            title: 'Aktifkan Akun?',
            text: 'Akun ini akan diaktifkan kembali dan bisa login.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Aktifkan',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { _token: token },
                    success: function (response) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        Swal.fire({
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message ?? 'Terjadi kesalahan.',
                            icon: 'error',
                            confirmButtonColor: '#d33',
                        });
                    }
                });
            }
        });
    });
}

if (document.getElementById("suspend-user")) {
    $('.suspend-user').on('click', function () {
        const userId = $(this).data('id');
        const url = $(this).data('url');
        const token = $(this).data('token');

        Swal.fire({
            title: 'Nonaktifkan Akun?',
            text: 'Akun ini akan dinonaktifkan dan tidak bisa login.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Nonaktifkan',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { _token: token },
                    success: function (response) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        Swal.fire({
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message ?? 'Terjadi kesalahan.',
                            icon: 'error',
                            confirmButtonColor: '#d33',
                        });
                    }
                });
            }
        });
    });
}

window.onRecaptchaLoad = function () {
    // Manually render the recaptcha widget
    if (document.getElementsByClassName('g-recaptcha').length > 0) {
        grecaptcha.render(document.querySelector('.g-recaptcha'));
    }
};

if (document.getElementById("formAuthentication")) {
    const validation = new JustValidate('#formAuthentication', {
        successFieldCssClass: 'is-valid',
        errorFieldCssClass: 'is-invalid',
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
        validateBeforeSubmitting: true, // ← live validation
    });

    window.onRecaptchaSuccess = function (token) {
        const input = document.getElementById('g-recaptcha-response');
        if (input) {
            input.value = token;
            validation.revalidateField('#g-recaptcha-response');
        }
    };

    window.onRecaptchaExpired = function () {
        const input = document.getElementById('g-recaptcha-response');
        if (input) {
            input.value = '';
            validation.revalidateField('#g-recaptcha-response');
        }
    };

    const rememberCheckbox = document.getElementById('form2Example3');

    rememberCheckbox.addEventListener('change', function () {
        this.value = this.checked ? '1' : '0';
    });

    function clearValidStates() {
        ['#user_name', '#password-input', '#g-recaptcha-response'].forEach(selector => {
            const el = document.querySelector(selector);
            if (el) {
                el.classList.remove('is-valid');
                el.classList.add('is-invalid');
            }
        });
    }

    function resetRecaptcha() {
        if (typeof grecaptcha !== 'undefined' && typeof grecaptcha.reset === 'function') {
            grecaptcha.reset();
        }
        document.getElementById('g-recaptcha-response').value = '';
        validation.revalidateField('#g-recaptcha-response');
    }

    validation
        .addField('#user_name', [
            { rule: 'required', errorMessage: 'Isi Username' },
            { rule: 'minLength', value: 3, errorMessage: 'Minimal 3 Karakter' },
        ], {
            successMessage: 'Username sudah benar',
        })
        .addField('#password-input', [
            { rule: 'required', errorMessage: 'Isi Password' },
            { rule: 'minLength', value: 6, errorMessage: 'Password setidaknya 8 karakter' },
        ], {
            errorsContainer: '#password-error',
            errorLabelCssClass: 'password-error-msg',
            successMessage: 'Password sudah benar',
        })
        .addField('#g-recaptcha-response', [
            {
                rule: 'required',
                errorMessage: 'Selesaikan verifikasi reCAPTCHA.',
            },
            {
                rule: 'custom',
                validator: (value) => {
                    if (typeof grecaptcha === 'undefined') return false;

                    try {
                        const response = grecaptcha.getResponse();
                        return response.length > 0;
                    } catch (e) {
                        return false;
                    }
                },
                errorMessage: 'Selesaikan verifikasi reCAPTCHA.',
            }
        ], {
            successMessage: 'Verifikasi berhasil!'
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
                            text: data.message ?? 'Login berhasil.',
                            confirmButtonColor: '#696cff'
                        }).then(() => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                document.getElementById('formAuthentication').reset();
                                resetRecaptcha();
                                validateInputs.forEach(el => el.classList.remove('is-valid', 'is-invalid'));
                                clearValidStates();
                            }
                        });
                    } else if (response.status === 422) {
                        const errors = data.errors ?? {};

                        // ← show controller errors directly on JustValidate fields
                        const fieldMap = {
                            'user_name': '#user_name',
                            'password': '#password-input',
                            'g-recaptcha-response': '#g-recaptcha-response',
                            'recaptcha_token': '#g-recaptcha-response',
                        };

                        const justValidateErrors = {};
                        for (const field in errors) {
                            if (fieldMap[field]) {
                                justValidateErrors[fieldMap[field]] = errors[field][0];
                            }
                        }

                        if (Object.keys(justValidateErrors).length > 0) {
                            validation.showErrors(justValidateErrors);
                        } else {
                            // fallback swal for errors not tied to a field
                            const messages = Object.values(errors).flat().join('<br>');
                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Gagal!',
                                html: messages || 'Terjadi kesalahan validasi.',
                                confirmButtonColor: '#d33'
                            });
                        }

                        resetRecaptcha();
                        validation.revalidateField('#g-recaptcha-response');
                        clearValidStates();

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan!',
                            text: data.message ?? 'Server error. Coba lagi nanti.',
                            confirmButtonColor: '#d33'
                        });

                        resetRecaptcha();
                        clearValidStates();
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Gagal!',
                        text: 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.',
                        confirmButtonColor: '#d33'
                    });

                    resetRecaptcha();
                    clearValidStates();
                })
                .finally(() => {
                    btnSave.disabled = false;
                    btnSpinner.classList.add('d-none');
                });
        });
}

if (document.getElementById("create-user")) {
    const validation = new JustValidate('#create-user', {
        errorFieldCssClass: 'is-invalid',
        successFieldCssClass: 'is-valid',
        errorLabelStyle: {},
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
        validateBeforeSubmitting: true,
        focusInvalidField: true,
    });

    validation
        .addField('#user_name', [
            {
                rule: 'required',
                errorMessage: 'Username wajib diisi.',
            },
            {
                rule: 'customRegexp',
                value: /^[a-zA-Z0-9_-]{3,16}$/,
                errorMessage: 'Username harus 3–16 karakter (huruf, angka, _ atau -).',
            },
        ], {
            successMessage: 'Username valid ✓',
            validateOptions: { on: 'input' },
        })

        .addField('#role-pengguna', [
            {
                rule: 'required',
                errorMessage: 'Role pengguna wajib dipilih.',
            },
            {
                validator: (value) => value !== '' && value !== null,
                errorMessage: 'Role pengguna wajib dipilih.',
            },
        ], {
            successMessage: 'Role valid ✓',
            validateOptions: { on: 'change' },
        })

        .addField('#email', [
            {
                rule: 'required',
                errorMessage: 'Email wajib diisi.',
            },
            {
                rule: 'email',
                errorMessage: 'Format email tidak valid.',
            },
        ], {
            successMessage: 'Email valid ✓',
            validateOptions: { on: 'input' },
        })

        .addField('#profile_picture', [
            {
                rule: 'files',
                value: {
                    files: {
                        extensions: ['jpg', 'jpeg', 'png', 'webp'],
                        maxSize: 2 * 1024 * 1024,
                        type: ['image/jpeg', 'image/png', 'image/webp'],
                    },
                },
                errorMessage: 'File harus berformat JPG/PNG/WEBP dan maksimal 2MB.',
            },
        ], {
            successMessage: 'File valid ✓',
            validateOptions: { on: 'change' },
        })

        .addField('#password-input', [
            {
                rule: 'required',
                errorMessage: 'Password wajib diisi.',
            },
            {
                rule: 'minLength',
                value: 8,
                errorMessage: 'Password minimal 8 karakter.',
            },
            {
                rule: 'customRegexp',
                value: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/,
                errorMessage: 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
            },
        ], {
            successMessage: 'Password valid ✓',
            errorsContainer: '#password-error',
            validateOptions: { on: 'input' },
        })
        .onSuccess((event) => {
            const form = event.target;
            const formData = new FormData(form);
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
                        await Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message ?? 'Pengguna berhasil dibuat.',
                            confirmButtonColor: '#696cff'
                        });

                        // ── always redirect to users.index on success ──
                        window.location.href = '/users';

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
        const selected = $(this).find(':selected');
        const total = selected.data('total') || 0;
        const display = document.getElementById('amount_display');
        display.value = total > 0 ? formatRupiah(total) : '';
        display.dataset.raw = total;
        validator.revalidateField('#amount_display');
    });

    // ── formatRupiah (WAS MISSING, YOU DUMBASS FORGOT IT) ─────
    function formatRupiah(value) {
        return Number(value).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        });
    }

    // ── Checkbox Toggle: 0 ↔ 1 ───────────────────────────────
    const payNow = document.getElementById('pay_now');
    payNow.addEventListener('change', function () {
        this.value = this.checked ? '1' : '0';
    });

    const validator = new JustValidate('#paymentForm', {
        errorFieldCssClass: 'is-invalid',
        successFieldCssClass: 'is-valid',
        errorLabelStyle: {},
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
        validateBeforeSubmitting: true,
        focusInvalidField: true,
    });

    // ── Revalidate all fields ─────────────────────────────────────────────────────

    const revalidateFields = ['#invoice_id', '#amount_display', '#metode_bayar', '#payment_date_estimation'];

    function revalidateAll() {
        revalidateFields.forEach(function (selector) {
            validator.revalidateField(selector).catch(() => { });
        });
    }

    const watchEvents = ['change', 'input', 'click', 'paste'];

    revalidateFields.forEach(function (selector) {
        const el = document.querySelector(selector);
        if (!el) return;
        watchEvents.forEach(function (event) {
            el.addEventListener(event, revalidateAll);
        });
    });

    // ── Validation ────────────────────────────────────────────────────────────────

    validator
        .addField('#invoice_id', [
            {
                rule: 'required',
                errorMessage: 'Invoice wajib dipilih.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => value !== '' && value !== null && value !== undefined,
                errorMessage: 'Invoice wajib dipilih.',
            },
            {
                rule: 'custom', // ← strict: must be a positive integer
                validator: (value) => Number.isInteger(Number(value)) && Number(value) > 0,
                errorMessage: 'Invoice tidak valid.',
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
                rule: 'custom', // ← fixed
                validator: () => {
                    const raw = document.getElementById('amount_display').dataset.raw;
                    return raw !== undefined && raw !== '' && raw !== '0' && Number(raw) > 0;
                },
                errorMessage: 'Pilih invoice terlebih dahulu.',
            },
            {
                rule: 'custom', // ← strict: must be a finite positive number
                validator: () => {
                    const raw = Number(document.getElementById('amount_display').dataset.raw);
                    return isFinite(raw) && raw > 0;
                },
                errorMessage: 'Jumlah pembayaran harus lebih dari 0.',
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
                rule: 'custom', // ← fixed
                validator: (value) => value !== '' && value !== null && value !== undefined,
                errorMessage: 'Metode pembayaran wajib dipilih.',
            },
            {
                rule: 'custom', // ← strict: must be one of the allowed values
                validator: (value) => {
                    const allowed = ['Tunai', 'Transfer Bank', 'Kartu Kredit', 'Kartu Debit', 'QRIS', 'OVO', 'GoPay', 'DANA', 'LinkAja', 'ShopeePay'];
                    return allowed.includes(value);
                },
                errorMessage: 'Metode pembayaran tidak dikenali.',
            },
        ], {
            successMessage: 'Metode pembayaran valid ✓',
        })

        .addField('#payment_date_estimation', [
            {
                rule: 'required',
                errorMessage: 'Tanggal pembayaran wajib diisi.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime());
                },
                errorMessage: 'Format tanggal tidak valid.',
            },
            {
                rule: 'custom', // ← strict: must match YYYY-MM-DD exactly
                validator: (value) => /^\d{4}-\d{2}-\d{2}$/.test(value),
                errorMessage: 'Format tanggal harus YYYY-MM-DD.',
            },
            {
                rule: 'custom',
                validator: (value) => {
                    const input = new Date(value);
                    const min = new Date('2000-01-01');
                    const max = new Date('2100-12-31');
                    return input >= min && input <= max;
                },
                errorMessage: 'Tanggal pembayaran di luar rentang yang diizinkan.',
            },
            {
                rule: 'custom', // ← strict: cannot be in the past
                validator: (value) => {
                    const input = new Date(value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    return input >= today;
                },
                errorMessage: 'Tanggal pembayaran tidak boleh sebelum hari ini.',
            },
        ], {
            successMessage: 'Tanggal valid ✓',
        })
        .onSuccess(async (event) => {
            event.preventDefault();

            // Trigger select2 fields manually since just-validate doesn't detect them natively
            const invoiceVal = document.getElementById('invoice_id').value;
            const metodeVal = document.getElementById('metode_bayar').value;

            const amountDisplay = document.getElementById('amount_display');
            const rawValue = amountDisplay.dataset.raw || '0';
            amountDisplay.value = rawValue;

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

            const payNow = document.getElementById('pay_now');
            formData.set('pay_now', payNow.checked ? '1' : '0');

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

                    window.location.href = data.redirect ?? '/payments';
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
                amountDisplay.value = formatRupiah(rawValue);
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
    const validation = new JustValidate('#edit-user', {
        errorFieldCssClass: 'is-invalid',
        successFieldCssClass: 'is-valid',
        errorLabelStyle: {},
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
        validateBeforeSubmitting: true,
        focusInvalidField: true,
    });

    validation
        .addField('#user_name', [
            {
                rule: 'required',
                errorMessage: 'Username wajib diisi.',
            },
            {
                rule: 'customRegexp',
                value: /^[a-zA-Z0-9_-]{3,16}$/,
                errorMessage: 'Username harus 3–16 karakter (huruf, angka, _ atau -).',
            },
        ], {
            successMessage: 'Username valid ✓',
            validateOptions: { on: 'input' },
        })

        .addField('#role-pengguna', [
            {
                rule: 'required',
                errorMessage: 'Role pengguna wajib dipilih.',
            },
            {
                validator: (value) => value !== '' && value !== null,
                errorMessage: 'Role pengguna wajib dipilih.',
            },
        ], {
            successMessage: 'Role valid ✓',
            validateOptions: { on: 'change' },
        })

        .addField('#email', [
            {
                rule: 'required',
                errorMessage: 'Email wajib diisi.',
            },
            {
                rule: 'email',
                errorMessage: 'Format email tidak valid.',
            },
        ], {
            successMessage: 'Email valid ✓',
            validateOptions: { on: 'input' },
        })

        .addField('#profile_picture', [
            {
                rule: 'files',
                value: {
                    files: {
                        extensions: ['jpg', 'jpeg', 'png', 'webp'],
                        maxSize: 2 * 1024 * 1024,
                        type: ['image/jpeg', 'image/png', 'image/webp'],
                    },
                },
                errorMessage: 'File harus berformat JPG/PNG/WEBP dan maksimal 2MB.',
            },
        ], {
            successMessage: 'File valid ✓',
            validateOptions: { on: 'change' },
        })

        .addField('#password-input', [
            {
                rule: 'minLength',
                value: 8,
                errorMessage: 'Password minimal 8 karakter.',
                conditionFunction: () => document.getElementById('password-input').value.length > 0,
            },
            {
                rule: 'customRegexp',
                value: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/,
                errorMessage: 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
                conditionFunction: () => document.getElementById('password-input').value.length > 0,
            },
        ], {
            successMessage: 'Password valid ✓',
            errorsContainer: '#password-error',
            validateOptions: { on: 'input' },
        })
        .onSuccess((event) => {
            const form = event.target;
            const formData = new FormData(form);
            const btnSave = document.getElementById('btnSave');
            const btnSpinner = document.getElementById('btnSpinner');

            btnSave.disabled = true;
            btnSpinner.classList.remove('d-none');

            fetch(form.action, {
                method: 'POST', // PUT via FormData needs _method spoofing — keep POST
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
                        await Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message ?? 'Pengguna berhasil diperbarui.',
                            confirmButtonColor: '#696cff'
                        });

                        // ── always redirect to /users on success ──
                        window.location.href = '/users';

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
        focusInvalidField: true,
    });

    // ── Revalidate all fields ─────────────────────────────────────────────────────

    const revalidateFields = [
        '#customer_id',
        '#nama_barang',
        '#tgl_po',
        '#qty',
        '#harga_display',
        '#margin_percentage',
    ];

    function revalidateAll() {
        revalidateFields.forEach(function (selector) {
            validator.revalidateField(selector).catch(() => { });
        });
    }

    const watchEvents = ['change', 'input', 'click', 'paste'];

    revalidateFields.forEach(function (selector) {
        const el = document.querySelector(selector);
        if (!el) return;
        watchEvents.forEach(function (event) {
            el.addEventListener(event, revalidateAll);
        });
    });

    // ── Validation ────────────────────────────────────────────────────────────────

    validator
        .addField('#customer_id', [
            {
                rule: 'required',
                errorMessage: 'Pelanggan wajib dipilih.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => value !== '' && value !== null && value !== undefined,
                errorMessage: 'Pelanggan wajib dipilih.',
            },
            {
                rule: 'custom', // ← strict: must be a positive integer ID
                validator: (value) => Number.isInteger(Number(value)) && Number(value) > 0,
                errorMessage: 'Pelanggan tidak valid.',
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
                rule: 'custom', // ← fixed
                validator: (value) => value.trim().length >= 2,
                errorMessage: 'Nama barang minimal 2 karakter.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => value.trim().length <= 255,
                errorMessage: 'Nama barang maksimal 255 karakter.',
            },
            {
                rule: 'custom', // ← strict: no special characters that could cause injection
                validator: (value) => /^[a-zA-Z0-9\s\-.,()\/]+$/.test(value.trim()),
                errorMessage: 'Nama barang mengandung karakter yang tidak diizinkan.',
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
                rule: 'custom', // ← fixed
                validator: (value) => /^\d{4}-\d{2}-\d{2}$/.test(value),
                errorMessage: 'Format tanggal PO harus YYYY-MM-DD.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => !isNaN(new Date(value).getTime()),
                errorMessage: 'Format tanggal PO tidak valid.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => {
                    const date = new Date(value);
                    return date >= new Date('2000-01-01') && date <= new Date('2100-12-31');
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
                rule: 'custom', // ← fixed
                validator: (value) => {
                    const qty = parseInt(value.replace(/\D/g, '')) || 0;
                    return qty > 0;
                },
                errorMessage: 'Jumlah harus lebih dari 0.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => {
                    const qty = parseInt(value.replace(/\D/g, '')) || 0;
                    return qty <= 999;
                },
                errorMessage: 'Jumlah maksimal 999.',
            },
            {
                rule: 'custom', // ← strict: must be a whole number, no decimals
                validator: (value) => /^\d+$/.test(value.replace(/\D/g, '')),
                errorMessage: 'Jumlah harus berupa bilangan bulat.',
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
                rule: 'custom', // ← fixed
                validator: () => {
                    const raw = getRaw('harga');
                    return raw > 0;
                },
                errorMessage: 'Harga per unit harus lebih dari Rp 0.',
            },
            {
                rule: 'custom', // ← fixed
                validator: () => {
                    const raw = getRaw('harga');
                    return isFinite(raw) && raw.toString().replace('.', '').length <= 16;
                },
                errorMessage: 'Harga per unit melebihi batas maksimum.',
            },
            {
                rule: 'custom', // ← strict: must be a positive finite number
                validator: () => {
                    const raw = getRaw('harga');
                    return typeof raw === 'number' && isFinite(raw) && raw > 0;
                },
                errorMessage: 'Harga per unit tidak valid.',
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
                rule: 'custom', // ← fixed
                validator: (value) => {
                    const margin = parseInt(value.replace(/\D/g, '')) || 0;
                    return margin >= 1;
                },
                errorMessage: 'Margin harus lebih dari 0%.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => {
                    const margin = parseInt(value.replace(/\D/g, '')) || 0;
                    return margin <= 99;
                },
                errorMessage: 'Margin tidak boleh lebih dari 99%.',
            },
            {
                rule: 'custom', // ← strict: whole number only, no decimals
                validator: (value) => /^\d+$/.test(value.replace(/\D/g, '')),
                errorMessage: 'Margin harus berupa bilangan bulat.',
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

                    window.location.href = data.redirect ?? '/incoming-purchase-orders';

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

if (document.getElementById("customerForm")) {
    const form = document.getElementById('customerForm');
    const btnSave = document.getElementById('btnSave');
    const btnSpinner = document.getElementById('btnSpinner');

    const validation = new JustValidate('#customerForm', {
        errorFieldCssClass: 'is-invalid',
        successFieldCssClass: 'is-valid',
        errorLabelStyle: {},
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
        validateBeforeSubmitting: true,
        focusInvalidField: true,
    });

    validation
        .addField('#cust_name', [
            {
                rule: 'required',
                errorMessage: 'Nama customer wajib diisi.',
            },
            {
                rule: 'minLength',
                value: 3,
                errorMessage: 'Nama customer minimal 3 karakter.',
            },
            {
                rule: 'maxLength',
                value: 100,
                errorMessage: 'Nama customer maksimal 100 karakter.',
            },
        ], {
            successMessage: 'Customer valid ✓',
        })
        .onSuccess(function () {
            // Show spinner, disable button
            btnSpinner.classList.remove('d-none');
            btnSave.disabled = true;

            const formData = new FormData(form);
            const method = form.dataset.method ?? 'POST';

            if (method === 'PUT') {
                formData.append('_method', 'PUT');
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message ?? 'Customer berhasil disimpan.',
                            timer: 1500,
                            showConfirmButton: false,
                        }).then(() => {
                            window.location.href = data.redirect ?? '/customers';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message ?? 'Terjadi kesalahan.',
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan server. Coba lagi.',
                    });
                })
                .finally(() => {
                    btnSpinner.classList.add('d-none');
                    btnSave.disabled = false;
                });
        });
}

if (document.getElementById("createPoForm")) {
    // --- Initialize Select2 ---
    $('#incoming_po_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: '-- Pilih Incoming PO --',
        allowClear: true
    });

    $('#customer_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: '-- Pilih Pelanggan --',
        allowClear: true
    });

    const incomingDetailsUrl = document.getElementById('createPoForm-meta')
        .getAttribute('data-incoming-details-url');

    // ── Helpers ───────────────────────────────────────────────────────────────────

    function formatIDR(amount) {
        if (!amount && amount !== 0) return '';
        let val = Math.floor(amount).toString().replace(/\./g, '');
        return val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function getRaw(id) {
        return parseInt(document.getElementById(id)?.value?.replace(/\D/g, '') || 0);
    }

    const validator = new JustValidate('#createPoForm', {
        errorFieldCssClass: 'is-invalid',
        successFieldCssClass: 'is-valid',
        errorLabelStyle: {},
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
        validateBeforeSubmitting: true,
        focusInvalidField: true,
    });

    // ── Revalidate all fields ─────────────────────────────────────────────────────

    const revalidateFields = [
        '#incoming_po_id',
        '#no_po',
        '#customer_id',
        '#nama_barang',
        '#tgl_po',
        '#qty',
        '#harga_display',
        '#margin_percentage',
    ];

    function revalidateAll() {
        revalidateFields.forEach(function (selector) {
            validator.revalidateField(selector).catch(() => { });
        });
    }

    const watchEvents = ['change', 'input', 'click', 'paste'];

    revalidateFields.forEach(function (selector) {
        const el = document.querySelector(selector);
        if (!el) return;
        watchEvents.forEach(function (event) {
            el.addEventListener(event, revalidateAll);
        });
    });

    // ── Validation ────────────────────────────────────────────────────────────────

    validator
        .addField('#incoming_po_id', [
            {
                rule: 'required',
                errorMessage: 'Incoming PO wajib dipilih.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => value !== '' && value !== null && value !== undefined,
                errorMessage: 'Incoming PO wajib dipilih.',
            },
            {
                rule: 'custom', // ← strict: must be a positive integer ID
                validator: (value) => Number.isInteger(Number(value)) && Number(value) > 0,
                errorMessage: 'Incoming PO tidak valid.',
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
                rule: 'custom', // ← fixed
                validator: (value) => value.trim().length >= 3,
                errorMessage: 'Nomor PO minimal 3 karakter.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => value.trim().length <= 10,
                errorMessage: 'Nomor PO maksimal 10 karakter.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => /^[0-9]+$/.test(value.trim()),
                errorMessage: 'Nomor PO hanya boleh berisi angka.',
            },
            {
                rule: 'custom', // ← strict: no leading zeros
                validator: (value) => !/^0/.test(value.trim()),
                errorMessage: 'Nomor PO tidak boleh diawali angka 0.',
            },
        ], {
            successMessage: 'Nomor PO valid ✓',
        })

        .addField('#customer_id', [
            {
                rule: 'required',
                errorMessage: 'Pelanggan wajib dipilih.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => value !== '' && value !== null && value !== undefined,
                errorMessage: 'Pelanggan wajib dipilih.',
            },
            {
                rule: 'custom', // ← strict: must be a positive integer ID
                validator: (value) => Number.isInteger(Number(value)) && Number(value) > 0,
                errorMessage: 'Pelanggan tidak valid.',
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
                rule: 'custom', // ← fixed
                validator: (value) => value.trim().length >= 2,
                errorMessage: 'Nama barang minimal 2 karakter.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => value.trim().length <= 255,
                errorMessage: 'Nama barang maksimal 255 karakter.',
            },
            {
                rule: 'custom', // ← strict: no special characters that could cause injection
                validator: (value) => /^[a-zA-Z0-9\s\-.,()\/]+$/.test(value.trim()),
                errorMessage: 'Nama barang mengandung karakter yang tidak diizinkan.',
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
                rule: 'custom', // ← fixed
                validator: (value) => /^\d{4}-\d{2}-\d{2}$/.test(value),
                errorMessage: 'Format tanggal PO harus YYYY-MM-DD.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => !isNaN(new Date(value).getTime()),
                errorMessage: 'Format tanggal PO tidak valid.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => {
                    const date = new Date(value);
                    return date >= new Date('2000-01-01') && date <= new Date('2100-12-31');
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
                rule: 'custom', // ← fixed
                validator: (value) => {
                    const qty = parseInt(value.replace(/\D/g, '')) || 0;
                    return qty > 0;
                },
                errorMessage: 'Jumlah harus lebih dari 0.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => {
                    const qty = parseInt(value.replace(/\D/g, '')) || 0;
                    return qty <= 999;
                },
                errorMessage: 'Jumlah maksimal 999.',
            },
            {
                rule: 'custom', // ← strict: must be a whole number, no decimals
                validator: (value) => /^\d+$/.test(value.replace(/\D/g, '')),
                errorMessage: 'Jumlah harus berupa bilangan bulat.',
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
                rule: 'custom', // ← fixed
                validator: () => {
                    const raw = getRaw('harga');
                    return raw > 0;
                },
                errorMessage: 'Harga per unit harus lebih dari Rp 0.',
            },
            {
                rule: 'custom', // ← fixed
                validator: () => {
                    const raw = getRaw('harga');
                    return isFinite(raw) && raw.toString().replace('.', '').length <= 16;
                },
                errorMessage: 'Harga per unit melebihi batas maksimum.',
            },
            {
                rule: 'custom', // ← strict: must be a positive finite number
                validator: () => {
                    const raw = getRaw('harga');
                    return typeof raw === 'number' && isFinite(raw) && raw > 0;
                },
                errorMessage: 'Harga per unit tidak valid.',
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
                rule: 'custom', // ← fixed
                validator: (value) => {
                    const margin = parseInt(value.replace(/\D/g, '')) || 0;
                    return margin >= 1;
                },
                errorMessage: 'Margin harus lebih dari 0%.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => {
                    const margin = parseInt(value.replace(/\D/g, '')) || 0;
                    return margin <= 99;
                },
                errorMessage: 'Margin tidak boleh lebih dari 99%.',
            },
            {
                rule: 'custom', // ← strict: whole number only, no decimals
                validator: (value) => /^\d+$/.test(value.replace(/\D/g, '')),
                errorMessage: 'Margin harus berupa bilangan bulat.',
            },
        ], {
            successMessage: 'Margin valid ✓',
        })

        .onSuccess(async (event) => {
            event.preventDefault();

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

                    window.location.href = data.redirect ?? '/purchase-orders';
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

                    // ← customer is now a select, set value and trigger Select2
                    if (data.customer) {
                        $('#customer_id').val(data.customer_id).trigger('change');
                    } else {
                        $('#customer_id').val('').trigger('change');
                    }

                    $('#no_po').val(data.no_po);
                    $('#nama_barang').val(data.nama_barang);
                    $('#qty').val(data.qty);
                    $('#harga').val(data.harga);
                    $('#harga_display').val(formatIDR(data.harga));
                    $('#margin_percentage').val(((data.margin / data.modal_awal) * 100).toFixed(0));
                    $('#tambahan_margin').val(data.tambahan_margin);
                    $('#tambahan_margin_display').val(formatIDR(data.tambahan_margin));

                    $('#createPoForm :input').prop('disabled', false);

                    // Revalidate all fields after population
                    [
                        '#incoming_po_id',
                        '#no_po',
                        '#customer_id',
                        '#nama_barang',
                        '#tgl_po',
                        '#qty',
                        '#harga_display',
                        '#margin_percentage',
                    ].forEach(field => validator.revalidateField(field).catch(() => { }));
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

    const payNow = document.getElementById('pay_now');
    payNow.addEventListener('change', function () {
        this.value = this.checked ? '1' : '0';
    });

    const validator = new JustValidate('#invoiceForm', {
        errorFieldCssClass: 'is-invalid',
        successFieldCssClass: 'is-valid',
        errorLabelStyle: {},
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
        validateBeforeSubmitting: true,
    });

    // ── Revalidate all fields ─────────────────────────────────────────────────────

    const revalidateFields = ['#delivery_select', '#tgl_invoice', '#due_date'];

    function revalidateAll() {
        revalidateFields.forEach(function (selector) {
            validator.revalidateField(selector).catch(() => { });
        });
    }

    const watchEvents = ['change', 'input', 'click', 'paste'];

    revalidateFields.forEach(function (selector) {
        const el = document.querySelector(selector);
        if (!el) return;
        watchEvents.forEach(function (event) {
            el.addEventListener(event, revalidateAll);
        });
    });

    // ── Validation ────────────────────────────────────────────────────────────────

    validator
        .addField('#delivery_select', [
            {
                rule: 'required',
                errorMessage: 'Invoice wajib dipilih.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => value !== '' && value !== null && value !== undefined,
                errorMessage: 'Invoice wajib dipilih.',
            },
            {
                rule: 'custom', // ← strict: must be a positive integer
                validator: (value) => Number.isInteger(Number(value)) && Number(value) > 0,
                errorMessage: 'Invoice tidak valid.',
            },
        ], {
            successMessage: 'Invoice valid ✓',
        })
        .addField('#delivery_select', [
            {
                rule: 'required',
                errorMessage: 'Catatan pengiriman wajib dipilih.',
            },
            {
                rule: 'custom', // ← fixed: missing rule: 'custom'
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
                rule: 'custom', // ← fixed
                validator: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime());
                },
                errorMessage: 'Format tanggal invoice tidak valid.',
            },
            {
                rule: 'custom', // ← fixed
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
                rule: 'custom', // ← fixed
                validator: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime());
                },
                errorMessage: 'Format tanggal jatuh tempo tidak valid.',
            },
            {
                rule: 'custom', // ← fixed
                validator: (value) => {
                    const dueDate = new Date(value);
                    const invoiceDate = new Date(document.getElementById('tgl_invoice').value);
                    if (isNaN(invoiceDate.getTime())) return true;
                    return dueDate > invoiceDate;
                },
                errorMessage: 'Tanggal jatuh tempo harus setelah tanggal invoice.',
            },
            {
                rule: 'custom', // ← fixed
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

            const payNow = document.getElementById('pay_now');
            formData.set('pay_now', payNow.checked ? '1' : '0');

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

                    window.location.href = data.redirect ?? '/invoices';
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

    // ── Helpers ───────────────────────────────────────────────────────────────────

    function getAvailableQty() {
        const select = document.getElementById('po_id');
        const opt = select.options[select.selectedIndex];
        return parseInt(opt?.getAttribute('data-qty') || 0);
    }

    function getInputQty() {
        const val = document.getElementById('qty_delivered').value;
        return parseInt(val.replace(/\D/g, '')) || 0;
    }

    const deliverNow = document.getElementById('deliver_now');
    deliverNow.addEventListener('change', function () {
        this.value = this.checked ? '1' : '0';
    });

    // ── Revalidate all fields ─────────────────────────────────────────────────────

    const revalidateFields = ['#po_id', '#qty_delivered', '#delivery_time_estimation'];

    function revalidateAll() {
        revalidateFields.forEach(function (selector) {
            validator.revalidateField(selector).catch(() => { });
        });
    }

    const watchEvents = ['change', 'input', 'click', 'paste'];

    revalidateFields.forEach(function (selector) {
        const el = document.querySelector(selector);
        if (!el) return;
        watchEvents.forEach(function (event) {
            el.addEventListener(event, revalidateAll);
        });
    });

    // ── Validation ────────────────────────────────────────────────────────────────

    validator
        .addField('#po_id', [
            {
                rule: 'required',
                errorMessage: 'Purchase Order wajib dipilih.',
            },
            {
                rule: 'custom',  // ← fixed: was missing rule: 'custom'
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
                rule: 'custom',  // ← fixed
                validator: (value) => {
                    const qty = parseInt(value.replace(/\D/g, '')) || 0;
                    return qty > 0;
                },
                errorMessage: 'Jumlah yang dikirim harus lebih dari 0.',
            },
            {
                rule: 'custom',  // ← fixed
                validator: (value) => {
                    const inputQty = parseInt(value.replace(/\D/g, '')) || 0;
                    const availableQty = getAvailableQty();
                    if (availableQty === 0) return true;
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
                rule: 'custom',  // ← fixed
                validator: (value) => {
                    const date = new Date(value);
                    return !isNaN(date.getTime());
                },
                errorMessage: 'Format tanggal tidak valid.',
            },
            {
                rule: 'custom',  // ← fixed
                validator: (value) => {
                    const input = new Date(value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    return input >= today;
                },
                errorMessage: 'Tanggal pengiriman tidak boleh sebelum hari ini.',
            },
            {
                rule: 'custom',  // ← fixed
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

            const deliverNow = document.getElementById('deliver_now');
            formData.set('deliver_now', deliverNow.checked ? '1' : '0');

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

                    window.location.href = data.redirect ?? '/deliveries';
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
    });

    // Numeric only — strip non-digits on input
    document.getElementById('qty_delivered').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
        validator.revalidateField('#qty_delivered');
    });
}

if (document.getElementById('delivery-table')) {
    const tableEl = document.getElementById('delivery-table');
    const ajaxUrl = tableEl.getAttribute('data-url');   // FIX #1
    const csrfToken = tableEl.getAttribute('data-csrf'); // FIX #2

    $('#delivery-table thead').append(`
        <tr class="column-search-row">
            <th></th>
            <th>
                <input type="text" id="search-delivery-details"
                    class="form-control form-control-sm"
                    placeholder="Cari No.Del / Qty / Tgl / Status..."
                    style="min-width:180px;">
            </th>
            <th>
                <input type="text" id="search-detail-po"
                    class="form-control form-control-sm" 
                    placeholder="Cari No.PO / Tgl / Barang / Status..."
                    style="min-width:180px;">
            </th>
            <th>
                <input type="text" id="search-status"
                    class="form-control form-control-sm"
                    placeholder="Cari estimasi / status..."
                    style="min-width:160px;">
            </th>
            <th></th>
        </tr>
    `);

    var table = $('#delivery-table').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        fixedHeader: true,
        scrollX: true,
        scrollCollapse: true,
        ajax: ajaxUrl,
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
                className: 'text-center fw-medium text-muted'
            },
            {
                data: 'delivery_details',
                name: 'delivery_details',
                className: 'ps-3',
                orderable: true,
                searchable: true
            },
            {
                data: 'detail_po',
                name: 'detail_po',
                className: 'ps-3',
                orderable: true,
                searchable: true
            },
            {
                data: 'status',
                name: 'status',
                className: 'ps-3 text-center',
                orderable: true,
                searchable: true
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center'
            }
        ],

        order: [[1, 'desc']],
        pageLength: 5,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],

        drawCallback: function () {
            // ← convert to static array so DOM shifts don't affect iteration
            var elements = Array.from(document.getElementsByClassName("delivery-timer"));

            elements.forEach(function (el) {
                var wrapper = el.closest('.timer-wrapper');
                var target = wrapper ? wrapper.getAttribute('data-target') : null;
                var deliveryId = wrapper ? wrapper.getAttribute('data-id') : null;
                if (!target || !deliveryId) return;

                (function (el, targetDate, id) {
                    function updateCountdown() {
                        var now = new Date();
                        var end = new Date(targetDate);

                        if (end <= now) {
                            var elapsed = intervalToDuration({ start: end, end: now });
                            var elapsedText = formatDuration(elapsed, {
                                format: ['years', 'months', 'days', 'hours', 'minutes'],
                                zero: true,
                                delimiter: ' ',
                                locale: idLocale
                            });

                            elapsedText = elapsedText
                                .replace(/(\d+) days?/, '$1 Hari')
                                .replace(/(\d+) hours?/, '$1 Jam')
                                .replace(/(\d+) minutes?/, '$1 Menit');

                            el.innerHTML = `<small style="color:#dc3545;">${elapsedText} yang lalu</small>`;
                            return;
                        }

                        var duration = intervalToDuration({ start: now, end: end });
                        el.textContent = formatDuration(duration, {
                            format: ['years', 'months', 'days', 'hours', 'minutes'],
                            zero: true,
                            delimiter: ' ',
                            locale: idLocale
                        });
                    }

                    updateCountdown();
                    setInterval(updateCountdown, 1000);
                })(el, target, deliveryId);
            });

            // ── Deliver Sekarang button ──
            document.querySelectorAll('.deliver-now-btn').forEach(function (btn) {
                btn.replaceWith(btn.cloneNode(true));
            });

            document.querySelectorAll('.deliver-now-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const url = btn.getAttribute('data-url');
                    const token = btn.getAttribute('data-token');
                    const id = btn.getAttribute('data-id');

                    Swal.fire({
                        title: 'Konfirmasi Pengiriman',
                        html: `
                    <div style="display:flex;flex-direction:column;align-items:center;gap:10px;padding:6px 0;">
                        <div style="width:56px;height:56px;border-radius:50%;background:#e0f2fe;display:flex;align-items:center;justify-content:center;">
                            <i class="ri-send-plane-fill" style="font-size:1.6rem;color:#0284c7;"></i>
                        </div>
                        <p style="margin:0;font-size:0.92rem;color:#475569;">
                            Tandai pengiriman ini sebagai <strong>sudah terkirim</strong>?<br>
                            <small style="color:#94a3b8;">Tindakan ini tidak dapat dibatalkan.</small>
                        </p>
                    </div>
                `,
                        showCancelButton: true,
                        confirmButtonColor: '#0284c7',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: '<i class="ri-send-plane-fill"></i> Ya, Kirim Sekarang',
                        cancelButtonText: 'Batal',
                        focusCancel: true,
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        btn.disabled = true;
                        btn.innerHTML = '<i class="ri-loader-4-line"></i> Memproses...';

                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ delivery_id: id })
                        })
                            .then(res => res.json())
                            .then(data => {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message ?? 'Pengiriman berhasil dikonfirmasi.',
                                    confirmButtonColor: '#0284c7',
                                }).then(() => {
                                    table.ajax.reload(null, false);
                                });
                            })
                            .catch(() => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: 'Terjadi kesalahan. Coba lagi.',
                                    confirmButtonColor: '#d33',
                                });

                                btn.disabled = false;
                                btn.innerHTML = '<i class="ri-send-plane-fill"></i> Deliver Sekarang';
                            });
                    });
                });
            });
        },
        orderCellsTop: true,
        initComplete: function () {
            var api = this.api();

            $('#search-delivery-details').on('keyup change', function () {
                if (api.column(1).search() !== this.value) {
                    api.column(1).search(this.value).draw();
                }
            });

            // ── Bind search input AFTER init ───────────────────
            $('#search-detail-po').on('keyup change', function () {
                if (api.column(2).search() !== this.value) {
                    api.column(2).search(this.value).draw();
                }
            });

            $('#search-status').on('keyup change', function () {
                if (api.column(3).search() !== this.value) {
                    api.column(3).search(this.value).draw();
                }
            });
        }
    });

    $(document).on('click', '.btn-delete-ajax', function (e) {
        e.preventDefault();

        const url = $(this).data('url');
        const deliveryNo = $(this).data('po');

        Swal.fire({
            title: 'Hapus Pengiriman?',
            text: `Hapus Pengiriman terkait PO ${deliveryNo}? Tindakan ini tidak dapat dibatalkan.`,
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

    $('#invoice-table thead').append(`
        <tr class="column-search-row">
            <th></th>
            <th>
                <input type="text" id="search-invoice-details"
                    class="form-control form-control-sm"
                    placeholder="Cari No.Inv / Tgl / Due / Tagihan / Status..."
                    style="min-width:200px;">
            </th>
            <th>
                <input type="text" id="search-delivery-details"
                    class="form-control form-control-sm"
                    placeholder="Cari No.Del / Qty / Tgl / Status..."
                    style="min-width:180px;">
            </th>
            <th>
                <input type="text" id="search-due-date"
                    class="form-control form-control-sm"
                    placeholder="Cari due date / status / tagihan..."
                    style="min-width:160px;">
            </th>
            <th></th>
        </tr>
    `);

    var table = $('#invoice-table').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        fixedHeader: true,
        scrollX: true,
        scrollCollapse: true,
        ajax: ajaxUrl,
        order: [
            [1, 'asc']
        ],
        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
        },
        {
            data: 'invoice_details',
            name: 'invoice_details',
            orderable: true,
            searchable: true
        },
        {
            data: 'delivery_details',
            name: 'delivery_details',
            orderable: true,
            searchable: true
        },
        {
            data: 'due_date_timer',
            name: 'due_date_timer',
            orderable: true,
            searchable: true
        },
        {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            className: 'text-center'
        }
        ],
        pageLength: 5,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        drawCallback: function () {
            var elements = Array.from(document.getElementsByClassName("invoice-timer"));

            elements.forEach(function (el) {
                var wrapper = el.closest('.timer-wrapper');
                var target = wrapper ? wrapper.getAttribute('data-target') : null;
                if (!target) return;

                (function (el, targetDate) {
                    function updateCountdown() {
                        var now = new Date();
                        var end = new Date(targetDate);

                        if (end <= now) {
                            var elapsed = intervalToDuration({ start: end, end: now });
                            var elapsedText = formatDuration(elapsed, {
                                format: ['years', 'months', 'days', 'hours', 'minutes'],
                                zero: true,
                                delimiter: ' ',
                                locale: idLocale
                            });

                            elapsedText = elapsedText
                                .replace(/(\d+) days?/, '$1 Hari')
                                .replace(/(\d+) hours?/, '$1 Jam')
                                .replace(/(\d+) minutes?/, '$1 Menit');

                            el.innerHTML = `<small style="color:#dc3545;font-weight:600;display:block;margin-top:3px;">${elapsedText} yang lalu</small>`;
                            clearInterval(interval);
                            return;
                        }

                        var duration = intervalToDuration({ start: now, end: end });
                        el.textContent = formatDuration(duration, {
                            format: ['years', 'months', 'days', 'hours', 'minutes'],
                            zero: true,
                            delimiter: ' ',
                            locale: idLocale
                        });
                    }

                    updateCountdown();
                    var interval = setInterval(updateCountdown, 1000);
                })(el, target);
            });
        },
        orderCellsTop: true,
        initComplete: function () {
            var api = this.api();

            $('#search-invoice-details').on('keyup change', function () {
                if (api.column(1).search() !== this.value) {
                    api.column(1).search(this.value).draw();
                }
            });

            $('#search-delivery-details').on('keyup change', function () {
                if (api.column(2).search() !== this.value) {
                    api.column(2).search(this.value).draw();
                }
            });

            $('#search-due-date').on('keyup change', function () {
                if (api.column(3).search() !== this.value) {
                    api.column(3).search(this.value).draw();
                }
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

if (document.getElementById("import-form")) {
    const validator = new JustValidate('#import-form', {
        successFieldCssClass: 'is-valid',
        errorFieldCssClass: 'is-invalid',
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
        validateBeforeSubmitting: true,
    });

    validator
        .addField('#import-file', [
            {
                rule: 'required',
                errorMessage: 'File diperlukan. Silakan pilih file yang ingin diimpor.',
            },
            {
                validator: (value, fields) => {
                    const file = fields['#import-file'].elem.files[0];
                    if (!file) return false;
                    const allowedTypes = [
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                        'application/csv',
                    ];
                    const allowedExtensions = ['xlsx', 'xls', 'csv'];
                    const ext = file.name.split('.').pop().toLowerCase();
                    return allowedTypes.includes(file.type) || allowedExtensions.includes(ext);
                },
                errorMessage: 'Tipe file tidak valid. Hanya file .xlsx, .xls, dan .csv yang diperbolehkan.',
            },
            {
                validator: (value, fields) => {
                    const file = fields['#import-file'].elem.files[0];
                    if (!file) return false;
                    const maxSize = 5 * 1024 * 1024;
                    return file.size <= maxSize;
                },
                errorMessage: 'Ukuran file melebihi batas 5MB. Harap unggah file yang lebih kecil.',
            },
            {
                validator: (value, fields) => {
                    const file = fields['#import-file'].elem.files[0];
                    if (!file) return false;
                    return file.size > 0;
                },
                errorMessage: 'Berkas tampaknya kosong. Silakan unggah berkas yang valid.',
            },
        ], {
            successMessage: 'File valid'
        });

    // ✅ Live revalidate on file change — await the Promise
    document.getElementById('import-file').addEventListener('change', async () => {
        const isValid = await validator.revalidate();  // ✅ awaited

        if (isValid) {
            const file = document.getElementById('import-file').files[0];
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);

            Swal.fire({
                toast: true,
                position: 'center',
                icon: 'success',
                title: 'Berkas siap diimpor!',
                html: `<small><b>${file.name}</b> (${sizeMB} MB)</small>`,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        }
    });

    // ✅ EVERYTHING BELOW MUST BE INSIDE A SUBMIT HANDLER
    document.getElementById('import-form').addEventListener('submit', async function (e) {
        e.preventDefault();

        // ✅ Validate before doing anything
        const isValid = await validator.revalidate();
        if (!isValid) return;

        // ✅ 'confirm' renamed to 'swalResult' — confirm is a reserved global
        const swalResult = await Swal.fire({
            title: 'Import Berkas?',
            text: 'Pastikan file Anda sudah benar sebelum mengimpornya.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Import!',
            cancelButtonText: 'Cancel',
        });

        if (!swalResult.isConfirmed) return;

        // Show loading
        Swal.fire({
            title: 'Importing...',
            text: 'Mohon tunggu sementara kami memproses berkas Anda.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });

        // Build FormData
        const formData = new FormData();
        formData.append('file', document.getElementById('import-file').files[0]);
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        const form = document.getElementById('import-form');  // ✅ const added

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const result = await response.json();

            if (response.ok) {
                await Swal.fire({
                    title: 'Import Berhasil!',
                    text: result.message ?? 'Seluruh PO telah berhasil diimpor.',
                    icon: 'success',
                    confirmButtonColor: '#198754',
                    confirmButtonText: 'OK',
                });

                document.getElementById('import-form').reset();
                document.getElementById('import-file').classList.remove('is-valid', 'is-invalid');

            } else if (response.status === 422) {
                const errors = result.errors
                    ? Object.values(result.errors).flat().join('<br>')
                    : result.message ?? 'Validation failed.';

                await Swal.fire({
                    title: 'Kesalahan Validasi!',
                    html: errors,
                    icon: 'warning',
                    confirmButtonColor: '#ffc107',
                    confirmButtonText: 'OK',
                });

            } else {
                await Swal.fire({
                    title: 'Impor Gagal!',
                    text: result.message ?? 'Terjadi kesalahan. Silakan coba lagi.',
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OK',
                });
            }

        } catch (error) {
            await Swal.fire({
                title: 'Terjadi kesalahan koneksi!',
                text: 'Tidak dapat terhubung ke server. Harap periksa koneksi Anda dan coba lagi.',
                icon: 'error',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'OK',
            });
        }
    });
}

if (document.getElementById('table-users')) {
    const tableEl = document.getElementById('table-users');
    const ajaxUrl = tableEl.getAttribute('data-url');   // FIX #1
    var dt_table = $('#table-users');
    $('#table-users thead').append(`
        <tr class="column-search-row">
            <th></th>
            <th>
                <input type="text" id="search-detail-users"
                    class="form-control form-control-sm" 
                    placeholder="Cari nama / role / terakhir login"
                    style="min-width:180px;">
            </th>
            <th></th>
        </tr>
    `);

    if (dt_table.length) {
        var table = dt_table.DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            fixedHeader: true,
            scrollX: true,
            scrollCollapse: true,
            ajax: {
                url: ajaxUrl,
            },
            columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,

            },
            {
                data: 'user_details',
                name: 'user_details',
                className: 'text-center'

            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            ],
            order: [
                [1, 'desc']
            ],
            pageLength: 5,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            orderCellsTop: true,
            initComplete: function () {
                var api = this.api();

                // ── Bind search input AFTER init ───────────────────
                $('#search-detail-users').on('keyup change', function () {
                    if (api.column(1).search() !== this.value) {
                        api.column(1).search(this.value).draw();
                    }
                });
            }
        });

        // ── Delete Handler ─────────────────────────────────────────
        $(document).on('click', '.btn-delete-ajax', function () {
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
    $('#table-incoming thead').append(`
        <tr class="column-search-row">
            <th></th>
            <th>
                <input type="text" id="search-detail-po"
                    class="form-control form-control-sm" 
                    placeholder="Cari No.PO / Tgl / Barang / Status..."
                    style="min-width:180px;">
            </th>
            <th>
                <input type="text" id="search-price"
                    class="form-control form-control-sm"
                    placeholder="Cari harga / modal / qty..."
                    style="min-width:150px;">
            </th>
            <th>
                <input type="text" id="search-margin" class="form-control form-control-sm" placeholder="Cari margin / % / health..." style="min-width:150px;">
            </th>
            <th></th>
        </tr>
    `);

    if (dt_table.length) {
        var table = dt_table.DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            fixedHeader: true,
            scrollX: true,
            scrollCollapse: true,
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
                className: 'fw-medium',
                orderable: true,
                searchable: true
            },
            {
                data: 'price_references',
                name: 'price_references',
                className: 'fw-medium',
                orderable: true,
                searchable: true

            },
            {
                data: 'margin_references',
                name: 'margin_references',
                className: 'fw-medium',
                orderable: true,
                searchable: true
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
                [1, 'desc']
            ],

            pageLength: 5,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            orderCellsTop: true,
            initComplete: function () {
                var api = this.api();

                // ── Bind search input AFTER init ───────────────────
                $('#search-detail-po').on('keyup change', function () {
                    if (api.column(1).search() !== this.value) {
                        api.column(1).search(this.value).draw();
                    }
                });
                $('#search-price').on('keyup change', function () {
                    if (api.column(2).search() !== this.value) {
                        api.column(2).search(this.value).draw();
                    }
                });
                $('#search-margin').on('keyup change', function () {
                    if (api.column(3).search() !== this.value) {
                        api.column(3).search(this.value).draw();
                    }
                });
            }

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
    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('truncate-po')) {
            document.getElementById('truncate-po').addEventListener('click', async function () {
                let validator = null;

                await Swal.fire({
                    template: '#truncate-form',

                    didOpen: () => {
                        validator = new JustValidate('#truncate-po-form', {
                            successFieldCssClass: 'is-valid',
                            errorFieldCssClass: 'is-invalid',
                            errorLabelCssClass: 'invalid-feedback',
                            successLabelCssClass: 'valid-feedback',
                            validateBeforeSubmitting: true,
                        });

                        validator
                            .addField('#swal-reason', [
                                { rule: 'required', errorMessage: 'Alasan wajib diisi.' },
                                { rule: 'minLength', value: 10, errorMessage: 'Alasan minimal 10 karakter.' },
                            ], { successMessage: 'Alasan sudah benar' })
                            .addField('#swal-confirm', [
                                { rule: 'required', errorMessage: 'Konfirmasi wajib diisi.' },
                                {
                                    validator: (value) => value === 'SAYA YAKIN ATAS TINDAKAN INI',
                                    errorMessage: 'Ketik tepat: SAYA YAKIN ATAS TINDAKAN INI',
                                },
                            ], { successMessage: 'Konfirmasi sudah benar' });
                    },

                    preConfirm: () => {
                        if (!validator) {
                            Swal.showValidationMessage('Validator belum siap, coba lagi.');
                            return false;
                        }
                        return new Promise((resolve) => {
                            validator.revalidate().then((isValid) => {  // ✅ validator not window._swalValidator
                                if (!isValid) {
                                    Swal.showValidationMessage('Harap isi semua field dengan benar.');
                                    resolve(false);
                                } else {
                                    Swal.resetValidationMessage();
                                    resolve(true);
                                }
                            });
                        });
                    },

                    allowOutsideClick: false,
                    allowEscapeKey: false,

                }).then(async (swalResult) => {
                    if (!swalResult.isConfirmed) return;  // ✅ check before fetch

                    try {
                        const formValues = {
                            reason: document.querySelector('#swal-reason').value,
                            confirm: document.querySelector('#swal-confirm').value,  // ✅ not 'reason'
                        };

                        const form = document.getElementById('truncate-po-form');

                        const response = await fetch(form.action, {  // ✅ await
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(formValues),
                        });

                        const data = await response.json();  // ✅ await + renamed

                        if (response.ok) {
                            await Swal.fire({ title: 'Berhasil!', text: data.message ?? 'Seluruh PO berhasil dihapus.', icon: 'success', confirmButtonColor: '#198754' });
                            window.location.reload();
                        } else if (response.status === 422) {
                            const errors = data.errors ? Object.values(data.errors).flat().join('<br>') : data.message ?? 'Validasi gagal.';
                            Swal.fire({ title: 'Validasi Gagal!', html: errors, icon: 'warning', confirmButtonColor: '#ffc107' });
                        } else {
                            Swal.fire({ title: 'Gagal!', text: data.message ?? 'Terjadi kesalahan.', icon: 'error', confirmButtonColor: '#dc3545' });
                        }

                    } catch (error) {
                        Swal.fire({ title: 'Koneksi Error!', text: 'Tidak dapat terhubung ke server.', icon: 'error', confirmButtonColor: '#dc3545' });
                    }
                });
            });
        }
    });

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
    $('#table-po thead').append(`
        <tr class="column-search-row">
            <th></th>
            <th>
                <input type="text" id="search-detail-po"
                    class="form-control form-control-sm" 
                    placeholder="Cari No.PO / Tgl / Barang / Status..."
                    style="min-width:180px;">
            </th>
            <th>
                <input type="text" id="search-relation_details"
                    class="form-control form-control-sm" 
                    placeholder="Cari delivery / invoice / payment"
                    style="min-width:120px;">
            </th>
            <th>
                <input type="text" id="search-price"
                    class="form-control form-control-sm"
                    placeholder="Cari harga / modal / qty..."
                    style="min-width:150px;">
            </th>
            <th>
                <input type="text" id="search-margin" class="form-control form-control-sm" placeholder="Cari margin / % / health..." style="min-width:150px;">
            </th>
            <th></th>
        </tr>
    `);


    if (dt_table.length) {
        var table = dt_table.DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            fixedHeader: true,
            scrollX: true,
            scrollCollapse: true,
            autoWidth: false,
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
            columnDefs: [
                {
                    targets: 0,     // No
                    width: '50px',
                    className: 'text-center',
                },
                {
                    targets: 1,     // Detail PO
                    width: 'auto',
                },
                {
                    targets: 2,     // Delivery / Invoice / Payment
                    width: 'auto',
                },
                {
                    targets: 3,     // Detail Harga PO
                    width: 'auto',
                },
                {
                    targets: 4,     // Detail Margin PO
                    width: 'auto',
                },
                {
                    targets: -1,    // Aksi
                    width: '60px',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                }
            ],
            columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
            },
            {
                data: 'detail_po',
                name: 'detail_po',
                className: 'fw-medium',
                orderable: true,
                searchable: true
            },
            {
                data: 'relation_details',
                name: 'relation_details',
                className: 'fw-medium text-center',
                orderable: true,
                searchable: true
            },
            {
                data: 'price_references',
                name: 'price_references',
                className: 'fw-medium',
                orderable: true,
                searchable: true
            },
            {
                data: 'margin_references',
                name: 'margin_references',
                className: 'fw-medium',
                orderable: true,
                searchable: true
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
            }
            ],
            order: [
                [1, 'desc']
            ],
            pageLength: 5,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            orderCellsTop: true,
            initComplete: function () {
                var api = this.api();

                // ── Bind search input AFTER init ───────────────────
                $('#search-detail-po').on('keyup change', function () {
                    if (api.column(1).search() !== this.value) {
                        api.column(1).search(this.value).draw();
                    }
                });

                $('#search-relation_details').on('keyup change', function () {
                    if (api.column(2).search() !== this.value) {
                        api.column(2).search(this.value).draw();
                    }
                });
                $('#search-price').on('keyup change', function () {
                    if (api.column(3).search() !== this.value) {
                        api.column(3).search(this.value).draw();
                    }
                });
                $('#search-margin').on('keyup change', function () {
                    if (api.column(4).search() !== this.value) {
                        api.column(4).search(this.value).draw();
                    }
                });
            }
        });

        // ── Delete Handler ─────────────────────────────────────────
        $(document).on('click', '.btn-delete-ajax', function () {
            const deleteUrl = $(this).data('url');
            const poNo = $(this).data('po');

            Swal.fire({
                title: 'Hapus PO?',
                text: `Apakah Anda yakin ingin menghapus PO #${poNo}?`,
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
                        type: 'POST', // ← change to POST
                        data: {
                            _method: 'DELETE', // ← spoof DELETE
                            _token: $('meta[name="csrf-token"]').attr('content'), // ← move token to data
                        },
                        headers: {
                            'Accept': 'application/json',
                        },
                    }).then(response => {
                        if (!response.success) {
                            Swal.showValidationMessage(response.message);
                            return false;
                        }
                        return response;
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
            label: 'Semuanya di deliver dan semuanya di Invoice',
        },
    };

    function renderBreakdown(breakdown) {
        const tbody = document.getElementById('status-breakdown-tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        breakdown.forEach(row => {
            const cfg = statusConfig[row.status] || {
                label: row.label,
                color: 'secondary'
            };
            const isEmpty = row.count === 0;

            tbody.insertAdjacentHTML('beforeend', `
                <tr class="${isEmpty ? 'opacity-50' : ''}">
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

        $.getJSON(`/api/po-filtered-stats?${params.toString()}`)
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
};

if (document.getElementById('paymentTable')) {
    const tableEl = document.getElementById('paymentTable');
    const ajaxUrl = tableEl.getAttribute('data-url');   // FIX #1

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#paymentTable thead').append(`
        <tr class="column-search-row">
            <th></th>
            <th>
                <input type="text" id="search-detail-pembayaran"
                    class="form-control form-control-sm"
                    placeholder="Cari nominal / tgl / barang / metode..."
                    style="min-width:200px;">
            </th>
            <th>
                <input type="text" id="search-payment-estimation"
                    class="form-control form-control-sm"
                    placeholder="Cari estimasi / status..."
                    style="min-width:160px;">
            </th>
            <th></th>
        </tr>
    `);

    var table = $('#paymentTable').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        fixedHeader: true,
        scrollX: true,
        scrollCollapse: true,
        ajax: ajaxUrl,
        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false,
            className: 'col-no'
        },
        {
            data: 'detail_pembayaran',
            name: 'detail_pembayaran',
            orderable: true,
            searchable: true,
        },
        {
            data: 'payment_date_estimation',
            name: 'payment_date_estimation',
            orderable: true,
            searchable: true,
        },
        {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            className: 'text-center'
        }
        ],
        pageLength: 5,
        order: [
            [1, 'desc']
        ],
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        drawCallback: function () {
            var elements = Array.from(document.getElementsByClassName("payment-timer"));

            elements.forEach(function (el) {
                var wrapper = el.closest('.timer-wrapper');
                var target = wrapper ? wrapper.getAttribute('data-target') : null;
                var paymentId = wrapper ? wrapper.getAttribute('data-id') : null;
                if (!target || !paymentId) return;

                if (el._countdownInterval) {
                    clearInterval(el._countdownInterval);
                    el._countdownInterval = null;
                }

                (function (el, targetDate, id) {
                    function formatText(duration) {
                        return formatDuration(duration, {
                            format: ['years', 'months', 'days', 'hours', 'minutes'],
                            zero: false,
                            delimiter: ' ',
                            locale: idLocale
                        })
                            .replace(/(\d+) years?/, '$1 Tahun')
                            .replace(/(\d+) months?/, '$1 Bulan')
                            .replace(/(\d+) days?/, '$1 Hari')
                            .replace(/(\d+) hours?/, '$1 Jam')
                            .replace(/(\d+) minutes?/, '$1 Menit');
                    }

                    function updateCountdown() {
                        var now = new Date();
                        var end = new Date(targetDate);

                        if (!end) { el.textContent = '—'; return; }

                        if (now >= end) {
                            var elapsed = intervalToDuration({ start: end, end: now });
                            el.innerHTML = '<small style="color:#dc3545;">' + formatText(elapsed) + ' yang lalu</small>';
                            clearInterval(el._countdownInterval);
                            return;
                        }

                        var duration = intervalToDuration({ start: now, end: end });
                        el.textContent = formatText(duration) + ' lagi';
                    }

                    updateCountdown();
                    el._countdownInterval = setInterval(updateCountdown, 1000);
                })(el, target, paymentId);
            });

            // ── Bayar Sekarang — strip duplicate listeners on redraw ──
            document.querySelectorAll('.bayar-sekarang-btn').forEach(function (btn) {
                btn.replaceWith(btn.cloneNode(true));
            });

            document.querySelectorAll('.bayar-sekarang-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const url = btn.getAttribute('data-url');
                    const token = btn.getAttribute('data-token');
                    const id = btn.getAttribute('data-id');
                    const state = btn.getAttribute('data-state');

                    const isOverdue = state === 'overdue';
                    const isDueToday = state === 'due-today';
                    const isPaid = state === 'paid';

                    const warningHtml = isOverdue
                        ? `<p style="margin:8px 0 0;font-size:0.78rem;color:#ef4444;"><i class="ri-error-warning-line"></i> Pembayaran ini sudah melewati estimasi.</p>`
                        : isDueToday
                            ? `<p style="margin:8px 0 0;font-size:0.78rem;color:#d97706;"><i class="ri-alarm-line"></i> Pembayaran ini jatuh tempo hari ini.</p>`
                            : isPaid
                                ? `<p style="margin:8px 0 0;font-size:0.78rem;color:#10b981;"><i class="ri-checkbox-circle-line"></i> Pembayaran ini sudah tercatat sebagai lunas.</p>`
                                : '';

                    Swal.fire({
                        title: 'Konfirmasi Pembayaran',
                        html: `
                    <div style="display:flex;flex-direction:column;align-items:center;gap:12px;padding:6px 0;">
                        <div style="width:56px;height:56px;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;">
                            <i class="ri-secure-payment-line" style="font-size:1.6rem;color:#10b981;"></i>
                        </div>
                        <div style="text-align:center;">
                            <p style="margin:0;font-size:0.92rem;color:#475569;">
                                Tandai pembayaran ini sebagai <strong>sudah dibayar</strong>?
                            </p>
                            ${warningHtml}
                        </div>
                    </div>
                `,
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: '<i class="ri-secure-payment-line"></i> Ya, Bayar Sekarang',
                        cancelButtonText: 'Batal',
                        focusCancel: true,
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        btn.disabled = true;
                        btn.innerHTML = '<i class="ri-loader-4-line"></i> Memproses...';

                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ payment_id: id })
                        })
                            .then(res => res.json())
                            .then(data => {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message ?? 'Pembayaran berhasil dikonfirmasi.',
                                    confirmButtonColor: '#10b981',
                                }).then(() => {
                                    table.ajax.reload(null, false);
                                });
                            })
                            .catch(() => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: 'Terjadi kesalahan. Coba lagi.',
                                    confirmButtonColor: '#d33',
                                });

                                btn.disabled = false;
                                btn.innerHTML = '<i class="ri-secure-payment-line"></i> Bayar Sekarang';
                            });
                    });
                });
            });
        },
        orderCellsTop: true,
        initComplete: function () {
            var api = this.api();

            $('#search-detail-pembayaran').on('keyup change', function () {
                if (api.column(1).search() !== this.value) {
                    api.column(1).search(this.value).draw();
                }
            });

            $('#search-payment-estimation').on('keyup change', function () {
                if (api.column(2).search() !== this.value) {
                    api.column(2).search(this.value).draw();
                }
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
    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('truncate-investasi')) {
            document.getElementById('truncate-investasi').addEventListener('click', async function () {
                let validator = null;

                await Swal.fire({
                    template: '#truncate-form',

                    didOpen: () => {
                        validator = new JustValidate('#truncate-investasi-form', {
                            successFieldCssClass: 'is-valid',
                            errorFieldCssClass: 'is-invalid',
                            errorLabelCssClass: 'invalid-feedback',
                            successLabelCssClass: 'valid-feedback',
                            validateBeforeSubmitting: true,
                        });

                        validator
                            .addField('#swal-reason', [
                                { rule: 'required', errorMessage: 'Alasan wajib diisi.' },
                                { rule: 'minLength', value: 10, errorMessage: 'Alasan minimal 10 karakter.' },
                            ], { successMessage: 'Alasan sudah benar' })
                            .addField('#swal-confirm', [
                                { rule: 'required', errorMessage: 'Konfirmasi wajib diisi.' },
                                {
                                    validator: (value) => value === 'SAYA YAKIN ATAS TINDAKAN INI',
                                    errorMessage: 'Ketik tepat: SAYA YAKIN ATAS TINDAKAN INI',
                                },
                            ], { successMessage: 'Konfirmasi sudah benar' });
                    },

                    preConfirm: () => {
                        if (!validator) {
                            Swal.showValidationMessage('Validator belum siap, coba lagi.');
                            return false;
                        }
                        return new Promise((resolve) => {
                            validator.revalidate().then((isValid) => {
                                if (!isValid) {
                                    Swal.showValidationMessage('Harap isi semua field dengan benar.');
                                    resolve(false);
                                } else {
                                    Swal.resetValidationMessage();
                                    resolve(true);
                                }
                            });
                        });
                    },

                    allowOutsideClick: false,
                    allowEscapeKey: false,

                }).then(async (swalResult) => {
                    if (!swalResult.isConfirmed) return;

                    try {
                        const formValues = {
                            reason: document.querySelector('#swal-reason').value,
                            confirm: document.querySelector('#swal-confirm').value,
                        };

                        const form = document.getElementById('truncate-investasi-form');

                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(formValues),
                        });

                        const data = await response.json();

                        if (response.ok) {
                            await Swal.fire({ title: 'Berhasil!', text: data.message ?? 'Seluruh PO berhasil dihapus.', icon: 'success', confirmButtonColor: '#198754' });
                            window.location.reload();
                        } else if (response.status === 422) {
                            const errors = data.errors ? Object.values(data.errors).flat().join('<br>') : data.message ?? 'Validasi gagal.';
                            Swal.fire({ title: 'Validasi Gagal!', html: errors, icon: 'warning', confirmButtonColor: '#ffc107' });
                        } else {
                            Swal.fire({ title: 'Gagal!', text: data.message ?? 'Terjadi kesalahan.', icon: 'error', confirmButtonColor: '#dc3545' });
                        }

                    } catch (error) {
                        Swal.fire({ title: 'Koneksi Error!', text: 'Tidak dapat terhubung ke server.', icon: 'error', confirmButtonColor: '#dc3545' });
                    }
                });
            });
        }
    });

    const tableEl = document.getElementById('investment-table');
    const ajaxUrl = tableEl.getAttribute('data-url');   // FIX #1

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });

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

    var role = parseInt($('#investment-table').data('role'));

    var columns = [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'investasi_details', name: 'investasi_details', orderable: true, searchable: true },
    ];

    if (role !== 2) {
        columns.push({ data: 'action', name: 'action', orderable: false, searchable: false });
    }

    var dt_table = $('#investment-table');

    // ── Only add the 3rd <th> if action column exists ──────────────
    $('#investment-table thead').append(`
    <tr class="column-search-row">
        <th></th>
        <th>
            <input type="text" id="search-detail-investasi"
                class="form-control form-control-sm" 
                placeholder="Cari modal setor / margin tersedia / dana tersedia"
                style="min-width:180px;">
        </th>
        ${role !== 2 ? '<th></th>' : ''}
    </tr>
`);

    if (dt_table.length) {
        var table = $('#investment-table').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            fixedHeader: true,
            scrollX: true,
            scrollCollapse: true,
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
            columns: columns,
            pageLength: 5,
            order: [[1, 'desc']],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            orderCellsTop: true,
            initComplete: function () {
                var api = this.api();

                $('#search-detail-investasi').on('keyup change', function () {
                    if (api.column(1).search() !== this.value) {
                        api.column(1).search(this.value).draw();
                    }
                });
            }
        });
    }

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
    var role = parseInt($('#customerTable').data('role'));

    var columns = [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'cust_name', name: 'cust_name', orderable: true, searchable: true },
        {
            data: 'input_date', name: 'input_date', orderable: true, searchable: true,
            render: function (data, type, row) {
                return '<div class="d-flex flex-column">' +
                    '<span class="fw-medium text-dark">' +
                    '<i class="ri-calendar-event-line me-1 text-success"></i>' +
                    data +
                    '</span>' +
                    '</div>';
            }
        },
    ];

    if (role !== 2) {
        columns.push({ data: 'action', name: 'action', orderable: false, searchable: false });
    }

    var table = $('#customerTable').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        fixedHeader: true,
        ajax: ajaxUrl,
        columns: columns,
        pageLength: 5,
        order: [
            [2, 'desc']
        ],
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
    });

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
                        _token: document.querySelector('meta[name="csrf-token"]').content
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

    const numberOptions = {
        startVal: 0,
        duration: 3
    };

    const statsMap = [
        'dana_tersedia',
        'investasi_dikembalikan',
        'investasi_tambahan',
        'investasi_ditahan',
        'total_investasi_transfer',
        'total_transfer_investasi',
        'margin_diterima',
        'margin_tersedia',
        'margin_ditahan',
        'total_margin',
        'sisa_margin',
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

if (document.getElementById("investasiForm")) {
    // Initialize Select2
    $('.select2-po').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // State objects
    var modes = {
        setor: 'auto',
        po_baru: 'auto',
        margin: 'auto'
    };

    // Signs object, initialized from hidden inputs
    var signs = {
        setor: parseFloat($('#sign_setor').val()) || 1,
        po_baru: parseFloat($('#sign_po_baru').val()) || 1,
        margin: parseFloat($('#sign_margin').val()) || 1
    };

    // Update sign button text based on current sign
    function updateSignButton(target) {
        var btn = $('.toggle-sign[data-target="' + target + '"]');
        if (signs[target] === -1) {
            btn.text('Make Positive');
        } else {
            btn.text('Make Negative');
        }
    }
    // Initialize button texts
    updateSignButton('setor');
    updateSignButton('po_baru');
    updateSignButton('margin');

    // Toggle sign handler
    $('.toggle-sign').on('click', function () {
        var target = $(this).data('target');
        // Toggle sign
        signs[target] = signs[target] === 1 ? -1 : 1;
        // Update hidden input
        $('#sign_' + target).val(signs[target]);
        // Update button text
        updateSignButton(target);
        // Recalculate
        recalculate();
    });

    // Mode toggle handler
    $('.input-mode-toggle').on('click', function () {
        var target = $(this).data('target');
        var isAuto = modes[target] === 'auto';
        if (isAuto) {
            modes[target] = 'manual';
            $('#mode_' + target).val('manual');
            $('#' + target + '_auto_container').addClass('hidden-input');
            $('#' + target + '_manual_container').removeClass('hidden-input');
            $(this).html('<i class="ri-list-check"></i> Switch to Select POs');
        } else {
            modes[target] = 'auto';
            $('#mode_' + target).val('auto');
            $('#' + target + '_auto_container').removeClass('hidden-input');
            $('#' + target + '_manual_container').addClass('hidden-input');
            $(this).html('<i class="ri-edit-line"></i> Switch to Manual');
        }
        recalculate();
    });

    // Prevent non-numeric input
    $(document).on('keypress', '.manual-input, .calc-trigger', function (e) {
        var charCode = e.which || e.keyCode;
        var val = $(this).val();
        if (charCode >= 48 && charCode <= 57) return true;
        if (charCode === 46 && val.indexOf('.') === -1) return true;
        e.preventDefault();
        return false;
    });

    $(document).on('input', '.manual-input, .calc-trigger', function () {
        var val = $(this).val();
        var cleanVal = val.replace(/[^0-9.]/g, '');
        var parts = cleanVal.split('.');
        if (parts.length > 2) cleanVal = parts[0] + '.' + parts.slice(1).join('');
        if (val !== cleanVal) $(this).val(cleanVal);
    });

    // Recalculation function
    function recalculate() {
        var prev = parseFloat($('#prev_dana').val()) || 0;

        // Setor Awal
        var setor = 0;
        if (modes.setor === 'auto') {
            $('[name="ids_setor_awal[]"] option:selected').each(function () {
                setor += parseFloat($(this).data('modal')) || 0;
            });
            setor = setor * signs.setor;
            $('#disp_setor').text(new Intl.NumberFormat('id-ID').format(setor));
            if (signs.setor === -1) $('#disp_setor').addClass('text-danger');
            else $('#disp_setor').removeClass('text-danger');
        } else {
            setor = parseFloat($('[name="manual_setor_awal"]').val()) || 0;
            setor = setor * signs.setor;
        }

        // PO Baru
        var poBaru = 0;
        if (modes.po_baru === 'auto') {
            $('[name="ids_po_baru[]"] option:selected').each(function () {
                poBaru += parseFloat($(this).data('modal')) || 0;
            });
            poBaru = poBaru * signs.po_baru;
            $('#disp_po_baru').text(new Intl.NumberFormat('id-ID').format(poBaru));
            if (signs.po_baru === -1) $('#disp_po_baru').addClass('text-danger');
            else $('#disp_po_baru').removeClass('text-danger');
        } else {
            poBaru = parseFloat($('[name="manual_po_baru"]').val()) || 0;
            poBaru = poBaru * signs.po_baru;
        }

        // Margin
        var margin = 0;
        if (modes.margin === 'auto') {
            $('[name="ids_margin[]"] option:selected').each(function () {
                margin += parseFloat($(this).data('margin')) || 0;
            });
            margin = margin * signs.margin;
            $('#disp_margin').text(new Intl.NumberFormat('id-ID').format(margin));
            if (signs.margin === -1) $('#disp_margin').addClass('text-danger');
            else $('#disp_margin').removeClass('text-danger');
        } else {
            margin = parseFloat($('[name="manual_total_margin"]').val()) || 0;
            margin = margin * signs.margin;
        }

        var pencairan = parseFloat($('#pencairan_modal').val()) || 0;
        var margin_cair = parseFloat($('#margin_cair').val()) || 0;
        var investasi_tambahan = parseFloat($('#investasi_tambahan').val()) || 0;
        var penarikan = 0;
        $('#penarikan option:selected').each(function () {
            // Sum up the value of each selected option
            penarikan += parseFloat($(this).val()) || 0;
        });

        // Optional: Update the display text below the select box
        $('#disp_penarikan').text(new Intl.NumberFormat('id-ID').format(penarikan));
        // Dana tersedia formula: (prev + setor + margin + pencairan) - (poBaru + penarikan)
        var total = (prev + setor + margin + pencairan) - (poBaru + penarikan);

        $('#dana_tersedia').val(new Intl.NumberFormat('id-ID').format(total));
        $('#pengembalian_dana').val(new Intl.NumberFormat('id-ID').format(pencairan + margin_cair));
        $('#formula_text').text('(' + prev.toLocaleString() + ' + ' + setor.toLocaleString() + ' + ' + margin.toLocaleString() + ' + ' + pencairan.toLocaleString() + ') - (' + poBaru.toLocaleString() + ' + ' + penarikan.toLocaleString() + ')');
    }

    // Attach recalculate to relevant events
    $('.select2-po').on('change', recalculate);
    $('.manual-input, .calc-trigger').on('input', recalculate);
    recalculate(); // initial calculation

    // Flip value function for simple toggle buttons
    window.flipValue = function (selector) {
        var input = $(selector);
        var val = parseFloat(input.val()) || 0;
        input.val(-val);
        recalculate();
    };

    // AJAX submit
    $('#investasiForm').on('submit', function (e) {
        e.preventDefault();
        var btn = $('#btnSubmit');
        btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: $(this).serialize(),
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(function () {
                    window.location.href = response.redirect_url;
                });
            },
            error: function (xhr) {
                btn.prop('disabled', false).text('Save Investment');
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error saving data';
                Swal.fire('Error', msg, 'error');
            }
        });
    });
}