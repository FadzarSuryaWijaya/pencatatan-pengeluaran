<?php

namespace App\Models;

use CodeIgniter\Model; // <--- PASTIKAN BARIS INI ADA!

class Auth_model extends Model // <--- UBAH DARI CI_Model MENJADI Model
{
    // Konfigurasi tabel yang akan digunakan model ini secara default
    protected $table        = 'pengguna'; // Nama tabel utama untuk model ini
    protected $primaryKey   = 'id';       // Primary key dari tabel 'pengguna' (asumsi ada kolom 'id')

    // Tentukan apakah auto-incrementing primary key digunakan
    protected $useAutoIncrement = true;

    // Tentukan tipe data yang dikembalikan oleh hasil query (array atau object)
    protected $returnType     = 'array'; // Mengembalikan hasil sebagai array asosiatif
    protected $useSoftDeletes = false; // Set true jika tabel menggunakan soft delete

    // Kolom yang diizinkan untuk diisi secara massal (untuk insert/update)
    protected $allowedFields = ['username', 'password', 'nama', 'role']; // Sesuaikan dengan kolom relevan lainnya di tabel 'pengguna'

    // Timestamp (atur true jika tabel memiliki kolom created_at, updated_at, deleted_at)
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validasi (opsional)
    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Memverifikasi kredensial login pengguna.
     * CATATAN: Metode ini TIDAK DISARANKAN jika Anda menggunakan password_verify() di controller.
     * Metode `getUser()` sudah cukup untuk controller.
     * Saya menyertakan ini hanya karena ada di kode asli Anda,
     * tetapi idealnya Anda harus menghapusnya dan hanya memanggil `getUser()` dari controller.
     *
     * @param string $username Nama pengguna.
     * @param string $password Kata sandi.
     * @return array|null Mengembalikan data pengguna jika kredensial cocok, null jika tidak.
     */
    public function login($username, $password)
    {
        // PENTING: Jika password di-hash, WHERE dengan password mentah TIDAK AKAN BERHASIL.
        // Controller harus mengambil pengguna berdasarkan username, lalu memverifikasi password.
        return $this->where('username', $username)
            ->where('password', $password) // Baris ini TIDAK AKAN BERHASIL jika password di-hash.
            ->first();
    }

    /**
     * Mengambil data pengguna berdasarkan username.
     * Ini adalah metode yang benar untuk digunakan oleh controller untuk login.
     *
     * @param string $username Nama pengguna.
     * @return array|null Mengembalikan data pengguna jika ditemukan, null jika tidak.
     */
    public function getUser(string $username): ?array
    {
        // Langsung panggil metode where() dan first() dari model
        return $this->where('username', $username)->first();
    }

    /**
     * Mengambil informasi toko.
     *
     * @return array|null Mengembalikan data nama dan alamat toko, null jika tidak ditemukan.
     */
    public function getToko(): ?array
    {
        // Karena ini mengakses tabel 'toko' yang berbeda dari '$table' default 'pengguna',
        // di sini kita menggunakan `$this->db->table()`, yang sudah benar.
        return $this->db->table('toko')
            ->select('nama, alamat')
            ->get()
            ->getRowArray();
    }
}
