<?php
/*
Plugin Name: Data Platform Connector
Description: Povezava WooCommerce izdelkov z ločeno platformo (API).
Version: 1.0
*/

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'admin-settings.php';
require_once plugin_dir_path(__FILE__) . 'jwt-helper.php';
require_once plugin_dir_path(__FILE__) . 'content-display.php';

// Dodaj "Data ID" custom field k izdelku
add_action('woocommerce_product_options_general_product_data', function() {
    woocommerce_wp_text_input([
        'id' => '_data_platform_id',
        'label' => 'Data Platform ID',
        'description' => 'ID vsebine na zunanji platformi',
        'desc_tip' => true,
    ]);
});

add_action('woocommerce_process_product_meta', function($post_id) {
    $data_id = $_POST['_data_platform_id'] ?? '';
    update_post_meta($post_id, '_data_platform_id', sanitize_text_field($data_id));
});
