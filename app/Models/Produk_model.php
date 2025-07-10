<?php

namespace App\Models; // Sesuaikan namespace jika model Anda berada di sub-folder lain

use CodeIgniter\Model; // Penting: Menggunakan kelas Model dari CodeIgniter

class Produk_model extends Model
{
	// Properti dasar untuk model CI4
	protected $table      = 'produk'; // Nama tabel utama yang terkait dengan model ini
	protected $primaryKey = 'id'; // Nama primary key dari tabel (asumsi ada kolom 'id')

	// Tentukan apakah primary key adalah auto-increment
	protected $useAutoIncrement = true;

	// Tentukan tipe data yang akan dikembalikan oleh metode find*
	// Bisa 'array' atau 'object'
	protected $returnType     = 'array'; // Mengembalikan hasil sebagai array asosiatif
	protected $useSoftDeletes = false; // Set true jika ingin menggunakan soft delete (tabel harus punya deleted_at)

	// Kolom yang diizinkan untuk diisi secara massal (untuk insert/update)
	// PASTIkan ini mencakup semua kolom yang akan Anda gunakan dalam form
	// Contoh kolom umum untuk tabel 'produk' (sesuaikan dengan tabel aktual Anda):
	protected $allowedFields = ['barcode', 'nama_produk', 'harga', 'stok', 'kategori', 'satuan', 'terjual'];
	// 'terjual' ditambahkan karena ada di query produkTerlaris
	// Sesuaikan dengan semua kolom yang dapat diisi dari form

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
	 * Menyimpan data produk baru.
	 *
	 * @param array $data Data produk yang akan disimpan.
	 * @return bool True jika berhasil, false jika gagal.
	 */
	public function createProduk($data) // Ubah nama fungsi agar lebih spesifik
	{
		// Metode insert() dari CodeIgniter\Model secara otomatis akan memasukkan ke $this->table
		return $this->insert($data);
	}

	/**
	 * Mengambil semua data produk beserta informasi kategori dan satuan.
	 *
	 * @return array|null Array of arrays produk atau null jika tidak ada data.
	 */
	public function readProduk() // Ubah nama fungsi agar lebih spesifik
	{
		// Menggunakan builder() untuk mengakses Query Builder saat melakukan join
		return $this->builder()
			->select('produk.id, produk.barcode, produk.nama_produk, produk.harga, produk.stok, kategori_produk.kategori, satuan_produk.satuan')
			->join('kategori_produk', 'produk.kategori = kategori_produk.id')
			->join('satuan_produk', 'produk.satuan = satuan_produk.id')
			->get()
			->getResultArray(); // Mengambil semua hasil sebagai array of arrays
	}

	/**
	 * Memperbarui data produk berdasarkan ID.
	 *
	 * @param int $id ID produk yang akan diperbarui.
	 * @param array $data Data baru untuk produk.
	 * @return bool True jika berhasil, false jika gagal.
	 */
	public function updateProduk($id, $data) // Ubah nama fungsi agar lebih spesifik
	{
		// Metode update() dari CodeIgniter\Model secara otomatis akan memperbarui berdasarkan $this->primaryKey
		return $this->update($id, $data);
	}

	/**
	 * Menghapus data produk berdasarkan ID.
	 *
	 * @param int $id ID produk yang akan dihapus.
	 * @return bool True jika berhasil, false jika gagal.
	 */
	public function deleteProduk($id) // Ubah nama fungsi agar lebih spesifik
	{
		// Metode delete() dari CodeIgniter\Model secara otomatis akan menghapus berdasarkan $this->primaryKey
		return $this->delete($id);
	}

	/**
	 * Mengambil satu data produk berdasarkan ID beserta informasi detail kategori dan satuan.
	 *
	 * @param int $id ID produk yang dicari.
	 * @return array|object|null Array/object dari produk atau null jika tidak ditemukan.
	 */
	public function getProdukById($id) // Ubah nama fungsi agar lebih spesifik
	{
		if (!is_numeric($id) || $id <= 0) {
			return null;
		}
		// Menggunakan builder() untuk mengakses Query Builder saat melakukan join
		return $this->builder()
			->select('produk.id, produk.barcode, produk.nama_produk, produk.harga, produk.stok, kategori_produk.id as kategori_id, kategori_produk.kategori, satuan_produk.id as satuan_id, satuan_produk.satuan')
			->join('kategori_produk', 'produk.kategori = kategori_produk.id')
			->join('satuan_produk', 'produk.satuan = satuan_produk.id')
			->where('produk.id', $id)
			->get()
			->getRowArray(); // Mengambil satu baris sebagai array asosiatif
	}

	/**
	 * Mencari produk berdasarkan barcode.
	 *
	 * @param string $search String pencarian barcode.
	 * @return array Array of arrays/objects yang cocok dengan pencarian barcode.
	 */

	// app/Models/Produk_model.php
	public function getBarcode(string $searchTerm = '')
	{
		$builder = $this->builder(); // Ini mengembalikan objek Query Builder
		$builder->select('id, barcode, nama_produk');

		if (!empty($searchTerm)) {
			$builder->groupStart()
				->like('barcode', $searchTerm)
				->orLike('nama_produk', $searchTerm)
				->groupEnd();
		}

		// Ubah findAll() menjadi get()->getResultArray() atau get()->getResult()
		return $builder->get()->getResultArray(); // Mengambil hasil dalam bentuk array asosiatif
		// Atau jika Anda ingin objek: return $builder->get()->getResult();
	}

