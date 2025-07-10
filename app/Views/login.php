a  <!DOCTYPE html>
  <html>

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>

    <style>
      body.login-page {
        background: url('<?= base_url('assets/background-login.png') ?>') no-repeat center center fixed;
        background-size: cover;
      }
    </style>

    <?php // CI4 way to include partials: using $this->include() 
    ?>
    <?= $this->include('partials/head') ?>
  </head>

  <body class="hold-transition login-page">

    <div class="login-box">
      <div class="login-logo">Login</div>
      <div class="card">
        <div class="card-body login-card-body">
          <p class="login-box-msg">Login untuk masuk</p>
          <div class="alert alert-danger d-none"></div>
          <form id="loginForm" action="<?= site_url('auth/login') ?>" method="post">
            <div class="input-group mb-3">
              <input type="text" class="form-control" name="username" placeholder="Username" required>
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-user"></span>
                </div>
              </div>
            </div>
            <div class="input-group mb-3">
              <input type="password" class="form-control" name="password" placeholder="Password" required>
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-lock"></span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <button type="submit" class="btn btn-block btn-primary">Login</button>
            </div>
            <p class="mb-0 text-center">
              Belum punya akun? <a href="<?= site_url('auth/daftar') ?>">Daftar di sini</a>
            </p>
          </form>
        </div>
      </div>
    </div>

    <?php // CI4 way to include partials: using $this->include() 
    ?>
    <?= $this->include('partials/footer') ?>
    <script src="<?php echo base_url('assets/vendor/adminlte/plugins/jquery-validation/jquery.validate.min.js') ?>"></script>
    <script>
      // Pastikan jQuery sudah dimuat sebelum script ini
      $(document).ready(function() { // Pastikan DOM sudah siap
        $('#loginForm').validate({ // Gunakan ID form yang spesifik
          errorElement: 'span',
          errorPlacement: (error, element) => {
            error.addClass('invalid-feedback')
            element.closest('.input-group').append(error)
          },
          submitHandler: (form) => { // 'form' adalah elemen form DOM
            // Tambahkan loader atau disable tombol di sini
            $('.btn-primary').prop('disabled', true).text('Loading...');

            $.ajax({
              url: '<?php echo site_url('auth/login') ?>', // Sesuaikan dengan rute Anda
              type: 'post',
              dataType: 'json',
              data: $(form).serialize(), // Serialize form yang dilewatkan
              success: res => {
                if (res.status == 'tidakada') {
                  $('.alert').html('Username tidak terdaftar')
                  $('.alert').removeClass('d-none alert-success').addClass('alert-danger')
                } else if (res.status == 'passwordsalah') {
                  $('.alert').html('Password Salah')
                  $('.alert').removeClass('d-none alert-success').addClass('alert-danger')
                } else if (res.status == 'sukses') {
                  $('.alert').html('Login Berhasil!')
                  $('.alert').removeClass('d-none alert-danger').addClass('alert-success')
                  setTimeout(function() {
                    if (res.role == 'admin') {
                      window.location.href = '<?= base_url('/') ?>';
                    } else {
                      window.location.href = '<?= base_url('/') ?>';
                    }
                  }, 1000);
                }
              },
              error: (xhr, status, error) => { // Tangani error AJAX lebih detail
                console.log("AJAX Error:", status, error);
                console.log("Response Text:", xhr.responseText);
                $('.alert').html('Terjadi kesalahan, silakan coba lagi.')
                $('.alert').removeClass('d-none').addClass('alert-danger');
              },
              complete: () => {
                // Aktifkan kembali tombol setelah AJAX selesai
                $('.btn-primary').prop('disabled', false).text('Login');
              }
            })
          }
        })
      }); // End $(document).ready
    </script>
  </body>

  </html>