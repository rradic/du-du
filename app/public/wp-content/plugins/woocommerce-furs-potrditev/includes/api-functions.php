<?php
function furs_api_request($endpoint, $data = [], $method = 'POST') {
    $url = "http://981.ablak.arnes.si:2025".$endpoint;

    // Če gre za GET, body mora biti null!
    if(strtoupper($method) === 'GET') {
        $args = [
            'method' => 'GET',
            'headers' => [
                'Authorization' => 'Bearer '.get_option('furs_token'),
                'Content-Type'  => 'application/json'
            ]
            // brez parametra 'body'!
        ];
    } else {
        // če gre za POST ali ostale metode
        $args = [
            'method' => $method,
            'headers' => [
                'Authorization' => 'Bearer '.get_option('furs_token'),
                'Content-Type'  => 'application/json'
            ],
            'body' => json_encode($data)
        ];
    }

    $response = wp_remote_request($url, $args);
    if(is_wp_error($response)){
        return $response->get_error_message();
    }
    return json_decode(wp_remote_retrieve_body($response), true);
}

