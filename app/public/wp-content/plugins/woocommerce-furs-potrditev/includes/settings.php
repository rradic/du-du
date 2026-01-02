<?php
include_once 'api-functions.php';

add_action('admin_menu', 'furs_menu');

function furs_menu() {
    add_menu_page('Nastavitve FURS', 'FURS Blagajna', 'manage_options', 'furs-nastavitve', 'furs_settings_page');
    add_submenu_page('furs-nastavitve', 'Preveri prostor', 'Preveri prostor', 'manage_options', 'furs-preveri-prostor', 'furs_check_premise_page');
    add_submenu_page('furs-nastavitve', 'Preveri račun', 'Preveri račun', 'manage_options', 'furs-preveri-racun', 'furs_check_invoice_page');
    add_submenu_page('furs-nastavitve', 'Registracija prostora', 'Registracija prostora', 'manage_options', 'furs-registracija-prostora', 'furs_register_premise_page');
}

// Nastavitve API
function furs_settings_page() {
    if (isset($_POST['submit'])) {
        update_option('furs_token', sanitize_text_field($_POST['furs_token']));
        update_option('furs_premise_id', sanitize_text_field($_POST['furs_premise_id']));
        update_option('furs_device_id', sanitize_text_field($_POST['furs_device_id']));
        echo '<div class="updated"><p>Nastavitve shranjene.</p></div>';
    }
    ?>
    <form method="post">
        <h2>Nastavitve FURS API</h2>
        Token FURS API:<br>
        <input type="text" name="furs_token" value="<?php echo esc_attr(get_option('furs_token')); ?>" required><br>
        ID poslovnega prostora:<br>
        <input type="text" name="furs_premise_id" value="<?php echo esc_attr(get_option('furs_premise_id')); ?>" required><br>
        ID elektronske naprave:<br>
        <input type="text" name="furs_device_id" value="<?php echo esc_attr(get_option('furs_device_id')); ?>" required><br><br>
        <input type="submit" name="submit" value="Shrani" class="button button-primary">
    </form>
    <?php
}

// Registracija poslovnega prostora
function furs_register_premise_page() {
    if(isset($_POST['submit_register'])) {

        $data = [
            "BusinessPremiseRequest" => [
                "Header" => [
                    "MessageID" => wp_generate_uuid4(),
                    "DateTime"  => gmdate("Y-m-d\TH:i:s\Z")
                ],
                "BusinessPremise" => [
                    "TaxNumber" => 10596631,
                    "BusinessPremiseID" => sanitize_text_field($_POST['business_premise_id']),
                    "BPIdentifier" => [
                        "RealEstateBP" => [
                            "PropertyID" => [
                                "CadastralNumber"      => intval($_POST['cadastral_number']),
                                "BuildingNumber"       => intval($_POST['building_number']),
                                "BuildingSectionNumber"=> intval($_POST['section_number'])
                            ],
                            "Address" => [
                                "Street"                => sanitize_text_field($_POST['street']),
                                "HouseNumber"           => sanitize_text_field($_POST['house_number']),
                                "Community"             => sanitize_text_field($_POST['community']),
                                "City"                  => sanitize_text_field($_POST['city']),
                                "PostalCode"            => sanitize_text_field($_POST['postal_code'])
                            ]
                        ]
                    ],
                    "ValidityDate" => gmdate("Y-m-d\TH:i:s\Z"),
                    "SoftwareSupplier" => [["TaxNumber" => 10596631]]
                ]
            ]
        ];

        $response = furs_api_request('/register', $data);
        echo '<pre>' . print_r($response, true) . '</pre>';
    }
    ?>

    <h2>Registracija poslovnega prostora</h2>
    <form method="post">
        <label>ID poslovnega prostora:</label><br>
        <input type="text" name="business_premise_id" required><br>

        <label>Katastrska številka:</label><br>
        <input type="number" name="cadastral_number" required><br>

        <label>Številka stavbe:</label><br>
        <input type="number" name="building_number" required><br>

        <label>Številka dela stavbe:</label><br>
        <input type="number" name="section_number" required><br>

        <label>Ulica:</label><br>
        <input type="text" name="street" required><br>

        <label>Hišna številka (npr. 12A):</label><br>
        <input type="text" name="house_number" required><br>

        <label>Naselje:</label><br>
        <input type="text" name="community" required><br>

        <label>Mesto:</label><br>
        <input type="text" name="city" required><br>

        <label>Poštna številka:</label><br>
        <input type="text" name="postal_code" required><br><br>

        <input type="submit" name="submit_register" value="Registriraj prostor" class="button button-primary">
    </form>
    <?php
}

// Preverjanje poslovnega prostora
function furs_check_premise_page() {
    if(isset($_POST['submit_check_premise'])){
        $id = sanitize_text_field($_POST['premise_id']);
        $response = furs_api_request("/check-premise?id=$id", [], 'GET');
        echo '<pre>'.print_r($response, true).'</pre>';
    }
    ?>
    <h2>Preverjanje poslovnega prostora</h2>
    <form method="post">
        ID poslovnega prostora:<br>
        <input type="text" name="premise_id" required><br><br>
        <input type="submit" name="submit_check_premise" value="Preveri prostor" class="button button-primary">
    </form>
    <?php
}

// Preverjanje računa
function furs_check_invoice_page() {
    if(isset($_POST['submit_check_invoice'])){
        $eor = sanitize_text_field($_POST['invoice_eor']);
        $response = furs_api_request("/check-invoice?eor=$eor", [], 'GET');
        echo '<pre>'.print_r($response, true).'</pre>';
    }
    ?>
    <h2>Preverjanje računa</h2>
    <form method="post">
        EOR računa:<br>
        <input type="text" name="invoice_eor" required><br><br>
        <input type="submit" name="submit_check_invoice" value="Preveri račun" class="button button-primary">
    </form>
    <?php
}