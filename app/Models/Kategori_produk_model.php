<?php

namespace App\Models;

use CodeIgniter\Model;

class Kategori_produk_model extends Model
{
    // Properti dasar untuk model CI4
    protected $table      = 'kategori_produk'; // Nama tabel yang terkait dengan model ini
    protected $primaryKey = 'id'; // Nama primary key dari tabel (asumsi ada kolom 'id')

    // Tentukan apakah primary key adalah auto-increment
    protected $useAutoIncrement = true;

    // Tentukan tipe data yang akan dikembalikan oleh metode find* (array atau object)
    protected $returnType     = 'array'; // Mengembalikan hasil sebagai array asosiatif
    protected $useSoftDeletes = false; // Set true jika ingin menggunakan soft delete (tabel harus punya deleted_at)

    // Kolom yang diizinkan untuk diisi secara massal (untuk insert/update)
    // PASTIkan ini mencakup semua kolom yang akan Anda gunakan dalam form
    protected $allowedFields = ['kategori']; // Contoh: jika tabel kategori_produk punya kolom 'kategori'

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
     * Menyimpan data kategori produk baru.
     *
     * @param array $data Data yang akan disimpan (misal: ['kategori' => 'Nama Kategori']).
     * @return bool True jika berhasil, false jika gagal.
     */
    public function createKategori($data)
    {
        // Metode insert() dari CodeIgniter\Model secara otomatis akan memasukkan ke $this->table
        return $this->insert($data);
    }

    /**
     * Mengambil semua data kategori produk.
     *
     * @return array|null Array of arrays kategori atau null jika tidak ada data.
     */
    public function readKategori()
    {
        // Metode findAll() dari CodeIgniter\Model mengambil semua baris dari $this->table
        return $this->findAll();
    }

    /**
     * Memperbarui data kategori produk berdasarkan ID.
     *
     * @param int $id ID kategori yang akan diperbarui.
     * @param array $data Data baru untuk kategori (misal: ['kategori' => 'Nama Kategori Baru']).
     * @return bool True jika berhasil, false jika gagal.
     */
    public function updateKategori($id, $data)
    {
        // Metode update() dari CodeIgniter\Model secara otomatis akan memperbarui berdasarkan $this->primaryKey
        return $this->update($id, $data);
    }

    /**
     * Menghapus data kategori produk berdasarkan ID.
     *
     * @param int $id ID kategori yang akan dihapus.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function deleteKategori($id)
    {
        // Metode delete() dari CodeIgniter\Model secara otomatis akan menghapus berdasarkan $this->primaryKey
        return $this->delete($id);
    }

    /**
     * Mengambil satu data kategori produk berdasarkan ID.
     *
     * @param int $id ID kategori yang dicari.
     * @return array|object|null Array/object dari kategori atau null jika tidak ditemukan.
     */
    public function getKategoriById($id)
    {
        // Metode find() dari CodeIgniter\Model adalah cara cepat untuk mendapatkan satu baris berdasarkan primary key
        return $this->find($id);
    }

    /**
     * Mencari kategori produk berdasarkan string pencarian di kolom 'kategori'.
     *
     * @param string $search String pencarian.
     * @return array Array of arrays/objects yang cocok dengan pencarian.
     */
    public function searchKategori($search = "")
    {
        // Memanggil metode like() dari Query Builder pada model, kemudian findAll() untuk mendapatkan hasilnya.
        // Jika $search kosong, ini akan mengembalikan semua kategori.
        return $this->like('kategori', $search)->findAll();
    }
}