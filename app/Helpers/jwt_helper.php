<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

defined('JWT_SECRET') || define('JWT_SECRET', env('JWT_SECRET_KEY', 'PulsaIo2026AppSecretKeyYangSangatAmanDanPanjang123!@#$'));
defined('JWT_EXPIRE') || define('JWT_EXPIRE', 60 * 60 * 24); // 24 jam

function generate_jwt(array $payload): string
{
    $now = time();
    return JWT::encode([
        'iat' => $now,
        'exp' => $now + JWT_EXPIRE,
        'data' => $payload,
    ], JWT_SECRET, 'HS256');
}

function decode_jwt(string $token): object
{
    // Perbaiki: cukup satu Key object
    return JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
}

function get_jwt_from_header(): ?string
{
    $header = service('request')->getHeaderLine('Authorization');
    if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
        return $matches[1];
    }
    return null;
}