	/**
	 * Mengambil nama produk dan stok berdasarkan ID.
	 *
	 * @param int $id ID produk.
	 * @return array|null Mengembalikan nama produk dan stok, null jika tidak ditemukan.
	 */
	public function getNamaProdukStok($id) // Ubah nama fungsi agar lebih spesifik
	{
		if (!is_numeric($id) || $id <= 0) {
			return null;
		}
		// Menggunakan select() dan find() untuk mendapatkan kolom tertentu dari satu baris
		return $this->select('nama_produk, stok')->find($id);
	}

	/**
	 * Mengambil stok, nama produk, harga, dan barcode berdasarkan ID.
	 *
	 * @param int $id ID produk.
	 * @return array|null Mengembalikan informasi stok dan produk, null jika tidak ditemukan.
	 */
	public function getDetailStokProduk($id) // Ubah nama fungsi agar lebih spesifik
	{
		// Menggunakan select() dan find() untuk mendapatkan kolom tertentu dari satu baris
		return $this->select('stok, nama_produk, harga, barcode')->find($id);
	}

	/**
	 * Mengambil produk terlaris dengan metode yang lebih efisien.
	 * Alternatif jika query di atas terlalu kompleks.
	 *
	 * @param string $periode Periode: 'today', '1month', '1year', 'all'
	 * @param int $limit Jumlah produk yang ditampilkan
	 * @return array
	 */
	public function produkTerlaris($periode = '1month', $limit = 5)
	{
		// Ambil semua transaksi dalam periode tertentu
		$builder = $this->db->table('transaksi');

		if ($periode !== 'all') {
			$dateRange = $this->getDateRange($periode);
			$builder->where('tanggal >=', $dateRange['start'])
				->where('tanggal <=', $dateRange['end']);
		}

		$transaksi = $builder->select('barcode, qty')->get()->getResultArray();

		// Proses data untuk menghitung total terjual per produk
		$produkTerjual = [];

		foreach ($transaksi as $trans) {
			$barcodes = explode(',', $trans['barcode']);
			$qtys = explode(',', $trans['qty']);

			foreach ($barcodes as $index => $produkId) {
				$produkId = trim($produkId);
				$qty = isset($qtys[$index]) ? (int)trim($qtys[$index]) : 0;

				if ($qty > 0) {
					if (!isset($produkTerjual[$produkId])) {
						$produkTerjual[$produkId] = 0;
					}
					$produkTerjual[$produkId] += $qty;
				}
			}
		}

		// Urutkan berdasarkan total terjual
		arsort($produkTerjual);

		// Ambil top products
		$topProdukIds = array_slice(array_keys($produkTerjual), 0, $limit);

		if (empty($topProdukIds)) {
			return [];
		}

		// Ambil nama produk
		$produkData = $this->db->table('produk')
			->select('id, nama_produk')
			->whereIn('id', $topProdukIds)
			->get()
			->getResultArray();

		// Gabungkan dengan data terjual
		$result = [];
		foreach ($produkData as $produk) {
			$result[] = [
				'nama_produk' => $produk['nama_produk'],
				'total_terjual' => $produkTerjual[$produk['id']]
			];
		}

		// Urutkan kembali berdasarkan total_terjual
		usort($result, function ($a, $b) {
			return $b['total_terjual'] - $a['total_terjual'];
		});

		return $result;
	}
	/**
	 * Mendapatkan kondisi tanggal untuk query SQL.
	 *
	 * @param string $periode
	 * @return string
	 */
	private function getDateCondition($periode)
	{
		switch ($periode) {
			case 'today':
				return "AND DATE(t.tanggal) = CURDATE()";
			case '1month':
				return "AND t.tanggal >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
			case '1year':
				return "AND t.tanggal >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
			case 'all':
			default:
				return "";
		}
	}

	/**
	 * Mendapatkan rentang tanggal berdasarkan periode.
	 *
	 * @param string $periode
	 * @return array
	 */
	private function getDateRange($periode)
	{
		$now = new \DateTime();
		$end = $now->format('Y-m-d 23:59:59');

		switch ($periode) {
			case 'today':
				$start = $now->format('Y-m-d 00:00:00');
				break;
			case '1month':
				$start = $now->modify('-1 month')->format('Y-m-d 00:00:00');
				break;
			case '1year':
				$start = $now->modify('-1 year')->format('Y-m-d 00:00:00');
				break;
			default:
				$start = '1970-01-01 00:00:00';
				break;
		}

		return ['start' => $start, 'end' => $end];
	}


	/**
	 * Mengambil 50 data produk dengan stok terbanyak.
	 * Catatan: Kolom 'stok' diasumsikan ada dan berisi nilai numerik (meskipun disimpan sebagai string).
	 *
	 * @return array Array of objects/arrays yang berisi nama_produk dan stok.
	 */
	public function dataStok()
	{
		return $this->builder()
			->select('nama_produk, stok')
			->orderBy('stok', 'DESC')
			->limit(50)
			->get()
			->getResultArray();
	}
	
	public function getProdukByBarcode(string $barcodeString): ?array
	{
		return $this->builder()
			->select('id, barcode, nama_produk, harga, stok, kategori, satuan') // Ambil semua kolom yang relevan
			->where('barcode', $barcodeString)
			->get()
			->getRowArray();
	}
}
