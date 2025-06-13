<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Transaksi_model; // Pastikan Anda mengimpor model Transaksi_model
use App\Models\Produk_model;    // Pastikan Anda mengimpor model Produk_model (digunakan untuk stok dan info produk)
use CodeIgniter\HTTP\ResponseInterface; // Untuk return type hint
use CodeIgniter\HTTP\RedirectResponse; // Untuk return type hint Redirect

class Transaksi extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all methods within the controller.
     *
     * @var array
     */
    protected $helpers = ['session', 'url', 'date']; // Memuat helper 'session', 'url', dan 'date'

    protected Transaksi_model $transaksiModel; // Deklarasikan properti untuk Transaksi_model
    protected Produk_model $produkModel;      // Deklarasikan properti untuk Produk_model

    /**
     * Constructor untuk menginisialisasi controller.
     * Inisialisasi model dilakukan di sini.
     */
    public function __construct()
    {
        $this->transaksiModel = new Transaksi_model(); // Inisialisasi Transaksi_model
        $this->produkModel    = new Produk_model();    // Inisialisasi Produk_model
    }

    /**
     * Metode initController digunakan untuk inisialisasi awal controller.
     * Ini adalah tempat yang tepat untuk melakukan pengecekan autentikasi/login.
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request
     * @param \CodeIgniter\HTTP\ResponseInterface $response
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        // Jangan edit baris ini
        parent::initController($request, $response, $logger);

        // Ambil instance layanan sesi
        $session = session();

        // Periksa status login pengguna. Jika tidak 'login', arahkan kembali ke halaman utama.
        if ($session->get('status') !== 'login') {
            return redirect()->to('/'); // Ubah ini ke URL halaman login atau halaman utama Anda jika diperlukan
        }
    }

    /**
     * Metode index untuk menampilkan halaman utama transaksi.
     *
     * @return string
     */
    public function index(): string
    {
        // Memuat view 'transaksi'.
        // Di CodeIgniter 4, Anda cukup menggunakan fungsi helper view() untuk memuat view.
        return view('transaksi');
    }

    /**
     * Membaca data transaksi untuk ditampilkan di tabel (misalnya DataTables).
     * Mengembalikan data dalam format JSON.
     *
     * @return ResponseInterface
     */
    public function read(): ResponseInterface
    {
        $data = []; // Inisialisasi array data
        
        // Panggil metode readTransaksi() dari model
        $transaksiList = $this->transaksiModel->readTransaksi();

        if (!empty($transaksiList)) {
            foreach ($transaksiList as $transaksi) {
                // Pastikan kolom 'barcode' dan 'qty' ada dan formatnya benar sebagai string koma-terpisah
                $barcodeIds = explode(',', $transaksi['barcode']);
                $qtysString = $transaksi['qty']; // Pass the original comma-separated string to the model

                // Dapatkan nama produk dan harga untuk ditampilkan dalam tabel
                // Asumsi Transaksi_model memiliki metode getProdukTransaksiDetail yang mengambil array ID dan Qty string
                $produkDetails = $this->transaksiModel->getProdukTransaksiDetail($barcodeIds, $qtysString);
                
                $namaProdukHtml = '<table>';
                if (!empty($produkDetails)) {
                    foreach ($produkDetails as $detail) {
                        $namaProdukHtml .= '<tr><td>' . esc($detail['nama_produk']) . '</td><td>' . esc($detail['qty']) . '</td></tr>';
                    }
                }
                $namaProdukHtml .= '</table>';
                
                // Tanggal parsing yang lebih robust
                try {
                    $tanggal = new \DateTime($transaksi['tanggal']); 
                    $formattedTanggal = $tanggal->format('d-m-Y H:i:s');
                } catch (\Exception $e) {
                    // Log the error or set a default value if date parsing fails
                    log_message('error', 'Failed to parse date for transaction ID ' . $transaksi['id'] . ': ' . $transaksi['tanggal'] . ' - ' . $e->getMessage());
                    $formattedTanggal = 'Invalid Date';
                }

                $data[] = [
                    'tanggal'     => $formattedTanggal,
                    'nama_produk' => $namaProdukHtml,
                    'total_bayar' => 'Rp. ' . number_format($transaksi['total_bayar'], 0, ',', '.'),
                    'jumlah_uang' => 'Rp. ' . number_format($transaksi['jumlah_uang'], 0, ',', '.'),
                    'diskon'      => 'Rp. ' . number_format($transaksi['diskon'], 0, ',', '.'),
                    'pelanggan'   => esc($transaksi['pelanggan_nama'] ?? 'Umum'), // Gunakan pelanggan_nama dari join, default to 'Umum'
                    'action'      => '<a class="btn btn-sm btn-success" href="' . url_to('Transaksi::cetak', $transaksi['id']) . '">Print</a> <button class="btn btn-sm btn-danger" onclick="remove(' . esc($transaksi['id']) . ')">Delete</button>'
                ];
            }
        }

        $response = [
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    /**
     * Menambahkan transaksi baru, mengurangi stok produk, dan menambahkan data terjual.
     *
     * @return ResponseInterface
     */
    public function add(): ResponseInterface
    {
        // Mendapatkan data produk dari input POST (diasumsikan sebagai JSON string)
        $produkArray = json_decode($this->request->getPost('produk'), true); // true untuk array asosiatif

        // Mendapatkan data lain dari input POST
        $tanggalInput = $this->request->getPost('tanggal');
        $qtyArray     = $this->request->getPost('qty'); // array qty per produk

        $barcodeIds = []; // Untuk menyimpan ID barcode produk yang terlibat dalam transaksi

        // Iterasi melalui setiap produk dalam transaksi untuk memperbarui stok dan data terjual
        if (!empty($produkArray)) {
            foreach ($produkArray as $produk) {
                // Pastikan ID produk, stok, dan terjual ada dan valid
                $productId = $produk['id'] ?? null;
                // 'stok' di sini adalah stok yang akan dikurangi (ini adalah jumlah produk yang terjual)
                $stokToBeRemoved = $produk['qty'] ?? 0; // Menggunakan 'qty' dari produkArray untuk stok yang dikurangi
                $terjualToAdd = $produk['qty'] ?? 0; // 'terjual' di sini adalah jumlah produk yang terjual

                if ($productId) {
                    // Mengurangi stok produk menggunakan metode dari Transaksi_model
                    $this->transaksiModel->removeStokProduk($productId, $stokToBeRemoved);
                    
                    // Menambahkan jumlah terjual produk menggunakan metode dari Transaksi_model
                    $this->transaksiModel->addTerjualProduk($productId, $terjualToAdd);
                    
                    // Tambahkan ID produk ke array barcode untuk disimpan di transaksi
                    $barcodeIds[] = $productId;
                }
            }
        }

        // Format tanggal untuk disimpan di database
        $tanggal = new \DateTime($tanggalInput ?: 'now'); // Gunakan tanggal input atau waktu sekarang jika kosong

        // Data untuk disimpan di tabel transaksi
        $dataTransaksi = [
            'tanggal'     => $tanggal->format('Y-m-d H:i:s'),
            'barcode'     => implode(',', $barcodeIds),      // ID barcode produk yang terlibat
            'qty'         => implode(',', $qtyArray),        // Jumlah (qty) masing-masing produk
            'total_bayar' => $this->request->getPost('total_bayar'),
            'jumlah_uang' => $this->request->getPost('jumlah_uang'),
            'diskon'      => $this->request->getPost('diskon'),
            'pelanggan'   => $this->request->getPost('pelanggan'),
            'nota'        => $this->request->getPost('nota'),
            'kasir'       => session()->get('id') // Mengambil ID kasir dari sesi
        ];

        // Simpan data transaksi ke database
        if ($this->transaksiModel->createTransaksi($dataTransaksi)) {
            // Mengembalikan ID transaksi yang baru saja di-insert
            return $this->response->setJSON($this->transaksiModel->db->insertID());
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal mencatat transaksi.']);
    }

    /**
     * Menghapus data transaksi.
     *
     * @return ResponseInterface
     */
    public function delete(): ResponseInterface
    {
        $id = $this->request->getPost('id');

        if ($this->transaksiModel->deleteTransaksi($id)) {
            return $this->response->setJSON(['status' => 'sukses']);
        }

        return $this->response->setJSON(['status' => 'gagal', 'message' => 'Gagal menghapus transaksi.']);
    }

    /**
     * Mencetak detail transaksi berdasarkan ID.
     *
     * @param int $id ID transaksi
     * @return string
     */
    public function cetak(int $id): string
    {
            $session = \Config\Services::session(); // Tambahkan baris ini
            $tokoData = $session->get('toko'); // Ambil data toko dari session

        // Mendapatkan detail transaksi dari model
        // Menggunakan getDetailTransaksi yang melakukan join untuk nama kasir dan pelanggan
        $transaksi = $this->transaksiModel->getDetailTransaksi($id);
        
        if (empty($transaksi)) {
            // Handle jika transaksi tidak ditemukan
            return view('errors/html/error_404', ['message' => 'Transaksi tidak ditemukan.']);
        }

        // Memecah string barcode dan qty menjadi array
        $barcodeIds = explode(',', $transaksi['barcode']);
        $qtys       = explode(',', $transaksi['qty']);

        // Mengubah format tanggal untuk tampilan
        try {
            $tanggalObj = new \DateTime($transaksi['tanggal']);
            $transaksi['tanggal'] = $tanggalObj->format('d m Y H:i:s');
        } catch (\Exception $e) {
            log_message('error', 'Failed to parse date for print transaction ID ' . $transaksi['id'] . ': ' . $transaksi['tanggal'] . ' - ' . $e->getMessage());
            $transaksi['tanggal'] = 'Invalid Date';
        }

        // Mendapatkan nama produk dan detail harga dari Transaksi_model
        $dataProdukRaw = $this->transaksiModel->getProdukNamesAndPrices($barcodeIds); 

        $dataProduk = [];
        $produkMap = [];
        foreach ($dataProdukRaw as $p) {
            $produkMap[$p['id']] = $p;
        }

        foreach ($barcodeIds as $key => $productId) {
            $currentQty = (int)($qtys[$key] ?? 0);
            $productInfo = $produkMap[$productId] ?? ['nama_produk' => 'Produk Tidak Ditemukan', 'harga' => 0];
            
            $dataProduk[] = [
                'nama_produk' => esc($productInfo['nama_produk']),
                'total_qty'   => $currentQty, 
                'harga_satuan'=> (float)($productInfo['harga'] ?? 0), 
                'total_harga' => (float)($productInfo['harga'] ?? 0) * $currentQty 
            ];
        }

        // Data yang akan dikirim ke view cetak
        $data = [
            'nota'      => esc($transaksi['nota']),
            'tanggal'   => esc($transaksi['tanggal']),
            'produk'    => $dataProduk, // Array detail produk dengan total dan harga yang sudah dihitung
            'total'     => esc(number_format($transaksi['total_bayar'], 0, ',', '.')), // Sudah diformat
            'bayar'     => esc(number_format($transaksi['jumlah_uang'], 0, ',', '.')), // Sudah diformat
            'kembalian' => esc(number_format((float)$transaksi['jumlah_uang'] - (float)$transaksi['total_bayar'], 0, ',', '.')), // Sudah diformat
            'kasir'     => esc($transaksi['kasir_nama'] ?? 'N/A'), //Menggunakan 'kasir_nama' dari join
            'toko'      => $tokoData // TERUSKAN DATA TOKO KE VIEW
        ];

        // Memuat view 'cetak' dengan data
        return view('cetak', $data);
    }

    /**
     * Menghitung penjualan per bulan (berdasarkan hari-hari dalam sebulan).
     *
     * @return ResponseInterface
     */
    public function penjualan_bulan(): ResponseInterface
    {
        $dayArray = $this->request->getPost('day'); // Array hari-hari (misalnya [1, 2, ..., 31])
        $data = [];

        if (is_array($dayArray)) {
            foreach ($dayArray as $day) {
                // Pastikan $day adalah integer atau string representasi hari yang valid
                $currentMonth = date('m');
                $currentYear  = date('Y');
                // Format tanggal agar sesuai dengan ekspektasi model: 'DD MM YYYY'
                $dateString   = sprintf('%02d %02d %04d', (int)$day, (int)$currentMonth, (int)$currentYear);
                
                // Panggil metode penjualanBulan() dari model
                $qtyResult = $this->transaksiModel->penjualanBulan($dateString);

                // Asumsi penjualanBulan mengembalikan array total qty atau angka tunggal
                if (!empty($qtyResult) && is_array($qtyResult)) {
                    $data[] = array_sum($qtyResult);
                } elseif (is_numeric($qtyResult)) {
                    $data[] = (float)$qtyResult;
                } else {
                    $data[] = 0; // Jika tidak ada penjualan atau hasil kosong
                }
            }
        }

        return $this->response->setJSON($data);
    }

    /**
     * Mengambil total transaksi untuk hari ini.
     *
     * @return ResponseInterface
     */
    public function transaksi_hari(): ResponseInterface
    {
        $now = date('d m Y'); // Contoh: '28 05 2025'
        
        // Panggil metode transaksiHari() dari model
        $total = $this->transaksiModel->transaksiHari($now);

        // Diasumsikan $total langsung berisi nilai yang diinginkan atau null
        return $this->response->setJSON($total ?? 0); // Mengembalikan 0 jika total adalah null
    }

    /**
     * Mengambil data transaksi terakhir untuk hari ini.
     *
     * @return ResponseInterface
     */
    public function transaksi_terakhir(): ResponseInterface
    {
        $now = date('d m Y');
        $total = []; // Inisialisasi array untuk menampung hasil explode

        // Panggil metode transaksiTerakhir() dari model
        // Asumsi transaksiTerakhir mengembalikan sebuah baris array dengan kunci 'qty'
        $resultRow = $this->transaksiModel->transaksiTerakhir($now);

        if (!empty($resultRow) && isset($resultRow['qty'])) {
            $total = explode(',', $resultRow['qty']);
        }

        return $this->response->setJSON($total);
    }
    
    public function get_barcode(): ResponseInterface
{
    // Ambil parameter 'barcode' dari POST request
    // Gunakan null coalescing operator (?? '') untuk memastikan selalu string
    $searchTerm = $this->request->getPost('barcode') ?? ''; 
    
    // Panggil metode model
    $searchResults = $this->produkModel->getBarcode($searchTerm); 

    $data = [];
    if (!empty($searchResults)) {
        foreach ($searchResults as $row) {
            $data[] = [
                'id'   => esc($row['id']),
                'text' => esc($row['barcode']) . ' - ' . esc($row['nama_produk']) // Pastikan 'nama_produk' juga diambil di model
            ];
        }
    }

    // Penting: Select2 versi terbaru mengharapkan hasil dalam kunci 'results'
        return $this->response->setJSON($data);
}

}