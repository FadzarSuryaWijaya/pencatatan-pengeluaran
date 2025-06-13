<?php

namespace App\Models; // Sesuaikan namespace jika model Anda berada di sub-folder lain

use CodeIgniter\Model; // Penting: Menggunakan kelas Model dari CodeIgniter

class Stok_masuk_model extends Model
{
    // Properti dasar untuk model CI4
    protected $table      = 'stok_masuk'; // Nama tabel utama yang terkait dengan model ini
    protected $primaryKey = 'id'; // Nama primary key dari tabel (asumsi ada kolom 'id')

    // Tentukan apakah primary key adalah auto-increment
    protected $useAutoIncrement = true;

    // Tentukan tipe data yang akan dikembalikan oleh metode find*
    // Bisa 'array' atau 'object'
    protected $returnType     = 'array'; // Mengembalikan hasil sebagai array asosiatif
    protected $useSoftDeletes = false; // Set true jika ingin menggunakan soft delete (tabel harus punya deleted_at)

    // Kolom yang diizinkan untuk diisi secara massal (untuk insert/update)
    // PASTIkan ini mencakup semua kolom yang akan Anda gunakan dalam form
    // Asumsi kolom-kolom untuk stok_masuk:
    protected $allowedFields = ['tanggal', 'jumlah', 'keterangan', 'produk_id', 'supplier_id']; // 'produk_id' dan 'supplier_id' adalah FK
    // Sesuaikan dengan kolom aktual di tabel Anda

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
     * Menyimpan data stok masuk baru.
     *
     * @param array $data Data stok masuk yang akan disimpan (misal: ['tanggal' => '...', 'jumlah' => '...', 'keterangan' => '...', 'produk_id' => '...', 'supplier_id' => '...']).
     * @return bool True jika berhasil, false jika gagal.
     */
    public function createStokMasuk($data) // Ubah nama fungsi agar lebih spesifik
    {
        // Metode insert() dari CodeIgniter\Model secara otomatis akan memasukkan ke $this->table
        return $this->insert($data);
    }

    /**
     * Mengambil semua data stok masuk beserta informasi produk.
     *
     * @return array Array of arrays stok masuk atau null jika tidak ada data.
     */
    public function readStokMasuk() // Ubah nama fungsi agar lebih spesifik
    {
        // Menggunakan builder() untuk mengakses Query Builder saat melakukan join
        // Join 'produk.id = stok_masuk.barcode' di CI3 mengindikasikan 'barcode' di stok_masuk adalah foreign key ke 'produk.id'
        // Saya asumsikan nama kolom di tabel stok_masuk yang menyimpan ID produk adalah 'produk_id'
        return $this->builder()
            ->select('stok_masuk.tanggal, stok_masuk.jumlah, stok_masuk.keterangan, produk.barcode, produk.nama_produk')
            ->join('produk', 'produk.id = stok_masuk.barcode', 'left') // Sesuaikan 'stok_masuk.produk_id' jika nama kolom berbeda
            ->get()
            ->getResultArray(); // Mengambil semua hasil sebagai array of arrays
    }

