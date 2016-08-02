<?php
// Heading
$_['heading_title'] = 'Payson All in One';

// Text
$_['text_modul_name']    = 'Payson_Allinone';
$_['text_modul_version'] = '1.0.4';
$_['text_payment']       = 'Payment';
$_['text_success']       = 'Success: You have modified Payson All in One payment module!';
$_['text_paysondirect']  = '<a onclick="window.open(\'https://www.payson.se/tj%C3%A4nster/ta-betalt\');"><img src="view/image/payment/Allinone.png" alt="Payson" title="Payson" /></a>';
$_['text_edit']          = 'Edit Payson Direct';

// Entry
$_['entry_total']                        = 'Total';
$_['entry_order_status']                 = 'Order Status';
$_['entry_geo_zone']                     = 'Geo Zone';
$_['entry_status']                       = 'Status';
$_['entry_sort_order']                   = 'Sort Order';
$_['entry_logg']                         = 'Logs';
$_['entry_totals_to_ignore']             = 'Order totals to ignore';
$_['entry_show_receipt_page']            = 'Show Receipt Page';
$_['entry_show_receipt_page_yes']        = 'Yes';
$_['entry_show_receipt_page_no']         = 'No';
$_['entry_order_item_details_to_ignore'] = 'Order Item Details to ignore by CREDITCARD / BANK:<br /><span class="help">Note: Order Items are required for INVOICE payments and optional for other payment types. Also, please note that the total sum of all order items amount (inc. VAT) must match the total sum of all receivers amount.</span>';

$_['paysondirect_method_card_bank_info'] = 'Payment methods:';
$_['paysondirect_method_mode']           = 'Mode';
$_['paysondirect_method_mode_live']      = 'Production';
$_['paysondirect_method_mode_sandbox']   = 'Test';
$_['paysondirect_example']               = 'Example Extra Text';

$_['user_name']   = 'Seller Email';
$_['agent_id']    = 'Agent id';
$_['md5']         = 'API-key';
$_['secure_word'] = 'Secure word';

// Payment
$_['payment_method_none']              = '------------------';
$_['payment_method_card']              = 'CREDITCARD';
$_['payment_method_bank']              = 'BANK';
$_['payment_method_inv']               = 'INVOICE';
$_['payment_method_sms']               = 'SMS';
$_['payment_method_sms_bank']          = 'SMS / BANK';
$_['payment_method_card_bank']         = 'CREDITCARD / BANK';
$_['payment_method_sms_card']          = 'CREDITCARD / SMS';
$_['payment_method_card_bank_sms']     = 'CREDITCARD / BANK / SMS';
$_['payment_method_sms_inv']           = 'INVOICE / SMS';
$_['payment_method_bank_inv']          = 'INVOICE / BANK';
$_['payment_method_card_inv']          = 'INVOICE / CREDITCARD';
$_['payment_method_sms_bank_inv']      = 'INVOICE / SMS / BANK';
$_['payment_method_sms_card_inv']      = 'INVOICE / SMS / CREDITCARD';
$_['payment_method_inv_car_ban']       = 'INVOICE / CREDITCARD / BANK';
$_['payment_method_sms_bank_card_inv'] = 'INVOICE / SMS / CREDITCARD / BANK';

// Error
$_['error_permission']           = 'Warning: You do not have permission to modify payment Payson module!';
$_['error_user_name']            = 'E-mail Required!';
$_['error_agent_id']             = 'Agent ID Required!';
$_['error_md5']                  = 'API-key Required!';
$_['error_ignored_order_totals'] = 'Enter a comma separated list with order totals not to send to payson';

// Help
$_['help_method_mode']           = 'Select environment (Production or Test)';
$_['help_user_name']             = 'Enter your seller email for Payson';
$_['help_agent_id']              = 'Enter your Agent id for Payson';
$_['help_md5']                   = 'Enter your API-key for Payson';
$_['help_method_card_bank_info'] = 'Enabled payment methods (Visa, Mastercard & Internetbank)';
$_['help_secure_word']           = 'Enter a secure word for Paysondirect';
$_['help_logg']                  = 'You can find your logs in Admin | System -> Error Log';
$_['help_total']                 = 'The checkout total the order must reach before this payment method becomes active';
$_['help_totals_to_ignore']      = 'Comma separated list with order totals not to send to payson';
