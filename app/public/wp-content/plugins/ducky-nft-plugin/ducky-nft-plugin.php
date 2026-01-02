<?php
/*
Plugin Name: Ducky NFT Plugin
Description: Vtičnik za interakcijo z DuckyNFT pametno pogodbo.
Version: 1.0
Author: Maj
*/

defined('ABSPATH') or die('No script kiddies please!');

//
// === ADMIN INTERFACE ===
//

add_action('admin_menu', 'ducky_nft_plugin_menu');
function ducky_nft_plugin_menu() {
    add_menu_page('Ducky NFT', 'Ducky NFT', 'manage_options', 'ducky-nft', 'ducky_nft_admin_page');
}

function ducky_nft_admin_page() {
    ?>
    <div class="wrap">
        <h1>Ducky NFT Nastavitve</h1>
        <?php if (isset($_GET['saved'])) echo '<div class="updated"><p>Nastavitve shranjene.</p></div>'; ?>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="save_ducky_nft">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="contract_address">Naslov pametne pogodbe</label></th>
                    <td><input name="contract_address" type="text" id="contract_address" value="<?php echo esc_attr(get_option('ducky_nft_contract_address')); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="contract_abi">ABI pogodbe (JSON)</label></th>
                    <td><textarea name="contract_abi" id="contract_abi" rows="10" class="large-text"><?php echo esc_textarea(get_option('ducky_nft_abi')); ?></textarea></td>
                </tr>
            </table>
            <?php submit_button('Shrani nastavitve'); ?>
        </form>
    </div>
    <?php
}

add_action('admin_post_save_ducky_nft', 'ducky_nft_save_options');
function ducky_nft_save_options() {
    if (current_user_can('manage_options')) {
        update_option('ducky_nft_contract_address', sanitize_text_field($_POST['contract_address']));
        update_option('ducky_nft_abi', wp_unslash($_POST['contract_abi']));
    }
    wp_redirect(admin_url('admin.php?page=ducky-nft&saved=true'));
    exit;
}

//
// === SHORTCODE ZA ZAŠČITO VSEBINE ===
//

add_shortcode('ducky_nft_protected', 'ducky_nft_protected_shortcode');
function ducky_nft_protected_shortcode($atts, $content = null) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return '<p>🔐 Za ogled te vsebine se moraš prijaviti.</p>';
    }

    $balance = intval(get_user_meta($user_id, 'nft_balance', true));

    if ($balance > 0) {
        return do_shortcode($content);
    } else {
        return '<p>🪙 Do te vsebine imajo dostop le imetniki Ducky NFT.</p>';
    }
}


//
// === JAVNI DEL – JS in komunikacija ===
//

add_action('wp_enqueue_scripts', 'ducky_nft_enqueue_scripts');
function ducky_nft_enqueue_scripts() {
    wp_enqueue_script('web3js', 'https://cdn.jsdelivr.net/npm/web3@1.10.0/dist/web3.min.js', [], null, true);
    wp_enqueue_script('ducky-nft-js', plugin_dir_url(__FILE__) . 'js/nft-script.js', ['jquery', 'web3js'], null, true);

    wp_localize_script('ducky-nft-js', 'duckyNFTData', [
        'contractAddress' => get_option('ducky_nft_contract_address'),
        'contractABI' => get_option('ducky_nft_abi'),
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'userId' => get_current_user_id()
    ]);
}

add_action('wp_ajax_save_user_eth_data', 'ducky_nft_save_user_data');
function ducky_nft_save_user_data() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('Ni prijavljen.');
        return;
    }

    update_user_meta($user_id, 'eth_address', sanitize_text_field($_POST['eth_address']));
    update_user_meta($user_id, 'nft_balance', intval($_POST['nft_balance']));
    wp_send_json_success();
}
