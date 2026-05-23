<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DebugJWT extends BaseController
{
    /**
     * Debug JWT - Lihat secret key dan test encode/decode
     */
    public function index()
    {
        helper('jwt');
        
        // Display current secret key info
        $secretKey = $_ENV['JWT_SECRET_KEY'] ?? getenv('JWT_SECRET_KEY') ?? 'PulsaIo2026AppSecretKeyYangSangatAmanDanPanjang123!@#$';
        $secretKey = trim($secretKey, '"\'');
        
        $info = [
            'jwt_secret_defined' => defined('JWT_SECRET'),
            'jwt_secret_key_trimmed' => $secretKey,
            'jwt_secret_length' => strlen($secretKey),
            'jwt_expire_seconds' => JWT_EXPIRE,
            'jwt_expire_hours' => JWT_EXPIRE / 3600,
            'env_jwt_secret_key' => $_ENV['JWT_SECRET_KEY'] ?? 'NOT_FOUND',
            'getenv_jwt_secret_key' => getenv('JWT_SECRET_KEY') ?: 'NOT_FOUND',
        ];
        
        // Test generate token
        $testPayload = [
            'user_id' => 1,
            'username' => 'test_user',
            'role_id' => 1,
        ];
        
        $testToken = generate_jwt($testPayload);
        
        // Test decode token
        $decodeResult = null;
        $decodeError = null;
        
        try {
            $decoded = decode_jwt($testToken);
            $decodeResult = (array) $decoded;
        } catch (\Exception $e) {
            $decodeError = [
                'error_type' => get_class($e),
                'message' => $e->getMessage(),
            ];
        }
        
        $data = [
            'title' => 'Debug JWT',
            'secret_info' => $info,
            'test_token' => $testToken,
            'decode_result' => $decodeResult,
            'decode_error' => $decodeError,
        ];
        
        return $this->response
            ->setJSON($data)
            ->setStatusCode(200);
    }
    
    /**
     * Test decode dengan token custom
     */
    public function testDecode()
    {
        helper('jwt');
        
        $token = $this->request->getPost('token');
        
        if (!$token) {
            return $this->response
                ->setJSON(['error' => 'Token tidak ditemukan'])
                ->setStatusCode(400);
        }
        
        try {
            $decoded = decode_jwt($token);
            return $this->response
                ->setJSON([
                    'success' => true,
                    'data' => (array) $decoded
                ])
                ->setStatusCode(200);
        } catch (\Exception $e) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'error_type' => get_class($e),
                    'message' => $e->getMessage(),
                ])
                ->setStatusCode(401);
        }
    }
}
