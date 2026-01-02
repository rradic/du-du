<?php
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
use Firebase\JWT\JWT;

function dpc_generate_jwt($user_id, $data_id) {
    $secret = get_option('dpc_jwt_secret');
    $issuedAt = time();
    $expiration = $issuedAt + 300; // 5 min

    $payload = [
        'sub' => (string) $user_id,
        'data_id' => $data_id,
        'iat' => $issuedAt,
        'exp' => $expiration,
    ];

    $jwt = JWT::encode($payload, $secret, 'HS256');
    return is_string($jwt) ? $jwt : $jwt->toString();
}
