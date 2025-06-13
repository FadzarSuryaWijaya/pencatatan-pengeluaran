<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Transaksi_model;

class Laporan_penjualan extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all methods within the controller.
     *
     * @var array
     */
    protected $helpers = ['session', 'url', 'date'];

    protected Transaksi_model $transaksiModel;

    /**
     * Constructor untuk menginisialisasi controller.
     * Inisialisasi model dilakukan di sini.
     */
    public function __construct()
    {
        $this->transaksiModel = new Transaksi_model();
    }

    /**
     * Metode initController digunakan untuk inisialisasi awal.
     * Ini adalah tempat yang baik untuk melakukan pengecekan login.
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $session = session();
        // Memeriksa status login. Jika tidak 'login', arahkan ke halaman utama/login.
        if ($session->get('status') !== 'login') {
            // Use CodeIgniter's built-in redirect helper
            $response->redirect(base_url('/'));
            exit(); // Ensure script stops execution after redirect
        }
    }

    /**
     * Menampilkan halaman laporan penjualan.
     *
     * @return string
     */
    public function index(): string
    {
        // Memuat view 'laporan_penjualan'.
        return view('laporan_penjualan');
    }

    /**
     * Membaca data laporan penjualan untuk DataTables.
     * Mengembalikan data dalam format JSON.
     *
     * @return ResponseInterface
     */
    public function read(): ResponseInterface
    {
        $data = [];
        
        // Panggil metode dari Transaksi_model untuk mendapatkan data laporan penjualan
        $laporanData = $this->transaksiModel->readTransaksi();

        if (!empty($laporanData)) {
            foreach ($laporanData as $transaksi) {
                try {
                    $tanggal = new \DateTime($transaksi['tanggal']);
                    $formattedTanggal = $tanggal->format('d-m-Y H:i:s');
                } catch (\Exception $e) {
                    log_message('error', 'Failed to parse date for report: ' . ($transaksi['tanggal'] ?? 'N/A') . ' - ' . $e->getMessage());
                    $formattedTanggal = 'Invalid Date';
                }

                $barcodeIds = explode(',', $transaksi['barcode'] ?? '');
                $qtysString = $transaksi['qty'] ?? '';
                $produkDetails = $this->transaksiModel->getProdukTransaksiDetail($barcodeIds, $qtysString);
                
                $namaProdukHtml = '<table>';
                if (!empty($produkDetails)) {
                    foreach ($produkDetails as $detail) {
                        $namaProdukHtml .= '<tr><td>' . esc($detail['nama_produk']) . '</td><td>' . esc($detail['qty']) . '</td></tr>';
                    }
                }
                $namaProdukHtml .= '</table>';

                $data[] = [
                    'tanggal'       => $formattedTanggal,
                    'nota'          => esc($transaksi['nota'] ?? 'N/A'),
                    'nama_produk'   => $namaProdukHtml,
                    'total_bayar'   => 'Rp. ' . number_format((float)($transaksi['total_bayar'] ?? 0), 0, ',', '.'),
                    'jumlah_uang'   => 'Rp. ' . number_format((float)($transaksi['jumlah_uang'] ?? 0), 0, ',', '.'),
                    'diskon'        => number_format((float)($transaksi['diskon'] ?? 0), 0, ',', '.') . '%',
                    'pelanggan'     => esc($transaksi['pelanggan_nama'] ?? 'Umum'),
                    'kasir'         => esc($transaksi['kasir_nama'] ?? 'N/A'),
                    
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
        // PERUBAHAN KRUSIAL DI SINI: Ubah getPost() menjadi getGet()
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');

        $data = [];
        // Perubahan logika di model akan menangani parameter null/kosong ini
        $laporanData = $this->transaksiModel->getTransaksiByDateRange($startDate, $endDate); 

        if (!empty($laporanData)) {
            foreach ($laporanData as $transaksi) {
                try {
                    $tanggal = new \DateTime($transaksi['tanggal']);
                    $formattedTanggal = $tanggal->format('d-m-Y H:i:s');
                } catch (\Exception $e) {
                    log_message('error', 'Failed to parse date for report: ' . ($transaksi['tanggal'] ?? 'N/A') . ' - ' . $e->getMessage());
                    $formattedTanggal = 'Invalid Date';
                }

                $barcodeIds = explode(',', $transaksi['barcode'] ?? '');
                $qtysString = $transaksi['qty'] ?? '';
                $produkDetails = $this->transaksiModel->getProdukTransaksiDetail($barcodeIds, $qtysString);
                
                $namaProdukHtml = '<table>';
                if (!empty($produkDetails)) {
                    foreach ($produkDetails as $detail) {
                        $namaProdukHtml .= '<tr><td>' . esc($detail['nama_produk']) . '</td><td>' . esc($detail['qty']) . '</td></tr>';
                    }
                }
                $namaProdukHtml .= '</table>';

                $data[] = [
                    'tanggal'       => $formattedTanggal,
                    'nota'          => esc($transaksi['nota'] ?? 'N/A'),
                    'nama_produk'   => $namaProdukHtml,
                    'total_bayar'   => 'Rp. ' . number_format((float)($transaksi['total_bayar'] ?? 0), 0, ',', '.'),
                    'jumlah_uang'   => 'Rp. ' . number_format((float)($transaksi['jumlah_uang'] ?? 0), 0, ',', '.'),
                    'diskon'        => number_format((float)($transaksi['diskon'] ?? 0), 0, ',', '.') . '%',
                    'pelanggan'     => esc($transaksi['pelanggan_nama'] ?? 'Umum'),
                    'kasir'         => esc($transaksi['kasir_nama'] ?? 'N/A'),

                ];
            }
        }

        return $this->response->setJSON(['data' => $data]);
    }
}