<?php

namespace App\Models; // Sesuaikan namespace jika model Anda berada di sub-folder lain

use CodeIgniter\Model; // Penting: Menggunakan kelas Model dari CodeIgniter

class Pengguna_model extends Model
{
    // Properti dasar untuk model CI4
    protected $table      = 'pengguna'; // Nama tabel yang terkait dengan model ini
    protected $primaryKey = 'id'; // Nama primary key dari tabel (asumsi ada kolom 'id')

    // Tentukan apakah primary key adalah auto-increment
    protected $useAutoIncrement = true;

    // Tentukan tipe data yang akan dikembalikan oleh metode find*
    // Bisa 'array' atau 'object'
    protected $returnType     = 'array'; // Mengembalikan hasil sebagai array asosiatif
    protected $useSoftDeletes = false; // Set true jika ingin menggunakan soft delete (tabel harus punya deleted_at)

    // Kolom yang diizinkan untuk diisi secara massal (untuk insert/update)
    // PASTIkan ini mencakup semua kolom yang akan Anda gunakan dalam form
    // Contoh kolom umum untuk tabel 'pengguna' (sesuaikan dengan tabel aktual Anda):
    protected $allowedFields = ['username', 'password', 'nama', 'role']; // Sesuaikan dengan kolom aktual di tabel Anda
                                                                     // Sangat penting untuk keamanan!

    // Timestamp (atur true jika tabel memiliki kolom created_at, updated_at, deleted_at)
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validasi (opsional, bisa didefinisikan di sini atau di Controller/Service)
    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Menyimpan data pengguna baru.
     *
     * @param array $data Data pengguna yang akan disimpan (misal: ['username' => '...', 'password' => '...', 'nama' => '...', 'role' => '...']).
     * @return bool True jika berhasil, false jika gagal.
     */
    public function createPengguna($data) // Ubah nama fungsi agar lebih spesifik
    {
        // Metode insert() dari CodeIgniter\Model secara otomatis akan memasukkan ke $this->table
        return $this->insert($data);
    }

    /**
     * Mengambil semua data pengguna dengan role tertentu (misal: '2').
     *
     * @return array|null Array of arrays pengguna atau null jika tidak ada data.
     */
    public function readPenggunaWithTokoByRole()
    {
        return $this->select('pengguna.*, toko.nama as nama_toko, toko.alamat as alamat_toko')
                    ->join('toko', 'toko.user_id = pengguna.id', 'left') // Use 'left' join to include users without a shop
                    ->where('pengguna.role', '0') // Assuming you want role '0' for this method
                    ->findAll();
    }

    /**
     * Memperbarui data pengguna berdasarkan ID.
     *
     * @param int $id ID pengguna yang akan diperbarui.
     * @param array $data Data baru untuk pengguna.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function updatePengguna($id, $data) // Ubah nama fungsi agar lebih spesifik
    {
        // Metode update() dari CodeIgniter\Model secara otomatis akan memperbarui berdasarkan $this->primaryKey
        return $this->update($id, $data);
    }

    /**
     * Menghapus data pengguna berdasarkan ID.
     *
     * @param int $id ID pengguna yang akan dihapus.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function deletePengguna($id) // Ubah nama fungsi agar lebih spesifik
    {
        // Metode delete() dari CodeIgniter\Model secara otomatis akan menghapus berdasarkan $this->primaryKey
        return $this->delete($id);
    }

    /**
     * Mengambil satu data pengguna berdasarkan ID dengan kolom tertentu.
     *
     * @param int $id ID pengguna yang dicari.
     * @return array|object|null Array/object dari pengguna atau null jika tidak ditemukan.
     */
    public function getPenggunaWithTokoById($id)
    {
        return $this->select('pengguna.*, toko.nama as nama_toko, toko.alamat as alamat_toko')
                    ->join('toko', 'toko.user_id = pengguna.id', 'left') // Use 'left' join
                    ->where('pengguna.id', $id)
                    ->first(); // Use first() to get a single row
    }

    /**
     * Mencari pengguna berdasarkan string pencarian di kolom 'nama'.
     *
     * Catatan: Fungsi asli mencari di 'kategori', yang mungkin typo untuk model pengguna.
     * Diasumsikan pencarian dilakukan pada kolom 'nama' pengguna.
     *
     * @param string $search String pencarian.
     * @return array Array of arrays/objects yang cocok dengan pencarian.
     */
    public function searchPenggunaWithToko($search = "")
    {
        $builder = $this->select('pengguna.*, toko.nama as nama_toko, toko.alamat as alamat_toko')
                        ->join('toko', 'toko.user_id = pengguna.id', 'left');

        if (!empty($search)) {
            $builder->groupStart() // Start a group for OR conditions
                    ->like('pengguna.nama', $search)
                    ->orLike('pengguna.username', $search)
                    ->orLike('toko.nama', $search) // Search by shop name
                    ->orLike('toko.alamat', $search) // Search by shop address
                    ->groupEnd(); // End the group
        }

        return $builder->findAll();
    }
}