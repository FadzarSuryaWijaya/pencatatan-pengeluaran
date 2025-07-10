<!-- Main Sidebar Container -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <div class="brand-link text-center" id="brand-link-toko" style="cursor: pointer; display: block; padding: 0.8125rem 0.5rem; text-decoration: none;">
    <span class="brand-text font-weight-light" id="nama-toko-display">
      <?php
      $toko = session()->get('toko');
      $role = session()->get('role');
      echo $toko && isset($toko['nama']) ? htmlspecialchars($toko['nama']) : 'Nama Toko';
      ?>
      <?php if ($role !== 'admin'): ?>
        <small class="d-block text-muted" style="font-size: 0.7rem;">Klik untuk edit</small>
      <?php endif; ?>
    </span>
  </div>

  <!-- Akses URI segment di CI4 -->
  <?php
  $uri = service('request')->getUri()->getSegment(1);
  $role = session()->get('role');
  ?>



  <!-- Sidebar Menu tetap sama -->
  <div class="sidebar">
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <?php if ($role === 'admin'): ?> 
        <li class="nav-item">
          <a href="<?php echo site_url('/') ?>" class="nav-link <?php echo $uri == 'dashboard' || $uri == '' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>
        <?php endif; ?> <li class="nav-item">
        <li class="nav-item">
          <a href="<?php echo site_url('transaksi') ?>" class="nav-link <?php echo $uri == 'transaksi' ? 'active' : '' ?>">
            <i class="fas fa-money-bill nav-icon"></i>
            <p>Transaksi</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="<?php echo site_url('supplier') ?>" class="nav-link <?php echo $uri == 'supplier' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-truck"></i>
            <p>Supplier</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="<?php echo site_url('pelanggan') ?>" class="nav-link <?php echo $uri == 'pelanggan' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-address-book"></i>
            <p>Pelanggan</p>
          </a>
        </li>
        <li class="nav-item has-treeview <?php echo $uri == 'produk' || $uri == 'kategori_produk' || $uri == 'satuan_produk' ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?php echo $uri == 'produk' || $uri == 'kategori_produk' || $uri == 'satuan_produk' ? 'active' : '' ?>">
            <i class="nav-icon fas fa-box"></i>
            <p>Produk</p>
            <i class="right fas fa-angle-right"></i>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo site_url('kategori_produk') ?>" class="nav-link <?php echo $uri == 'kategori_produk' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Kategori Produk</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo site_url('satuan_produk') ?>" class="nav-link <?php echo $uri == 'satuan_produk' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Satuan Produk</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo site_url('produk') ?>" class="nav-link <?php echo $uri == 'produk' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Produk</p>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item has-treeview <?php echo $uri == 'stok_masuk' || $uri == 'stok_keluar' ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?php echo $uri == 'stok_masuk' || $uri == 'stok_keluar' ? 'active' : '' ?>">
            <i class="fas fa-archive nav-icon"></i>
            <p>Stok</p>
            <i class="right fas fa-angle-right"></i>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo site_url('stok_masuk') ?>" class="nav-link <?php echo $uri == 'stok_masuk' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Stok Masuk</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo site_url('stok_keluar') ?>" class="nav-link <?php echo $uri == 'stok_keluar' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Stok Keluar</p>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item">
          <a href="<?php echo site_url('pengaturan') ?>" class="nav-link <?php echo $uri == 'pengaturan' ? 'active' : '' ?>">
            <i class="fas fa-cog nav-icon"></i>
            <p>Pengaturan</p>
          </a>
        </li>
        <?php if ($role === 'admin'): ?>
        <li class="nav-item has-treeview <?php echo $uri == 'laporan_penjualan' || $uri == 'laporan_stok_masuk' || $uri == 'laporan_stok_keluar' ? 'menu-open' : '' ?>">
          <a href="<?php echo site_url('laporan') ?>" class="nav-link <?php echo $uri == 'laporan_penjualan' || $uri == 'laporan_stok_masuk' || $uri == 'laporan_stok_keluar' ? 'active' : '' ?>">
            <i class="fas fa-book nav-icon"></i>
            <p>Laporan</p>
            <i class="right fas fa-angle-right"></i>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo site_url('laporan_penjualan') ?>" class="nav-link <?php echo $uri == 'laporan_penjualan' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Laporan Penjualan</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo site_url('laporan_stok_masuk') ?>" class="nav-link <?php echo $uri == 'laporan_stok_masuk' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Laporan Stok Masuk</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo site_url('laporan_stok_keluar') ?>" class="nav-link <?php echo $uri == 'laporan_stok_keluar' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Laporan Stok Keluar</p>
              </a>
            </li>
          </ul>
        </li>
        
        <li class="nav-item">
          <a href="<?php echo site_url('pengguna') ?>" class="nav-link <?php echo $uri == 'pengguna' ? 'active' : '' ?>">
            <i class="fas fa-user nav-icon"></i>
            <p>Pengguna</p>
          </a>
        </li>
        <?php endif ?>
      </ul>
    </nav>
  </div>
