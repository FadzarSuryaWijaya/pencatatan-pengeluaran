<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Dashboard'); // Default controller jika tidak ada yang ditentukan di URL
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is disabled by default.
// You can enable it by moving the comment from the line below.
// $routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We GET a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->GET('/', 'Dashboard::index', ['filter' => 'role: admin']);
$routes->POST('dashboard/penjualan_bulan', 'Dashboard::penjualan_bulan');




// Signup routes
$routes->match(['GET', 'POST'], 'auth/daftar', 'Auth::signup');

// Auth Controller Routes
$routes->match(['GET', 'POST'], 'auth/login', 'Auth::login');
$routes->GET('auth/logout', 'Auth::logout');
$routes->POST('auth/updateNamaToko', 'Auth::updateNamaToko', ['filter' => 'role: user']);

// Pengaturan Controller Routes
$routes->GET('pengaturan', 'Pengaturan::index');
$routes->POST('pengaturan/set_toko', 'Pengaturan::set_toko');

// Pelanggan Controller Routes
$routes->GET('pelanggan', 'Pelanggan::index'); // Menampilkan halaman utama pelanggan
$routes->GET('pelanggan/read', 'Pelanggan::read'); // Membaca data untuk tabel (biasanya GET untuk DataTables)
$routes->POST('pelanggan/add', 'Pelanggan::add'); // Menambahkan pelanggan baru
$routes->POST('pelanggan/delete', 'Pelanggan::delete'); // Menghapus pelanggan
$routes->POST('pelanggan/edit', 'Pelanggan::edit'); // Memperbarui pelanggan
$routes->POST('pelanggan/get_pelanggan', 'Pelanggan::get_pelanggan'); // Mengambil satu pelanggan berdasarkan ID (biasanya POST untuk request AJAX)
$routes->POST('pelanggan/search', 'Pelanggan::search'); // Mencari pelanggan (biasanya POST untuk Select2/autocomplete)

// Kategori_produk Controller Routes
$routes->GET('kategori_produk', 'Kategori_produk::index'); // Menampilkan halaman utama kategori produk
$routes->GET('kategori_produk/read', 'Kategori_produk::read'); // Membaca data untuk tabel
$routes->POST('kategori_produk/add', 'Kategori_produk::add'); // Menambahkan kategori baru
$routes->POST('kategori_produk/delete', 'Kategori_produk::delete'); // Menghapus kategori
$routes->POST('kategori_produk/edit', 'Kategori_produk::edit'); // Memperbarui kategori
$routes->POST('kategori_produk/GET_kategori', 'Kategori_produk::GET_kategori'); // Mengambil satu kategori berdasarkan ID
$routes->POST('kategori_produk/search', 'Kategori_produk::search'); // Mencari kategori

// Laporan_stok_keluar Controller Routes
$routes->GET('laporan_stok_keluar', 'Laporan_stok_keluar::index');
$routes->GET('laporan_stok_keluar/read', 'Laporan_stok_keluar::read'); // Membaca data untuk tabel laporan stok keluar
$routes->GET('laporan_stok_keluar/filter_tanggal', 'Laporan_stok_keluar::filter_tanggal'); // Jika Anda punya filter
$routes->POST('laporan_stok_keluar/filter_tanggal', 'Laporan_stok_keluar::filter_tanggal'); // Jika Anda punya filter

// Laporan_stok_masuk Controller Routes
$routes->GET('laporan_stok_masuk', 'Laporan_stok_masuk::index');
$routes->GET('laporan_stok_masuk/read', 'Laporan_stok_masuk::read');
$routes->GET('laporan_stok_masuk/filter_tanggal', 'Laporan_stok_masuk::filter_tanggal');
$routes->POST('laporan_stok_masuk/filter_tanggal', 'Laporan_stok_masuk::filter_tanggal');


// Laporan_penjualan Controller Routes
$routes->GET('laporan_penjualan', 'Laporan_penjualan::index');
$routes->GET('laporan_penjualan/read', 'Laporan_penjualan::read');
$routes->GET('laporan_penjualan/filter_tanggal', 'Laporan_penjualan::filter_tanggal'); // Jika Anda punya filter
$routes->POST('laporan_penjualan/filter_tanggal', 'Laporan_penjualan::filter_tanggal'); // Jika Anda punya filter



// --- Penambahan Rute Baru dari Controller yang Disediakan ---

