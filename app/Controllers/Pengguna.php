<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Pengguna_model; // Pastikan Anda mengimpor model ini
use CodeIgniter\HTTP\ResponseInterface; // Untuk return type hint
use CodeIgniter\HTTP\RedirectResponse; // Untuk return type hint Redirect

class Pengguna extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all methods within the controller.
     *
     * @var array
     */
    protected $helpers = ['session', 'url']; // 'session' untuk session(), 'url' untuk redirect()

    protected Pengguna_model $penggunaModel; // Deklarasikan properti untuk model

    /**
     * Constructor untuk menginisialisasi controller.
     * Inisialisasi model dilakukan di sini.
     */
    public function __construct()
    {
        $this->penggunaModel = new Pengguna_model(); // Inisialisasi model
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
     * Menampilkan halaman utama manajemen pengguna.
     *
     * @return string
     */
    public function index(): string
    {
        // Memuat view 'pengguna'
        return view('pengguna');
    }

    /**
     * Membaca data pengguna (dengan role '2') untuk ditampilkan di tabel (misalnya DataTables).
     * Mengembalikan data dalam format JSON.
     *
     * @return ResponseInterface
     */
    public function read(): ResponseInterface
    {
        $data = []; // Inisialisasi array data

        // Panggil metode readPenggunaByRole() dari model
        $penggunaList = $this->penggunaModel->readPenggunaByRole();

        if (!empty($penggunaList)) {
            foreach ($penggunaList as $pengguna) {
                $data[] = [
                    'username' => esc($pengguna['username']), // Gunakan esc() untuk escaping HTML
                    'nama'     => esc($pengguna['nama']),     // Gunakan esc() untuk escaping HTML
                    'action'   => '<button class="btn btn-sm btn-success" onclick="edit(' . esc($pengguna['id']) . ')">Edit</button> <button class="btn btn-sm btn-danger" onclick="remove(' . esc($pengguna['id']) . ')">Delete</button>'
                ];
            }
        }

        $response = [
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    /**
     * Menambahkan data pengguna baru.
     *
     * @return ResponseInterface
     */
    public function add(): ResponseInterface
    {
        $data = [
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT), // Password di-hash
            'nama'     => $this->request->getPost('nama'),
            'role'     => '2' // Role default untuk pengguna baru
        ];

        if ($this->penggunaModel->createPengguna($data)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menambahkan pengguna.']);
    }

    /**
     * Menghapus data pengguna.
     *
     * @return ResponseInterface
     */
    public function delete(): ResponseInterface
    {
        $id = $this->request->getPost('id');

        if ($this->penggunaModel->deletePengguna($id)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menghapus pengguna.']);
    }

    /**
     * Memperbarui data pengguna.
     *
     * @return ResponseInterface
     */
    public function edit(): ResponseInterface
    {
        $id   = $this->request->getPost('id');
        $data = [
            'username' => $this->request->getPost('username'),
            'nama'     => $this->request->getPost('nama')
        ];

        // Hanya perbarui password jika ada input password baru
        $newPassword = $this->request->getPost('password');
        if (!empty($newPassword)) {
            $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        if ($this->penggunaModel->updatePengguna($id, $data)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal memperbarui pengguna.']);
    }

    /**
     * Mengambil satu data pengguna berdasarkan ID.
     *
     * @return ResponseInterface
     */
    public function get_pengguna(): ResponseInterface
    {
        $id = $this->request->getPost('id');
        $pengguna = $this->penggunaModel->getPenggunaById($id); // Memanggil metode getPenggunaById() dari model

        if (!empty($pengguna)) {
            return $this->response->setJSON($pengguna);
        }

        return $this->response->setJSON(['status' => 'tidak_ditemukan', 'message' => 'Pengguna tidak ditemukan.']);
    }
}