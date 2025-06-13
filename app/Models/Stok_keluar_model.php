<?php

namespace App\Models; // Sesuaikan namespace jika model Anda berada di sub-folder lain

use CodeIgniter\Model; // Penting: Menggunakan kelas Model dari CodeIgniter

class Stok_keluar_model extends Model
{
    // Properti dasar untuk model CI4
    protected $table      = 'stok_keluar'; // Nama tabel utama yang terkait dengan model ini
    protected $primaryKey = 'id'; // Nama primary key dari tabel (asumsi ada kolom 'id')

    // Tentukan apakah primary key adalah auto-increment
    protected $useAutoIncrement = true;

    // Tentukan tipe data yang akan dikembalikan oleh metode find*
    // Bisa 'array' atau 'object'
    protected $returnType     = 'array'; // Mengembalikan hasil sebagai array asosiatif
    protected $useSoftDeletes = false; // Set true jika ingin menggunakan soft delete (tabel harus punya deleted_at)

    // Kolom yang diizinkan untuk diisi secara massal (untuk insert/update)
    // PASTIkan ini mencakup semua kolom yang akan Anda gunakan dalam form
    // Asumsi kolom-kolom untuk stok_keluar:
    protected $allowedFields = ['tanggal', 'jumlah', 'keterangan', 'produk_id']; // Mengganti 'barcode' dengan 'produk_id'
                                                                                // karena itu adalah FK ke produk.id

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
     * Menyimpan data stok keluar baru.
     *
     * @param array $data Data stok keluar yang akan disimpan (misal: ['tanggal' => '...', 'jumlah' => '...', 'keterangan' => '...', 'produk_id' => '...']).
     * @return bool True jika berhasil, false jika gagal.
     */
    public function createStokKeluar($data) // Ubah nama fungsi agar lebih spesifik
    {
        // Metode insert() dari CodeIgniter\Model secara otomatis akan memasukkan ke $this->table
        return $this->insert($data);
    }

    /**
     * Mengambil semua data stok keluar beserta informasi produk.
     *
     * @return array|null Array of arrays stok keluar atau null jika tidak ada data.
     */
    public function readStokKeluar() // Ubah nama fungsi agar lebih spesifik
    {
        // Menggunakan builder() untuk mengakses Query Builder saat melakukan join
        // Join 'produk.id = stok_keluar.barcode' di CI3 mengindikasikan 'barcode' di stok_keluar adalah foreign key ke 'produk.id'
        // Saya asumsikan nama kolom di tabel stok_keluar yang menyimpan ID produk adalah 'produk_id'
        return $this->builder()
                    ->select('stok_keluar.tanggal, stok_keluar.jumlah, stok_keluar.keterangan, produk.barcode, produk.nama_produk')
                    ->join('produk', 'produk.id = stok_keluar.id') // Sesuaikan 'stok_keluar.produk_id' jika nama kolom berbeda
                    ->get()
                    ->getResultArray(); // Mengambil semua hasil sebagai array of arrays
    }
    /**
     * Metode untuk memfilter laporan stok masuk berdasarkan rentang tanggal.
     * Jika startDate dan endDate kosong/null, akan mengembalikan semua data stok masuk.
     *
     * @param string|null $startDate Tanggal mulai dalam format 'YYYY-MM-DD'. Bisa null/kosong.
     * @param string|null $endDate Tanggal akhir dalam format 'YYYY-MM-DD'. Bisa null/kosong.
     * @return array Array of arrays laporan stok masuk yang difilter.
     */
    public function laporanStokKeluar(?string $startDate, ?string $endDate): array
{
    $builder = $this->builder()
                    ->select('stok_keluar.tanggal, stok_keluar.jumlah, stok_keluar.keterangan, produk.barcode, produk.nama_produk')
                    ->join('produk', 'produk.id = stok_keluar.id', 'left');

    if (!empty($startDate) && !empty($endDate)) {
        $start = (new \DateTime($startDate))->format('Y-m-d 00:00:00');
        $end = (new \DateTime($endDate))->format('Y-m-d 23:59:59');
        $builder->where('stok_keluar.tanggal >=', $start)
                ->where('stok_keluar.tanggal <=', $end);
    }

    return $builder
        ->orderBy('stok_keluar.tanggal', 'DESC')
        ->get()
        ->getResultArray();
}

    /**
     * Mengambil stok produk berdasarkan ID produk.
     *
     * @param int $id ID produk.
     * @return array|null Mengembalikan data stok produk (kolom 'stok'), null jika tidak ditemukan.
     */
    public function getStokProduk($id) // Ubah nama fungsi agar lebih spesifik
    {
        // Fungsi ini beroperasi pada tabel 'produk', bukan 'stok_keluar'.
        // Menggunakan $this->db->table('produk') untuk mengakses tabel produk secara langsung.
        return $this->db->table('produk')
                        ->select('stok')
                        ->where('id', $id)
                        ->get()
                        ->getRowArray(); // Mengambil satu baris sebagai array asosiatif
    }

    /**
     * Mengurangi stok produk berdasarkan ID produk.
     *
     * Catatan: Nama fungsi asli 'addStok' mungkin menyesatkan karena ini untuk stok keluar,
     * yang biasanya berarti mengurangi stok. Saya mempertahankan nama fungsi asli.
     *
     * @param int $id ID produk.
     * @param int $stok Nilai stok baru setelah dikurangi.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function addStok($id, $stok) // Nama fungsi asli 'addStok' dipertahankan
    {
        // Fungsi ini beroperasi pada tabel 'produk', bukan 'stok_keluar'.
        // Menggunakan $this->db->table('produk') untuk mengakses tabel produk secara langsung.
        return $this->db->table('produk')
                        ->where('id', $id)
                        ->set('stok', $stok)
                        ->update();
    }
}