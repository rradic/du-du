<?php
add_shortcode('dpc_user_content', 'dpc_show_user_content');

function dpc_show_user_content() {
    if (!is_user_logged_in()) return "<p>Prijava je obvezna za prikaz vsebine.</p>";

    $user_id = get_current_user_id();
    $orders = wc_get_orders([
        'customer_id' => $user_id,
        'status' => ['completed']
    ]);

    $output = "<ul>";
    $api_url = rtrim(get_option('dpc_api_url'), '/');

    $processed_ids = []; // sledimo že prikazanim data_id

    foreach ($orders as $order) {
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $data_id = $product->get_meta('_data_platform_id');

            // Preskoči, če data_id manjka ali je že bil obdelan
            if (!$data_id || in_array($data_id, $processed_ids)) continue;

            $processed_ids[] = $data_id;

            $output .= "<li><strong>Preverjam izdelek:</strong> " . esc_html($product->get_name()) . " (data_id: " . esc_html($data_id) . ")</li>";

            // Ustvari JWT
            $token = dpc_generate_jwt($user_id, $data_id);

            // Pokliči API
            $response = wp_remote_get("$api_url/getContent", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

            if (is_wp_error($response)) {
                $output .= "<li><em>Napaka pri zahtevi: " . esc_html($response->get_error_message()) . "</em></li>";
                continue;
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);

            if (isset($body['value'])) {
                $output .= "<li><strong>Vsebina:</strong> " . esc_html($body['value']) . "</li>";
            } elseif (isset($body['error'])) {
                $output .= "<li><em>Napaka API-ja: " . esc_html($body['error']) . "</em></li>";
            } else {
                $output .= "<li><em>API vrnil: " . esc_html(json_encode($body)) . "</em></li>";
            }
        }
    }

    $output .= "</ul>";
    return $output;
}
