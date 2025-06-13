<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Pelanggan_model; // Pastikan Anda mengimpor model ini
use CodeIgniter\HTTP\ResponseInterface; // Untuk return type hint
use CodeIgniter\HTTP\RedirectResponse; // Untuk return type hint Redirect

class Pelanggan extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all methods within the controller.
     *
     * @var array
     */
    protected $helpers = ['session', 'url']; // 'session' untuk session(), 'url' untuk redirect()

    protected Pelanggan_model $pelangganModel; // Deklarasikan properti untuk model

    /**
     * Constructor untuk menginisialisasi controller.
     * Inisialisasi model dilakukan di sini.
     */
    public function __construct()
    {
        $this->pelangganModel = new Pelanggan_model(); // Inisialisasi model
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
     * Menampilkan halaman utama manajemen pelanggan.
     *
     * @return string
     */
    public function index(): string
    {
        // Memuat view 'pelanggan'
        return view('pelanggan');
    }

    /**
     * Membaca data pelanggan untuk ditampilkan di tabel (misalnya DataTables).
     * Mengembalikan data dalam format JSON.
     *
     * @return ResponseInterface
     */
    public function read(): ResponseInterface
    {
        $data = []; // Inisialisasi array data

        // Panggil metode readPelanggan() dari model
        $pelangganList = $this->pelangganModel->readPelanggan();

        if (!empty($pelangganList)) {
            foreach ($pelangganList as $pelanggan) {
                $data[] = [
                    'nama'          => esc($pelanggan['nama']),
                    'jenis_kelamin' => esc($pelanggan['jenis_kelamin']),
                    'alamat'        => esc($pelanggan['alamat']),
                    'telepon'       => esc($pelanggan['telepon']),
                    'action'        => '<button class="btn btn-sm btn-success" onclick="edit(' . esc($pelanggan['id']) . ')">Edit</button> <button class="btn btn-sm btn-danger" onclick="remove(' . esc($pelanggan['id']) . ')">Delete</button>'
                ];
            }
        }

        $response = [
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    /**
     * Menambahkan data pelanggan baru.
     *
     * @return ResponseInterface
     */
    public function add(): ResponseInterface
    {
        $data = [
            'nama'          => $this->request->getPost('nama'),
            'alamat'        => $this->request->getPost('alamat'),
            'telepon'       => $this->request->getPost('telepon'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin')
        ];

        if ($this->pelangganModel->createPelanggan($data)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menambahkan pelanggan.']);
    }

    /**
     * Menghapus data pelanggan.
     *
     * @return ResponseInterface
     */
    public function delete(): ResponseInterface
    {
        $id = $this->request->getPost('id');

        if ($this->pelangganModel->deletePelanggan($id)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menghapus pelanggan.']);
    }

    /**
     * Memperbarui data pelanggan.
     *
     * @return ResponseInterface
     */
    public function edit(): ResponseInterface
    {
        $id   = $this->request->getPost('id');
        $data = [
            'nama'          => $this->request->getPost('nama'),
            'alamat'        => $this->request->getPost('alamat'),
            'telepon'       => $this->request->getPost('telepon'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin')
        ];

        if ($this->pelangganModel->updatePelanggan($id, $data)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal memperbarui pelanggan.']);
    }

    /**
     * Mengambil satu data pelanggan berdasarkan ID.
     *
     * @return ResponseInterface
     */
    public function get_pelanggan(): ResponseInterface
    {
        $id = $this->request->getPost('id');
        // KOREKSI: Mengganti getSupplier dengan getPelangganById
        $pelanggan = $this->pelangganModel->getPelanggan($id);

        if (!empty($pelanggan)) {
            return $this->response->setJSON($pelanggan);
        }

        return $this->response->setJSON(['status' => 'tidak_ditemukan', 'message' => 'Pelanggan tidak ditemukan.']);
    }

    /**
     * Mencari pelanggan berdasarkan string pencarian.
     * Digunakan untuk fitur seperti Select2 atau autocomplete.
     *
     * @return ResponseInterface
     */
    public function search(): ResponseInterface
    {
        $searchQuery = $this->request->getPost('pelanggan'); // Asumsi input pencarian adalah 'pelanggan'
        $searchResults = $this->pelangganModel->searchPelanggan($searchQuery); // Memanggil metode searchPelanggan() dari model

        $data = [];
        if (!empty($searchResults)) {
            foreach ($searchResults as $pelanggan) {
                $data[] = [
                    'id'   => esc($pelanggan['id']),
                    'text' => esc($pelanggan['nama']) // Gunakan esc() untuk escaping HTML
                ];
            }
        }

        return $this->response->setJSON($data);
    }
}