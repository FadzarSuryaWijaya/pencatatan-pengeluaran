<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Supplier_model; // Pastikan Anda mengimpor model ini
use CodeIgniter\HTTP\ResponseInterface; // Untuk return type hint
use CodeIgniter\HTTP\RedirectResponse; // Untuk return type hint Redirect

class Supplier extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all methods within the controller.
     *
     * @var array
     */
    protected $helpers = ['session', 'url']; // 'session' untuk session(), 'url' untuk redirect()

    protected Supplier_model $supplierModel; // Deklarasikan properti untuk model

    /**
     * Constructor untuk menginisialisasi controller.
     * Inisialisasi model dilakukan di sini.
     */
    public function __construct()
    {
        $this->supplierModel = new Supplier_model(); // Inisialisasi model
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
     * Menampilkan halaman utama manajemen supplier.
     *
     * @return string
     */
    public function index(): string
    {
        // Memuat view 'supplier'
        return view('supplier');
    }

    /**
     * Membaca data supplier untuk ditampilkan di tabel (misalnya DataTables).
     * Mengembalikan data dalam format JSON.
     *
     * @return ResponseInterface
     */
    public function read(): ResponseInterface
    {
        $data = []; // Inisialisasi array data

        // Panggil metode readSupplier() dari model
        $supplierList = $this->supplierModel->readSupplier();

        if (!empty($supplierList)) {
            foreach ($supplierList as $supplier) {
                $data[] = [
                    'nama'       => esc($supplier['nama']),
                    'alamat'     => esc($supplier['alamat']),
                    'telepon'    => esc($supplier['telepon']),
                    'keterangan' => esc($supplier['keterangan']),
                    'action'     => '<button class="btn btn-sm btn-success" onclick="edit(' . esc($supplier['id']) . ')">Edit</button> <button class="btn btn-sm btn-danger" onclick="remove(' . esc($supplier['id']) . ')">Delete</button>'
                ];
            }
        }

        $response = [
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    /**
     * Menambahkan data supplier baru.
     *
     * @return ResponseInterface
     */
    public function add(): ResponseInterface
    {
        $data = [
            'nama'       => $this->request->getPost('nama'),
            'alamat'     => $this->request->getPost('alamat'),
            'telepon'    => $this->request->getPost('telepon'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->supplierModel->createSupplier($data)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menambahkan supplier.']);
    }

    /**
     * Menghapus data supplier.
     *
     * @return ResponseInterface
     */
    public function delete(): ResponseInterface
    {
        $id = $this->request->getPost('id');

        if ($this->supplierModel->deleteSupplier($id)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menghapus supplier.']);
    }

    /**
     * Memperbarui data supplier.
     *
     * @return ResponseInterface
     */
    public function edit(): ResponseInterface
    {
        $id   = $this->request->getPost('id');
        $data = [
            'nama'       => $this->request->getPost('nama'),
            'alamat'     => $this->request->getPost('alamat'),
            'telepon'    => $this->request->getPost('telepon'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->supplierModel->updateSupplier($id, $data)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal memperbarui supplier.']);
    }

    /**
     * Mengambil satu data supplier berdasarkan ID.
     *
     * @return ResponseInterface
     */
    public function get_supplier(): ResponseInterface
    {
        $id = $this->request->getPost('id');
        $supplier = $this->supplierModel->getSupplierById($id); // Memanggil metode getSupplierById() dari model

        if (!empty($supplier)) {
            return $this->response->setJSON($supplier);
        }

        return $this->response->setJSON(['status' => 'tidak_ditemukan', 'message' => 'Supplier tidak ditemukan.']);
    }

    /**
     * Mencari supplier berdasarkan string pencarian.
     * Digunakan untuk fitur seperti Select2 atau autocomplete.
     *
     * @return ResponseInterface
     */
    public function search(): ResponseInterface
    {
        $searchQuery = $this->request->getPost('supplier'); // Asumsi input pencarian adalah 'supplier'
        $searchResults = $this->supplierModel->searchSupplier($searchQuery); // Memanggil metode searchSupplier() dari model

        $data = [];
        if (!empty($searchResults)) {
            foreach ($searchResults as $supplier) {
                $data[] = [
                    'id'   => esc($supplier['id']),
                    'text' => esc($supplier['nama']) // Gunakan esc() untuk escaping HTML
                ];
            }
        }

        return $this->response->setJSON($data);
    }
}