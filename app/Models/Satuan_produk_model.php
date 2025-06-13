<?php

namespace App\Models; // Sesuaikan namespace jika model Anda berada di sub-folder lain

use CodeIgniter\Model; // Penting: Menggunakan kelas Model dari CodeIgniter

class Satuan_produk_model extends Model
{
    // Properti dasar untuk model CI4
    protected $table      = 'satuan_produk'; // Nama tabel yang terkait dengan model ini
    protected $primaryKey = 'id'; // Nama primary key dari tabel (asumsi ada kolom 'id')

    // Tentukan apakah primary key adalah auto-increment
    protected $useAutoIncrement = true;

    // Tentukan tipe data yang akan dikembalikan oleh metode find*
    // Bisa 'array' atau 'object'
    protected $returnType     = 'array'; // Mengembalikan hasil sebagai array asosiatif
    protected $useSoftDeletes = false; // Set true jika ingin menggunakan soft delete (tabel harus punya deleted_at)

    // Kolom yang diizinkan untuk diisi secara massal (untuk insert/update)
    // PASTIkan ini mencakup semua kolom yang akan Anda gunakan dalam form
    // Contoh kolom umum untuk tabel 'satuan_produk':
    protected $allowedFields = ['satuan']; // Sesuaikan dengan kolom aktual di tabel Anda

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
     * Menyimpan data satuan produk baru.
     *
     * @param array $data Data yang akan disimpan (misal: ['satuan' => 'Pcs']).
     * @return bool True jika berhasil, false jika gagal.
     */
    public function createSatuan($data) // Ubah nama fungsi agar lebih spesifik
    {
        // Metode insert() dari CodeIgniter\Model secara otomatis akan memasukkan ke $this->table
        return $this->insert($data);
    }

    /**
     * Mengambil semua data dari tabel satuan_produk.
     *
     * @return array|null Array of arrays/objects atau null jika tidak ada data.
     */
    public function readSatuan() // Ubah nama fungsi agar lebih spesifik
    {
        // Metode findAll() dari CodeIgniter\Model mengambil semua baris dari $this->table
        return $this->findAll();
    }

    /**
     * Memperbarui data di tabel satuan_produk berdasarkan ID.
     *
     * @param int $id ID satuan yang akan diperbarui.
     * @param array $data Data baru untuk satuan.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function updateSatuan($id, $data) // Ubah nama fungsi agar lebih spesifik
    {
        // Metode update() dari CodeIgniter\Model secara otomatis akan memperbarui berdasarkan $this->primaryKey
        return $this->update($id, $data);
    }

    /**
     * Menghapus data dari tabel satuan_produk berdasarkan ID.
     *
     * @param int $id ID satuan yang akan dihapus.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function deleteSatuan($id) // Ubah nama fungsi agar lebih spesifik
    {
        // Metode delete() dari CodeIgniter\Model secara otomatis akan menghapus berdasarkan $this->primaryKey
        return $this->delete($id);
    }

    /**
     * Mengambil satu data satuan produk berdasarkan ID.
     *
     * Catatan: Nama fungsi asli 'getKategori' kemungkinan typo dan seharusnya 'getSatuan'.
     *
     * @param int $id ID satuan yang dicari.
     * @return array|object|null Array/object dari satuan atau null jika tidak ditemukan.
     */
    public function getSatuan($id) // Mengoreksi nama fungsi dari getKategori menjadi getSatuan
    {
        // Metode find() dari CodeIgniter\Model adalah cara cepat untuk mendapatkan satu baris berdasarkan primary key
        return $this->find($id);
    }

    /**
     * Mencari satuan produk berdasarkan string pencarian.
     *
     * @param string $search String pencarian.
     * @return array Array of arrays/objects yang cocok dengan pencarian.
     */
    public function searchSatuan($search = "") // Ubah nama fungsi agar lebih spesifik
    {
        // Menggunakan metode like() dari Query Builder yang bisa diakses melalui model
        // dan kemudian findAll() untuk mendapatkan semua hasil yang cocok.
        return $this->like('satuan', $search)->findAll();
    }
}