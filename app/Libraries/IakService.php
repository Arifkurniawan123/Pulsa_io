<?php

namespace App\Libraries;

use Config\Iak;

class IakService
{
    protected $config;

    public function __construct()
    {
        $this->config = new Iak();
    }

    public function cekSaldo()
    {
        $url = $this->config->base_url . '/v1/balance';

        $signature = md5($this->config->api_key . $this->config->secret);
        $headers = [
            'Content-Type: application/json',
        ];

        $data = [
            'key' => $this->config->api_key,
            'sign' => $signature
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
