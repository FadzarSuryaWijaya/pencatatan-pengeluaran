<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Produk_model; // Pastikan Anda mengimpor model ini
use CodeIgniter\HTTP\ResponseInterface; // Untuk return type hint
use CodeIgniter\HTTP\RedirectResponse; // Untuk return type hint Redirect

class Produk extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all methods within the controller.
     *
     * @var array
     */
    protected $helpers = ['session', 'url']; // 'session' untuk session(), 'url' untuk redirect()

    protected Produk_model $produkModel; // Deklarasikan properti untuk model

    /**
     * Constructor untuk menginisialisasi controller.
     * Inisialisasi model dilakukan di sini.
     */
    public function __construct()
    {
        $this->produkModel = new Produk_model(); // Inisialisasi model
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
     * Menampilkan halaman utama manajemen produk.
     *
     * @return string
     */
    public function index(): string
    {
        // Memuat view 'produk'
        return view('produk');
    }

    /**
     * Membaca data produk untuk ditampilkan di tabel (misalnya DataTables).
     * Mengembalikan data dalam format JSON.
     *
     * @return ResponseInterface
     */
    public function read(): ResponseInterface
    {
        $data = []; // Inisialisasi array data

        // Panggil metode readProduk() dari model
        $produkList = $this->produkModel->readProduk();

        if (!empty($produkList)) {
            foreach ($produkList as $produk) {
                $data[] = [
                    'barcode'   => esc($produk['barcode']),
                    'nama'      => esc($produk['nama_produk']),
                    'kategori'  => esc($produk['kategori']),
                    'satuan'    => esc($produk['satuan']),
                    'harga'     => esc($produk['harga']),
                    'stok'      => esc($produk['stok']),
                    'action'    => '<button class="btn btn-sm btn-success" onclick="edit(' . esc($produk['id']) . ')">Edit</button> <button class="btn btn-sm btn-danger" onclick="remove(' . esc($produk['id']) . ')">Delete</button>'
                ];
            }
        }

        $response = [
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    /**
     * Menambahkan data produk baru.
     *
     * @return ResponseInterface
     */
    public function add(): ResponseInterface
    {
        $data = [
            'barcode'     => $this->request->getPost('barcode'),
            'nama_produk' => $this->request->getPost('nama_produk'),
            'satuan'      => $this->request->getPost('satuan'),
            'kategori'    => $this->request->getPost('kategori'),
            'harga'       => $this->request->getPost('harga'),
            'stok'        => $this->request->getPost('stok')
        ];

        if ($this->produkModel->createProduk($data)) {
            // Mengembalikan data yang baru saja ditambahkan sebagai respons sukses
            // atau bisa juga mengembalikan status 'sukses' saja
            return $this->response->setJSON(['status' => 'sukses', 'data' => $data]);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menambahkan produk.']);
    }

    /**
     * Menghapus data produk.
     *
     * @return ResponseInterface
     */
    public function delete(): ResponseInterface
    {
        $id = $this->request->getPost('id');

        if ($this->produkModel->deleteProduk($id)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menghapus produk.']);
    }

    /**
     * Memperbarui data produk.
     *
     * @return ResponseInterface
     */
    public function edit(): ResponseInterface
    {
        $id   = $this->request->getPost('id');
        $data = [
            'barcode'     => $this->request->getPost('barcode'),
            'nama_produk' => $this->request->getPost('nama_produk'),
            'satuan'      => $this->request->getPost('satuan'),
            'kategori'    => $this->request->getPost('kategori'),
            'harga'       => $this->request->getPost('harga'),
            'stok'        => $this->request->getPost('stok')
        ];

        if ($this->produkModel->updateProduk($id, $data)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal memperbarui produk.']);
    }

    /**
     * Mengambil satu data produk berdasarkan ID.
     *
     * @return ResponseInterface
     */
    public function get_produk(): ResponseInterface
    {
        $id = $this->request->getPost('id');
        $produk = $this->produkModel->getProdukById($id); // Memanggil metode getProdukById() dari model

        if (!empty($produk)) {
            return $this->response->setJSON($produk);
        }

        return $this->response->setJSON(['status' => 'tidak_ditemukan', 'message' => 'Produk tidak ditemukan.']);
    }

    /**
     * Mencari barcode produk.
     * Digunakan untuk fitur seperti Select2 atau autocomplete.
     *
     * @return ResponseInterface
     */
    // app/Controllers/Stok_masuk.php

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

        // Penting: Select2 versi terbaru (setidaknya yang saya tahu) mengharapkan hasil dalam kunci 'results'
        // Jadi, respon JSON harus {"results": [...]}
        return $this->response->setJSON(['results' => $data]);
    }


    /**
     * Mengambil nama produk dan stok berdasarkan ID.
     *
     * @return ResponseInterface
     */
    public function get_nama(): ResponseInterface
    {
        $id = $this->request->getPost('id');
        $produkInfo = $this->produkModel->getNamaProdukStok($id); // Memanggil metode getNamaProdukStok() dari model

        if (!empty($produkInfo)) {
            return $this->response->setJSON($produkInfo);
        }

        return $this->response->setJSON(['status' => 'tidak_ditemukan', 'message' => 'Produk tidak ditemukan.']);
    }

    /**
     * Mengambil detail stok produk (stok, nama_produk, harga, barcode) berdasarkan ID.
     *
     * @return ResponseInterface
     */
    public function get_stok(): ResponseInterface
    {
        $id = $this->request->getPost('id');
        $stokInfo = $this->produkModel->getDetailStokProduk($id); // Memanggil metode getDetailStokProduk() dari model

        if (!empty($stokInfo)) {
            return $this->response->setJSON($stokInfo);
        }

        return $this->response->setJSON(['status' => 'tidak_ditemukan', 'message' => 'Produk tidak ditemukan.']);
    }

    /**
     * Mengambil data produk terlaris untuk chart/laporan.
     *
     * @return ResponseInterface
     */
    public function produk_terlaris(): ResponseInterface
    {
        // Ambil parameter periode dari request (GET atau POST)
        $periode = $this->request->getGet('periode') ?? $this->request->getPost('periode') ?? '1month';
        $limit = (int)($this->request->getGet('limit') ?? $this->request->getPost('limit') ?? 5);

        // Validasi periode
        $allowedPeriods = ['today', '1month', '1year', 'all'];
        if (!in_array($periode, $allowedPeriods)) {
            $periode = '1month';
        }

        // Validasi limit
        if ($limit < 1 || $limit > 20) {
            $limit = 5;
        }

        // Panggil metode model dengan parameter
        $produkTerlarisList = $this->produkModel->produkTerlaris($periode, $limit);

        $label = [];
        $data  = [];

        if (!empty($produkTerlarisList)) {
            foreach ($produkTerlarisList as $item) {
                $label[] = esc($item['nama_produk']);
                $data[]  = (int)$item['total_terjual'];
            }
        }

        $result = [
            'label' => $label,
            'data'  => $data,
            'periode' => $periode,
            'total_produk' => count($produkTerlarisList)
        ];

        return $this->response->setJSON($result);
    }

    /**
     * Method khusus untuk mendapatkan statistik produk terlaris dengan berbagai periode.
     *
     * @return ResponseInterface
     */
    public function statistik_produk_terlaris(): ResponseInterface
    {
        $periode = $this->request->getGet('periode') ?? '1month';
        $limit = (int)($this->request->getGet('limit') ?? 10);

        $allowedPeriods = ['today', '1month', '1year', 'all'];
        if (!in_array($periode, $allowedPeriods)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Periode tidak valid. Gunakan: today, 1month, 1year, atau all'
            ]);
        }

        $data = $this->produkModel->produkTerlaris($periode, $limit);

        // Tambahkan informasi periode dalam response
        $periodeName = [
            'today' => 'Hari Ini',
            '1month' => '1 Bulan Terakhir',
            '1year' => '1 Tahun Terakhir',
            'all' => 'Semua Waktu'
        ];

        return $this->response->setJSON([
            'status' => 'success',
            'periode' => $periode,
            'periode_nama' => $periodeName[$periode],
            'total_produk' => count($data),
            'data' => $data
        ]);
    }

    /**
     * Mengambil data stok produk untuk chart/laporan.
     *
     * @return ResponseInterface
     */
    public function data_stok(): ResponseInterface
    {
        $dataStokList = $this->produkModel->dataStok(); // Memanggil metode dataStok() dari model

        // Data sudah dalam format array of arrays dari model, bisa langsung di-encode
        return $this->response->setJSON($dataStokList);
    }
}