// Pengguna Controller Routes
$routes->GET('pengguna', 'Pengguna::index');
$routes->GET('pengguna/read', 'Pengguna::read');
$routes->POST('pengguna/add', 'Pengguna::add');
$routes->POST('pengguna/delete', 'Pengguna::delete');
$routes->POST('pengguna/edit', 'Pengguna::edit');
$routes->POST('pengguna/get_pengguna', 'Pengguna::get_pengguna');
$routes->POST('pengguna/search', 'Pengguna::search');

// Produk Controller Routes
$routes->GET('produk', 'Produk::index');
$routes->GET('produk/read', 'Produk::read');
$routes->POST('produk/add', 'Produk::add');
$routes->POST('produk/delete', 'Produk::delete');
$routes->POST('produk/edit', 'Produk::edit');
$routes->POST('produk/get_produk', 'Produk::get_produk');
$routes->POST('produk/get_nama', 'Produk::get_nama');
$routes->POST('produk/get_stok', 'Produk::get_stok');
$routes->POST('produk/search', 'Produk::search');
$routes->POST('produk/GET_detail_stok', 'Produk::GET_detail_stok');
$routes->GET('produk/produk_terlaris', 'Produk::produk_terlaris'); // Asumsi ini untuk chart/data, bisa GET
$routes->GET('produk/data_stok', 'Produk::data_stok'); // Asumsi ini untuk chart/data, bisa GET

// Satuan_produk Controller Routes
$routes->GET('satuan_produk', 'Satuan_produk::index');
$routes->GET('satuan_produk/read', 'Satuan_produk::read');
$routes->POST('satuan_produk/add', 'Satuan_produk::add');
$routes->POST('satuan_produk/delete', 'Satuan_produk::delete');
$routes->POST('satuan_produk/edit', 'Satuan_produk::edit');
$routes->POST('satuan_produk/get_satuan', 'Satuan_produk::get_satuan');
$routes->POST('satuan_produk/search', 'Satuan_produk::search');

// Supplier Controller Routes
$routes->GET('supplier', 'Supplier::index');
$routes->GET('supplier/read', 'Supplier::read');
$routes->POST('supplier/add', 'Supplier::add');
$routes->POST('supplier/delete', 'Supplier::delete');
$routes->POST('supplier/edit', 'Supplier::edit');
$routes->POST('supplier/get_supplier', 'Supplier::get_supplier');
$routes->POST('supplier/search', 'Supplier::search');

// Stok_masuk Controller Routes
$routes->GET('stok_masuk', 'Stok_masuk::index');
$routes->GET('stok_masuk/read', 'Stok_masuk::read');
$routes->POST('stok_masuk/read', 'Stok_masuk::read');
$routes->POST('stok_masuk/add', 'Stok_masuk::add');
$routes->POST('stok_masuk/get_barcode', 'Stok_masuk::get_barcode'); // Mengambil detail produk berdasarkan barcode
$routes->GET('stok_masuk/stok_hari', 'Stok_masuk::stok_hari'); // Mengambil total stok masuk untuk hari ini

// Stok_keluar Controller Routes
$routes->GET('stok_keluar', 'Stok_keluar::index');
$routes->GET('stok_keluar/read', 'Stok_keluar::read');
$routes->POST('stok_keluar/read', 'Stok_keluar::read');
$routes->POST('stok_keluar/add', 'Stok_keluar::add');
$routes->POST('stok_keluar/get_barcode', 'Stok_keluar::get_barcode'); // Mengambil detail produk berdasarkan barcode
$routes->GET('stok_keluar/stok_hari', 'Stok_keluar::stok_hari'); // Mengambil total stok keluar untuk hari ini


// Transaksi Controller Routes
$routes->GET('transaksi', 'Transaksi::index');
$routes->POST('transaksi/add', 'Transaksi::add'); // Menambahkan transaksi baru
$routes->POST('transaksi/get_barcode', 'Transaksi::get_barcode'); // Mengambil info produk berdasarkan barcode
$routes->GET('transaksi/data_chart', 'Transaksi::data_chart'); // Mengambil data untuk chart (penjualan per hari)
$routes->GET('transaksi/transaksi_hari', 'Transaksi::transaksi_hari'); // Mengambil total transaksi hari ini
$routes->GET('transaksi/transaksi_terakhir', 'Transaksi::transaksi_terakhir'); // Mengambil data transaksi terakhir hari ini
$routes->get('transaksi/read', 'Transaksi::read'); 
$routes->get('transaksi/cetak/(:segment)', 'Transaksi::cetak/$1');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes are one such example.
 *
 * Please go to the routes file in your environment to customize it
 * further.
 */