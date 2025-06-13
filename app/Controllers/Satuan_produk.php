<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Satuan_produk_model; // Pastikan Anda mengimpor model ini
use CodeIgniter\HTTP\ResponseInterface; // Untuk return type hint
use CodeIgniter\HTTP\RedirectResponse; // Untuk return type hint Redirect

class Satuan_produk extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all methods within the controller.
     *
     * @var array
     */
    protected $helpers = ['session', 'url']; // 'session' untuk session(), 'url' untuk redirect()

    protected Satuan_produk_model $satuanProdukModel; // Deklarasikan properti untuk model

    /**
     * Constructor untuk menginisialisasi controller.
     * Inisialisasi model dilakukan di sini.
     */
    public function __construct()
    {
        $this->satuanProdukModel = new Satuan_produk_model(); // Inisialisasi model
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
            return redirect()->to('/'); // Ubah ini ke URL halaman login Anda jika berbeda
        }
    }

    /**
     * Menampilkan halaman utama manajemen satuan produk.
     *
     * @return string
     */
    public function index(): string
    {
        // Memuat view 'satuan_produk'
        return view('satuan_produk');
    }

    /**
     * Membaca data satuan produk untuk ditampilkan di tabel (misalnya DataTables).
     * Mengembalikan data dalam format JSON.
     *
     * @return ResponseInterface
     */
    public function read(): ResponseInterface
    {
        $data = []; // Inisialisasi array data

        // Panggil metode readSatuan() dari model
        $satuanProdukList = $this->satuanProdukModel->readSatuan();

        if (!empty($satuanProdukList)) {
            foreach ($satuanProdukList as $satuan_produk) {
                $data[] = [
                    'satuan' => esc($satuan_produk['satuan']), // Gunakan esc() untuk escaping HTML
                    'action' => '<button class="btn btn-sm btn-success" onclick="edit(' . esc($satuan_produk['id']) . ')">Edit</button> <button class="btn btn-sm btn-danger" onclick="remove(' . esc($satuan_produk['id']) . ')">Delete</button>'
                ];
            }
        }

        $response = [
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    /**
     * Menambahkan data satuan produk baru.
     *
     * @return ResponseInterface
     */
    public function add(): ResponseInterface
    {
        $data = [
            'satuan' => $this->request->getPost('satuan')
        ];

        if ($this->satuanProdukModel->createSatuan($data)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menambahkan satuan.']);
    }

    /**
     * Menghapus data satuan produk.
     *
     * @return ResponseInterface
     */
    public function delete(): ResponseInterface
    {
        $id = $this->request->getPost('id');

        if ($this->satuanProdukModel->deleteSatuan($id)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menghapus satuan.']);
    }

    /**
     * Memperbarui data satuan produk.
     *
     * @return ResponseInterface
     */
    public function edit(): ResponseInterface
    {
        $id   = $this->request->getPost('id');
        $data = [
            'satuan' => $this->request->getPost('satuan')
        ];

        if ($this->satuanProdukModel->updateSatuan($id, $data)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal memperbarui satuan.']);
    }

    /**
     * Mengambil satu data satuan produk berdasarkan ID.
     *
     * @return ResponseInterface
     */
    public function get_satuan(): ResponseInterface
    {
        $id = $this->request->getPost('id');
        // KOREKSI: Mengganti getKategori dengan getSatuan (atau getSatuanById jika itu nama di model Anda)
        $satuan = $this->satuanProdukModel->getSatuan($id); // Menggunakan nama fungsi yang dikoreksi

        if (!empty($satuan)) {
            return $this->response->setJSON($satuan);
        }

        return $this->response->setJSON(['status' => 'tidak_ditemukan', 'message' => 'Satuan tidak ditemukan.']);
    }

    /**
     * Mencari satuan produk berdasarkan string pencarian.
     * Digunakan untuk fitur seperti Select2 atau autocomplete.
     *
     * @return ResponseInterface
     */
    public function search(): ResponseInterface
    {
        $searchQuery = $this->request->getPost('satuan'); // Asumsi input pencarian adalah 'satuan'
        $searchResults = $this->satuanProdukModel->searchSatuan($searchQuery); // Memanggil metode searchSatuan() dari model

        $data = [];
        if (!empty($searchResults)) {
            foreach ($searchResults as $satuan) {
                $data[] = [
                    'id'   => esc($satuan['id']),
                    'text' => esc($satuan['satuan']) // Gunakan esc() untuk escaping HTML
                ];
            }
        }

        return $this->response->setJSON($data);
    }
}