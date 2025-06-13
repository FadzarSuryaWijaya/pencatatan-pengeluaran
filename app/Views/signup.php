<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Signup User</title>

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body.signup-page {
            background: url('<?= base_url('assets/background-login.png') ?>') no-repeat center center fixed;
            background-size: cover;

            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .signup-box {
            width: 100%;
            max-width: 400px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            margin: 0;
        }
    </style>


    <?= $this->include('partials/head') ?>
</head>

<body class="hold-transition signup-page">

    <div class="signup-box">
        <div class="signup-logo">Register User Baru</div>
        <div class="card">
            <div class="card-body signup-card-body">
                <p class="signup-box-msg">Isi form untuk membuat akun baru</p>

                <div class="alert alert-danger d-none"></div>
                <div class="alert alert-success d-none"></div>

                <form id="signupForm" action="<?= site_url('auth/daftar') ?>" method="post">
                    <div class="input-group mb-3">
                        <input type="text" name="username" class="form-control" placeholder="Username" required />
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-user"></span></div>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required />
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-id-card"></span></div>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required minlength="6" />
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-lock"></span></div>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <input type="password" name="password_confirm" class="form-control" placeholder="Konfirmasi Password" required minlength="6" />
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-lock"></span></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-block btn-primary">Daftar</button>
                    </div>
                </form>

                <p class="mb-0 text-center">
                    Sudah punya akun? <a href="<?= site_url('auth/login') ?>">Login di sini</a>
                </p>
            </div>
        </div>
    </div>

    <?= $this->include('partials/footer') ?>

    <script src="<?= base_url('assets/vendor/adminlte/plugins/jquery-validation/jquery.validate.min.js') ?>"></script>
    <script>
        $(document).ready(function() {
            $('#signupForm').validate({
                rules: {
                    password_confirm: {
                        equalTo: '[name="password"]'
                    }
                },
                messages: {
                    password_confirm: {
                        equalTo: 'Konfirmasi password tidak sama'
                    }
                },
                errorElement: 'span',
                errorPlacement: (error, element) => {
                    error.addClass('invalid-feedback');
                    element.closest('.input-group').append(error);
                },
                submitHandler: (form) => {
                    $('.btn-primary').prop('disabled', true).text('Loading...');

                    $.ajax({
                        url: '<?= site_url('auth/daftar') ?>',
                        type: 'post',
                        dataType: 'json',
                        data: $(form).serialize(),
                        success: res => {
                            if (res.status === 'error') {
                                $('.alert-danger').html(res.message).removeClass('d-none');
                                $('.alert-success').addClass('d-none');
                            } else if (res.status === 'success') {
                                $('.alert-success').html('Registrasi berhasil! Silakan login.').removeClass('d-none');
                                $('.alert-danger').addClass('d-none');
                                setTimeout(() => {
                                    window.location.href = '<?= site_url('auth/login') ?>';
                                }, 1500);
                            }
                        },
                        error: (xhr, status, error) => {
                            $('.alert-danger').html('Terjadi kesalahan, coba lagi.').removeClass('d-none');
                            $('.alert-success').addClass('d-none');
                        },
                        complete: () => {
                            $('.btn-primary').prop('disabled', false).text('Daftar');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>