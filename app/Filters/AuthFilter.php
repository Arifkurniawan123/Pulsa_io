<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('jwt');
        
        $token = get_jwt_from_header();
        
        if (!$token) {
            return response()
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Token tidak ditemukan'
                ]);
        }
        
        try {
            $decoded = decode_jwt($token);
            $request->user = $decoded->data;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            // Signature verification failed
            log_message('error', 'JWT Signature Invalid: ' . $e->getMessage());
            return response()
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Token signature tidak valid',
                    'error' => 'SIGNATURE_INVALID'
                ]);
        } catch (\Firebase\JWT\ExpiredException $e) {
            // Token expired
            log_message('error', 'JWT Expired: ' . $e->getMessage());
            return response()
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Token sudah expired',
                    'error' => 'TOKEN_EXPIRED'
                ]);
        } catch (\Exception $e) {
            log_message('error', 'JWT Error: ' . $e->getMessage());
            return response()
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Token tidak valid: ' . $e->getMessage()
                ]);
        }
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}