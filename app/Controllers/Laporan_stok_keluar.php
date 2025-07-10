<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse; // Untuk return type hint Redirect
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Stok_keluar_model;


class Laporan_stok_keluar extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all methods within the controller.
     *
     * @var array
     */
    protected $helpers = ['session', 'url']; // Memuat helper 'session' untuk fungsi session() dan 'url' untuk redirect()
    protected Stok_keluar_model $stokKeluarModel;
    public function __construct()
    {
        $this->stokKeluarModel = new Stok_keluar_model();
    }



    /**
     * Metode initController digunakan untuk inisialisasi awal controller.
     * Ini adalah tempat yang tepat untuk melakukan pengecekan autentikasi/login.
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request
     * @param \CodeIgniter\HTTP\ResponseInterface $response
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        // Jangan edit baris ini
        parent::initController($request, $response, $logger);

        // Ambil instance layanan sesi
        $session = session();

        // Periksa status login pengguna. Jika tidak 'login', arahkan kembali ke halaman utama.
        if ($session->get('status') !== 'login') {
            return redirect()->to('/'); // Ubah ini ke URL halaman login atau halaman utama Anda jika diperlukan
            exit();
        }
    }

    /**
     * Metode index untuk menampilkan halaman laporan stok keluar.
     *
     * @return string
     */
    public function index(): string
    {
        // Memuat view 'laporan_stok_keluar'.
        // Di CodeIgniter 4, Anda cukup menggunakan fungsi helper view() untuk memuat view.
        return view('laporan_stok_keluar');
    }
    /**
     * Membaca data stok keluar untuk ditampilkan di tabel (misalnya DataTables).
     * Mengembalikan data dalam format JSON.
     *
     * @return ResponseInterface
     */
    public function read(): ResponseInterface
    {
        $data = []; // Inisialisasi array data

        // Panggil metode readStokKeluar() dari model
        $stokKeluarList = $this->stokKeluarModel->readLaporanStokKeluar();

        if (!empty($stokKeluarList)) {
            foreach ($stokKeluarList as $stok_keluar) {
                // Pastikan kolom 'tanggal' ada dan formatnya benar untuk DateTime
                // Asumsi $stok_keluar adalah array asosiatif (sesuai returnType model)
                $tanggal = new \DateTime($stok_keluar['tanggal']);
                $data[] = [
                    'tanggal'      => $tanggal->format('d-m-Y H:i:s'),
                    'barcode'       => esc($stok_keluar['produk_barcode'] ?? 'N/A'),
                    'nama_produk'   => esc($stok_keluar['nama_produk'] ?? 'Produk Tidak Ditemukan'),
                    'jumlah'        => number_format((float)($stok_keluar['jumlah'] ?? 0), 0, ',', '.'),
                    'keterangan'    => esc($stok_keluar['keterangan'] ?? 'Tanpa Keterangan'),
                ];
            }
        }
        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * Membaca data laporan penjualan untuk DataTables berdasarkan rentang tanggal.
     * Mengembalikan data dalam format JSON.
     *
     * @return ResponseInterface
     */
    public function filter_tanggal(): ResponseInterface
    {
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');

        $data = []; // Inisialisasi array data

        // Panggil metode readStokKeluar() dari model
        $stokKeluarList = $this->stokKeluarModel->laporanStokKeluar($startDate, $endDate);

        if (!empty($stokKeluarList)) {
            foreach ($stokKeluarList as $stok_keluar) {
                try {
                    $tanggal = new \DateTime($stok_keluar['tanggal']);
                    $formattedTanggal = $tanggal->format('d-m-Y H:i:s');
                } catch (\Exception $e) {
                    log_message('error', 'Failed to parse date for stok masuk report: ' . ($stokKeluar['tanggal'] ?? 'N/A') . ' - ' . $e->getMessage());
                    $formattedTanggal = 'Invalid Date';
                }
                $data[] = [
                    'tanggal'      => $formattedTanggal,
                    'barcode'       => esc($stok_keluar['produk_barcode'] ?? 'N/A'),
                    'nama_produk'   => esc($stok_keluar['nama_produk'] ?? 'Produk Tidak Ditemukan'),
                    'jumlah'        => number_format((float)($stok_keluar['jumlah'] ?? 0), 0, ',', '.'),
                    'keterangan'    => esc($stok_keluar['keterangan'] ?? 'Tanpa Keterangan'),
                ];
            }
        }
        return $this->response->setJSON(['data' => $data]);
    }
}
