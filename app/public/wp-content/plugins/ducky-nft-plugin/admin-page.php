<?php if (isset($_GET['saved'])) echo '<div class="updated"><p>Nastavitve shranjene.</p></div>'; ?>

<h2>Ducky NFT Nastavitve</h2>
<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
    <input type="hidden" name="action" value="save_ducky_nft">
    
    <table class="form-table">
        <tr valign="top">
            <th>Naslov pametne pogodbe</th>
            <td><input type="text" name="contract_address" value="<?php echo esc_attr(get_option('ducky_nft_contract_address')); ?>" size="50" /></td>
        </tr>
        <tr valign="top">
            <th>ABI (JSON)</th>
            <td><textarea name="contract_abi" rows="10" cols="70"><?php echo esc_textarea(get_option('ducky_nft_abi')); ?></textarea></td>
        </tr>
    </table>

    <?php submit_button(); ?>
</form>
