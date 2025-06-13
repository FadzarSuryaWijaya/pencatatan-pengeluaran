<?php

namespace App\Models; // Sesuaikan namespace jika model Anda berada di sub-folder lain

use CodeIgniter\Model; // Penting: Menggunakan kelas Model dari CodeIgniter

class Transaksi_model extends Model
{
    // Properti dasar untuk model CI4
    protected $table        = 'transaksi'; // Nama tabel utama yang terkait dengan model ini
    protected $primaryKey   = 'id'; // Nama primary key dari tabel (asumsi ada kolom 'id')

    // Tentukan apakah primary key adalah auto-increment
    protected $useAutoIncrement = true;

    // Tentukan tipe data yang akan dikembalikan oleh metode find*
    // Bisa 'array' atau 'object'
    protected $returnType     = 'array'; // Mengembalikan hasil sebagai array asosiatif
    protected $useSoftDeletes = false; // Set true jika ingin menggunakan soft delete (tabel harus punya deleted_at)

    // Kolom yang diizinkan untuk diisi secara massal (untuk insert/update)
    // PASTIkan ini mencakup semua kolom yang akan Anda gunakan dalam form
    // Asumsi kolom-kolom untuk transaksi:
    protected $allowedFields = ['tanggal', 'barcode', 'qty', 'total_bayar', 'jumlah_uang', 'diskon', 'pelanggan', 'kasir', 'nota'];
                               // 'barcode' di sini kemungkinan adalah produk_id
                               // 'pelanggan' di sini kemungkinan adalah pelanggan_id
                               // 'kasir' di sini kemungkinan adalah kasir_id (FK ke pengguna.id)
                               // Sesuaikan dengan kolom aktual di tabel Anda

    // Timestamp (atur true jika tabel memiliki kolom created_at, updated_at, deleted_at)
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validasi (opsional, bisa didefinisikan di sini atau di Controller/Service)
    protected $validationRules      = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Mengurangi stok produk berdasarkan ID produk.
     *
     * @param int $id ID produk.
     * @param int $stok Nilai stok baru setelah pengurangan.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function removeStokProduk($id, $stok) // Ubah nama fungsi agar lebih spesifik
    {
        // Fungsi ini beroperasi pada tabel 'produk', bukan 'transaksi'.
        // Menggunakan $this->db->table('produk') untuk mengakses tabel produk secara langsung.
        return $this->db->table('produk')
                        ->where('id', $id)
                        ->set('stok', $stok)
                        ->update();
    }

    /**
     * Menambahkan jumlah terjual pada produk berdasarkan ID produk.
     *
     * @param int $id ID produk.
     * @param int $jumlah Jumlah terjual yang akan ditambahkan.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function addTerjualProduk($id, $jumlah) // Ubah nama fungsi agar lebih spesifik
    {
        // Fungsi ini beroperasi pada tabel 'produk', bukan 'transaksi'.
        // Menggunakan $this->db->table('produk') untuk mengakses tabel produk secara langsung.
        return $this->db->table('produk')
                        ->where('id', $id)
                        ->set('terjual', $jumlah)
                        ->update();
    }

    /**
     * Menyimpan data transaksi baru.
     *
     * @param array $data Data transaksi yang akan disimpan.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function createTransaksi($data) // Ubah nama fungsi agar lebih spesifik
    {
        // Metode insert() dari CodeIgniter\Model secara otomatis akan memasukkan ke $this->table
        return $this->insert($data);
    }

    /**
     * Mengambil semua data transaksi beserta nama pelanggan.
     *
     * @return array Array of arrays transaksi atau null jika tidak ada data.
     */
    public function readTransaksi() // Ubah nama fungsi agar lebih spesifik
    {
        // Menggunakan builder() untuk mengakses Query Builder saat melakukan join
        // Asumsi 'transaksi.pelanggan' adalah foreign key ke 'pelanggan.id'
        return $this->builder()
                    ->select('transaksi.id, transaksi.tanggal, transaksi.nota, transaksi.barcode, transaksi.qty, transaksi.total_bayar, transaksi.jumlah_uang, transaksi.diskon, pelanggan.nama as pelanggan_nama,  pengguna.nama as kasir_nama')
                    ->join('pelanggan', 'transaksi.pelanggan = pelanggan.id', 'left') // 'left outer' di CI3 menjadi 'left' di CI4
                    ->join('pengguna', 'transaksi.kasir = pengguna.id', 'left')
                    ->get()
                    ->getResultArray(); // Mengambil semua hasil sebagai array of arrays
    }

