<?php
namespace App\Controllers;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Pengaturan extends Controller
{
    protected $helpers = ['url', 'session'];
    protected $db;
    
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        if (session()->get('status') !== 'login') {
            return redirect()->to(base_url('/'));
        }
        $this->db = \Config\Database::connect();
    }
    
    public function index()
    {
        $userId = session()->get('id');
        // Ambil data toko berdasarkan user_id
        $toko = $this->db->table('toko')
            ->where('user_id', $userId)
            ->get()
            ->getRow();
        
        // Jika belum ada data, buat record kosong
        if (!$toko) {
            $toko = (object)[
                'nama' => '',
                'alamat' => ''
            ];
        }
        
        $data['toko'] = $toko;
        return view('pengaturan', $data);
    }
    
    public function set_toko()
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(405)->setBody('Method Not Allowed');
        }
        
        $userId = session()->get('id');
        $role = session()->get('role');
        $nama = $this->request->getPost('nama');
        $alamat = $this->request->getPost('alamat');
        
        // Validasi input
        if (empty($alamat)) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Alamat harus diisi'
            ]);
        }
        
        // Jika admin, validasi nama toko
        if ($role === 'admin' && empty($nama)) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Nama toko harus diisi'
            ]);
        }
        
        $data = [
            'alamat' => $alamat,
            'user_id' => $userId
        ];
        
        // Tambahkan nama hanya jika admin
        if ($role === 'admin') {
            $data['nama'] = $nama;
        }
        
        // Cek apakah data sudah ada
        $exists = $this->db->table('toko')
            ->where('user_id', $userId)
            ->countAllResults();
        
        if ($exists) {
            $updated = $this->db->table('toko')
                ->where('user_id', $userId)
                ->update($data);
        } else {
            $updated = $this->db->table('toko')->insert($data);
        }
        
        if ($updated) {
            // Ambil data toko terbaru untuk response
            $tokoTerbaru = $this->db->table('toko')
                ->where('user_id', $userId)
                ->get()
                ->getRow();
            
            $response = [
                'status' => 'success', 
                'message' => 'Data berhasil disimpan'
            ];
            
            // Tambahkan nama toko baru jika admin dan nama berubah
            if ($role === 'admin' && $tokoTerbaru && !empty($tokoTerbaru->nama)) {
                $response['nama_toko_baru'] = $tokoTerbaru->nama;
                
                // Update session toko
                $sessionToko = session()->get('toko') ?: [];
                $sessionToko['nama'] = $tokoTerbaru->nama;
                session()->set('toko', $sessionToko);
            }
            
            return $this->response->setJSON($response);
        }
        
        return $this->response->setJSON([
            'status' => 'error', 
            'message' => 'Gagal menyimpan data'
        ]);
    }
}