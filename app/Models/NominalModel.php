<?php

namespace App\Models;

use CodeIgniter\Model;

class NominalModel extends Model
{
    protected $table            = 'tbl_nominal_pulsa';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'provider_id', 
        'nominal', 
        'harga_modal', 
        'harga_jual', 
        'status'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Join dengan provider
    public function getWithProvider($filters = [])
    {
        $builder = $this->select('tbl_nominal_pulsa.*, tbl_provider_pulsa.nama_provider, tbl_provider_pulsa.kode_provider')
                        ->join('tbl_provider_pulsa', 'tbl_provider_pulsa.id = tbl_nominal_pulsa.provider_id');

        // Apply filters
        if (!empty($filters['search'])) {
            $builder->groupStart()
                   ->like('tbl_provider_pulsa.nama_provider', $filters['search'])
                   ->orLike('tbl_provider_pulsa.kode_provider', $filters['search'])
                   ->orLike('tbl_nominal_pulsa.nominal', $filters['search'])
                   ->groupEnd();
        }

        if (!empty($filters['provider_id']) && $filters['provider_id'] !== 'all') {
            $builder->where('tbl_nominal_pulsa.provider_id', $filters['provider_id']);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $builder->where('tbl_nominal_pulsa.status', $filters['status']);
        }

        return $builder->orderBy('tbl_provider_pulsa.nama_provider', 'ASC')
                      ->orderBy('tbl_nominal_pulsa.nominal', 'ASC')
                      ->findAll();
    }

    public function findWithProvider($id)
    {
        return $this->select('tbl_nominal_pulsa.*, tbl_provider_pulsa.nama_provider, tbl_provider_pulsa.kode_provider')
                    ->join('tbl_provider_pulsa', 'tbl_provider_pulsa.id = tbl_nominal_pulsa.provider_id')
                    ->where('tbl_nominal_pulsa.id', $id)
                    ->first();
    }

    public function findByProvider($provider_id)
    {
        return $this->where('provider_id', $provider_id)->findAll();
    }

    /**
     * Get active nominals by provider ID
     */
    public function getActiveByProvider($providerId)
    {
        return $this->where('provider_id', $providerId)
                    ->where('status', 'active')
                    ->orderBy('nominal', 'ASC')
                    ->findAll();
    }
}