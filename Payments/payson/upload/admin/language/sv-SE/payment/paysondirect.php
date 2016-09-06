<?php
// Heading
$_['heading_title'] = 'Payson All in One';

// Text
$_['text_payment']      = 'Payment';
$_['text_success']      = 'Success: Du har &auml;ndrat Payson Direktbetalning modulen!';
$_['text_paysondirect'] = '<a onclick="window.open(\'https://www.payson.se/tj%C3%A4nster/ta-betalt\');"><img src="view/image/payment/Allinone.png" alt="Payson" title="Payson" /></a>';
$_['text_edit']         = 'Uppdatera Payson Invoice';

// Entry
$_['entry_total']                        = 'Totalt';
$_['entry_order_status']                 = 'Order Status';
$_['entry_geo_zone']                     = 'Geo Zone';
$_['entry_status']                       = 'Status';
$_['entry_sort_order']                   = 'Sorteringsordning';
$_['entry_logg']                         = 'Logg';
$_['entry_totals_to_ignore']             = 'Ignorerade ordertillägg';
$_['entry_show_receipt_page']            = 'Visa Kvittosidan';
$_['entry_show_receipt_page_yes']        = 'Ja';
$_['entry_show_receipt_page_no']         = 'Nej';
$_['entry_order_item_details_to_ignore'] = 'Ignorerade produktlista vid KORT och BANK:<br /><span class="help">Note: produklistan kr&auml;vs f&ouml;r fakturabetalning och frivilligt f&ouml;r andra typer av betalningar.</span>';

$_['user_name']            = 'E-postadress';
$_['agent_id']             = 'Agent Id';
$_['md5']                  = 'API-nyckel';
$_['secure_word']          = 'Hemligt ord';
$_['paysondirect_example'] = 'Example Extra Text';

// Payment
$_['payment_method_card_bank_info']    = 'Betalningsmetoder:';
$_['payment_method_none']              = '------------------';
$_['payment_method_card']              = 'KORT';
$_['payment_method_bank']              = 'BANK';
$_['payment_method_inv']               = 'FAKTURA';
$_['payment_method_sms']               = 'SMS';
$_['payment_method_sms_bank']          = 'SMS / BANK';
$_['payment_method_card_bank']         = 'KORT / BANK';
$_['payment_method_sms_card']          = 'KORT / SMS';
$_['payment_method_card_bank_sms']     = 'KORT / BANK / SMS';
$_['payment_method_sms_inv']           = 'FAKTURA / SMS';
$_['payment_method_bank_inv']          = 'FAKTURA / BANK';
$_['payment_method_card_inv']          = 'FAKTURA / KORT';
$_['payment_method_sms_bank_inv']      = 'FAKTURA / SMS / BANK';
$_['payment_method_sms_card_inv']      = 'FAKTURA / SMS / KORT';
$_['payment_method_inv_car_ban']       = 'FAKTURA / KORT / BANK';
$_['payment_method_sms_bank_card_inv'] = 'FAKTURA / SMS / KORT / BANK';
$_['payment_method_mode']              = 'Mode';
$_['payment_method_mode_live']         = 'Produktionsmilj&ouml;';
$_['payment_method_mode_sandbox']      = 'Testmilj&ouml;';

// Error
$_['error_permission']           = 'Varning: Du har inte beh&ouml;righet att &auml; ndra betalningsmetoden Payson Direkt!';
$_['error_user_name']            = 'E-postadress saknas!';
$_['error_agent_id']             = 'Agent ID saknas!';
$_['error_md5']                  = 'API-nyckel saknas!';
$_['error_ignored_order_totals'] = 'Ange en kommaseparerad lista med ordertillägg som ej skall skickas till Payson';

// Help
$_['help_method_mode']           = 'V&auml;lj l&auml;get (Produktionsmilj&ouml; eller testmilj&ouml;)';
$_['help_user_name']             = 'Ange din e-postadress f&ouml;r ditt Paysonkonto';
$_['help_agent_id']              = 'Ange ditt agentID f&ouml;r ditt Paysonkonto';
$_['help_md5']                   = 'Ange din API-nyckel f&ouml;r ditt Paysonkonto';
$_['help_method_card_bank_info'] = 'Aktiverade betalsätt (Visa, Mastercard & Internetbank)';
$_['help_secure_word']           = 'Ange ett hemligt ord';
$_['help_total']                 = 'Kassan totala ordern m&aring;ste uppn&aring; innan betalningsmetod blir aktiv';
$_['help_logg']                  = 'Du hittar dina loggar i Admin | System -> Error Log';
$_['help_totals_to_ignore']      = 'Kommaseparerad lista med ordertillägg som ej skall skickas till Payson';