    /**
     * Menghapus data transaksi berdasarkan ID.
     *
     * @param int $id ID transaksi yang akan dihapus.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function deleteTransaksi($id) // Ubah nama fungsi agar lebih spesifik
    {
        // Metode delete() dari CodeIgniter\Model secara otomatis akan menghapus berdasarkan $this->primaryKey
        return $this->delete($id);
    }

    /**
     * Mengambil nama produk dan kuantitas untuk transaksi tertentu.
     *
     * Catatan: Fungsi ini menghasilkan string HTML dan menggunakan loop di dalam model,
     * yang kurang ideal untuk praktik MVC. Sebaiknya model mengembalikan data,
     * dan view yang bertanggung jawab untuk HTML. Saya akan mengembalikan data array
     * yang lebih bersih.
     *
     * @param array $barcode_ids Array berisi ID produk (yang dinamai 'barcode' di CI3).
     * @param string $qty_string String kuantitas yang dipisahkan koma (misal: "2,1,5").
     * @return array Array berisi nama produk dan kuantitasnya.
     */
    public function getProdukTransaksiDetail(array $barcode_ids, string $qty_string) // Ubah nama fungsi & params
    {
        $total_qty = explode(',', $qty_string);
        $data = [];

        if (empty($barcode_ids)) {
            return [];
        }

        // Ambil semua nama produk sekaligus untuk menghindari N+1 query
        $produk_data = $this->db->table('produk')
                                ->select('id, nama_produk')
                                ->whereIn('id', $barcode_ids)
                                ->get()
                                ->getResultArray();

        $produk_map = [];
        foreach ($produk_data as $produk) {
            $produk_map[$produk['id']] = $produk['nama_produk'];
        }

        foreach ($barcode_ids as $key => $product_id) {
            $nama_produk = $produk_map[$product_id] ?? 'Produk Tidak Ditemukan';
            $qty = $total_qty[$key] ?? 0;
            $data[] = [
                'nama_produk' => $nama_produk,
                'qty' => $qty
            ];
        }
        return $data;
    }

    /**
     * Menghitung total kuantitas penjualan untuk tanggal tertentu.
     *
     * @param string $date Tanggal dalam format 'DD MMYYYY' (misal: '28 05 2025').
     * @return array Array total jumlah terjual per transaksi.
     */
    public function penjualanBulan($date)
    {
        // Ambil semua string qty untuk tanggal yang cocok
        $qty_results = $this->db->query("SELECT qty FROM transaksi WHERE DATE_FORMAT(tanggal, '%d %m %Y') = ?", [$date])->getResultArray();

        $d = [];
        foreach ($qty_results as $row) {
            $d[] = array_map('intval', explode(',', $row['qty'])); // Ensure quantities are integers
        }

        $data = [];
        foreach ($d as $key) {
            $data[] = array_sum($key);
        }
        return $data;
    }

    /**
     * Menghitung jumlah transaksi untuk tanggal tertentu.
     *
     * @param string $hari Tanggal dalam format 'DD MMYYYY'.
     * @return array|null Mengembalikan total transaksi untuk hari tersebut, null jika tidak ada.
     */
    public function transaksiHari($hari)
    {
        // Menggunakan prepared statement (?) untuk mencegah SQL Injection
        return $this->db->query("SELECT COUNT(*) AS total FROM transaksi WHERE DATE_FORMAT(tanggal, '%d %m %Y') = ?", [$hari])
                        ->getRowArray(); // Mengambil satu baris hasil agregasi sebagai array
    }

