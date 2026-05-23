<?php

namespace App\Validation;

use App\Models\NominalModel;

class Nominal
{
    protected $nominalModel;

    public function __construct()
    {
        $this->nominalModel = new NominalModel();
    }

    /**
     * Validation rules for store (create)
     */
    public function ruleStore()
    {
        return [
            'provider_id' => [
                'rules'  => 'required|is_not_unique[tbl_provider_pulsa.id]',
                'errors' => [
                    'required' => 'Provider harus dipilih',
                    'is_not_unique' => 'Provider tidak valid'
                ]
            ],
            'nominal' => [
                'rules'  => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required' => 'Nominal harus diisi',
                    'numeric' => 'Nominal harus berupa angka',
                    'greater_than' => 'Nominal harus lebih dari 0'
                ]
            ],
            'harga_jual' => [
                'rules'  => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required' => 'Harga jual harus diisi',
                    'numeric' => 'Harga jual harus berupa angka',
                    'greater_than' => 'Harga jual harus lebih dari 0'
                ]
            ],
            'harga_modal' => [
                'rules'  => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required' => 'Harga modal harus diisi',
                    'numeric' => 'Harga modal harus berupa angka',
                    'greater_than' => 'Harga modal harus lebih dari 0'
                ]
            ],
            'status' => [
                'rules'  => 'required|in_list[active,inactive]',
                'errors' => [
                    'required' => 'Status harus dipilih',
                    'in_list'  => 'Status harus Active atau Inactive'
                ]
            ]
        ];
    }

    /**
     * Validation rules for update
     */
    public function ruleUpdate($id)
    {
        return [
            'provider_id' => [
                'rules'  => 'required|is_not_unique[tbl_provider_pulsa.id]',
                'errors' => [
                    'required' => 'Provider harus dipilih',
                    'is_not_unique' => 'Provider tidak valid'
                ]
            ],
            'nominal' => [
                'rules'  => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required' => 'Nominal harus diisi',
                    'numeric' => 'Nominal harus berupa angka',
                    'greater_than' => 'Nominal harus lebih dari 0'
                ]
            ],
            'harga_jual' => [
                'rules'  => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required' => 'Harga jual harus diisi',
                    'numeric' => 'Harga jual harus berupa angka',
                    'greater_than' => 'Harga jual harus lebih dari 0'
                ]
            ],
            'harga_modal' => [
                'rules'  => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required' => 'Harga modal harus diisi',
                    'numeric' => 'Harga modal harus berupa angka',
                    'greater_than' => 'Harga modal harus lebih dari 0'
                ]
            ],
            'status' => [
                'rules'  => 'required|in_list[active,inactive]',
                'errors' => [
                    'required' => 'Status harus dipilih',
                    'in_list'  => 'Status harus Active atau Inactive'
                ]
            ]
        ];
    }
}