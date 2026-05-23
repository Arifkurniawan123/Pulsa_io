<?php

namespace App\Validation;

use Config\Services;

class KasirValidation
{
    /**
     * Validation rules untuk tambah produk ke keranjang (fisik dan digital)
     */
    public function rulesTambahProduk(): array
    {
        return [
            'jenis_produk' => [
                'rules' => 'required|in_list[fisik,digital]',
                'errors' => [
                    'required' => 'Jenis produk wajib dipilih',
                    'in_list' => 'Jenis produk harus Fisik atau Digital'
                ]
            ],
            // Field untuk produk fisik
            'produk_id' => [
                'rules' => 'permit_empty',
                'errors' => [
                    // Hapus validasi integer karena ID produk bisa string
                ]
            ],
            'jumlah' => [
                'rules' => 'permit_empty|integer|greater_than[0]',
                'errors' => [
                    'integer' => 'Jumlah harus berupa angka',
                    'greater_than' => 'Jumlah harus lebih dari 0'
                ]
            ],
            // Field untuk produk digital (pulsa)
            'no_tujuan_pulsa' => [
                'rules' => 'permit_empty|numeric|min_length[10]|max_length[15]',
                'errors' => [
                    'numeric' => 'Nomor tujuan harus berupa angka',
                    'min_length' => 'Nomor tujuan minimal 10 digit',
                    'max_length' => 'Nomor tujuan maksimal 15 digit'
                ]
            ],
            'operator_id' => [
                'rules' => 'permit_empty',
                'errors' => [
                    // Hapus validasi integer
                ]
            ],
            'provider_id' => [
                'rules' => 'permit_empty',
                'errors' => [
                    // Hapus validasi integer
                ]
            ],
            'nominal_id' => [
                'rules' => 'permit_empty',
                'errors' => [
                    // Hapus validasi integer
                ]
            ],
            'metode_pembayaran_pulsa' => [
                'rules' => 'permit_empty|in_list[tunai,saldo,transfer,grip]',
                'errors' => [
                    'in_list' => 'Metode pembayaran harus salah dari: tunai, saldo, transfer, grip'
                ]
            ]
        ];
    }

    /**
     * Custom validation untuk produk fisik
     */
    public function validateProdukFisik(array $data): bool
    {
        $validation = Services::validation();
        
        $rules = [
            'produk_id' => 'required',
            'jumlah' => 'required|integer|greater_than[0]'
        ];

        $messages = [
            'produk_id' => [
                'required' => 'Produk wajib dipilih untuk produk fisik'
            ],
            'jumlah' => [
                'required' => 'Jumlah wajib diisi untuk produk fisik',
                'integer' => 'Jumlah harus berupa angka',
                'greater_than' => 'Jumlah harus lebih dari 0'
            ]
        ];

        $validation->setRules($rules, $messages);
        return $validation->run($data);
    }

    /**
     * Custom validation untuk produk digital
     */
    public function validateProdukDigital(array $data): bool
    {
        $validation = Services::validation();
        
        $rules = [
            'no_tujuan_pulsa' => 'required|numeric|min_length[10]|max_length[15]',
            'operator_id' => 'required',
            'provider_id' => 'required',
            'nominal_id' => 'required',
            'metode_pembayaran_pulsa' => 'required|in_list[tunai,saldo,transfer,grip]'
        ];

        $messages = [
            'no_tujuan_pulsa' => [
                'required' => 'Nomor tujuan wajib diisi untuk produk digital',
                'numeric' => 'Nomor tujuan harus berupa angka',
                'min_length' => 'Nomor tujuan minimal 10 digit',
                'max_length' => 'Nomor tujuan maksimal 15 digit'
            ],
            'provider_id' => [
                'required' => 'Provider wajib dipilih untuk produk digital'
            ],
            'nominal_id' => [
                'required' => 'Nominal pulsa wajib dipilih untuk produk digital'
            ],
            'metode_pembayaran_pulsa' => [
                'required' => 'Metode pembayaran wajib dipilih untuk produk digital',
                'in_list' => 'Metode pembayaran harus salah dari: tunai, saldo, transfer, grip'
            ]
        ];

        $validation->setRules($rules, $messages);
        return $validation->run($data);
    }

    /**
     * Run validation berdasarkan jenis produk
     */
    public function validateByJenis(array $data): bool
    {
        $jenis = $data['jenis_produk'] ?? '';
        
        // Validasi dasar
        $validation = Services::validation();
        $validation->setRules($this->rulesTambahProduk());
        
        if (!$validation->run($data)) {
            return false;
        }

        // Validasi spesifik berdasarkan jenis
        if ($jenis === 'fisik') {
            return $this->validateProdukFisik($data);
        } elseif ($jenis === 'digital') {
            return $this->validateProdukDigital($data);
        }

        return true;
    }

    /**
     * Validation rules untuk checkout
     */
    public function rulesCheckout(): array
    {
        return [
            'ppn_percent' => [
                'rules' => 'permit_empty|decimal',
                'errors' => [
                    'decimal' => 'PPN harus berupa angka'
                ]
            ],
            'diskon' => [
                'rules' => 'permit_empty|decimal',
                'errors' => [
                    'decimal' => 'Diskon harus berupa angka'
                ]
            ],
            'metode_pembayaran' => [
                'rules' => 'required|in_list[cash,transfer]',
                'errors' => [
                    'required' => 'Metode pembayaran wajib dipilih',
                    'in_list' => 'Metode pembayaran harus Cash atau Transfer'
                ]
            ]
        ];
    }

    /**
     * Get errors dari validation
     */
    public function getErrors(): array
    {
        $validation = Services::validation();
        return $validation->getErrors();
    }
}