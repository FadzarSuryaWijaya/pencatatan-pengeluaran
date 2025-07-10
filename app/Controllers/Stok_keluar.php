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
    protected $db;

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
        $this->db = \Config\Database::connect();
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
                    'barcode'      => esc($stok_keluar['produk_barcode']),     // Gunakan esc() untuk escaping HTML
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
    $inputData = [
        'barcode' => $this->request->getPost('barcode'),
        'jumlah' => (int)$this->request->getPost('jumlah'),
        'keterangan' => $this->request->getPost('keterangan'),
        'tanggal' => $this->request->getPost('tanggal')
    ];

    // Basic validation
    $validation = \Config\Services::validation();
    $validation->setRules([
        'barcode' => 'required',
        'jumlah' => 'required|numeric|greater_than[0]',
        'keterangan' => 'required',
        'tanggal' => 'required'
    ]);

    if (!$validation->withRequest($this->request)->run()) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Semua field harus diisi dan jumlah harus lebih dari 0',
            'errors' => $validation->getErrors() // Include validation errors
        ]);
    }

    // Check product existence
    $produk = $this->produkModel->find($inputData['barcode']);
    if (!$produk) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Produk tidak ditemukan',
            'product_id' => $inputData['barcode']
        ]);
    }

    // Stock validation
    if ($produk['stok'] < $inputData['jumlah']) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Stok tidak mencukupi',
            'current_stock' => $produk['stok'],
            'requested_amount' => $inputData['jumlah']
        ]);
    }

    // Begin transaction
    $this->db->transBegin();

    try {
        // Update product stock
        $newStok = $produk['stok'] - $inputData['jumlah'];
        $this->produkModel->update($inputData['barcode'], ['stok' => $newStok]);

        // Record stock out
        $dataStokKeluar = [
            'tanggal' => (new \DateTime($inputData['tanggal']))->format('Y-m-d H:i:s'),
            'barcode' => $inputData['barcode'],
            'jumlah' => $inputData['jumlah'],
            'keterangan' => $inputData['keterangan'],
            'user_id' => session()->get('id')
        ];

        $this->stokKeluarModel->insert($dataStokKeluar);

        // Commit transaction if all successful
        $this->db->transCommit();

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Stok keluar berhasil dicatat',
            'new_stock' => $newStok
        ]);

    } catch (\Exception $e) {
        // Rollback transaction on error
        $this->db->transRollback();
        
        log_message('error', 'Stok keluar error: ' . $e->getMessage());
        
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
        ]);
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
