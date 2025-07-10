<?php

namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    protected $userIdField = 'user_id'; // Field untuk user isolation
    
    /**
     * Override find() untuk otomatis filter berdasarkan user_id
     */
    public function find($id = null)
    {
        if ($this->hasUserIsolation()) {
            $this->where($this->userIdField, $this->getCurrentUserId());
        }
        
        return parent::find($id);
    }
    
    /**
     * Override findAll() untuk otomatis filter berdasarkan user_id
     */
    public function findAll($limit = 0, $offset = 0)
    {
        if ($this->hasUserIsolation()) {
            $this->where($this->userIdField, $this->getCurrentUserId());
        }
        
        return parent::findAll($limit, $offset);
    }
    
    /**
     * Override insert() untuk otomatis menambahkan user_id
     */
    public function insert($data = null, bool $returnID = true)
    {
        if ($this->hasUserIsolation() && is_array($data)) {
            if (!isset($data[$this->userIdField])) {
                $data[$this->userIdField] = $this->getCurrentUserId();
            }
        }
        
        return parent::insert($data, $returnID);
    }
    
    /**
     * Override update() untuk memastikan hanya update data milik user
     */
    public function update($id = null, $data = null): bool
    {
        if ($this->hasUserIsolation()) {
            // Pastikan data yang diupdate adalah milik user yang login
            $this->where($this->userIdField, $this->getCurrentUserId());
        }
        
        return parent::update($id, $data);
    }
    
    /**
     * Override delete() untuk memastikan hanya delete data milik user
     */
    public function delete($id = null, bool $purge = false)
    {
        if ($this->hasUserIsolation()) {
            // Pastikan data yang dihapus adalah milik user yang login
            $this->where($this->userIdField, $this->getCurrentUserId());
        }
        
        return parent::delete($id, $purge);
    }
    
    /**
     * Method untuk mengambil data dengan filter user_id
     */
    public function getUserData(int $limit = 0, int $offset = 0): array
    {
        if ($this->hasUserIsolation()) {
            $this->where($this->userIdField, $this->getCurrentUserId());
        }
        
        return $this->findAll($limit, $offset);
    }
    
    /**
     * Method untuk mengambil data dengan pagination dan filter user_id
     */
    public function getUserDataPaginated($perPage = 10, $page = 1)
    {
        if ($this->hasUserIsolation()) {
            $this->where($this->userIdField, $this->getCurrentUserId());
        }
        
        return $this->paginate($perPage, 'default', $page);
    }
    
    /**
     * Method untuk pencarian dengan filter user_id
     */
    public function searchUserData($field, $value, $limit = 0): array
    {
        if ($this->hasUserIsolation()) {
            $this->where($this->userIdField, $this->getCurrentUserId());
        }
        
        $this->like($field, $value);
        
        return $this->findAll($limit);
    }
    
    /**
     * Method untuk mengecek apakah model menggunakan user isolation
     */
    protected function hasUserIsolation(): bool
    {
        // Cek apakah tabel memiliki kolom user_id dan user sedang login
        return in_array($this->userIdField, $this->allowedFields) && $this->getCurrentUserId() !== null;
    }
    
    /**
     * Method untuk mendapatkan user_id dari session
     */
    protected function getCurrentUserId(): ?int
    {
        $session = session();
        return $session->get('id');
    }
    
    /**
     * Method untuk mengambil data tanpa filter user (khusus untuk admin tertentu)
     */
    public function getAllDataWithoutFilter(): array
    {
        // Reset any existing where conditions
        $this->builder()->resetQuery();
        return parent::findAll();
    }
    
    /**
     * Method untuk mengambil data berdasarkan user_id tertentu (untuk admin)
     */
    public function getDataByUserId($userId): array
    {
        $this->where($this->userIdField, $userId);
        return $this->findAll();
    }
    
    /**
     * Method untuk count data milik user
     */
    public function countUserData(): int
    {
        if ($this->hasUserIsolation()) {
            $this->where($this->userIdField, $this->getCurrentUserId());
        }
        
        return $this->countAllResults();
    }
}