<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Stok_masuk_model;

class Laporan_stok_masuk extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all methods within the controller.
     *
     * @var array
     */
    protected $helpers = ['session', 'url']; // Memuat helper 'session' untuk fungsi session() dan 'url' untuk redirect()
    protected Stok_masuk_model $stokMasukModel;
        public function __construct()
        {
            $this->stokMasukModel = new Stok_masuk_model();
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
            exit(); // Ensure script stops execution after redirect

        }
    }

    /**
     * Metode index untuk menampilkan halaman laporan stok masuk.
     *
     * @return string
     */
    public function index(): string
    {
        // Memuat view 'laporan_stok_masuk'.
        // Di CodeIgniter 4, Anda cukup menggunakan fungsi helper view() untuk memuat view.
        return view('laporan_stok_masuk');
    }
        /**
     * Membaca data laporan stok masuk untuk DataTables.
     * Mengembalikan data dalam format JSON.
     *
     * @return ResponseInterface
     */
    public function read(): ResponseInterface
    {

        $data = [];
        // Memanggil metode dari Stok_masuk_model untuk mendapatkan data laporan stok masuk
        $laporanData = $this->stokMasukModel->readLaporanStokMasuk();  // referensi dari Model/stok_masuk_model.php

        if (!empty($laporanData)) {
            foreach ($laporanData as $stokMasuk) {
                try {
                    $tanggal = new \DateTime($stokMasuk['tanggal']);
                    $formattedTanggal = $tanggal->format('d-m-Y H:i:s');
                } catch (\Exception $e) {
                    log_message('error', 'Failed to parse date for stok masuk report: ' . ($stokMasuk['tanggal'] ?? 'N/A') . ' - ' . $e->getMessage());
                    $formattedTanggal = 'Invalid Date';
                }

                $data[] = [
                    'tanggal'       => $formattedTanggal,
                    'barcode'       => esc($stokMasuk['barcode'] ?? 'N/A'),
                    'nama_produk'   => esc($stokMasuk['nama_produk'] ?? 'Produk Tidak Ditemukan'),
                    'jumlah'        => number_format((float)($stokMasuk['jumlah'] ?? 0), 0, ',', '.'),
                    'keterangan'    => esc($stokMasuk['keterangan'] ?? 'Tanpa Keterangan'),
                    'supplier'      => esc($stokMasuk['supplier_nama'] ?? 'Umum'),
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

            $data = [];
            // Memanggil metode dari Stok_masuk_model untuk mendapatkan data laporan stok masuk
            $laporanData = $this->stokMasukModel->laporanStokMasuk($startDate, $endDate);  // referensi dari Model/stok_masuk_model.php

            if (!empty($laporanData)) {
                foreach ($laporanData as $stokMasuk) {
                    try {
                        $tanggal = new \DateTime($stokMasuk['tanggal']);
                        $formattedTanggal = $tanggal->format('d-m-Y H:i:s');
                    } catch (\Exception $e) {
                        log_message('error', 'Failed to parse date for stok masuk report: ' . ($stokMasuk['tanggal'] ?? 'N/A') . ' - ' . $e->getMessage());
                        $formattedTanggal = 'Invalid Date';
                    }

                    $data[] = [
                        'tanggal'       => $formattedTanggal,
                        'barcode'       => esc($stokMasuk['barcode'] ?? 'N/A'),
                        'nama_produk'   => esc($stokMasuk['nama_produk'] ?? 'Produk Tidak Ditemukan'),
                        'jumlah'        => number_format((float)($stokMasuk['jumlah'] ?? 0), 0, ',', '.'),
                        'keterangan'    => esc($stokMasuk['keterangan'] ?? 'Tanpa Keterangan'),
                        'supplier'      => esc($stokMasuk['supplier_nama'] ?? 'Umum'),
                    ];
                }
            }
            return $this->response->setJSON(['data' => $data]);
        }
}
