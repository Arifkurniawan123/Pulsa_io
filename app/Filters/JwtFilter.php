<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\ExpiredException;

class JwtFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Allow CORS preflight requests to pass through without JWT validation.
        if (strtolower($request->getMethod()) === 'options') {
            return $request;
        }

        helper('jwt'); // memuat helper

        $token = get_jwt_from_header();

        if (empty($token)) {
            return $this->unauthorized('Token tidak ditemukan');
        }

        try {
            $decoded = decode_jwt($token);
            // Simpan user data ke request
            $request->user = $decoded->data ?? null;
            // Kembalikan request agar request berlanjut
            return $request;
        } catch (ExpiredException $e) {
            return $this->unauthorized('Token sudah expired');
        } catch (\Exception $e) {
            log_message('error', 'JWT Error: ' . $e->getMessage());
            return $this->unauthorized('Token tidak valid: ' . $e->getMessage());
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // tidak perlu aksi
    }

    private function unauthorized($message)
    {
        return service('response')
            ->setStatusCode(401)
            ->setJSON([
                'success' => false,
                'message' => $message,
                'code' => 401,
            ]);
    }
}