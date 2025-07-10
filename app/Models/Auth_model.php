<?php

namespace App\Models;

use App\Models\BaseModel;

class Auth_model extends BaseModel
{
    // Konfigurasi tabel yang akan digunakan model ini secara default
    protected $table        = 'pengguna';
    protected $primaryKey   = 'id';

    // Tentukan apakah auto-incrementing primary key digunakan
    protected $useAutoIncrement = true;

    // Tentukan tipe data yang dikembalikan oleh hasil query (array atau object)
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    // Kolom yang diizinkan untuk diisi secara massal
    // CATATAN: pengguna tidak perlu user_id karena ini adalah tabel master user
    protected $allowedFields = ['username', 'password', 'nama', 'role'];

    // Timestamp (atur true jika tabel memiliki kolom created_at, updated_at, deleted_at)
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validasi
    protected $validationRules    = [
        'username' => 'required|min_length[3]|max_length[50]|is_unique[pengguna.username,id,{id}]',
        'password' => 'required|min_length[6]',
        'nama'     => 'required|min_length[2]|max_length[100]',
        'role'     => 'required|in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'username' => [
            'required'    => 'Username harus diisi',
            'min_length'  => 'Username minimal 3 karakter',
            'max_length'  => 'Username maksimal 50 karakter',
            'is_unique'   => 'Username sudah digunakan'
        ],
        'password' => [
            'required'    => 'Password harus diisi',
            'min_length'  => 'Password minimal 6 karakter'
        ],
        'nama' => [
            'required'    => 'Nama harus diisi',
            'min_length'  => 'Nama minimal 2 karakter',
            'max_length'  => 'Nama maksimal 100 karakter'
        ],
        'role' => [
            'required' => 'Role harus diisi',
            'in_list'  => 'Role harus 0 (user) atau 1 (admin)'
        ]
    ];
    
    protected $skipValidation = false;

    /**
     * Override hasUserIsolation untuk tabel pengguna
     * Tabel pengguna tidak menggunakan user isolation
     */
    protected function hasUserIsolation(): bool
    {
        return false; // Tabel pengguna tidak perlu filter user_id
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
        return $this->where('username', $username)->first();
    }

    /**
     * Mengambil informasi toko berdasarkan user_id
     * Setiap user memiliki toko sendiri
     *
     * @param int|null $userId ID user, jika null maka ambil dari session
     * @return array|null Mengembalikan data nama dan alamat toko, null jika tidak ditemukan.
     */
    public function getToko(?int $userId = null): ?array
    {
        if ($userId === null) {
            $userId = $this->getCurrentUserId();
        }
        
        if ($userId === null) {
            return null;
        }
        // mendaptakan id, nama , alamat berdasaka  table toko
        $toko = $this->db->table('toko')
            ->select('id, nama, alamat')
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();
            
        // Jika toko tidak ditemukan, buat toko default
        if (!$toko) {
            $this->createDefaultToko($userId);
            $toko = $this->db->table('toko')
                ->select('id, nama, alamat')
                ->where('user_id', $userId)
                ->get()
                ->getRowArray();
    }
    return $toko;
}

/**
     * Update nama toko berdasarkan user_id
     *
     * @param int $userId ID user
     * @param string $namaToko Nama toko baru
     * @return bool
     */
    public function updateNamaToko(int $userId, string $namaToko): bool
    {
        // Pastikan user bukan admin
        $user = $this->find($userId);
        if ($user && $user['role'] == '1') {
            return false;
        }

        return $this->db->table('toko')
            ->where('user_id', $userId)
            ->update(['nama' => $namaToko]);
    }
    

    /**
     * Membuat toko default untuk user baru
     *
     * @param int $userId ID user yang baru dibuat
     * @param string $namaToko Nama toko default
     * @param string $alamatToko Alamat toko default
     * @return bool
     */
    public function createDefaultToko(int $userId, string $namaToko = 'Toko Saya', string $alamatToko = 'Alamat Toko'): bool
    {
        // Cek apakah user adalah admin
        $user = $this->find($userId);
        if ($user && $user['role'] == '1') {
            $namaToko = 'Toko Admin';
            $alamatToko = 'Alamat Admin';
        }
        
        $data = [
            'user_id' => $userId,
            'nama'    => $namaToko,
            'alamat'  => $alamatToko
        ];
        
        return $this->db->table('toko')->insert($data);
    }
    
    /**
     * Membuat data default untuk user baru
     * Dipanggil setelah registrasi berhasil
     *
     * @param int $userId ID user yang baru dibuat
     * @return bool
     */
    public function createDefaultUserData(int $userId): bool
    {
        $this->db->transStart();
        
        try {
            // Buat toko default
            $this->createDefaultToko($userId, 'Toko Saya', 'Alamat Toko');
            
            // Buat kategori produk default
            $this->db->table('kategori_produk')->insert([
                'user_id' => $userId,
                'kategori' => 'Umum'
            ]);
            
            // Buat satuan produk default
            $defaultSatuan = ['Pcs', 'Kg', 'Liter', 'Meter', 'Dus'];
            foreach ($defaultSatuan as $satuan) {
                $this->db->table('satuan_produk')->insert([
                    'user_id' => $userId,
                    'satuan' => $satuan
                ]);
            }
            
            $this->db->transComplete();
            return $this->db->transStatus();
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            return false;
        }
    }
    
    /**
     * Get all users (untuk admin super jika diperlukan)
     */
    public function getAllUsers(): array
    {
        return $this->findAll();
    }
    
    /**
     * Get user statistics
     */
    public function getUserStats(int $userId): array
    {
        $stats = [];
        
        // Total produk
        $stats['total_produk'] = $this->db->table('produk')
            ->where('user_id', $userId)
            ->countAllResults();
            
        // Total transaksi
        $stats['total_transaksi'] = $this->db->table('transaksi')
            ->where('user_id', $userId)
            ->countAllResults();
            
        // Total supplier
        $stats['total_supplier'] = $this->db->table('supplier')
            ->where('user_id', $userId)
            ->countAllResults();
            
        // Total pelanggan
        $stats['total_pelanggan'] = $this->db->table('pelanggan') 
            ->where('user_id', $userId)
            ->countAllResults();
        
        return $stats;
    }
}