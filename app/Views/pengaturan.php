<!DOCTYPE html>
<html>

<head>
  <title>Pengaturan</title>
  <link rel="stylesheet" href="<?php echo base_url('assets/vendor/adminlte/plugins/sweetalert2/sweetalert2.min.css') ?>">
  <link rel="stylesheet" href="<?php echo base_url('assets/vendor/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') ?>">
  <?= $this->include('partials/head'); ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <?= $this->include('includes/nav'); ?>
    <?= $this->include('includes/aside'); ?>

    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col">
              <h1 class="m-0 text-dark">Pengaturan <?= (session()->get('role') === 'admin') ? 'Toko' : 'Alamat' ?></h1>
            </div>
          </div>
        </div>
      </div>

      <section class="content">
        <div class="container-fluid">
          <div class="card">
            <div class="card-body">
              <form id="toko">
                <div class="form-row">
                  <div class="col-6">
                    <?php if (session()->get('role') === 'admin'): ?>
                      <div class="form-group">
                        <label>Nama Toko</label>
                        <input type="text" class="form-control" placeholder="Nama Toko" name="nama"
                          value="<?= $toko->nama ?? '' ?>" <?= (session()->get('role') === 'admin') ? 'required' : 'disabled' ?>>
                      </div>
                    <?php endif; ?>

                    <div class="form-group">
                      <label>Alamat <?= (session()->get('role') !== 'admin') ? 'Anda' : 'Toko' ?></label>
                      <textarea name="alamat" placeholder="Alamat" class="form-control" required><?= $toko->alamat ?? '' ?></textarea>
                    </div>

                    <div class="form-group">
                      <button class="btn btn-success" type="submit">Simpan</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </section>
    </div>

  </div>
  <?= $this->include('includes/footer'); ?>
  <?= $this->include('partials/footer'); ?>
  <script src="<?php echo base_url('assets/vendor/adminlte/plugins/jquery-validation/jquery.validate.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/vendor/adminlte/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>
  <script>
$(document).ready(function() {
    // Adjust validation rules based on role
    var isAdmin = <?= (session()->get('role') === 'admin') ? 'true' : 'false' ?>;

    $('#toko').validate({
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        rules: {
            nama: {
                required: isAdmin
            },
            alamat: {
                required: true
            }
        },
        submitHandler: function(form) {
            // Disable submit button
            var submitBtn = $(form).find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('Menyimpan...');
            
            $.ajax({
                url: '<?php echo site_url('pengaturan/set_toko') ?>',
                type: 'post',
                dataType: 'json',
                data: $(form).serialize(),
                success: function(res) {
                    if (res.status === 'success') {
                        // Update nama toko di sidebar jika ada perubahan
                        if (res.nama_toko_baru && $('#nama-toko-display').length > 0) {
                            // Update text node pertama (nama toko)
                            var namaTokoElement = $('#nama-toko-display');
                            var textNode = namaTokoElement.contents().filter(function() {
                                return this.nodeType === 3; // Text node
                            });
                            
                            if (textNode.length > 0) {
                                textNode[0].nodeValue = res.nama_toko_baru;
                            } else {
                                // Fallback: update seluruh teks
                                var smallText = namaTokoElement.find('small');
                                if (smallText.length > 0) {
                                    namaTokoElement.html(res.nama_toko_baru).append(smallText);
                                } else {
                                    namaTokoElement.text(res.nama_toko_baru);
                                }
                            }
                        }
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Response:', xhr.responseText);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat mengirim data'
                    });
                },
                complete: function() {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).text('Simpan');
                }
            });
        }
    });
    
    // Disable nama field for non-admin users
    if (!isAdmin) {
        $('input[name="nama"]').prop('disabled', true).removeAttr('required');
    }
});
  </script>
</body>

</html>