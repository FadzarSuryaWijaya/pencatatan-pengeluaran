<?php

namespace App\Models; // Sesuaikan namespace jika model Anda berada di sub-folder lain

use CodeIgniter\Model; // Penting: Menggunakan kelas Model dari CodeIgniter

class Pelanggan_model extends Model
{
    // Properti dasar untuk model CI4
    protected $table      = 'pelanggan'; // Nama tabel yang terkait dengan model ini
    protected $primaryKey = 'id'; // Nama primary key dari tabel (asumsi ada kolom 'id')

    // Tentukan apakah primary key adalah auto-increment
    protected $useAutoIncrement = true;
    
    // Tentukan tipe data yang akan dikembalikan oleh metode find*
    // Bisa 'array' atau 'object'
    protected $returnType     = 'array'; // Mengembalikan hasil sebagai array asosiatif
    protected $useSoftDeletes = false; // Set true jika ingin menggunakan soft delete (tabel harus punya deleted_at)

    // Kolom yang diizinkan untuk diisi secara massal (untuk insert/update)
    // PASTIkan ini mencakup semua kolom yang akan Anda gunakan dalam form
    // Contoh kolom umum untuk tabel 'pelanggan':
    protected $allowedFields = ['nama', 'alamat', 'telepon', 'email']; // Sesuaikan dengan kolom aktual di tabel Anda

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
     * Menyimpan data pelanggan baru.
     *
     * @param array $data Data yang akan disimpan (misal: ['nama' => 'John Doe', 'alamat' => '...', ...]).
     * @return bool True jika berhasil, false jika gagal.
     */
    public function createPelanggan($data) // Ubah nama fungsi agar lebih spesifik
    {
        // Metode insert() dari CodeIgniter\Model secara otomatis akan memasukkan ke $this->table
        return $this->insert($data);
    }

    /**
     * Mengambil semua data pelanggan.
     *
     * @return array|null Array of arrays pelanggan atau null jika tidak ada data.
     */
    public function readPelanggan() // Ubah nama fungsi agar lebih spesifik
    {
        // Metode findAll() dari CodeIgniter\Model mengambil semua baris dari $this->table
        return $this->findAll();
    }

    /**
     * Memperbarui data pelanggan berdasarkan ID.
     *
     * @param int $id ID pelanggan yang akan diperbarui.
     * @param array $data Data baru untuk pelanggan.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function updatePelanggan($id, $data) // Ubah nama fungsi agar lebih spesifik
    {
        // Metode update() dari CodeIgniter\Model secara otomatis akan memperbarui berdasarkan $this->primaryKey
        return $this->update($id, $data);
    }

    /**
     * Menghapus data pelanggan berdasarkan ID.
     *
     * @param int $id ID pelanggan yang akan dihapus.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function deletePelanggan($id) // Ubah nama fungsi agar lebih spesifik
    {
        // Metode delete() dari CodeIgniter\Model secara otomatis akan menghapus berdasarkan $this->primaryKey
        return $this->delete($id);
    }

    /**
     * Mengambil satu data pelanggan berdasarkan ID.
     *
     * Catatan: Nama fungsi asli 'getSupplier' kemungkinan typo dan seharusnya 'getPelanggan'.
     *
     * @param int $id ID pelanggan yang dicari.
     * @return array|object|null Array/object dari pelanggan atau null jika tidak ditemukan.
     */
    public function getPelanggan($id) // Mengoreksi nama fungsi dari getSupplier menjadi getPelanggan
    {
        // Metode find() dari CodeIgniter\Model adalah cara cepat untuk mendapatkan satu baris berdasarkan primary key
        return $this->find($id);
    }

    /**
     * Mencari pelanggan berdasarkan string pencarian di kolom 'nama'.
     *
     * @param string $search String pencarian.
     * @return array Array of arrays/objects yang cocok dengan pencarian.
     */
    public function searchPelanggan($search = "") // Ubah nama fungsi agar lebih spesifik
    {
        // Memanggil metode like() dari Query Builder pada model, kemudian findAll() untuk mendapatkan hasilnya.
        // Jika $search kosong, ini akan mengembalikan semua pelanggan.
        return $this->like('nama', $search)->findAll();
    }
}