@extends('layouts/contentNavbarLayout')
@section('content')
<div class="row">
    <div class="col-xl-8 col-lg-10 mx-auto">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Buat Pengguna</h5>
            </div>
            <div class="card-body">
                <form class="needs-validation" id="create-users" action="{{ route('users.store') }}" method="POST" novalidate autocomplete="off">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-bold">Nama Pengguna</label>
                        <input type="text" name="user_name" class="form-control live-validate" placeholder="Username">
                        <div class="valid-feedback">Sudah Benar</div>
                        <div class="invalid-feedback">Hanya boleh menggunakan huruf, angka, garis bawah, dan tanda hubung. Panjangnya harus antara 3 dan 16 karakter.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Role Pengguna</label>
                        <select class="form-select" name="role_id" id="role-pengguna">
                            <option value="1">Administrator</option>
                            <option value="2">Visitor</option>
                        </select>
                        <div class="valid-feedback">Sudah Benar</div>
                        <div class="invalid-feedback">Mohon pilih role</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Email</label>
                        <input type="text" name="email" class="form-control live-validate" placeholder="Email">
                        <div class="valid-feedback">Sudah Benar</div>
                        <div class="invalid-feedback">Harus Cocok dengan nama domain. Harus Cocok dengan titik dan TLD (seperti .com, .net, .org) dengan minimal 2 karakter.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Password</label>
                        <div class="input-group has-validation">
                            <input type="password" name="password" id="password-input" class="form-control live-validate" placeholder="Password">
                            <button type="button" class="btn btn-outline-secondary" id="toggle-password">
                                <i class="icon-base ri ri-eye-off-line icon-20px"></i>
                            </button>
                            <div class="valid-feedback" style="flex-basis: 100%;">Sudah Benar</div>
                            <div class="invalid-feedback" style="flex-basis: 100%;">Harus mengandung setidaknya satu huruf kecil. Harus mengandung setidaknya satu huruf besar. Harus mengandung setidaknya satu angka. Harus mengandung setidaknya satu karakter khusus. Panjang minimal 8 karakter.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" id="btnSave" class="btn btn-primary px-5">
                            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                            Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const RULES = {
            user_name: /^[a-zA-Z0-9_-]{3,16}$/,
            email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
            password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/
        };

        const validateInputs = document.getElementsByClassName('live-validate');

        function validateField(input) {
            const rule = RULES[input.name];
            if (!rule) return true;
            const valid = rule.test(input.value);
            input.classList.toggle('is-valid', valid);
            input.classList.toggle('is-invalid', !valid);
            return valid;
        }

        for (let i = 0; i < validateInputs.length; i++) {
            validateInputs[i].addEventListener('input', function() {
                validateField(this);
            });
        }

        document.getElementById('toggle-password').addEventListener('click', function() {
            const pwInput = document.getElementById('password-input');
            const eyeIcon = document.querySelector('#toggle-password i');

            pwInput.type = pwInput.type === 'password' ? 'text' : 'password';

            eyeIcon.classList.toggle('ri-eye-off-line');
            eyeIcon.classList.toggle('ri-eye-line');
        });

        document.getElementById('create-users').addEventListener('submit', function(e) {
            e.preventDefault();
            let allValid = true;
            for (let i = 0; i < validateInputs.length; i++) {
                if (!validateField(validateInputs[i])) {
                    allValid = false;
                }
            }

            if (!allValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal!',
                    text: 'Harap perbaiki kolom yang tidak valid sebelum melanjutkan.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            const btnSave = document.getElementById('btnSave');
            const btnSpinner = document.getElementById('btnSpinner');
            btnSave.disabled = true;
            btnSpinner.classList.remove('d-none');

            const formData = new FormData(this);

            fetch(this.action, {
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
                                document.getElementById('create-users').reset();
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
    });
</script>
@endsection