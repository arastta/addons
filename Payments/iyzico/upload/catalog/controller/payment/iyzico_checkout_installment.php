<?php
/**
 * @package        Arastta eCommerce
 * @copyright      Copyright (C) 2015-2016 Arastta Association. All rights reserved. (arastta.org)
 * @license        GNU General Public License version 3; see LICENSE.txt
 */

require_once DIR_SYSTEM . "library/iyzico/IyzipayBootstrap.php";

class ControllerPaymentIyzicoCheckoutInstallment extends Controller
{
    private $base_url     = "https://api.iyzipay.com";
    private $order_prefix = "Arastta_";

    /**
     * Checkout initialization
     *
     */
    public function index()
    {
        $this->load->language('payment/iyzico_checkout_installment');

        $data = $this->language->all();

        $data['form_class'] = $this->config->get('iyzico_checkout_installment_form_class');

        $data['continue'] = $this->url->link('checkout/success', '', 'SSL');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/iyzico_checkout_installment.tpl')) {
            return $this->load->view($this->config->get('config_template') . '/template/payment/iyzico_checkout_installment.tpl', $data);
        } else {
            return $this->load->view('default/template/payment/iyzico_checkout_installment.tpl', $data);
        }
    }

    /**
     * Generate payment form
     *
     */
    public function gettoken()
    {
        error_reporting(0);

        try {
            IyzipayBootstrap::init();

            $data['checkout_form_content']  = '';
            $data['error']                  = '';
            $data['form_class']             = $this->config->get('iyzico_checkout_installment_form_class');
            $data['continue']               = $this->url->link('checkout/success', '', 'SSL');
            $data['display_direct_confirm'] = 'no';

            $callback_url = $this->getSiteUrl() . 'index.php?route=payment/iyzico_checkout_installment/callback';

            $order_id               = $this->session->data['order_id'];
            $unique_conversation_id = uniqid($this->order_prefix) . "_" . $order_id;

            $merchant_api_id     = $this->config->get('iyzico_checkout_installment_api_id_live');
            $merchant_secret_key = $this->config->get('iyzico_checkout_installment_secret_key_live');

            $configuration = new \Iyzipay\Client\Configuration\ClientConfiguration();

            $configuration->setApiKey($merchant_api_id);
            $configuration->setSecretKey($merchant_secret_key);
            $configuration->setBaseUrl($this->base_url);

            $client = \Iyzipay\Client\Service\EcomCheckoutFormServiceClient::fromConfiguration($configuration);

            $this->load->language('payment/iyzico_checkout_installment');

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/iyzico_checkout_installment.tpl')) {
                $this->template = $this->config->get('config_template') . '/template/payment/iyzico_checkout_installment.tpl';
            } else {
                $this->template = 'default/template/payment/iyzico_checkout_installment.tpl';
            }

            $this->load->model('checkout/order');
            $this->load->model('catalog/product');
            $this->load->model('catalog/category');
            $this->load->model('extension/extension');

            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            if ($order_info['currency_code'] != 'TRY') {
                throw new \Exception($this->language->get('error_invalid_currency'));
            }

            $cart_total_amount = round($order_info['total'] * $order_info['currency_value'], 2);

            if ($cart_total_amount == 0) {
                $data['display_direct_confirm'] = 'yes';

                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($data));

                return true;
            }

            $request = new \Iyzipay\Client\Ecom\Payment\Request\EcomPaymentCheckoutFormInitializeRequest();

            $request->setLocale(\Iyzipay\Client\RequestLocaleType::TR);
            $request->setConversationId($unique_conversation_id);
            $request->setPaidPrice($cart_total_amount);
            $request->setBasketId($unique_conversation_id);
            $request->setPaymentGroup(\Iyzipay\Client\Ecom\Payment\Enumtype\PaymentGroupRequestType::PRODUCT);
            $request->setPaymentSource(\Iyzipay\Client\Ecom\Payment\Enumtype\PaymentSourceRequestType::ARASTTA);
            $request->setCallbackUrl($callback_url);

            $customer_address = trim($order_info['payment_address_1'] . " " . $order_info['payment_address_2']);
            $customer_address = !empty($customer_address) ? $customer_address : "NOT PROVIDED";

            $buyer = new \Iyzipay\Client\Ecom\Payment\Dto\EcomPaymentBuyerDto();
            $buyer->setId($order_info['customer_id']);

            $order_info_firstname = !empty($order_info['firstname']) ? $order_info['firstname'] : "NOT PROVIDED";
            $order_info_lastname  = !empty($order_info['lastname']) ? $order_info['lastname'] : "NOT PROVIDED";
            $order_info_telephone = !empty($order_info['telephone']) ? $order_info['telephone'] : "NOT PROVIDED";
            $order_info_email     = !empty($order_info['email']) ? $order_info['email'] : "NOT PROVIDED";

            $buyer->setName($order_info_firstname);
            $buyer->setSurname($order_info_lastname);
            $buyer->setGsmNumber($order_info_telephone);
            $buyer->setEmail($order_info_email);
            $buyer->setRegistrationAddress($customer_address);

            $order_info_payment_zone     = !empty($order_info['payment_zone']) ? $order_info['payment_zone'] : "NOT PROVIDED";
            $order_info_payment_city     = !empty($order_info['payment_city']) ? $order_info['payment_city'] : $order_info_payment_zone;
            $order_info_payment_country  = !empty($order_info['payment_country']) ? $order_info['payment_country'] : "NOT PROVIDED";
            $order_info_payment_postcode = !empty($order_info['payment_postcode']) ? $order_info['payment_postcode'] : "NOT PROVIDED";
            $order_info_ip               = !empty($order_info['ip']) ? $order_info['ip'] : "NOT PROVIDED";

            $buyer->setCity($order_info_payment_city);
            $buyer->setCountry($order_info_payment_country);
            $buyer->setZipCode($order_info_payment_postcode);
            $buyer->setIp($order_info_ip);

            $customer_identity_number = str_pad($order_info['customer_id'], 11, '0', STR_PAD_LEFT);

            $buyer->setIdentityNumber($customer_identity_number);

            if ($order_info['customer_id'] > 0) {
                $customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int) $this->session->data['customer_id'] . "' AND status = '1'");
                $added_date     = !empty($customer_query->row['date_added']) ? $customer_query->row['date_added'] : "NOT PROVIDED";

                $buyer->setRegistrationDate($added_date);
            }

            $request->setBuyer($buyer);

            $billing_address = new \Iyzipay\Client\Ecom\Payment\Dto\EcomPaymentBillingAddressDto();
            $billing_address->setContactName($order_info_firstname);
            $billing_address->setCity($order_info_payment_city);
            $billing_address->setCountry($order_info_payment_country);
            $billing_address->setAddress($customer_address);
            $billing_address->setZipCode($order_info_payment_postcode);

            $request->setBillingAddress($billing_address);

            $customer_shipping_address1 = !empty($order_info['shipping_address_1']) ? $order_info['shipping_address_1'] : $order_info['payment_address_1'];
            $customer_shipping_address2 = !empty($order_info['shipping_address_2']) ? $order_info['shipping_address_2'] : $order_info['payment_address_2'];
            $customer_shipping_address  = trim($customer_shipping_address1 . " " . $customer_shipping_address2);
            $customer_shipping_address  = !empty($customer_shipping_address) ? $customer_shipping_address : "NOT PROVIDED";

            $shipping_name     = !empty($order_info['shipping_firstname']) ? $order_info['shipping_firstname'] : $order_info_firstname;
            $shipping_zone     = !empty($order_info['shipping_zone']) ? $order_info['shipping_zone'] : $order_info_payment_zone;
            $shipping_city     = !empty($order_info['shipping_city']) ? $order_info['shipping_city'] : $shipping_zone;
            $shipping_country  = !empty($order_info['shipping_country']) ? $order_info['shipping_country'] : $order_info_payment_country;
            $shipping_zip_code = !empty($order_info['shipping_postcode']) ? $order_info['shipping_postcode'] : $order_info_payment_postcode;
            $shipping_address  = new \Iyzipay\Client\Ecom\Payment\Dto\EcomPaymentShippingAddressDto();

            $shipping_address->setContactName($shipping_name);
            $shipping_address->setCity($shipping_city);
            $shipping_address->setCountry($shipping_country);
            $shipping_address->setAddress($customer_shipping_address);
            $shipping_address->setZipCode($shipping_zip_code);

            $request->setShippingAddress($shipping_address);

            $results = $this->model_extension_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            $allowed_order_total_extensions = array(
                'reward',
                'shipping',
                'coupon',
                'voucher'
            );

            $allowed_order_total_extensions_sort = array();

            foreach ($results as $result) {
                if ($this->config->get($result['code'] . '_status') && in_array($result['code'], $allowed_order_total_extensions)) {
                    $allowed_order_total_extensions_sort[] = $result['code'];
                }
            }

            $items    = array();
            $products = $this->cart->getProducts();

            $product_prices  = $total_discounts = $voucher_amount = $voucher_per_item = $applied_voucher_amount = 0;
            $shipping_amount = $total_shipping_charge = $points_total = 0;
            $has_coupon      = $has_voucher = $has_rewards = $has_shipping = false;
            $coupon_info     = $voucher_info = $shipping_info = array();

            $purchase_voucher_amount = 0;

            $sub_total = $this->cart->getSubTotal();

            //get coupon detail
            if (isset($this->session->data['coupon'])) {
                $has_coupon = true;

                $coupon = $this->session->data['coupon'];

                $this->load->model('checkout/coupon');

                $coupon_info = $this->model_checkout_coupon->getCoupon($coupon);

                if ($coupon_info['type'] == 'F') {
                    $coupon_info['discount'] = min($coupon_info['discount'], $sub_total);
                }
            }

            //get voucher detail
            if (isset($this->session->data['voucher'])) {
                $has_voucher = true;

                $voucher = $this->session->data['voucher'];

                $this->load->model('checkout/voucher');

                $voucher_info = $this->model_checkout_voucher->getVoucher($voucher);

                $voucher_info['amount'] = min($voucher_info['amount'], $sub_total);
            }

            //get points detail
            $points = $this->customer->getRewardPoints();

            if (isset($this->session->data['reward']) && $this->session->data['reward'] <= $points) {
                $has_rewards = true;

                foreach ($this->cart->getProducts() as $product) {
                    if ($product['points']) {
                        $points_total += $product['points'];
                    }
                }
            }

            //get shipping detail
            if ($this->cart->hasShipping() && isset($this->session->data['shipping_method'])) {
                $has_shipping    = true;
                $shipping_info   = $this->session->data['shipping_method'];
                $shipping_amount = $shipping_info['cost'];

                if ($shipping_info['tax_class_id']) {
                    $shipping_info['tax'] = $this->tax->getRates($shipping_info['cost'], $shipping_info['tax_class_id']);
                }
            }

            foreach ($products as $product) {
                $discount = 0;

                $item = new \Iyzipay\Client\Ecom\Payment\Dto\EcomPaymentBasketItemDto();

                $product_id   = !empty($product['product_id']) ? $product['product_id'] : 0;
                $product_name = !empty($product['name']) ? $product['name'] : "NOT PROVIDED";

                $item->setId($product_id);
                $item->setName($product_name);
                $item->setItemType(\Iyzipay\Client\Ecom\Payment\Enumtype\BasketItemRequestType::PHYSICAL);

                $product_tax = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

                $total_product_price = $product_tax * $product['quantity'];

                foreach ($allowed_order_total_extensions_sort as $key => $code) {
                    if ($code == "coupon" && $has_coupon) { //process coupon
                        if (!$coupon_info['product']) {
                            $status = true;
                        } else {
                            if (in_array($product['product_id'], $coupon_info['product'])) {
                                $status = true;
                            } else {
                                $status = false;
                            }
                        }

                        if ($status) {
                            if ($coupon_info['type'] == 'F') {
                                $discount = $coupon_info['discount'] * ($product['total'] / $sub_total);
                            } elseif ($coupon_info['type'] == 'P') {
                                $discount = $product['total'] / 100 * $coupon_info['discount'];
                            }

                            if ($product['tax_class_id']) {
                                $tax_rates = $this->tax->getRates($product['total'] - ($product['total'] - $discount), $product['tax_class_id']);

                                foreach ($tax_rates as $tax_rate) {
                                    if ($tax_rate['type'] == 'P') {
                                        $discount += $tax_rate['amount'];
                                    }
                                }
                            }
                        }

                        $total_product_price -= $discount;

                        $total_discounts += $discount;
                    } else {
                        if ($code == "voucher" && $has_voucher) { //process voucher
                            $discount = $voucher_info['amount'] * ($product['total'] / $sub_total);

                            $total_product_price -= $discount;

                            $total_discounts += $discount;
                        } else {
                            if ($code == "reward" && $has_rewards && !empty($product['points'])) { //process reward points
                                $discount = $product['total'] * ($this->session->data['reward'] / $points_total);

                                if ($product['tax_class_id']) {
                                    $tax_rates = $this->tax->getRates($product['total'] - ($product['total'] - $discount), $product['tax_class_id']);

                                    foreach ($tax_rates as $tax_rate) {
                                        if ($tax_rate['type'] == 'P') {
                                            $discount += $tax_rate['amount'];
                                        }
                                    }
                                }

                                $total_product_price -= $discount;

                                $total_discounts += $discount;
                            } else {
                                if ($code == "shipping" && $has_shipping) { //process shipping charges
                                    $per_item_shipping = $shipping_amount * ($product['total'] / $sub_total);

                                    $total_product_price += $per_item_shipping;

                                    $total_shipping_charge += $per_item_shipping;

                                    if ($shipping_info['tax_class_id']) {
                                        $tax_rates = $shipping_info['tax'];

                                        foreach ($tax_rates as $tax_rate) {
                                            $shipping_tax_amount = 0;

                                            if ($tax_rate['type'] == 'F') {
                                                $shipping_tax_amount = $tax_rate['rate'] * ($product['total'] / $sub_total);
                                            } elseif ($tax_rate['type'] == 'P') {
                                                $shipping_tax_amount = $per_item_shipping / 100 * $tax_rate['rate'];
                                            }

                                            $total_product_price += $shipping_tax_amount;
                                            $total_shipping_charge += $shipping_tax_amount;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $total_product_price *= $order_info['currency_value'];

                $total_product_price = round($total_product_price, 2);

                $product_prices += $total_product_price;

                $item->setPrice($total_product_price);

                $product_categories = $this->model_catalog_product->getCategories($product['product_id']);

                if ($product_categories) {
                    foreach ($product_categories as $key => $product_category_data) {
                        if ($key > 1) {
                            break;
                        }

                        $category_info = $this->model_catalog_category->getCategory($product_category_data['category_id']);

                        $set_category = 'setCategory' . ($key + 1);

                        $category_name = !empty($category_info['name']) ? $category_info['name'] : "NOT PROVIDED";

                        $item->$set_category($category_name);
                    }
                } else {
                    $item->setCategory1('NOT PROVIDED');
                }

                if ($total_product_price > 0) {
                    $items[] = $item;
                }
            }

            $exchange_rate = $this->currency->getValue('TRY');

            $tax_total = $this->cart->getTaxes();

            foreach ($tax_total as $value) {
                $sub_total += $value;
            }

            $sub_total -= $total_discounts;
            $sub_total += $total_shipping_charge;
            $sub_total *= $exchange_rate;
            $sub_total = round($sub_total, 2);

            //adjust sub total and product prices
            if ($sub_total != $product_prices) {
                $last_item_index  = end(array_keys($items));
                $last_item_object = $items[$last_item_index];
                $item_price       = $last_item_object->getPrice();

                $amount_difference = $sub_total - $product_prices;

                $new_price = $item_price + $amount_difference;

                $last_item_object->setPrice($new_price);

                $items[$last_item_index] = $last_item_object;

                if ($new_price <= 0) {
                    unset($items[$last_item_index]);
                }
            }

            //adjust final payable amount and product prices
            if ($cart_total_amount != $sub_total) {
                $last_item_index  = end(array_keys($items));
                $last_item_object = $items[$last_item_index];
                $item_price       = $last_item_object->getPrice();

                $amount_difference = $cart_total_amount - $sub_total;
                $new_price         = $item_price + $amount_difference;

                $sub_total += $amount_difference;

                $last_item_object->setPrice($new_price);

                $items[$last_item_index] = $last_item_object;

                if ($new_price <= 0) {
                    unset($items[$last_item_index]);
                }
            }

            $items = array_values($items);

            $request->setPrice($sub_total);

            $request->setBasketItems($items);

            if (function_exists('curl_version')) {

                $this->load->model('payment/iyzico_checkout_installment');

                $save_data_array = array(
                    'order_id'           => $order_id,
                    'item_id'            => 0,
                    'transaction_status' => 'in process',
                    'date_created'       => date('Y-m-d H:i:s'),
                    'date_modified'      => date('Y-m-d H:i:s'),
                    'api_request'        => $request->toJsonString(),
                    'api_response'       => '',
                    'request_type'       => 'payment_form_initialization',
                    'note'               => ''
                );

                $create_order_entry_id = $this->model_payment_iyzico_checkout_installment->createOrderEntry($save_data_array);

                $response = $client->initializeCheckoutForm($request);

                $update_data_array = array(
                    'date_modified'        => date('Y-m-d H:i:s'),
                    'api_response'         => $response->getRawResult(),
                    'transaction_status'   => $response->getStatus(),
                    'processing_timestamp' => date('Y-m-d H:i:s', $response->getSystemTime() / 1000)
                );

                $this->model_payment_iyzico_checkout_installment->updateOrderEntry($update_data_array, $create_order_entry_id);

                $data['siteUrl'] = $this->getSiteUrl();

                if ($response->getStatus() == "success") {
                    $data['checkout_form_content'] = $response->getCheckoutFormContent();
                } else {
                    $data['error'] = !is_null($response->getErrorMessage()) ? $response->getErrorMessage() : $response->getErrorCode();
                }
            } else {
                $data['error'] = $this->language->get("Error_message_curl");
            }
        } catch (\Exception $exc) {
            $data['error'] = $exc->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    /**
     * Get site url
     *
     */
    public function getSiteUrl()
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
            $site_url = HTTPS_SERVER;
        } else {
            $site_url = HTTP_SERVER;
        }

        return $site_url;
    }

    /**
     * Get valid slug
     *
     */
    public function getServerConnectionSlug()
    {
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $connection = 'SSL';
        } else {
            $connection = 'NONSSL';
        }

        return $connection;
    }

    /**
     * Callback url for iyzico transaction response
     *
     */
    public function callback()
    {
        error_reporting(0);

        $server_conn_slug = $this->getServerConnectionSlug();

        $this->load->language('payment/iyzico_checkout_installment');
        $this->load->model('payment/iyzico_checkout_installment');
        $this->load->model('checkout/order');

        $postData          = $this->request->post;
        $message           = '';
        $get_auth_table_id = 0;

        try {
            $save_data_array = array(
                'order_id'             => 0,
                'transaction_status'   => '',
                'date_created'         => date('Y-m-d H:i:s'),
                'date_modified'        => date('Y-m-d H:i:s'),
                'api_request'          => '',
                'api_response'         => json_encode($postData),
                'request_type'         => 'post_callback',
                'processing_timestamp' => date('Y-m-d H:i:s'),
                'note'                 => ''
            );

            $post_callback_id = $this->model_payment_iyzico_checkout_installment->createOrderEntry($save_data_array);

            if (empty($postData['token'])) {
                throw new \Exception($this->language->get('invalid_request'));
            }

            $this->load->model('payment/iyzico_checkout_installment');

            $order_id = $this->session->data['order_id'];

            IyzipayBootstrap::init();

            $merchant_api_id     = $this->config->get('iyzico_checkout_installment_api_id_live');
            $merchant_secret_key = $this->config->get('iyzico_checkout_installment_secret_key_live');

            $configuration = new \Iyzipay\Client\Configuration\ClientConfiguration();

            $configuration->setApiKey($merchant_api_id);
            $configuration->setSecretKey($merchant_secret_key);
            $configuration->setBaseUrl($this->base_url);

            $client = \Iyzipay\Client\Service\EcomCheckoutFormServiceClient::fromConfiguration($configuration);

            $request = new \Iyzipay\Client\Ecom\Payment\Request\EcomRetrievePaymentCheckoutFormAuthRequest();
            $request->setLocale(\Iyzipay\Client\RequestLocaleType::TR);
            $request->setToken($postData['token']);

            $save_data_array = array(
                'order_id'           => 0,
                'item_id'            => 0,
                'transaction_status' => 'in process',
                'date_created'       => date('Y-m-d H:i:s'),
                'date_modified'      => date('Y-m-d H:i:s'),
                'api_request'        => $request->toJsonString(),
                'api_response'       => '',
                'request_type'       => 'get_auth',
                'note'               => ''
            );

            $get_auth_table_id = $this->model_payment_iyzico_checkout_installment->createOrderEntry($save_data_array);

            $response = $client->getAuthResponse($request);

            $update_data_array = array(
                'date_modified'        => date('Y-m-d H:i:s'),
                'api_response'         => $response->getRawResult(),
                'transaction_status'   => $response->getStatus(),
                'processing_timestamp' => date('Y-m-d H:i:s', $response->getSystemTime() / 1000)
            );

            $this->model_payment_iyzico_checkout_installment->updateOrderEntry($update_data_array, $get_auth_table_id);

            if ($response->getStatus() == "failure") {
                throw new \Exception($response->getErrorMessage());
            }

            if ($response->getPaymentStatus() == "FAILURE") {
                throw new \Exception($response->getErrorMessage());
            }

            $basketId  = $response->getBasketId();
            $paymentId = $response->getPaymentId();

            if (!empty($paymentId)) {
                $message .= 'Payment ID: ' . $paymentId . "\n";
            }

            $explode_basket_id = explode("_", $basketId);
            $response_order_id = end($explode_basket_id);

            $update_data_array = array(
                'order_id'             => $response_order_id,
                'date_modified'        => date('Y-m-d H:i:s'),
                'processing_timestamp' => date('Y-m-d H:i:s', $response->getSystemTime() / 1000)
            );

            $this->model_payment_iyzico_checkout_installment->updateOrderEntry($update_data_array, $get_auth_table_id);
            $this->model_payment_iyzico_checkout_installment->updateOrderEntry($update_data_array, $post_callback_id);

            if (empty($response_order_id) || $response_order_id != $order_id) {
                throw new \Exception($this->language->get('invalid_order'));
            }

            $order_info = $this->model_checkout_order->getOrder($response_order_id);

            if (!empty($order_info['order_status_id']) && $order_info['order_status'] != null) {
                throw new \Exception($this->language->get('order_already_exists'));
            }

            $installment = $response->getInstallment();

            if ($installment > 1) {
                $this->load->model('checkout/order');
                $order_total               = (array) $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int) $order_id . "' AND code = 'total' ");
                $last_sort_value           = $order_total['row']['sort_order'] - 1;
                $exchange_rate             = $this->currency->getValue('TRY');
                $new_amount                = str_replace(',', '', $response->getPaidPrice());
                $old_amount                = str_replace(',', '', $order_info['total'] * $order_info['currency_value']);
                $installment_fee_variation = ($new_amount - $old_amount) / $exchange_rate;

                $this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "',code = '" . $this->db->escape('iyzico_checkout_installment_fee') . "',  title = '" . $this->db->escape('Installment Charge') . "' , `value` = '" .
                                 (float) $installment_fee_variation . "', sort_order = '" . (int) $last_sort_value . "'");

                $order_total_data = (array) $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int) $order_id . "' AND code != 'total' ");
                $calculate_total  = 0;

                foreach ($order_total_data['rows'] as $row) {
                    $calculate_total += $row['value'];
                }

                $this->db->query("UPDATE " . DB_PREFIX . "order_total SET  `value` = '" . (float) $calculate_total . "' WHERE order_id = '$order_id' AND code = 'total' ");

                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET total = '" . $calculate_total . "' WHERE order_id = '" . (int) $order_id . "'");

                $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('iyzico_checkout_installment_order_status_id'), $message, false);

                $comment = $response->getCardFamily() . ' - ' . $response->getInstallment() . '  Taksit';

                $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int) $order_id . "', order_status_id = '" .
                                 $this->config->get('iyzico_checkout_installment_order_status_id') . "', notify = '0', comment = '" .
                                 $this->db->escape($comment) . "', date_added = NOW()");
            } else {
                $this->model_checkout_order->addOrderHistory($response_order_id, $this->config->get('iyzico_checkout_installment_order_status_id'), $message, false);
            }

            $item_transactions = $response->getItemTransactions();

            foreach ($item_transactions as $item_transaction) {
                $item_data_array = array(
                    'order_id'               => $order_id,
                    'paid_price'             => $item_transaction->getPaidPrice(),
                    'item_id'                => $item_transaction->getItemId(),
                    'payment_transaction_id' => $item_transaction->getPaymentTransactionId(),
                    'total_refunded'         => 0
                );

                $this->model_payment_iyzico_checkout_installment->createRefundItemEntry($item_data_array);
            }

            $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));
        } catch (\Exception $ex) {
            $resp_msg = $ex->getMessage();
            $resp_msg = !empty($resp_msg) ? $resp_msg : $this->language->get('invalid_request');

            if (!empty($get_auth_table_id)) {
                $update_data_array = array(
                    'note'          => $resp_msg,
                    'date_modified' => date('Y-m-d H:i:s'),
                );

                $this->model_payment_iyzico_checkout_installment->updateOrderEntry($update_data_array, $get_auth_table_id);
            }

            $this->session->data['error'] = $resp_msg;

            $this->response->redirect($this->url->link('checkout/checkout', '', $server_conn_slug));
        }

        $this->response->redirect($this->url->link('checkout/error', '', $server_conn_slug));
    }

    /**
     * Handle error from callback
     *
     */
    public function error()
    {
        $this->language->load('payment/iyzico_checkout_installment');
        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        if (isset($this->request->get['route'])) {
            $data = $this->request->get;

            unset($data['_route_']);

            $route = $data['route'];

            unset($data['route']);

            $url = '';

            if ($data) {
                $url = '&' . urldecode(http_build_query($data, '', '&'));
            }

            $connection = $this->getServerConnectionSlug();

            $data['breadcrumbs'][] = array(
                'text'      => $this->language->get('heading_title'),
                'href'      => $this->url->link($route, $url, $connection),
                'separator' => $this->language->get('text_separator')
            );
        }

        if (!empty($error['response']['state']) && $error['response']['state'] == 'failed') {
            $data['heading_title'] = "Payment error...";
            $data['text_error']    = $error['response']['error_message'];
        }

        $data['button_continue'] = $this->language->get('button_continue');

        $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . '/1.1 404 Not Found');

        $data['continue'] = $this->url->link('checkout/checkout', '', 'SSL');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
            $this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/error/not_found.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view('default/template/error/not_found.tpl', $data));
        }
    }

    /**
     * Confirm order if cart total = 0
     *
     */
    public function confirm()
    {
        if ($this->session->data['payment_method']['code'] == 'iyzico_checkout_installment') {
            $this->load->model('checkout/order');
            $this->load->model('payment/iyzico_checkout_installment');

            $order_id = $this->session->data['order_id'];

            $order_info = $this->model_checkout_order->getOrder($order_id);

            if (!empty($order_info['order_status_id']) && $order_info['order_status'] != null) {
                throw new \Exception($this->language->get('order_already_exists'));
            }

            $save_data_array = array(
                'order_id'             => $order_id,
                'item_id'              => 0,
                'transaction_status'   => 'success',
                'date_created'         => date('Y-m-d H:i:s'),
                'date_modified'        => date('Y-m-d H:i:s'),
                'processing_timestamp' => date('Y-m-d H:i:s'),
                'api_request'          => '',
                'api_response'         => '',
                'request_type'         => 'get_auth',
                'note'                 => ''
            );

            $this->model_payment_iyzico_checkout_installment->createOrderEntry($save_data_array);

            $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('iyzico_checkout_installment_order_status_id'));
        }
    }
}
