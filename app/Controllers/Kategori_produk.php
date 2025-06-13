<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Kategori_produk_model; // Pastikan Anda mengimpor model ini
use CodeIgniter\HTTP\ResponseInterface; // Untuk return type hint
use CodeIgniter\HTTP\RedirectResponse; // Untuk return type hint Redirect

class Kategori_produk extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all methods within the controller.
     *
     * @var array
     */
    protected $helpers = ['session', 'url']; // 'session' untuk session(), 'url' untuk redirect()

    protected Kategori_produk_model $kategoriProdukModel; // Deklarasikan properti untuk model

    /**
     * Constructor untuk menginisialisasi controller.
     * Di CI4, inisialisasi model dilakukan di sini.
     */
    public function __construct()
    {
        $this->kategoriProdukModel = new Kategori_produk_model(); // Inisialisasi model
    }

    /**
     * Metode initController digunakan untuk inisialisasi awal.
     * Ini adalah tempat yang baik untuk melakukan pengecekan login.
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $session = session();
        if ($session->get('status') !== 'login') {
            return redirect()->to('/'); // Arahkan ke halaman utama/login jika belum login
        }
    }

    /**
     * Menampilkan halaman utama manajemen kategori produk.
     *
     * @return string
     */
    public function index(): string
    {
        // Memuat view 'kategori_produk'
        return view('kategori_produk');
    }

    /**
     * Membaca data kategori produk untuk ditampilkan di tabel (misalnya DataTables).
     * Mengembalikan data dalam format JSON.
     *
     * @return ResponseInterface
     */
    public function read(): ResponseInterface
    {
        $data = []; // Inisialisasi array data

        // Panggil metode readKategori() dari model
        $kategoriProdukList = $this->kategoriProdukModel->readKategori();

        if (!empty($kategoriProdukList)) {
            foreach ($kategoriProdukList as $kategori_produk) {
                $data[] = [
                    'kategori' => esc($kategori_produk['kategori']), // Gunakan esc() untuk escaping HTML
                    'action'   => '<button class="btn btn-sm btn-success" onclick="edit(' . esc($kategori_produk['id']) . ')">Edit</button> <button class="btn btn-sm btn-danger" onclick="remove(' . esc($kategori_produk['id']) . ')">Delete</button>'
                ];
            }
        }

        $response = [
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    /**
     * Menambahkan data kategori produk baru.
     *
     * @return ResponseInterface
     */
    public function add(): ResponseInterface
    {
        $data = [
            'kategori' => $this->request->getPost('kategori')
        ];

        if ($this->kategoriProdukModel->createKategori($data)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menambahkan kategori.']);
    }

    /**
     * Menghapus data kategori produk.
     *
     * @return ResponseInterface
     */
    public function delete(): ResponseInterface
    {
        $id = $this->request->getPost('id');

        if ($this->kategoriProdukModel->deleteKategori($id)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menghapus kategori.']);
    }

    /**
     * Memperbarui data kategori produk.
     *
     * @return ResponseInterface
     */
    public function edit(): ResponseInterface
    {
        $id   = $this->request->getPost('id');
        $data = [
            'kategori' => $this->request->getPost('kategori')
        ];

        if ($this->kategoriProdukModel->updateKategori($id, $data)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal memperbarui kategori.']);
    }

    /**
     * Mengambil satu data kategori produk berdasarkan ID.
     *
     * @return ResponseInterface
     */
    public function get_kategori(): ResponseInterface
    {
        $id = $this->request->getPost('id');
        $kategori = $this->kategoriProdukModel->getKategoriById($id); // Memanggil metode getKategoriById() dari model

        if (!empty($kategori)) {
            return $this->response->setJSON($kategori);
        }

        return $this->response->setJSON(['status' => 'tidak_ditemukan', 'message' => 'Kategori tidak ditemukan.']);
    }

    /**
     * Mencari kategori produk berdasarkan string pencarian.
     * Digunakan untuk fitur seperti Select2 atau autocomplete.
     *
     * @return ResponseInterface
     */
    public function search(): ResponseInterface
    {
        $searchQuery = $this->request->getPost('kategori'); // Asumsi input pencarian adalah 'kategori'
        $searchResults = $this->kategoriProdukModel->searchKategori($searchQuery); // Memanggil metode searchKategori() dari model

        $data = [];
        if (!empty($searchResults)) {
            foreach ($searchResults as $kategori) {
                $data[] = [
                    'id'   => esc($kategori['id']),
                    'text' => esc($kategori['kategori']) // Gunakan esc() untuk escaping HTML
                ];
            }
        }

        return $this->response->setJSON($data);
    }
}