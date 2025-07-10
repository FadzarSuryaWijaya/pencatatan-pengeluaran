<?php

namespace App\Models;

use CodeIgniter\Model;

class Stok_masuk_model extends Model
{
    protected $table          = 'stok_masuk';
    protected $primaryKey     = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    // --- KOREKSI PENTING DI SINI ---
    // Sesuaikan dengan kolom AKTUAL di tabel 'stok_masuk' Anda
    protected $allowedFields = ['tanggal', 'jumlah', 'keterangan', 'barcode', 'supplier', 'user_id'];
    // Saya juga menambahkan 'user_id' karena ada di tabel Anda.
    // Pastikan semua kolom yang mungkin diisi dari form ada di sini.
    // --- AKHIR KOREKSI PENTING ---

    protected $useTimestamps = false;
    protected $dateFormat     = 'datetime';
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';
    protected $deletedField   = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Menyimpan data stok masuk baru.
     *
     * @param array $data Data stok masuk yang akan disimpan (misal: ['tanggal' => '...', 'jumlah' => '...', 'keterangan' => '...', 'barcode' => '...', 'supplier' => '...']).
     * @return bool True jika berhasil, false jika gagal.
     */
    public function createStokMasuk($data)
    {
        return $this->insert($data);
    }

    /**
     * Mengambil semua data stok masuk beserta informasi produk.
     *
     * @return array Array of arrays stok masuk atau null jika tidak ada data.
     */
    public function readStokMasuk()
    {
        return $this->builder()
            ->select('stok_masuk.id, stok_masuk.tanggal, stok_masuk.jumlah, stok_masuk.keterangan, 
                    produk.barcode as produk_barcode, produk.nama_produk,
                    supplier.nama as supplier_nama')
            ->join('produk', 'produk.id = stok_masuk.barcode', 'left')
            ->join('supplier', 'supplier.id = stok_masuk.supplier', 'left')
            ->orderBy('stok_masuk.tanggal', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function readLaporanStokMasuk()
    {
        return $this->builder()
            ->select('stok_masuk.id, stok_masuk.tanggal, stok_masuk.jumlah, stok_masuk.keterangan, 
                    produk.barcode as produk_barcode, produk.nama_produk,
                    supplier.nama as supplier_nama')
            ->join('produk', 'produk.id = stok_masuk.barcode', 'left')
            ->join('supplier', 'supplier.id = stok_masuk.supplier', 'left')
            ->orderBy('stok_masuk.tanggal', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Metode untuk memfilter laporan stok masuk berdasarkan rentang tanggal.
     * Jika startDate dan endDate kosong/null, akan mengembalikan semua data stok masuk.
     *
     * @param string|null $startDate Tanggal mulai dalam format 'YYYY-MM-DD'. Bisa null/kosong.
     * @param string|null $endDate Tanggal akhir dalam format 'YYYY-MM-DD'. Bisa null/kosong.
     * @return array Array of arrays laporan stok masuk yang difilter.
     */
    public function laporanStokMasuk(?string $startDate, ?string $endDate): array
    {
        $builder = $this->builder()
            ->select('stok_masuk.id, stok_masuk.tanggal, stok_masuk.jumlah, stok_masuk.keterangan, 
                    produk.barcode as produk_barcode, produk.nama_produk,
                    supplier.nama as supplier_nama')
            ->join('produk', 'produk.id = stok_masuk.barcode', 'left')
            ->join('supplier', 'supplier.id = stok_masuk.supplier', 'left');

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
    public function getStokProduk($id)
    {
        return $this->db->table('produk')
            ->select('stok')
            ->where('id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Menambahkan stok produk berdasarkan ID produk.
     *
     * @param int $id ID produk.
     * @param int $stok Nilai stok baru setelah penambahan.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function addStokProduk($id, $stok)
    {
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
        // Pastikan format $hari yang masuk adalah 'YYYY-MM-DD'
        // Jika format yang Anda gunakan adalah 'DD MM YYYY' seperti di komentar, Anda perlu parsing terlebih dahulu.
        // Contoh: $tanggalObj = DateTime::createFromFormat('d m Y', $hari);
        // $start = $tanggalObj->format('Y-m-d 00:00:00');
        // $end = $tanggalObj->format('Y-m-d 23:59:59');

        // Asumsi format $hari sudah 'YYYY-MM-DD' atau 'YYYY-MM-DD HH:MM:SS'
        // Jika 'DD MM YYYY' maka akan ada error parsing
        $start = $hari . ' 00:00:00';
        $end = $hari . ' 23:59:59';

        return $this->db->table('stok_masuk')
            ->select('SUM(CAST(jumlah AS UNSIGNED)) AS total')
            ->where('tanggal >=', $start)
            ->where('tanggal <=', $end)
            ->get()
            ->getRowArray();
    }
}
