<!DOCTYPE html>
<html>
<head>
  <title>Dashboard</title>
  <?= $this->include('partials/head') ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?= $this->include('includes/nav'); ?>

  <?= $this->include('includes/aside'); ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col">
            <h1 class="m-0 text-dark">Dashboard</h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>

    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-4 col-sm-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3 id="transaksi_hari">0</h3>
                <p>Transaksi Hari Ini</p>
              </div>
              <div class="icon">
                <i class="fas fa-shopping-cart"></i>
              </div>
              <a href="<?php echo site_url('transaksi') ?>" class="small-box-footer">
                More Info <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
          <div class="col-lg-4 col-sm-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3 id="transaksi_terakhir">0</h3>
                <p>Produk Transaksi Terakhir</p>
              </div>
              <div class="icon">
                <i class="fas fa-money-bill"></i>
              </div>
              <a href="<?php echo site_url('laporan_penjualan') ?>" class="small-box-footer">
                More Info <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
          <div class="col-lg-4 col-sm-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <h3 id="stok_hari">0</h3>
                <p>Stok Masuk Hari Ini</p>
              </div>
              <div class="icon">
                <i class="fas fa-archive"></i>
              </div>
              <a href="<?php echo site_url('laporan_stok_masuk') ?>" class="small-box-footer">
                More Info <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
          <div class="col-12">
            <h1 class="mt-2 mb-3 h2 text-dark">Grafik</h1>
          </div>
          <div class="col-md-6">
            <div class="card card-primary">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0" id="judul-produk-terlaris">Produk Terlaris - 1 Bulan Terakhir</h3>
                <div class="card-tools">
                  <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-light btn-sm periode-btn" data-periode="today">
                      Hari Ini
                    </button>
                    <button type="button" class="btn btn-light btn-sm periode-btn active" data-periode="1month">
                      1 Bulan
                    </button>
                    <button type="button" class="btn btn-outline-light btn-sm periode-btn" data-periode="1year">
                      1 Tahun
                    </button>
                    <button type="button" class="btn btn-outline-light btn-sm periode-btn" data-periode="all">
                      Semua
                    </button>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="chart">
                  <canvas id="produkTerlaris" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%"></canvas>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card card-warning">
              <div class="card-header">
                <h3 class="card-title">Stok Produk</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" onclick="refreshStokProduk()">
                    <i class="fas fa-sync-alt"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="chart" style="height: 250px;max-height: 250px; overflow-y: scroll;">
                  <ul class="list-group" id="stok_produk"></ul>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12">
            <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title">Penjualan Bulan Ini</h3> 
                <div class="card-tools">
                  <span class="badge badge-success" id="total-penjualan-bulan"></span>
                </div>
              </div>
              <div class="card-body">
                <div class="chart">
                  <canvas id="bulanIni" style="min-height: 250px; height: 450px; max-height: 450px; max-width: 100%"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

</div>
<!-- ./wrapper -->

<?= $this->include('includes/footer'); ?>

<?= $this->include('partials/footer'); ?>
<script src="<?php echo base_url('assets/vendor/adminlte/plugins/chart.js/Chart.min.js') ?>"></script>
<script>
  var transaksi_hariUrl = '<?php echo site_url('transaksi/transaksi_hari') ?>';
  var transaksi_terakhirUrl = '<?php echo site_url('transaksi/transaksi_terakhir') ?>';
  var stok_hariUrl = '<?php echo site_url('stok_masuk/stok_hari') ?>';
  var produk_terlarisUrl = '<?php echo site_url('produk/produk_terlaris') ?>';
  var data_stokUrl = '<?php echo site_url('produk/data_stok') ?>';
  var penjualan_bulanUrl = '<?php echo site_url('dashboard/penjualan_bulan') ?>';

  // Function untuk refresh stok produk
  function refreshStokProduk() {
    $("#stok_produk").empty();
    $.ajax({
      url: data_stokUrl,
      type: "get",
      dataType: "json",
      success: res => {
        $.each(res, (key, index) => {
          let html = `<li class="list-group-item">
            ${index.nama_produk}
            <span class="float-right">${index.stok}</span>
          </li>`;
          $("#stok_produk").append(html)
        })
      },
      error: () => {
        $("#stok_produk").append('<li class="list-group-item text-danger">Gagal memuat data stok</li>');
      }
    });
  }

  // Event handler untuk tombol periode
  $(document).ready(function() {
    $('.periode-btn').on('click', function() {
      // Remove active class from all buttons
      $('.periode-btn').removeClass('btn-light active').addClass('btn-outline-light');
      
      // Add active class to clicked button
      $(this).removeClass('btn-outline-light').addClass('btn-light active');
      
      // Get selected periode
      var periode = $(this).data('periode');
      
      // Update chart title
      var titleText = 'Produk Terlaris - ';
      switch(periode) {
        case 'today':
          titleText += 'Hari Ini';
          break;
        case '1month':
          titleText += '1 Bulan Terakhir';
          break;
        case '1year':
          titleText += '1 Tahun Terakhir';
          break;
        case 'all':
          titleText += 'Semua Waktu';
          break;
      }
      $('#judul-produk-terlaris').text(titleText);
      
      // Here you can add AJAX call to load chart data based on selected periode
      // loadProdukTerlaris(periode);
    });
  });
</script>
<script src="<?php echo base_url('assets/js/unminify/dashboard.js') ?>"></script>

<style>
/* Custom styling untuk card header produk terlaris */
.card-primary .card-header {
  margin-right: 20px; 
  min-height: 60px;
  padding: 10px 15px;
}

/* Custom styling untuk tombol periode */
.periode-btn {
  font-size: 11px;
  padding: 4px 10px;
  white-space: nowrap;
  border: 1px solid rgba(255,255,255,0.3);
  margin: 0;
}

.periode-btn:not(:first-child) {
  border-left: none;
}

.periode-btn.btn-light.active {
  background-color: rgba(255,255,255,0.95);
  border-color: rgba(255,255,255,0.95);
  color: #007bff;
  font-weight: 600;
}

.periode-btn.btn-outline-light {
  background-color: transparent;
  border-color: rgba(255,255,255,0.3);
  color: rgba(255,255,255,0.8);
}

.periode-btn:hover {
  background-color: rgba(255,255,255,0.2);
  border-color: rgba(255,255,255,0.5);
  color: white;
}

.periode-btn.btn-light:hover {
  background-color: rgba(255,255,255,1);
  color: #007bff;
}

/* Memastikan btn-group tidak wrap */
.btn-group {
  flex-wrap: nowrap;
}

/* Flex untuk card header */
.card-header.d-flex {
  flex-wrap: nowrap;
}

.card-tools {
  flex-shrink: 0;
  margin-left: auto;
}

/* Responsive untuk mobile */
@media (max-width: 576px) {
  .periode-btn {
    font-size: 10px;
    padding: 3px 6px;
  }
  
  .card-title {
    font-size: 14px;
  }
}

/* Styling untuk info produk */
.produk-info {
  font-size: 12px;
  margin-bottom: 10px;
}

/* Loading animation */
.chart-loading {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 250px;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}
/* Add this to your existing styles */
/* Chart Container */
.card-success .card-body {
    padding: 15px;
}

/* Chart Canvas */
#bulanIni {
    min-height: 350px;
    height: 350px;
    max-height: 350px;
}

/* Total Sales Badge */
#total-penjualan-bulan {
    font-size: 14px;
    padding: 5px 10px;
    background-color: #28a745 !important;
}
</style>

</body>
</html>