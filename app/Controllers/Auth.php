<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Auth_model; // Pastikan Anda mengimpor Auth_model
use CodeIgniter\HTTP\ResponseInterface; // Opsional, untuk tipe return hint
use CodeIgniter\HTTP\RedirectResponse; // Opsional, untuk tipe return hint Redirect

class Auth extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all methods within the controller.
     *
     * @var array
     */
    protected $helpers = ['session', 'url']; // 'url' helper untuk fungsi redirect()

    protected Auth_model $authModel; // Deklarasikan properti untuk model

    /**
     * Constructor untuk menginisialisasi controller.
     * Di CI4, gunakan `initController` untuk inisialisasi awal.
     * Atau, jika Anda hanya perlu memuat model, konstruktor biasa juga bisa.
     */
    public function __construct()
    {
        $this->authModel = new Auth_model(); // Inisialisasi Auth_model
    }

    public function signup()
    {
        $session = session();

        if ($this->request->isAJAX() || $this->request->getPost()) {
            $username = $this->request->getPost('username');
            $nama = $this->request->getPost('nama');
            $password = $this->request->getPost('password');
            $password_confirm = $this->request->getPost('password_confirm');

            // Validasi sederhana
            if ($password !== $password_confirm) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Password dan konfirmasi tidak sama']);
            }

            // Cek apakah username sudah ada
            if ($this->authModel->where('username', $username)->first()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Username sudah digunakan']);
            }

            // Hash password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Simpan data user baru, role default misal 'user' atau 'kasir'
            $data = [
                'username' => $username,
                'nama'     => $nama,
                'password' => $passwordHash,
                'role'     => '0' // 0 = user/kasir, 1 = admin (sesuaikan)
            ];

            $this->authModel->insert($data);

            return $this->response->setJSON(['status' => 'success']);
        } else {
            return view('signup');
        }
    }


    /**
     * Menampilkan halaman login atau memproses login.
     *
     * @return string|ResponseInterface|RedirectResponse
     */
    public function login()
    {
        $session = session();

        if ($session->get('status') === 'login') {
            // Redirect sesuai role
            if ($session->get('role') === 'admin') {
                return redirect()->to('/');
            } else {
                return redirect()->to('/');
            }
        }

        if ($this->request->isAJAX() || $this->request->getPost('username')) {
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');

            $userData = $this->authModel->getUser($username);

            if (!empty($userData)) {
                if (password_verify($password, $userData['password'])) {
                    $toko = $this->authModel->getToko();

                    $role = ($userData['role'] == '1') ? 'admin' : 'user'; // 'user' untuk kasir

                    $sessionData = [
                        'id'        => $userData['id'],
                        'username'  => $userData['username'],
                        'nama'      => $userData['nama'],
                        'role'      => $role,
                        'status'    => 'login',
                        'toko'      => $toko
                    ];

                    $session->set($sessionData);

                    return $this->response->setJSON(['status' => 'sukses', 'role' => $role]);
                } else {
                    return $this->response->setJSON(['status' => 'passwordsalah']);
                }
            } else {
                return $this->response->setJSON(['status' => 'tidakada']);
            }
        } else {
            return view('login');
        }
    }


    /**
     * Melakukan logout pengguna.
     *
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        $session = session();
        $session->destroy(); // Hancurkan semua data session

        return redirect()->to('/'); // Arahkan kembali ke halaman utama atau login
    }
}
