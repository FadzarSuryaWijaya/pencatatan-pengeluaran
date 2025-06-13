<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Stok_keluar_model; // Pastikan Anda mengimpor model Stok_keluar_model
use App\Models\Produk_model;      // Pastikan Anda mengimpor model Produk_model (digunakan di get_barcode())
use CodeIgniter\HTTP\ResponseInterface; // Untuk return type hint
use CodeIgniter\HTTP\RedirectResponse; // Untuk return type hint Redirect

class Stok_keluar extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all methods within the controller.
     *
     * @var array
     */
    protected $helpers = ['session', 'url']; // Memuat helper 'session' untuk fungsi session() dan 'url' untuk redirect()

    protected Stok_keluar_model $stokKeluarModel; // Deklarasikan properti untuk Stok_keluar_model
    protected Produk_model $produkModel;         // Deklarasikan properti untuk Produk_model

    /**
     * Constructor untuk menginisialisasi controller.
     * Inisialisasi model dilakukan di sini.
     */
    public function __construct()
    {
        $this->stokKeluarModel = new Stok_keluar_model(); // Inisialisasi Stok_keluar_model
        $this->produkModel     = new Produk_model();      // Inisialisasi Produk_model
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
     * Metode index untuk menampilkan halaman manajemen stok keluar.
     *
     * @return string
     */
    public function index(): string
    {
        // Memuat view 'stok_keluar'.
        // Di CodeIgniter 4, Anda cukup menggunakan fungsi helper view() untuk memuat view.
        return view('stok_keluar');
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
        $stokKeluarList = $this->stokKeluarModel->readStokKeluar();

        if (!empty($stokKeluarList)) {
            foreach ($stokKeluarList as $stok_keluar) {
                // Pastikan kolom 'tanggal' ada dan formatnya benar untuk DateTime
                // Asumsi $stok_keluar adalah array asosiatif (sesuai returnType model)
                $tanggal = new \DateTime($stok_keluar['tanggal']); 
                $data[] = [
                    'tanggal'      => $tanggal->format('d-m-Y H:i:s'),
                    'barcode'      => esc($stok_keluar['barcode']),     // Gunakan esc() untuk escaping HTML
                    'nama_produk'  => esc($stok_keluar['nama_produk']), // Gunakan esc() untuk escaping HTML
                    'jumlah'       => esc($stok_keluar['jumlah']),
                    'keterangan'   => esc($stok_keluar['keterangan']), // Gunakan esc() untuk escaping HTML
                ];
            }
        }

        $response = [
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    /**
     * Menambahkan data stok keluar baru dan mengurangi stok produk.
     *
     * @return ResponseInterface
     */
    public function add(): ResponseInterface
    {
        $produkId   = $this->request->getPost('barcode'); // Asumsi 'barcode' dari form adalah ID produk
        $jumlah     = (int)$this->request->getPost('jumlah');
        $keterangan = $this->request->getPost('keterangan');
        $tanggalInput = $this->request->getPost('tanggal'); // Ambil tanggal dari input form

        // 1. Ambil stok saat ini dari tabel produk
        $currentStokData = $this->stokKeluarModel->getStokProduk($produkId);
        $currentStok = $currentStokData['stok'] ?? 0; // Default ke 0 jika produk tidak ditemukan atau stok kosong

        // 2. Hitung stok baru (tidak boleh kurang dari 0)
        $newStok = max($currentStok - $jumlah, 0);

        // 3. Perbarui stok di tabel produk
        $updateStokSuccess = $this->stokKeluarModel->addStokProduk($produkId, $newStok); // Nama fungsi di model adalah addStokProduk

        if ($updateStokSuccess) {
            // 4. Jika update stok produk sukses, simpan data stok keluar
            // Pastikan format tanggal sesuai atau gunakan current datetime jika tanggal input kosong
            $tanggal = new \DateTime($tanggalInput ?: 'now'); // Menggunakan 'now' jika tanggal input kosong

            $dataStokKeluar = [
                'tanggal'    => $tanggal->format('Y-m-d H:i:s'),
                'produk_id'  => $produkId, // Menggunakan produk_id sebagai Foreign Key ke tabel produk
                'jumlah'     => $jumlah,
                'keterangan' => $keterangan
            ];

            if ($this->stokKeluarModel->createStokKeluar($dataStokKeluar)) {
                return $this->response->setJSON(['status' => 'sukses']);
            } else {
                // Jika gagal mencatat stok keluar, pertimbangkan untuk melakukan rollback stok produk
                // (Logika rollback bisa lebih kompleks, untuk saat ini hanya respons gagal)
                return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal mencatat transaksi stok keluar.']);
            }
        } else {
            return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal memperbarui stok produk.']);
        }
    }

    /**
     * Mengambil detail produk berdasarkan barcode.
     * Diasumsikan ini digunakan untuk mengisi form stok keluar (misalnya dengan Select2).
     *
     * @return ResponseInterface
     */
    public function get_barcode(): ResponseInterface
    {
        $searchTerm = $this->request->getPost('barcode');
        
        // Menggunakan Produk_model untuk mencari produk berdasarkan barcode
        // Metode getBarcode() di Produk_model mengembalikan array of arrays {id, barcode}
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
        
        return $this->response->setJSON($data);
    }
}