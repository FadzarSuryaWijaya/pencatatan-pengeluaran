<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InsertUserId extends Migration
{
    public function up()
    {
        $tables = [
            'produk', 'kategori_produk', 'satuan_produk', 'supplier', 
            'pelanggan', 'transaksi', 'stok_masuk', 'stok_keluar', 'toko'
        ];

        foreach ($tables as $table) {
            // Cek apakah tabel ada
            if (!$this->db->tableExists($table)) {
                continue;
            }

            // Cek apakah kolom user_id sudah ada
            $fields = $this->db->getFieldData($table);
            $userIdExists = false;
            
            foreach ($fields as $field) {
                if ($field->name === 'user_id') {
                    $userIdExists = true;
                    break;
                }
            }

            // Tambahkan kolom user_id jika belum ada
            if (!$userIdExists) {
                $this->forge->addColumn($table, [
                    'user_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => false,
                        'after' => 'id'
                    ]
                ]);

                // Tambahkan index
                $this->db->query("ALTER TABLE `{$table}` ADD INDEX `idx_{$table}_user_id` (`user_id`)");
            }
        }

        // Tambahkan foreign key constraints jika tabel pengguna ada
        if ($this->db->tableExists('pengguna')) {
            $fkNames = [
                'produk' => 'fk_produk_user',
                'kategori_produk' => 'fk_kategori_produk_user',
                'satuan_produk' => 'fk_satuan_produk_user',
                'supplier' => 'fk_supplier_user',
                'pelanggan' => 'fk_pelanggan_user',
                'transaksi' => 'fk_transaksi_user',
                'stok_masuk' => 'fk_stok_masuk_user',
                'stok_keluar' => 'fk_stok_keluar_user',
                'toko' => 'fk_toko_user'
            ];

            foreach ($tables as $table) {
                if (!$this->db->tableExists($table)) {
                    continue;
                }

                // Cek apakah kolom user_id ada di tabel
                $fields = $this->db->getFieldData($table);
                $userIdExists = false;
                
                foreach ($fields as $field) {
                    if ($field->name === 'user_id') {
                        $userIdExists = true;
                        break;
                    }
                }

                if ($userIdExists) {
                    try {
                        // Tambahkan foreign key constraint
                        $this->db->query("ALTER TABLE `{$table}` ADD CONSTRAINT `{$fkNames[$table]}` FOREIGN KEY (`user_id`) REFERENCES `pengguna`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
                    } catch (\Exception $e) {
                        // Foreign key mungkin sudah ada, lanjutkan
                        continue;
                    }
                }
            }
        }
    }

    public function down()
    {
        $tables = [
            'produk', 'kategori_produk', 'satuan_produk', 'supplier', 
            'pelanggan', 'transaksi', 'stok_masuk', 'stok_keluar', 'toko'
        ];

        $fkNames = [
            'produk' => 'fk_produk_user',
            'kategori_produk' => 'fk_kategori_produk_user',
            'satuan_produk' => 'fk_satuan_produk_user',
            'supplier' => 'fk_supplier_user',
            'pelanggan' => 'fk_pelanggan_user',
            'transaksi' => 'fk_transaksi_user',
            'stok_masuk' => 'fk_stok_masuk_user',
            'stok_keluar' => 'fk_stok_keluar_user',
            'toko' => 'fk_toko_user'
        ];

        // Hapus foreign key constraints terlebih dahulu
        foreach ($tables as $table) {
            if (!$this->db->tableExists($table)) {
                continue;
            }

            try {
                $this->db->query("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkNames[$table]}`");
            } catch (\Exception $e) {
                // Foreign key mungkin tidak ada, lanjutkan
                continue;
            }
        }

        // Hapus kolom user_id dari semua tabel
        foreach ($tables as $table) {
            if (!$this->db->tableExists($table)) {
                continue;
            }

            // Cek apakah kolom user_id ada
            $fields = $this->db->getFieldData($table);
            $userIdExists = false;
            
            foreach ($fields as $field) {
                if ($field->name === 'user_id') {
                    $userIdExists = true;
                    break;
                }
            }

            if ($userIdExists) {
                $this->forge->dropColumn($table, 'user_id');
            }
        }
    }
}