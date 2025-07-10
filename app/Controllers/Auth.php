<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Auth_model;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;

class Auth extends Controller
{
    protected $helpers = ['session', 'url'];
    protected Auth_model $authModel;

    public function __construct()
    {
        $this->authModel = new Auth_model();
    }

    /**
     * Handle user registration
     */
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
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Password dan konfirmasi tidak sama'
                ]);
            }

            // Cek apakah username sudah ada
            if ($this->authModel->where('username', $username)->first()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Username sudah digunakan'
                ]);
            }

            // Hash password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Start transaction untuk memastikan data consistency
            $this->authModel->db->transStart();

            try {
                // Simpan data user baru
                $userData = [
                    'username' => $username,
                    'nama'     => $nama,
                    'password' => $passwordHash,
                    'role'     => '0' // Default user
                ];

                $userId = $this->authModel->insert($userData);

                if ($userId) {
                    // Buat data default untuk user baru
                    $this->authModel->createDefaultUserData($userId);

                    $this->authModel->db->transComplete();

                    if ($this->authModel->db->transStatus()) {
                        return $this->response->setJSON([
                            'status' => 'success',
                            'message' => 'Registrasi berhasil! Silakan login.'
                        ]);
                    } else {
                        throw new \Exception('Gagal membuat data default');
                    }
                } else {
                    throw new \Exception('Gagal menyimpan data user');
                }
            } catch (\Exception $e) {
                $this->authModel->db->transRollback();
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat registrasi: ' . $e->getMessage()
                ]);
            }
        } else {
            return view('signup');
        }
    }

    /**
     * Handle user login
     */
    public function login()
    {
        $session = session();

        // Cek jika sudah login
        if ($session->get('status') === 'login') {
            if ($session->get('role') === 'admin') {
                return redirect()->to('/');
            } else {
                return redirect()->to('/transaksi');
            }
        }

        if ($this->request->isAJAX() || $this->request->getPost('username')) {
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');

            $userData = $this->authModel->getUser($username);

            if (!empty($userData)) {
                if (password_verify($password, $userData['password'])) {
                    // Ambil data toko user
                    $toko = $this->authModel->getToko($userData['id']);

                    $role = ($userData['role'] == '1') ? 'admin' : 'user';

                    $sessionData = [
                        'id'        => $userData['id'],
                        'username'  => $userData['username'],
                        'nama'      => $userData['nama'],
                        'role'      => $role,
                        'status'    => 'login',
                        'toko'      => $toko
                    ];

                    $session->set($sessionData);

                    return $this->response->setJSON([
                        'status' => 'sukses',
                        'role' => $role,
                        'redirect' => base_url('/')
                    ]);
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
     * Handle user logout
     */
    public function logout(): RedirectResponse
    {
        $session = session();
        $session->destroy();

        return redirect()->to('/auth/login');
    }

    /**
     * Get current user profile
     */
    // public function profile()
    // {
    //     $session = session();

    //     if ($session->get('status') !== 'login') {
    //         return redirect()->to('/auth/login');
    //     }

    //     $userId = $session->get('id');
    //     $user = $this->authModel->find($userId);
    //     $stats = $this->authModel->getUserStats($userId);

    //     $data = [
    //         'user' => $user,
    //         'stats' => $stats,
    //         'toko' => $session->get('toko')
    //     ];

    //     return view('profile', $data);
    // }

    // /**
    //  * Update user profile
    //  */
    // public function updateProfile()
    // {
    //     $session = session();

    //     if ($session->get('status') !== 'login') {
    //         return $this->response->setJSON(['status' => 'error', 'message' => 'Not logged in']);
    //     }

    //     $userId = $session->get('id');
    //     $nama = $this->request->getPost('nama');
    //     $password = $this->request->getPost('password');
    //     $password_confirm = $this->request->getPost('password_confirm');

    //     $updateData = ['nama' => $nama];

    //     // Jika password diisi, update password
    //     if (!empty($password)) {
    //         if ($password !== $password_confirm) {
    //             return $this->response->setJSON([
    //                 'status' => 'error',
    //                 'message' => 'Password dan konfirmasi tidak sama'
    //             ]);
    //         }
    //         $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
    //     }

    //     if ($this->authModel->update($userId, $updateData)) {
    //         // Update session nama jika berubah
    //         $session->set('nama', $nama);

    //         return $this->response->setJSON([
    //             'status' => 'success',
    //             'message' => 'Profile berhasil diupdate'
    //         ]);
    //     } else {
    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'message' => 'Gagal update profile'
    //         ]);
    //     }
    // }

    /**
     * Update nama toko untuk user yang sedang login
     */
    public function updateNamaToko()
    {
        // Set response header untuk JSON
        $this->response->setContentType('application/json');
        
        $session = session();

        if ($session->get('status') !== 'login') {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Anda belum login'
            ]);
        }

        // Hanya user biasa yang bisa edit nama toko
        if ($session->get('role') === 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Admin tidak dapat mengubah nama toko'
            ]);
        }

        $userId = $session->get('id');
        $namaToko = $this->request->getPost('nama_toko');

        // Validasi input
        if (empty($namaToko) || trim($namaToko) === '') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Nama toko harus diisi'
            ]);
        }

        // Sanitize input
        $namaToko = trim($namaToko);

        try {
            // Update nama toko
            if ($this->authModel->updateNamaToko($userId, $namaToko)) {
                // Update session toko
                $toko = $this->authModel->getToko($userId);
                $session->set('toko', $toko);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Nama toko berhasil diupdate',
                    'nama' => $namaToko
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal update nama toko'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating nama toko: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }
}