    public function readLaporanStokMasuk() // Ubah nama fungsi agar lebih spesifik
    {
        // Menggunakan builder() untuk mengakses Query Builder saat melakukan join
        // Join 'produk.id = stok_masuk.barcode' di CI3 mengindikasikan 'barcode' di stok_masuk adalah foreign key ke 'produk.id'
        // Saya asumsikan nama kolom di tabel stok_masuk yang menyimpan ID produk adalah 'produk_id'
        return $this->builder()
            ->select('stok_masuk.tanggal, stok_masuk.jumlah, stok_masuk.keterangan, produk.barcode, produk.nama_produk')
            ->join('produk', 'produk.id = stok_masuk.barcode', 'left') // Sesuaikan 'stok_masuk.produk_id' jika nama kolom berbeda
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
    public function laporanStokMasuk(?string $startDate, ?string $endDate): array // Ubah nama fungsi agar lebih spesifik
    {
        // Menggunakan builder() untuk mengakses Query Builder saat melakukan join
        // Join 'produk.id = stok_masuk.barcode' di CI3 mengindikasikan 'barcode' di stok_masuk adalah foreign key ke 'produk.id'
        // Saya asumsikan nama kolom di tabel stok_masuk yang menyimpan ID produk adalah 'produk_id'
        // Saya asumsikan nama kolom di tabel stok_masuk yang menyimpan ID supplier adalah 'supplier_id'
        $builder = $this->builder()
            ->select('stok_masuk.tanggal, stok_masuk.jumlah, stok_masuk.keterangan, produk.barcode, produk.nama_produk, supplier.nama as supplier_nama')
            ->join('produk', 'produk.id = stok_masuk.barcode', 'left') // Join untuk mendapatkan detail produk
            ->join('supplier', 'supplier.id = stok_masuk.id', 'left'); // Join untuk mendapatkan nama supplier

        // HANYA tambahkan kondisi WHERE jika tanggal disediakan
        if (!empty($startDate) && !empty($endDate)) {
            $start = (new \DateTime($startDate))->format('Y-m-d 00:00:00');
            $end = (new \DateTime($endDate))->format('Y-m-d 23:59:59');
            $builder->where('stok_masuk.tanggal >=', $start)
                ->where('stok_masuk.tanggal <=', $end);
        }

        return $builder
            ->orderBy('stok_masuk.tanggal', 'DESC')
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
        // Fungsi ini beroperasi pada tabel 'produk', bukan 'stok_masuk'.
        // Menggunakan $this->db->table('produk') untuk mengakses tabel produk secara langsung.
        return $this->db->table('produk')
            ->select('stok')
            ->where('id', $id)
            ->get()
            ->getRowArray(); // Mengambil satu baris sebagai array asosiatif
    }

    /**
     * Menambahkan stok produk berdasarkan ID produk.
     *
     * @param int $id ID produk.
     * @param int $stok Nilai stok baru setelah penambahan.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function addStokProduk($id, $stok) // Ubah nama fungsi agar lebih spesifik
    {
        // Fungsi ini beroperasi pada tabel 'produk', bukan 'stok_masuk'.
        // Menggunakan $this->db->table('produk') untuk mengakses tabel produk secara langsung.
        return $this->db->table('produk')
            ->where('id', $id)
            ->set('stok', $stok)
            ->update();
    }

    /**
     * Menghitung total stok masuk untuk tanggal tertentu.
     *
     * @param string $hari Tanggal dalam format 'DD MM YYYY' (misal: '28 05 2025').
     * @return array|null Mengembalikan total jumlah stok masuk untuk hari tersebut, null jika tidak ada.
     */
    public function stokMasukHari(string $hari)
    {
        $start = $hari . ' 00:00:00';
        $end = $hari . ' 23:59:59';

        return $this->db->table('stok_masuk')
            ->select('SUM(CAST(jumlah AS UNSIGNED)) AS total')
            ->where('tanggal >=', $start)
            ->where('tanggal <=', $end)
            ->get()
            ->getRowArray();
    }



    /**
     * Metode untuk memfilter laporan stok masuk berdasarkan rentang tanggal.
     * Jika startDate dan endDate kosong/null, akan mengembalikan semua data stok masuk.
     *
     * @param string|null $startDate Tanggal mulai dalam format 'YYYY-MM-DD'. Bisa null/kosong.
     * @param string|null $endDate Tanggal akhir dalam format 'YYYY-MM-DD'. Bisa null/kosong.
     * @return array Array of arrays laporan stok masuk yang difilter.
     */
    // public function getLaporanStokMasukByDateRange(?string $startDate, ?string $endDate): array
    // {
    //     $builder = $this->builder()
    //                     ->select('stok_masuk.tanggal, stok_masuk.jumlah, stok_masuk.keterangan, produk.barcode, produk.nama_produk, supplier.nama as supplier_nama')
    //                     ->join('produk', 'produk.id = stok_masuk.barcode', 'left') // Join untuk mendapatkan detail produk
    //                     ->join('supplier', 'supplier.id = stok_masuk.id', 'left'); // Join untuk mendapatkan nama supplier

    //     // HANYA tambahkan kondisi WHERE jika tanggal disediakan
    //     if (!empty($startDate) && !empty($endDate)) {
    //         $start = (new \DateTime($startDate))->format('Y-m-d 00:00:00');
    //         $end = (new \DateTime($endDate))->format('Y-m-d 23:59:59');
    //         $builder->where('stok_masuk.tanggal >=', $start)
    //                 ->where('stok_masuk.tanggal <=', $end);
    //     }

    //     return $builder
    //     ->orderBy('stok_masuk.tanggal', 'DESC')
    //     ->get()
    //     ->getResultArray();
    // }
}
