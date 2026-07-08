<?php

namespace App\Services;

use App\Models\NominalModel;
use App\Models\ProviderModel;
use App\Validation\Nominal as NominalValidation;

class NominalService
{
    protected $nominalModel;
    protected $providerModel;
    protected $nominalValidation;

    public function __construct()
    {
        $this->nominalModel = new NominalModel();
        $this->providerModel = new ProviderModel();
        $this->nominalValidation = new NominalValidation();
    }

    /**
     * Get all nominals dengan filter (TANPA JOIN PROVIDER)
     */
    public function getAll($filters = [])
    {
        $builder = $this->nominalModel;

        if (!empty($filters['search'])) {
            $builder->like('nominal', $filters['search']);
        }

        if (!empty($filters['provider_id'])) {
            $builder->where('provider_id', $filters['provider_id']);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $builder->where('status', $filters['status']);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Get nominal by ID with provider data
     */
    public function find($id)
    {
        return $this->nominalModel->findWithProvider($id);
    }

    /**
     * Get nominal by ID (basic, tanpa join)
     */
    public function findBasic($id)
    {
        return $this->nominalModel->find($id);
    }

    /**
     * Get all active providers
     */
    public function getActiveProviders()
    {
        return $this->providerModel->where('status', 'active')->findAll();
    }

    /**
     * Create new nominal
     */
    public function create($data)
    {
        try {
            if (!$this->providerModel->find($data['provider_id'])) {
                throw new \Exception('Provider tidak valid');
            }

            $insertData = [
                'provider_id' => (int) $data['provider_id'],
                'nominal'     => (int) $data['nominal'],
                'harga_modal' => (int) $data['harga_modal'],
                'harga_jual'  => (int) $data['harga_jual'],
                'status'      => $data['status']
            ];

            $result = $this->nominalModel->insert($insertData);

            if ($result) {
                return $result;
            } else {
                $errors = $this->nominalModel->errors();
                throw new \Exception('Gagal menyimpan data: ' . implode(', ', $errors));
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Update nominal
     */
    public function update($id, $data)
    {
        try {
            if (!$this->providerModel->find($data['provider_id'])) {
                throw new \Exception('Provider tidak valid');
            }

            $updateData = [
                'provider_id' => (int) $data['provider_id'],
                'nominal'     => (int) $data['nominal'],
                'harga_modal' => (int) $data['harga_modal'],
                'harga_jual'  => (int) $data['harga_jual'],
                'status'      => $data['status']
            ];

            $result = $this->nominalModel->update($id, $updateData);

            if ($result) {
                return true;
            } else {
                $errors = $this->nominalModel->errors();
                throw new \Exception('Gagal mengupdate data: ' . implode(', ', $errors));
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Delete nominal (HARD DELETE)
     */
    public function deleteData($id)
    {
        try {
            if (!$this->nominalModel->find($id)) {
                return [
                    'success' => false,
                    'message' => 'Data nominal tidak ditemukan',
                    'code'    => 404,
                ];
            }

            $result = $this->nominalModel->delete($id);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Data nominal berhasil dihapus',
                    'code'    => 200,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal menghapus data nominal',
                    'code'    => 500,
                ];
            }
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat menghapus nominal karena masih terkait dengan data lain',
                    'code'    => 409,
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal menghapus data nominal: ' . $e->getMessage(),
                'code'    => 500,
            ];
        }
    }

    /**
     * Validate nominal data for CREATE
     */
    public function validateCreate($data)
    {
        $validation = \Config\Services::validation();
        $validation->setRules($this->nominalValidation->ruleStore());

        if (!$validation->run($data)) {
            return $validation->getErrors();
        }

        return [];
    }

    /**
     * Validate nominal data for UPDATE
     */
    public function validateUpdate($data, $id)
    {
        $validation = \Config\Services::validation();
        $validation->setRules($this->nominalValidation->ruleUpdate($id));

        if (!$validation->run($data)) {
            return $validation->getErrors();
        }

        return [];
    }

    /**
     * Get nominal statistics
     */
    public function getStats()
    {
        return [
            'total'    => $this->nominalModel->countAll(),
            'active'   => $this->nominalModel->where('status', 'active')->countAllResults(),
            'inactive' => $this->nominalModel->where('status', 'inactive')->countAllResults()
        ];
    }
}