<?php

namespace App\Models;

use CodeIgniter\Model;

class ProviderModel extends Model
{
    protected $table            = 'tbl_provider_pulsa';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // FALSE untuk HARD DELETE
    protected $protectFields    = true;
    protected $allowedFields    = ['nama_provider', 'kode_provider', 'status'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // Tidak ada deletedField karena hard delete
}