<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Stok_masuk_model; // Pastikan Anda mengimpor model Stok_masuk_model
use App\Models\Produk_model;     // Pastikan Anda mengimpor model Produk_model (digunakan di get_barcode())
use CodeIgniter\HTTP\ResponseInterface; // Untuk return type hint
use CodeIgniter\HTTP\RedirectResponse; // Untuk return type hint Redirect

class Stok_masuk extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all methods within the controller.
     *
     * @var array
     */
    protected $helpers = ['session', 'url', 'date']; // Memuat helper 'session', 'url', dan 'date'

    protected Stok_masuk_model $stokMasukModel; // Deklarasikan properti untuk Stok_masuk_model
    protected Produk_model $produkModel;        // Deklarasikan properti untuk Produk_model

    /**
     * Constructor untuk menginisialisasi controller.
     * Inisialisasi model dilakukan di sini.
     */
    public function __construct()
    {
        $this->stokMasukModel = new Stok_masuk_model(); // Inisialisasi Stok_masuk_model
        $this->produkModel    = new Produk_model();     // Inisialisasi Produk_model
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
        }
    }

    /**
     * Metode index untuk menampilkan halaman manajemen stok masuk.
     *
     * @return string
     */
    public function index(): string
    {
        // Memuat view 'stok_masuk'.
        // Di CodeIgniter 4, Anda cukup menggunakan fungsi helper view() untuk memuat view.
        return view('stok_masuk');
    }

    /**
     * Membaca data stok masuk untuk ditampilkan di tabel (misalnya DataTables).
     * Mengembalikan data dalam format JSON.
     *
     * @return ResponseInterface
     */
    public function read(): ResponseInterface
    {
        $data = [];
        
        $stokMasukList = $this->stokMasukModel->readStokMasuk();

        if (!empty($stokMasukList)) {
            foreach ($stokMasukList as $stok_masuk) {
                try {
                    $tanggal = new \DateTime($stok_masuk['tanggal']);
                    $formattedTanggal = $tanggal->format('d-m-Y H:i:s');
                } catch (\Exception $e) {
                    $formattedTanggal = 'Invalid Date';
                    log_message('error', 'Date parsing error: ' . $e->getMessage());
                }

                $data[] = [
                    'tanggal'      => $formattedTanggal,
                    'barcode'      => esc($stok_masuk['produk_barcode'] ?? 'N/A'), // PERBAIKAN: gunakan produk_barcode
                    'nama_produk'  => esc($stok_masuk['nama_produk'] ?? 'Produk Tidak Ditemukan'),
                    'jumlah'       => esc($stok_masuk['jumlah'] ?? '0'),
                    'keterangan'   => esc($stok_masuk['keterangan'] ?? 'Tanpa Keterangan'),
                    'supplier'     => esc($stok_masuk['supplier_nama'] ?? 'Umum'),
                    'action'       => '',
                ];
            }
        }

        $response = [
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    /**
     * Menambahkan data stok masuk baru dan menambah stok produk.
     *
     * @return ResponseInterface
     */
    public function add(): ResponseInterface
    {
        // Debug: Log input data
        $inputData = [
            'barcode' => $this->request->getPost('barcode'),
            'jumlah' => $this->request->getPost('jumlah'),
            'keterangan' => $this->request->getPost('keterangan'),
            'supplier' => $this->request->getPost('supplier'),
            'tanggal' => $this->request->getPost('tanggal')
        ];
        log_message('debug', 'Input data: ' . print_r($inputData, true));

        $produkId     = $this->request->getPost('barcode'); // ID produk yang dipilih
        $jumlah       = (int)$this->request->getPost('jumlah');
        $keterangan   = $this->request->getPost('keterangan');
        $supplierId   = $this->request->getPost('supplier');
        $tanggalInput = $this->request->getPost('tanggal');

        // Validasi input
        if (empty($produkId) || $jumlah <= 0) {
            return $this->response->setJSON([
                'status' => 'gagal', 
                'message' => 'Data tidak valid. Pastikan produk dipilih dan jumlah > 0.'
            ]);
        }

        // 1. Ambil stok saat ini dari tabel produk
        $currentStokData = $this->stokMasukModel->getStokProduk($produkId);
        $currentStok = (int)($currentStokData['stok'] ?? 0);

        // 2. Hitung stok baru
        $newStok = $currentStok + $jumlah;

        // 3. Perbarui stok di tabel produk
        $updateStokSuccess = $this->stokMasukModel->addStokProduk($produkId, $newStok);

        if ($updateStokSuccess) {
            // 4. Simpan data stok masuk
            $tanggal = !empty($tanggalInput) ? new \DateTime($tanggalInput) : new \DateTime();

            // PERBAIKAN: Gunakan nama field yang sesuai dengan allowedFields
            $dataStokMasuk = [
                'tanggal'    => $tanggal->format('Y-m-d H:i:s'),
                'barcode'    => $produkId,    // FK ke tabel produk
                'jumlah'     => $jumlah,
                'keterangan' => $keterangan,
                'supplier'   => $supplierId,  // FK ke tabel supplier
                // 'user_id'    => session()->get('user_id') ?? null // Jika ada session user
            ];

            log_message('debug', 'Data to insert: ' . print_r($dataStokMasuk, true));

            if ($this->stokMasukModel->createStokMasuk($dataStokMasuk)) {
                return $this->response->setJSON(['status' => 'sukses']);
            } else {
                // Rollback stok jika gagal insert stok masuk
                $this->stokMasukModel->addStokProduk($produkId, $currentStok);
                return $this->response->setJSON([
                    'status' => 'gagal', 
                    'message' => 'Gagal mencatat transaksi stok masuk.'
                ]);
            }
        } else {
            return $this->response->setJSON([
                'status' => 'gagal', 
                'message' => 'Gagal memperbarui stok produk.'
            ]);
        }
    }

    /**
     * Mengambil detail produk berdasarkan barcode.
     * Diasumsikan ini digunakan untuk mengisi form stok masuk (misalnya dengan Select2).
     *
     * @return ResponseInterface
     */
// ... (bagian lain dari controller Anda)

public function get_barcode(): ResponseInterface
{
    // Ambil parameter 'barcode' dari POST request
    // Gunakan null coalescing operator (?? '') untuk memastikan selalu string
    $searchTerm = $this->request->getPost('barcode') ?? ''; 
    
    // Panggil metode model
    $searchResults = $this->produkModel->getBarcode($searchTerm); 

    $data = [];
    if (!empty($searchResults)) {
        foreach ($searchResults as $row) {
            $data[] = [
                'id'   => esc($row['id']),
                'text' => esc($row['barcode']) . ' - ' . esc($row['nama_produk']) // Pastikan 'nama_produk' juga diambil di model
            ];
        }
    }

    // Penting: Select2 versi terbaru mengharapkan hasil dalam kunci 'results'
        return $this->response->setJSON($data);
}

    /**
     * Mengambil data laporan stok masuk beserta informasi produk dan supplier.
    *
    * @return ResponseInterface
     */
    // public function laporan(): ResponseInterface
    // {
    //     $data = []; // Inisialisasi array data
        
    //     // Panggil metode laporanStokMasuk() dari model
    //     $laporanList = $this->stokMasukModel->laporanStokMasuk();

    //     if (!empty($laporanList)) {
    //         foreach ($laporanList as $stok_masuk) {
    //             $tanggal = new \DateTime($stok_masuk['tanggal']); 
    //             $data[] = [
    //                 'tanggal'      => $tanggal->format('d-m-Y H:i:s'),
    //                 'barcode'      => esc($stok_masuk['barcode']),
    //                 'nama_produk'  => esc($stok_masuk['nama_produk']),
    //                 'jumlah'       => esc($stok_masuk['jumlah']),
    //                 'keterangan'   => esc($stok_masuk['keterangan']),
    //                 'supplier'     => esc($stok_masuk['supplier_nama'] ?? 'N/A') // Gunakan null coalescing jika supplier bisa null
    //             ];
    //         }
    //     }

    //     $response = [
    //         'data' => $data
    //     ];

    //     return $this->response->setJSON($response);
    // }

    /**
     * Mengambil total stok masuk untuk hari ini.
     *
     * @return ResponseInterface
     */
    public function stok_hari(): ResponseInterface
    {
        // Menggunakan helper date() untuk mendapatkan tanggal hari ini dalam format yang sesuai
        $now = date('Y-m-d');        
        log_message('debug', 'Calling stokMasukHari for date: ' . $now);
        // Panggil metode stokMasukHari() dari model
        
        $totalStok = $this->stokMasukModel->stokMasukHari($now);
        log_message('debug', 'Result from model for stokMasukHari: ' . print_r($totalStok, true));

        // Mengembalikan total (atau 0 jika null) dalam format JSON
        // Pastikan model mengembalikan array dengan kunci 'total'
        $response = $totalStok['total'] ?? 0; // Menggunakan null coalescing operator untuk default 0
        log_message('debug', 'JSON response for stok_hari: ' . json_encode($response));

        return $this->response->setJSON($response);
    }
}