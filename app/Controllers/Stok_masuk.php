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
        $data = []; // Inisialisasi array data
        
        // Panggil metode readStokMasuk() dari model
        $stokMasukList = $this->stokMasukModel->readStokMasuk();

        if (!empty($stokMasukList)) {
            foreach ($stokMasukList as $stok_masuk) {
                // Pastikan kolom 'tanggal' ada dan formatnya benar untuk DateTime
                // Asumsi $stok_masuk adalah array asosiatif (sesuai returnType model)
                $tanggal = new \DateTime($stok_masuk['tanggal']); 
                $data[] = [
                    'tanggal'      => $tanggal->format('d-m-Y H:i:s'),
                    'barcode'      => esc($stok_masuk['barcode']),     // Gunakan esc() untuk escaping HTML
                    'nama_produk'  => esc($stok_masuk['nama_produk']), // Gunakan esc() untuk escaping HTML
                    'jumlah'       => esc($stok_masuk['jumlah']),
                    'keterangan'   => esc($stok_masuk['keterangan']), // Gunakan esc() untuk escaping HTML
                    'supplier'      => esc($stokMasuk['supplier_nama'] ?? 'Umum'),
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
        $produkId     = $this->request->getPost('barcode'); // Asumsi 'barcode' dari form adalah ID produk
        $jumlah       = (int)$this->request->getPost('jumlah');
        $keterangan   = $this->request->getPost('keterangan');
        $supplierId   = $this->request->getPost('supplier'); // Ambil ID supplier
        $tanggalInput = $this->request->getPost('tanggal'); // Ambil tanggal dari input form

        // 1. Ambil stok saat ini dari tabel produk
        $currentStokData = $this->stokMasukModel->getStokProduk($produkId);
        $currentStok = $currentStokData['stok'] ?? 0; // Default ke 0 jika produk tidak ditemukan atau stok kosong

        // 2. Hitung stok baru (penambahan)
        $newStok = $currentStok + $jumlah;

        // 3. Perbarui stok di tabel produk
        $updateStokSuccess = $this->stokMasukModel->addStokProduk($produkId, $newStok); // Nama fungsi di model adalah addStokProduk

        if ($updateStokSuccess) {
            // 4. Jika update stok produk sukses, simpan data stok masuk
            // Pastikan format tanggal sesuai atau gunakan current datetime jika tanggal input kosong
            $tanggal = new \DateTime($tanggalInput ?: 'now'); // Menggunakan 'now' jika tanggal input kosong

            $dataStokMasuk = [
                'tanggal'    => $tanggal->format('Y-m-d H:i:s'),
                'produk_id'  => $produkId,   // Menggunakan produk_id sebagai Foreign Key ke tabel produk
                'jumlah'     => $jumlah,
                'keterangan' => $keterangan,
                'supplier_id' => $supplierId // Simpan ID supplier
            ];

            if ($this->stokMasukModel->createStokMasuk($dataStokMasuk)) {
                return $this->response->setJSON(['status' => 'sukses']);
            } else {
                // Jika gagal mencatat stok masuk, pertimbangkan untuk melakukan rollback stok produk
                // (Logika rollback bisa lebih kompleks, untuk saat ini hanya respons gagal)
                return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal mencatat transaksi stok masuk.']);
            }
        } else {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal memperbarui stok produk.']);
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
        $now = date('d m Y'); // Contoh: '28 05 2025'
        
        // Panggil metode stokMasukHari() dari model
        $totalStok = $this->stokMasukModel->stokMasukHari($now);

        // Mengembalikan total (atau 0 jika null) dalam format JSON
        // Pastikan model mengembalikan array dengan kunci 'total'
        $response = $totalStok['total'] ?? 0; // Menggunakan null coalescing operator untuk default 0

        return $this->response->setJSON($response);
    }
}