    /**
     * Mengambil kuantitas dari transaksi terakhir untuk tanggal tertentu.
     *
     * @param string $hari Tanggal dalam format 'DD MMYYYY'.
     * @return array|null Mengembalikan data qty transaksi terakhir, null jika tidak ada.
     */
    public function transaksiTerakhir($hari)
    {
        // Menggunakan prepared statement (?) untuk mencegah SQL Injection
        // Assuming you want the 'qty' string from the latest transaction on that day
        $result = $this->db->query("SELECT transaksi.qty FROM transaksi WHERE DATE_FORMAT(tanggal, '%d %m %Y') = ? ORDER BY tanggal DESC LIMIT 1", [$hari])
                        ->getRowArray();
        
        // If result is found, explode and sum the quantities
        if ($result && isset($result['qty'])) {
            return array_sum(array_map('intval', explode(',', $result['qty'])));
        }
        return 0; // Return 0 if no transactions found or qty is empty
    }


    /**
     * Mengambil detail transaksi lengkap berdasarkan ID, termasuk nama kasir.
     *
     * @param int $id ID transaksi.
     * @return array|object|null Array/object detail transaksi atau null jika tidak ditemukan.
     */
    public function getDetailTransaksi($id) // Ubah nama fungsi agar lebih spesifik
    {
        // Menggunakan builder() untuk mengakses Query Builder saat melakukan join
        // Asumsi 'transaksi.kasir' adalah foreign key ke 'pengguna.id'
        return $this->builder()
                    ->select('transaksi.nota, transaksi.tanggal, transaksi.barcode, transaksi.qty, transaksi.total_bayar, transaksi.jumlah_uang, pengguna.nama as kasir_nama')
                    ->join('pengguna', 'transaksi.kasir = pengguna.id')
                    ->where('transaksi.id', $id)
                    ->get()
                    ->getRowArray(); // Mengambil satu baris sebagai array asosiatif
    }

    /**
     * Mengambil nama produk dan harga berdasarkan array ID produk.
     *
     * Catatan: Fungsi ini menggunakan loop di dalam model, yang kurang optimal.
     * Saya akan mengkonversinya untuk menggunakan whereIn() untuk efisiensi.
     *
     * @param array $barcode_ids Array berisi ID produk (yang dinamai 'barcode' di CI3).
     * @return array Array of arrays yang berisi nama produk dan harga.
     */
    public function getProdukNamesAndPrices(array $barcode_ids) // Ubah nama fungsi & params
    {
        if (empty($barcode_ids)) {
            return [];
        }

        // Ambil semua nama produk dan harga sekaligus untuk menghindari N+1 query
        return $this->db->table('produk')
                        ->select('id, nama_produk, harga')
                        ->whereIn('id', $barcode_ids)
                        ->get()
                        ->getResultArray();
    }

    /**
     * Metode untuk memfilter transaksi berdasarkan rentang tanggal.
     *
     * @param string|null $startDate Tanggal mulai dalam format 'YYYY-MM-DD'. Bisa null/kosong.
     * @param string|null $endDate Tanggal akhir dalam format 'YYYY-MM-DD'. Bisa null/kosong.
     * @return array Array of arrays transaksi yang difilter.
     */
    public function getTransaksiByDateRange(?string $startDate, ?string $endDate): array
    {
        $builder = $this->builder()
                        ->select('transaksi.id, transaksi.tanggal, transaksi.nota, transaksi.barcode, transaksi.qty, transaksi.total_bayar, transaksi.jumlah_uang, transaksi.diskon, pelanggan.nama as pelanggan_nama, pengguna.nama as kasir_nama')
                        ->join('pelanggan', 'transaksi.pelanggan = pelanggan.id', 'left')
                        ->join('pengguna', 'transaksi.kasir = pengguna.id', 'left');

        // HANYA tambahkan kondisi WHERE jika tanggal disediakan
        if (!empty($startDate) && !empty($endDate)) {
            $start = (new \DateTime($startDate))->format('Y-m-d 00:00:00');
            $end = (new \DateTime($endDate))->format('Y-m-d 23:59:59');
            $builder->where('transaksi.tanggal >=', $start)
                    ->where('transaksi.tanggal <=', $end);
        }

        return $builder
            ->orderBy('transaksi.tanggal', 'DESC')
            ->get()
            ->getResultArray();
    }
}