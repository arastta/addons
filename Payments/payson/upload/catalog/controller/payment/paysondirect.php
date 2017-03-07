<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class ControllerPaymentPaysondirect extends Controller
{
    private $testMode;
    private $api;
    private $isInvoice;
    private $data = array();

    const MODULE_VERSION = 'Aion_1.0.4';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->testMode = ($this->config->get('paysondirect_mode') == 0);

        $this->api = $this->getAPIInstance();
    }

    public function index()
    {
        $constraints = $this->getConstrains($this->config->get('paysondirect_payment_method'));

        if (in_array(FundingConstraint::INVOICE, $constraints)) {
            $this->isInvoice         = true;
            $this->data['isInvoice'] = true;
        }

        $this->load->language('payment/paysondirect');

        $this->data['button_confirm'] = $this->language->get('button_confirm');
        $this->data['text_wait']      = $this->language->get('text_wait');

        if ($this->isInvoice) {
            $Fee = $this->config->get('paysoninvoice_fee_fee');

            $this->data['text_invoice_terms'] = sprintf($this->language->get('text_invoice_terms'), ($this->isInvoice) ? $Fee : 0);
        }

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paysondirect.tpl')) {
            return $this->load->view($this->config->get('config_template') . '/template/payment/paysondirect.tpl', $this->data);
        } else {
            $this->template = 'default/template/payment/paysondirect.tpl';

            return $this->load->view('default/template/payment/paysondirect.tpl', $this->data);
        }
    }

    public function confirm()
    {
        $this->setupPurchaseData();
    }

    protected function setupPurchaseData()
    {
        $this->load->language('payment/paysondirect');
        $this->load->model('checkout/order');

        $order_data = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $this->data['store_name'] = html_entity_decode($order_data['store_name'], ENT_QUOTES, 'UTF-8');

        $this->data['ok_url']     = $this->url->link('payment/paysondirect/returnFromPayson');
        $this->data['cancel_url'] = $this->url->link('checkout/checkout');
        $this->data['ipn_url']    = $this->url->link('payment/paysondirect/paysonIpn');

        $this->data['order_id']      = $order_data['order_id'];
        $this->data['amount']        = $this->currency->format($order_data['total'] * 100, $order_data['currency_code'], $order_data['currency_value'], false) / 100;
        $this->data['currency_code'] = $order_data['currency_code'];
        $this->data['language_code'] = $order_data['language_code'];
        $this->data['salt']          = md5($this->config->get('paysondirect_secure_word')) . '1-' . $this->data['order_id'];

        $this->data['sender_email']      = $order_data['email'];
        $this->data['sender_first_name'] = html_entity_decode($order_data['firstname'], ENT_QUOTES, 'UTF-8');
        $this->data['sender_last_name']  = html_entity_decode($order_data['lastname'], ENT_QUOTES, 'UTF-8');
        $this->data['countrycode']       = html_entity_decode($order_data['payment_country'], ENT_QUOTES, 'UTF-8');


        $result = $this->getPaymentURL();

        $returnData = array();

        if ($result["Result"] == "OK") {
            $returnData["paymentURL"] = $result["PaymentURL"];
        } else {
            $returnData["error"] = $this->language->get("text_payson_payment_error");
        }

        $this->response->setOutput(json_encode($returnData));
    }

    public function returnFromPayson()
    {
        $this->load->language('payment/paysondirect');

        $paymentDetails = null;

        if (isset($this->request->get['TOKEN'])) {
            $secureWordFromShop     = md5($this->config->get('paysondirect_secure_word')) . '1';
            $paymentDetailsResponse = $this->api->paymentDetails(new PaymentDetailsData($this->request->get['TOKEN']));

            if ($paymentDetailsResponse->getResponseEnvelope()->wasSuccessful()) {
                $paymentDetails = $paymentDetailsResponse->getPaymentDetails();

                $trackingFromDetails = explode('-', $paymentDetails->getTrackingId());

                if ($secureWordFromShop != $trackingFromDetails[0]) {
                    $this->writeToLog($this->language->get('Call doesnt seem to come from Payson. Please contact store owner if this should be a valid call.'), $paymentDetails);
                    $this->paysonApiError($this->language->get('Call doesnt seem to come from Payson. Please contact store owner if this should be a valid call.'));

                    return false;
                }

                if ($this->handlePaymentDetails($paymentDetails, $trackingFromDetails[1])) {
                    $this->response->redirect($this->url->link('checkout/success'));
                } else {
                    $this->response->redirect($this->url->link('checkout/checkout'));
                }
            } else {
                $this->logErrorsAndReturnThem($paymentDetailsResponse);
            }
        } else {
            $this->writeToLog("Returned from Payson without a Token");
        }
    }

    public function getInvoiceFee()
    {
        $this->load->language('payment/paysondirect');
        $this->load->model('checkout/order');
        $this->load->language('total/paysoninvoice_fee');

        $fee = $this->config->get('paysoninvoice_fee_fee');

        if ($this->config->get('paysoninvoice_fee_tax_class_id')) {
            $tax           = $this->config->get('paysoninvoice_fee_tax_class_id');
            $tax_rule_id   = $this->db->query("SELECT tax_rate_id FROM `" . DB_PREFIX . "tax_rule` where  tax_class_id='" . $tax . "'");
            $invoiceFeeTax = $this->db->query("SELECT rate FROM `" . DB_PREFIX . "tax_rate` where  tax_rate_id='" . $tax_rule_id->row['tax_rate_id'] . "'");
            $invoiceFeeTax = ($invoiceFeeTax->row['rate'] / 100) + 1;
            $fee *= $invoiceFeeTax;
        }

        return $fee;
    }

    protected function handlePaymentDetails($paymentDetails, $orderId = 0, $ipnCall = false)
    {
        $this->load->language('payment/paysondirect');
        $this->load->model('checkout/order');
        $this->load->language('total/paysoninvoice_fee');

        $paymentType    = $paymentDetails->getType();
        $transferStatus = $paymentDetails->getStatus();
        $invoiceStatus  = $paymentDetails->getInvoiceStatus();

        $orderId = $orderId ? $orderId : $this->session->data['order_id'];

        $order_info = $this->model_checkout_order->getOrder($orderId);

        if (!$order_info) {
            return false;
        }

        $amount = $paymentDetails->getAmount();

        $total = $amount += $this->getInvoiceFee();

        $this->storeIPNResponse($paymentDetails, $orderId);

        $succesfullStatus = null;

        if ($paymentType == "TRANSFER" && $transferStatus == "COMPLETED") {
            $succesfullStatus = $this->config->get('paysondirect_order_status_id');
        }

        if ($paymentType == "INVOICE" && $invoiceStatus == "ORDERCREATED") {
            $succesfullStatus = $this->config->get('paysondirect_invoice_status_id');

            $invoiceFee = $this->db->query("SELECT code FROM `" . DB_PREFIX . "order_total` where code='paysoninvoice_fee' and order_id='" . $orderId . "'");

            if ($invoiceFee->num_rows == 0) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "order_total` (order_id, code, title, value, sort_order)
                    VALUES('" . $orderId . "', "
                                 . "'paysoninvoice_fee',  "
                                 . "'" . $this->language->get('text_paysoninvoice_fee') . "',  "
                                 . "'" . $this->getInvoiceFee() . "',  "
                                 . "'" . 2 . "')");

                $this->db->query("UPDATE `" . DB_PREFIX . "order_total` SET
                                value  = '" . $total . "'
                                WHERE order_id      = '" . $orderId . "' 
                                and code = 'total'");
            }

            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET 
                                shipping_firstname  = '" . $paymentDetails->getShippingAddressName() . "',
                                shipping_lastname   = '',
                                shipping_address_1  = '" . $paymentDetails->getShippingAddressStreetAddress() . "',
                                shipping_city       = '" . $paymentDetails->getShippingAddressCity() . "', 
                                shipping_country    = '" . $paymentDetails->getShippingAddressCountry() . "', 
                                shipping_postcode   = '" . $paymentDetails->getShippingAddressPostalCode() . "',
                                total                   = '" . $total . "',
                                payment_code            = 'paysoninvoice'
                                WHERE order_id      = '" . $orderId . "'");
        }

        if ($succesfullStatus) {
            if (!$order_info['order_status_id']) {
                $this->model_checkout_order->addOrderHistory($orderId, $succesfullStatus);
            } else {
                //$this->model_checkout_order->update($orderId, $succesfullStatus);
            }

            return true;
        }

        if ($transferStatus == "ERROR" || $transferStatus == "EXPIRED" || $transferStatus == "DENIED") {
            if ($ipnCall) {
                $this->writeToLog('Order was denied by payson.&#10;Purchase type:&#9;&#9;' . $paymentType . '&#10;Order id:&#9;&#9;&#9;&#9;' . $orderId, $paymentDetails);
            }

            $this->paysonApiError($this->language->get('text_denied'));

            return false;
        }

        $this->response->redirect($this->url->link('checkout/checkout'));
    }

    protected function getConstrains($paymentMethod)
    {
        $constraints = array();

        $opts = array(
            0  => array(''),
            1  => array('card'),
            2  => array('bank'),
            3  => array('invoice'),
            4  => array('sms'),
            5  => array('sms', 'bank'),
            6  => array('sms', 'card'),
            7  => array('bank', 'card'),
            8  => array('bank', 'card', 'sms'),
            9  => array('sms', 'invoice'),
            10 => array('bank', 'invoice'),
            11 => array('card', 'invoice'),
            12 => array('sms', 'bank', 'invoice'),
            13 => array('sms', 'card', 'invoice'),
            14 => array('bank', 'card', 'invoice'),
            15 => array('sms', 'bank', 'card', 'invoice'),
        );

        $optsStrings = array('' => FundingConstraint::NONE, 'bank' => FundingConstraint::BANK, 'card' => FundingConstraint::CREDITCARD, 'invoice' => FundingConstraint::INVOICE, 'sms' => FundingConstraint::SMS);

        if ($opts[$paymentMethod]) {
            foreach ($opts[$paymentMethod] as $methodStringName) {
                $constraints[] = $optsStrings[$methodStringName];
            }
        }

        return $constraints;
    }

    protected function getPaymentURL()
    {
        require_once 'payson/paysonapi.php';

        $this->load->language('payment/paysondirect');
        $constraints = $this->getConstrains($this->config->get('paysondirect_payment_method'));
        $orderItems  = $this->getOrderItems();
        $invoiceFee  = $this->getInvoiceFee();

        if (in_array(FundingConstraint::INVOICE, $constraints)) {
            if ($this->data['amount'] < 30) {
                $key = array_search(FundingConstraint::INVOICE, $constraints);
                unset($constraints[$key]);
            }

            if ($this->currencyPaysondirect() != 'SEK') {
                $key = array_search(FundingConstraint::INVOICE, $constraints);

                unset($constraints[$key]);
            }

            $countryCode = trim(strtoupper($this->data['countrycode']));

            if ($countryCode !== 'SWEDEN' && $countryCode !== 'SVERIGE') {
                $key = array_search(FundingConstraint::INVOICE, $constraints);

                unset($constraints[$key]);
            }
        }

        if (in_array(FundingConstraint::INVOICE, $constraints)) {
            $this->data['amount'] += $invoiceFee;
        }

        if (!$this->testMode) {
            $user     = explode('##', $this->config->get('paysondirect_user_name'));
            $store    = $this->config->get('config_store_id');
            $userName = $user[$store];

            $receiver = new Receiver(trim($userName), $this->data['amount']);
        } else {
            $receiver = new Receiver('testagent-1@payson.se', $this->data['amount']);
        }

        $sender = new Sender($this->data['sender_email'], $this->data['sender_first_name'], $this->data['sender_last_name']);

        $receivers = array($receiver);

        $payData = new PayData($this->data['ok_url'], $this->data['cancel_url'], $this->data['ipn_url'], $this->data['store_name'] . ' Order: ' . $this->data['order_id'], $sender, $receivers);

        $payData->setCurrencyCode($this->currencyPaysondirect());
        $payData->setLocaleCode($this->languagePaysondirect());

        if ($invoiceFee && in_array(FundingConstraint::INVOICE, $constraints)) {
            $payData->setInvoiceFee($invoiceFee);
        }

        $payData->setOrderItems($orderItems);

        $showReceiptPage = $this->config->get('paysondirect_receipt');

        $payData->setShowReceiptPage($showReceiptPage);

        if (in_array(FundingConstraint::INVOICE, $constraints)) {
            $this->writeArrayToLog($orderItems, sprintf('Order items sent to Payson, with Payson Invoice as optional payment option. Invoice fee(%sSEK) Total amount(%sSEK).', $this->config->get('paysoninvoice_fee_fee'), $this->data['amount']));
        } else {
            $this->writeArrayToLog($orderItems, sprintf('Order items sent to Payson, with Payson direct as payment option, Total amount(%sSEK)', $this->data['amount']));
        }

        $payData->setFundingConstraints($constraints);
        $payData->setGuaranteeOffered('NO');
        $payData->setTrackingId($this->data['salt']);

        $payResponse = $this->api->pay($payData);

        if ($payResponse->getResponseEnvelope()->wasSuccessful()) {
            return array("Result" => "OK", "PaymentURL" => $this->api->getForwardPayUrl($payResponse));
        } else {
            $errors = $this->logErrorsAndReturnThem($payResponse);

            return array("Result" => "ERROR", "ERRORS" => $errors);
        }
    }

    public function logErrorsAndReturnThem($response)
    {
        $errors = $response->getResponseEnvelope()->getErrors();

        if ($this->config->get('paysondirect_logg') == 1) {
            $this->writeToLog(print_r($errors, true));
        }

        return $errors;
    }

    public function writeToLog($message, $paymentDetails = false)
    {
        $paymentDetailsFormat = "Payson reference:&#9;%s&#10;Correlation id:&#9;%s&#10;";

        if ($this->config->get('paysondirect_logg') == 1) {
            $this->log->write('PAYSON&#10;' . $message . '&#10;' . ($paymentDetails != false ? sprintf($paymentDetailsFormat, $paymentDetails->getPurchaseId(), $paymentDetails->getCorrelationId()) : '') . $this->writeModuleInfoToLog());
        }
    }

    protected function writeArrayToLog($array, $additionalInfo = "")
    {
        if ($this->config->get('paysondirect_logg') == 1) {
            $this->log->write('PAYSON&#10;Additional information:&#9;' . $additionalInfo . '&#10;&#10;' . print_r($array, true) . '&#10;' . $this->writeModuleInfoToLog());
        }
    }

    protected function writeModuleInfoToLog()
    {
        return 'Module version: ' . $this->config->get('paysondirect_modul_version') . '&#10;------------------------------------------------------------------------&#10;';
    }

    protected function getAPIInstance()
    {
        require_once 'payson/paysonapi.php';

        if (!$this->testMode) {
            $agent   = explode('##', $this->config->get('paysondirect_agent_id'));
            $md5     = explode('##', $this->config->get('paysondirect_md5'));
            $store   = $this->config->get('config_store_id');
            $agentid = $agent[$store];
            $md5key  = $md5[$store];

            $credentials = new PaysonCredentials(trim($agentid), trim($md5key), null, 'payson_arastta|' . $this->config->get('paysondirect_modul_version') . '|' . VERSION);
        } else {
            $credentials = new PaysonCredentials(4, '2acab30d-fe50-426f-90d7-8c60a7eb31d4', null, 'payson_arastta|' . $this->config->get('paysondirect_modul_version') . '|' . VERSION);
        }

        $api = new PaysonApi($credentials, $this->testMode);

        return $api;
    }

    protected function getOrderItems()
    {
        require_once 'payson/orderitem.php';

        $this->load->language('payment/paysondirect');

        $orderId = $this->session->data['order_id'];

        $order_data = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $query = "SELECT `order_product_id`, `name`, `model`, `price`, `quantity`, `tax` / `price` as 'tax_rate' FROM `" . DB_PREFIX . "order_product` WHERE `order_id` = " . (int) $orderId . " UNION ALL SELECT 0, '" . $this->language->get('text_gift_card') . "', `code`, `amount`, '1', 0.00 FROM `" . DB_PREFIX . "order_voucher` WHERE `order_id` = " . (int) $orderId;

        $product_query = $this->db->query($query)->rows;

        foreach ($product_query as $product) {
            $productOptions = $this->db->query("SELECT name, value FROM " . DB_PREFIX . 'order_option WHERE order_id = ' . (int) $orderId . ' AND order_product_id=' . (int) $product['order_product_id'])->rows;

            $optionsArray = array();

            if ($productOptions) {
                foreach ($productOptions as $option) {
                    $optionsArray[] = $option['name'] . ': ' . $option['value'];
                }
            }

            $productTitle = $product['name'];

            if (!empty($optionsArray)) {
                $productTitle .= ' | ' . join('; ', $optionsArray);
            }

            $productTitle = (strlen($productTitle) > 80 ? substr($productTitle, 0, strpos($productTitle, ' ', 80)) : $productTitle);

            $product_price = $this->currency->format($product['price'] * 100, $order_data['currency_code'], $order_data['currency_value'], false) / 100;

            $this->data['order_items'][] = new OrderItem(html_entity_decode($productTitle, ENT_QUOTES, 'UTF-8'), $product_price, $product['quantity'], $product['tax_rate'], $product['model']);
        }

        $orderTotals = $this->getOrderTotals();

        foreach ($orderTotals as $orderTotal) {
            $orderTotalAmount = $this->currency->format($orderTotal['value'] * 100, $order_data['currency_code'], $order_data['currency_value'], false) / 100;

            $this->data['order_items'][] = new OrderItem(html_entity_decode($orderTotal['title'], ENT_QUOTES, 'UTF-8'), $orderTotalAmount, 1, $orderTotal['tax_rate'] / 100, $orderTotal['code']);
        }

        return $this->data['order_items'];
    }

    protected function getOrderTotals()
    {
        $total_data = array();
        $total      = 0;
        $payson_tax = array();

        $cartTax = $this->cart->getTaxes();

        $this->load->model('extension/extension');

        $sort_order = array();

        $results = $this->model_extension_extension->getExtensions('total');

        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            if ($this->config->get($result['code'] . '_status')) {
                $amount = 0;

                $taxes = array();

                foreach ($cartTax as $key => $value) {
                    $taxes[$key] = 0;
                }

                $this->load->model('total/' . $result['code']);

                $this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);

                foreach ($taxes as $tax_id => $value) {
                    $amount += $value;
                }

                $payson_tax[$result['code']] = $amount;
            }
        }

        $sort_order = array();

        foreach ($total_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $total_data);

        foreach ($total_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];

            if (isset($payson_tax[$value['code']])) {
                if ($payson_tax[$value['code']]) {
                    $total_data[$key]['tax_rate'] = abs($payson_tax[$value['code']] / $value['value'] * 100);
                } else {
                    $total_data[$key]['tax_rate'] = 0;
                }
            } else {
                $total_data[$key]['tax_rate'] = '0';
            }
        }

        $ignoredTotals = $this->config->get('paysondirect_ignored_order_totals');

        if ($ignoredTotals == null) {
            $ignoredTotals = 'sub_total, total, taxes';
        }

        $ignoredOrderTotals = array_map('trim', explode(',', $ignoredTotals));

        foreach ($total_data as $key => $orderTotal) {
            if (in_array($orderTotal['code'], $ignoredOrderTotals)) {
                unset($total_data[$key]);
            }
        }

        return $total_data;
    }

    public function paysonIpn()
    {
        $this->load->model('checkout/order');
        $postData = file_get_contents("php://input");

        $response = $this->api->validate($postData);

        if ($response->isVerified()) {
            $salt = explode("-", $response->getPaymentDetails()->getTrackingId());

            if ($salt[0] == (md5($this->config->get('paysondirect_secure_word')) . '1')) {
                $orderId = $salt[count($salt) - 1];

                $this->storeIPNResponse($response->getPaymentDetails(), $orderId);

                $this->handlePaymentDetails($response->getPaymentDetails(), $orderId, true);
            } else {
                $this->writeToLog('The secure word could not be verified.', $response->getPaymentDetails());
            }
        } else {
            $this->writeToLog('The IPN response from Payson could not be validated.', $response->getPaymentDetails());
        }
    }

    protected function storeIPNResponse($paymentDetails, $orderId)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "payson_order SET
                            order_id                      = '" . $orderId . "', 
                            valid                         = '" . 1 . "', 
                            added                         = NOW(), 
                            updated                       = NOW(), 
                            ipn_status                    = '" . $paymentDetails->getStatus() . "',     
                            sender_email                  = '" . $paymentDetails->getSenderEmail() . "', 
                            currency_code                 = '" . $paymentDetails->getCurrencyCode() . "',
                            tracking_id                   = '" . $paymentDetails->getTrackingId() . "',
                            type                          = '" . $paymentDetails->getType() . "',
                            purchase_id                   = '" . $paymentDetails->getPurchaseId() . "',
                            invoice_status                = '" . $paymentDetails->getInvoiceStatus() . "',
                            customer                      = '" . $paymentDetails->getCustom() . "', 
                            shippingAddress_name          = '" . $paymentDetails->getShippingAddressName() . "', 
                            shippingAddress_street_ddress = '" . $paymentDetails->getShippingAddressStreetAddress() . "', 
                            shippingAddress_postal_code   = '" . $paymentDetails->getShippingAddressPostalCode() . "', 
                            shippingAddress_city          = '" . $paymentDetails->getShippingAddressPostalCode() . "', 
                            shippingAddress_country       = '" . $paymentDetails->getShippingAddressCity() . "', 
                            token                         =  '" . $paymentDetails->getToken() . "'"
        );
    }

    public function languagePaysondirect()
    {
        switch (strtoupper($this->data['language_code'])) {
            case "SE":
            case "SV":
                return "SV";
            case "FI":
                return "FI";
            default:
                return "EN";
        }
    }

    public function currencyPaysondirect()
    {
        switch (strtoupper($this->data['currency_code'])) {
            case "SEK":
                return "SEK";
            default:
                return "EUR";
        }
    }

    public function paysonApiError($error)
    {
        $this->load->language('payment/paysondirect');

        $error_code = '<html>
                            <head>
                                <script type="text/javascript"> 
                                    alert("' . $error . $this->language->get('text_payson_payment_method') . '");
                                    window.location="' . (HTTPS_SERVER . 'index.php?route=checkout/checkout') . '";
                                </script>
                            </head>
                    </html>';

        echo($error_code);
        exit;
    }
}
