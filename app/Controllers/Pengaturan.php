<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Pengaturan extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all methods within the controller.
     *
     * @var array
     */
    protected $helpers = ['url', 'session']; // Tambahkan 'session' jika Anda perlu helper session di tempat lain

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        //--------------------------------------------------------------------
        // Preload any models, libraries, etc, here.
        //--------------------------------------------------------------------
        // E.g.: $this->session = \Config\Services::session();

        // Check login status
        if (session()->get('status') !== 'login') {
            return redirect()->to(base_url('/'));
        }
    }

    public function index()
    {
        // Menggunakan instance database default
        $db = \Config\Database::connect();
        // Mengambil satu baris dari tabel 'toko'
        $toko = $db->table('toko')->get()->getRow(); // PERBAIKAN: Gunakan getRow() dengan G kapital

        $data['toko'] = $toko;
        return view('pengaturan', $data); // Memuat view
    }

    public function set_toko()
    {
        // Pastikan ini adalah request POST
        if ($this->request->isAJAX() || $this->request->getMethod() === 'post') {
            $nama = $this->request->getPost('nama');
            $alamat = $this->request->getPost('alamat');

            $data = [
                'nama' => $nama,
                'alamat' => $alamat
            ];

            $db = \Config\Database::connect(); // Mendapatkan instance database
            
            // Update data di tabel 'toko' berdasarkan 'id' = 1
            if ($db->table('toko')->where('id', 1)->update($data)) {
                // Setelah update, ambil kembali data toko yang baru untuk session
                $toko = $db->table('toko')->select('nama, alamat')->get()->getRow(); // PERBAIKAN: Gunakan getRow() dengan G kapital
                session()->set('toko', $toko); // Set data toko ke session

                return $this->response->setJSON('sukses'); // Mengembalikan respons JSON
            } else {
                // Handle jika update gagal
                return $this->response->setJSON('gagal'); 
            }
        } else {
            // Jika bukan request POST, mungkin redirect atau kembalikan error
            return $this->response->setStatusCode(405)->setBody('Method Not Allowed');
        }
    }
}