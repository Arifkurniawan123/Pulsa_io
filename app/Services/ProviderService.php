<?php

namespace App\Services;

use App\Models\ProviderModel;
use App\Validation\Provider as ProviderValidation;

class ProviderService
{
    protected $providerModel;
    protected $providerValidation;

    public function __construct()
    {
        $this->providerModel = new ProviderModel();
        $this->providerValidation = new ProviderValidation();
    }

    /**
     * Get all providers with filters
     */
    public function getAll($filters = [])
    {
        $builder = $this->providerModel;

        if (!empty($filters['search'])) {
            $builder->groupStart()
                   ->like('nama_provider', $filters['search'])
                   ->orLike('kode_provider', $filters['search'])
                   ->groupEnd();
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $builder->where('status', $filters['status']);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Get provider by ID
     */
    public function find($id)
    {
        return $this->providerModel->find($id);
    }

    /**
     * Create new provider
     */
    public function create($data)
    {
        $data['kode_provider'] = strtoupper($data['kode_provider']);
        
        // Check if kode provider already exists
        if ($this->isKodeProviderExists($data['kode_provider'])) {
            throw new \Exception('Kode provider sudah digunakan');
        }
        
        return $this->providerModel->insert($data);
    }

    /**
     * Update provider
     */
    public function update($id, $data)
    {
        $data['kode_provider'] = strtoupper($data['kode_provider']);
        
        // Check if kode provider already exists (excluding current id)
        if (!$this->isKodeProviderUnique($data['kode_provider'], $id)) {
            throw new \Exception('Kode provider sudah digunakan');
        }
        
        return $this->providerModel->update($id, $data);
    }

    /**
     * Delete provider (HARD DELETE) - Mengembalikan array response
     */
    public function deleteData($id)
    {
        try {
            // Cek apakah provider exists
            if (!$this->providerModel->find($id)) {
                return [
                    'success' => false,
                    'message' => 'Provider tidak ditemukan',
                    'code'    => 404,
                ];
            }

            // HARD DELETE (permanen dari database)
            $this->providerModel->delete($id);

            return [
                'success' => true,
                'message' => 'Provider berhasil dihapus',
                'code'    => 200,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menghapus provider: ' . $e->getMessage(),
                'code'    => 500,
            ];
        }
    }

    /**
     * Validate provider data for CREATE menggunakan Validation Class
     */
    public function validateCreate($data)
    {
        $validation = \Config\Services::validation();
        $validation->setRules($this->providerValidation->ruleStore());

        if (!$validation->run($data)) {
            return $validation->getErrors();
        }

        return [];
    }

    /**
     * Validate provider data for UPDATE menggunakan Validation Class
     */
    public function validateUpdate($data, $id)
    {
        $validation = \Config\Services::validation();
        $validation->setRules($this->providerValidation->ruleUpdate($id));

        if (!$validation->run($data)) {
            return $validation->getErrors();
        }

        return [];
    }

    /**
     * Check if kode provider exists
     */
    public function isKodeProviderExists($kode)
    {
        return $this->providerModel->where('kode_provider', strtoupper($kode))->countAllResults() > 0;
    }

    /**
     * Check if kode provider is unique (for update)
     */
    public function isKodeProviderUnique($kode, $excludeId = null)
    {
        $builder = $this->providerModel->where('kode_provider', strtoupper($kode));
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() === 0;
    }

    /**
     * Get provider statistics
     */
    public function getStats()
    {
        return [
            'total' => $this->providerModel->countAll(),
            'active' => $this->providerModel->where('status', 'active')->countAllResults(),
            'inactive' => $this->providerModel->where('status', 'inactive')->countAllResults()
        ];
    }
}