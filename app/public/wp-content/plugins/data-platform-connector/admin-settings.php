<?php
add_action('admin_menu', function() {
    add_options_page('Platforma Nastavitve', 'Platforma', 'manage_options', 'dpc-settings', 'dpc_settings_page');
});

add_action('admin_init', function() {
    register_setting('dpc_settings', 'dpc_jwt_secret');
    register_setting('dpc_settings', 'dpc_api_url');
});

function dpc_settings_page() {
    ?>
    <div class="wrap">
        <h1>Nastavitve za Platformo</h1>
        <form method="post" action="options.php">
            <?php
                settings_fields('dpc_settings');
                do_settings_sections('dpc_settings');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="dpc_jwt_secret">JWT Secret</label></th>
                    <td><input type="text" name="dpc_jwt_secret" value="<?php echo esc_attr(get_option('dpc_jwt_secret')); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="dpc_api_url">API URL (npr. http://localhost:5001)</label></th>
                    <td><input type="text" name="dpc_api_url" value="<?php echo esc_attr(get_option('dpc_api_url')); ?>" class="regular-text" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