</aside>

  <!-- Modal untuk edit nama toko - ID UNIK -->
  <div class="modal" id="editTokoNameModal" tabindex="-1" role="dialog" aria-labelledby="editTokoNameModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editTokoNameModalLabel">Edit Nama Toko</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="formEditNamaToko">
          <div class="modal-body">
            <div class="form-group">
              <label for="inputNamaTokoSidebar">Nama Toko</label>
              <input type="text" class="form-control" id="inputNamaTokoSidebar" name="nama_toko"
                value="<?php echo $toko && isset($toko['nama']) ? htmlspecialchars($toko['nama']) : 'Nama Toko'; ?>"
                placeholder="Masukkan nama toko" required maxlength="100">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="btnSimpanNamaToko">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>


<!-- SCRIPT DIPERBAIKI DENGAN ID UNIK -->
<script>
// Tunggu sampai DOM dan jQuery siap
$(document).ready(function() {
    const role = '<?php echo session()->get("role"); ?>';
    let isSubmittingToko = false;

    // Global modal management
    $(document).on('show.bs.modal', '.modal', function() {
        // Close all other modals before opening a new one
        $('.modal').not(this).modal('hide');
        
        // Clean up any lingering backdrops
        $('.modal-backdrop').not('.modal-backdrop:last').remove();
    });

    // Handle brand link click
    if (role !== 'admin') {
        $('#brand-link-toko').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close all modals first
            $('.modal').modal('hide');
            
            // Clean backdrops
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            
            // Open our modal after cleanup
            setTimeout(() => {
                $('#editTokoNameModal').modal('show');
            }, 100);
        });
    }

    // Modal shown handler
    $('#editTokoNameModal').on('shown.bs.modal', function() {
        $('#inputNamaTokoSidebar').focus().select();
    });

    // Modal hidden handler with thorough cleanup
    $('#editTokoNameModal').on('hidden.bs.modal', function() {
        $('#formEditNamaToko')[0].reset();
        isSubmittingToko = false;
        
        // Enhanced backdrop cleanup
        setTimeout(() => {
            const activeModals = $('.modal.show').length;
            if (activeModals === 0) {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
                $('body').css('padding-right', '');
            }
        }, 100);
    });

    // Form submission handler (keep your existing AJAX code)
    $('#formEditNamaToko').on('submit', function(e) {
        e.preventDefault();
        if (isSubmittingToko) {
            console.log('Already submitting toko form, skipping...');
            return;
        }
        
        isSubmittingToko = true;
        
        const namaToko = $('#inputNamaTokoSidebar').val().trim();
        
        if (!namaToko) {
            alert('Nama toko harus diisi!');
            isSubmittingToko = false;
            return;
        }
        
        // Disable button submit
        $('#btnSimpanNamaToko').prop('disabled', true).text('Menyimpan...');
        
        console.log('Submitting toko form with nama:', namaToko);
        
        // Kirim data dengan AJAX
        $.ajax({
            url: '<?php echo site_url('auth/updateNamaToko'); ?>',
            type: 'POST',
            data: {
                nama_toko: namaToko
            },
            dataType: 'json',
            success: function(response) {
                console.log('Server response:', response);
                
                if (response.status === 'success') {
                    // Update tampilan nama toko
                    $('#nama-toko-display').contents().first()[0].textContent = namaToko;
                    
                    // Tutup modal
                    $('#editTokoNameModal').modal('hide');
                    
                    // Tampilkan pesan sukses
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        alert(response.message);
                    }
                } else {
                    alert(response.message || 'Terjadi kesalahan');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                console.error('Response:', xhr.responseText);
                
                let errorMessage = 'Terjadi kesalahan saat mengirim data';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        // Ignore parsing error
                    }
                }
                
                alert(errorMessage);
            },
            complete: function() {
                // Re-enable button submit
                $('#btnSimpanNamaToko').prop('disabled', false).text('Simpan');
                isSubmittingToko = false;
            }
        });
    });
    
    // Event handler untuk tombol close
    $('#editTokoNameModal [data-dismiss="modal"]').on('click', function() {
        $('#editTokoNameModal').modal('hide');
    });
    
    // Event handler untuk keyboard
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#editTokoNameModal').hasClass('show')) {
            $('#editTokoNameModal').modal('hide');
        }
    });
    
    // Pastikan modal bisa ditutup dengan klik backdrop
    $('#editTokoNameModal').on('click', function(e) {
        if (e.target === this) {
            $(this).modal('hide');
        }
    });
    
    // Handler khusus untuk tombol Simpan
    $(document).off('click', '#btnSimpanNamaToko').on('click', '#btnSimpanNamaToko', function(e) {
        e.preventDefault();
        console.log('Save toko button clicked');
        $('#formEditNamaToko').submit();
    });
        // Global backdrop cleanup on page changes
    $(window).on('beforeunload', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
});
</script>

<!-- CSS tambahan untuk memastikan modal berfungsi dengan baik -->
<style>
.modal {
    z-index: 1050;
}

.modal-backdrop {
    z-index: 1040;
}

.modal-open .modal {
    overflow-x: hidden;
    overflow-y: auto;
}

#editTokoNameModal .modal-body {
    padding: 1rem;
}

#editTokoNameModal .form-control {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

#editTokoNameModal .form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

#brand-link-toko {
    position: relative;
    z-index: 9999;
    pointer-events: auto;
}

#brand-link-toko:hover {
    background-color: rgba(255, 255, 255, 0.1);
    text-decoration: none;
}

#nama-toko-display {
    display: inline-block;
    padding: 5px;
}
</style>