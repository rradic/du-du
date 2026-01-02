<?php

add_action('woocommerce_order_status_completed', 'furs_invoice_send');
add_action('woocommerce_email_after_order_table', 'furs_email_display_zoi_eor', 10, 4);

function furs_invoice_send($order_id) {
    $order = wc_get_order($order_id);

    $total_amount = round(floatval($order->get_total()), 2);
    $taxable_amount = round($total_amount / 1.22, 2);
    $tax_amount = round($total_amount - $taxable_amount, 2);

    $invoice_data = [
        "InvoiceRequest" => [
            "Header" => [
                "MessageID" => wp_generate_uuid4(),
                "DateTime" => gmdate("Y-m-d\TH:i:s\Z"),
            ],
            "Invoice" => [
                "TaxNumber" => 10596631,
                "IssueDateTime" => gmdate("Y-m-d\TH:i:s\Z"),
                "NumberingStructure" => "B",
                "InvoiceIdentifier" => [
                    "BusinessPremiseID" => get_option('furs_premise_id'),
                    "ElectronicDeviceID" => get_option('furs_device_id'),
                    "InvoiceNumber" => strval($order_id),
                ],
                "InvoiceAmount" => $total_amount,
                "PaymentAmount" => $total_amount,
                "TaxesPerSeller" => [[
                    "VAT" => [[
                        "TaxRate" => 22.00,
                        "TaxableAmount" => $taxable_amount,
                        "TaxAmount" => $tax_amount
                    ]]
                ]],
                "OperatorTaxNumber" => 10596631,
                "ProtectedID" => wp_generate_password(32, false)
            ]
        ]
    ];

    // DEBUG vrstica
    $order->add_order_note('Debug JSON: ' . json_encode($invoice_data));

    $response = furs_api_request('/invoice', $invoice_data);

    if (isset($response['ZOI'], $response['response']['InvoiceResponse']['UniqueInvoiceID'])) {
        $zoi = $response['ZOI'];
        $eor = $response['response']['InvoiceResponse']['UniqueInvoiceID'];
    
        $order->add_order_note('Račun uspešno potrjen na FURS. ZOI: '.$zoi.', EOR: '.$eor);
    
        $order->update_meta_data('_furs_zoi', $zoi);
        $order->update_meta_data('_furs_eor', $eor);
        $order->save();

        // Manually trigger the completed order email after ZOI/EOR are saved
        WC()->mailer()->get_emails()['WC_Email_Customer_Completed_Order']->trigger($order_id);
    } else {
        $order->add_order_note('Napaka pri potrditvi računa na FURS: '.print_r($response, true));
    }
    
}

function furs_email_display_zoi_eor($order, $sent_to_admin, $plain_text, $email) {
    // Apply only to customer completed orders
    if ( $email->id === 'customer_completed_order' && !$sent_to_admin ) {
        $zoi = $order->get_meta('_furs_zoi');
        $eor = $order->get_meta('_furs_eor');

        if ($zoi && $eor) {
            if ($plain_text) {
                echo "\nDavčna potrditev računa\n";
                echo "ZOI: " . $zoi . "\n";
                echo "EOR: " . $eor . "\n";
            } else {
                echo '<h3>Davčna potrditev računa</h3>';
                echo '<p><strong>ZOI:</strong> ' . esc_html($zoi) . '<br>';
                echo '<strong>EOR:</strong> ' . esc_html($eor) . '</p>';
            }
        } else {
            echo '<!-- ZOI/EOR not yet set when email generated -->';
        }
    }
}
