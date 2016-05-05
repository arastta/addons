<?php
/**
 * @package        Arastta eCommerce
 * @copyright      Copyright (C) 2015-2016 Arastta Association. All rights reserved. (arastta.org)
 * @license        GNU General Public License version 3; see LICENSE.txt
 */

require_once DIR_SYSTEM . "library/iyzico/IyzipayBootstrap.php";

class ControllerPaymentIyzicoCheckoutInstallment extends Controller
{
    private $error        = array();
    private $base_url     = "https://api.iyzipay.com/";
    private $order_prefix = "Arastta_";

    public function index()
    {
        $this->language->load('payment/iyzico_checkout_installment');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('iyzico_checkout_installment', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            if (isset($this->request->post['button']) and $this->request->post['button'] == 'save') {
                $this->response->redirect($this->url->link($this->request->get['route'], 'token=' . $this->session->data['token'], 'SSL'));
            }

            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $data = $this->language->all();

        $data['message']       = '';
        $data['error_warning'] = '';

        $error_data_array_key = array(
            'api_id_live',
            'secret_key_live'
        );

        foreach ($error_data_array_key as $key) {
            $data["error_{$key}"] = isset($this->error[$key]) ? $this->error[$key] : '';
        }

        $data['action'] = $this->url->link('payment/iyzico_checkout_installment', 'token=' . $this->session->data['token'], 'SSL');

        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        $merchant_keys_name_array = array(
            'iyzico_checkout_installment_api_id_live',
            'iyzico_checkout_installment_secret_key_live',
            'iyzico_checkout_installment_order_status_id',
            'iyzico_checkout_installment_status',
            'iyzico_checkout_installment_sort_order',
            'iyzico_checkout_installment_form_class',
            'iyzico_checkout_installment_cancel_order_status_id'
        );

        foreach ($merchant_keys_name_array as $key) {
            $data[$key] = isset($this->request->post[$key]) ? $this->request->post[$key] : $this->config->get($key);
        }

        if (isset($this->request->post['iyzico_checkout_installment_test'])) {
            $data['iyzico_checkout_installment_test'] = $this->request->post['iyzico_checkout_installment_test'];
        } else {
            $mode = $this->config->get('iyzico_checkout_installment_test');

            if (isset($mode) && $mode == 0) {
                $data['iyzico_checkout_installment_test'] = 0;
            } else {
                $data['iyzico_checkout_installment_test'] = 1;
            }
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/iyzico_checkout_installment.tpl', $data));
    }

    /**
     * Install necessary tables and configurations
     */
    public function install()
    {
        $this->load->model('payment/iyzico_checkout_installment');

        $this->model_payment_iyzico_checkout_installment->install();
    }

    /**
     * Uninstall necessary tables and configurations
     */
    public function uninstall()
    {
        $this->load->model('payment/iyzico_checkout_installment');

        $this->model_payment_iyzico_checkout_installment->uninstall();
    }

    /**
     * Display iyzico transactions in order details
     *
     */
    public function order()
    {
        $this->load->model('payment/iyzico_checkout_installment');
        $this->language->load('payment/iyzico_checkout_installment');

        $data = $this->language->all();

        $language_id = (int) $this->config->get('config_language_id');

        $order_id = (int) $this->request->get['order_id'];

        $data['token'] = $this->request->get['token'];

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "iyzico_order` WHERE `order_id` = '{$order_id}' ORDER BY `iyzico_order_id` DESC");

        $data['successful_cancel_counts']         = 0;
        $data['successful_refund_counts']         = 0;
        $data['same_date']                        = 'no';
        $data['iyzico_transactions']              = array();
        $data['order_id']                         = $order_id;
        $data['payment_success']                  = 'no';
        $data['display_cancel_option']            = 'no';
        $data['iyzico_transactions_refunds_data'] = array();

        foreach ($query->rows as $row) {
            switch ($row['request_type']) {
                case 'get_auth':
                    $row['transaction_type'] = $this->language->get('payment_transaction_type');

                    if ($row['transaction_status'] == "success") {
                        if (date('Y-m-d', strtotime($row['date_modified'])) == date('Y-m-d')) {
                            $data['same_date'] = 'yes';
                        }

                        $data['payment_success'] = 'yes';

                        if (!empty($row['api_response'])) {
                            $auth_response = json_decode($row['api_response'], true);

                            if (is_array($auth_response) && $auth_response['status'] == 'success' && $auth_response['paymentStatus'] == "SUCCESS") {
                                if (!empty($auth_response['paymentId'])) {
                                    $data['display_cancel_option'] = 'yes';
                                }
                            }
                        }
                    }

                    $data['iyzico_transactions'][] = $row;

                    break;
                case 'order_cancel':
                    if ($row['transaction_status'] == "success") {
                        $data['successful_cancel_counts']++;
                    }

                    $row['transaction_type']       = $this->language->get('cancel_transaction_type');
                    $data['iyzico_transactions'][] = $row;

                    break;
                case 'order_refund':
                    if ($row['transaction_status'] == "success") {
                        $data['successful_refund_counts']++;
                    }

                    $row['transaction_type']       = $this->language->get('refund_transaction_type');
                    $data['iyzico_transactions'][] = $row;

                    break;
            }
        }

        $this->load->model('sale/order');

        $order_details = $this->model_sale_order->getOrder($order_id);

        $refunded_transactions_query_string = "SELECT rf.*, pd.name FROM `" . DB_PREFIX . "iyzico_order_refunds` rf " .
            "LEFT JOIN `" . DB_PREFIX . "product_description` pd  ON pd.product_id = rf.item_id " .
            "WHERE `rf`.`order_id` = '{$order_id}' " .
            "AND `pd`.`language_id` = {$language_id} " .
            "AND (`rf`.`paid_price` != `rf`.`total_refunded`) " .
            "ORDER BY `iyzico_order_refunds_id` ASC";

        $refunded_transactions_query = $this->db->query($refunded_transactions_query_string);

        foreach ($refunded_transactions_query->rows as $refunded_item) {
            $refunded_item['paid_price_converted']     = $this->currency->format($refunded_item['paid_price'], $order_details['currency_code'], false);
            $refunded_item['total_refunded_converted'] = $this->currency->format($refunded_item['total_refunded'], $order_details['currency_code'], false);
            $refunded_item['remaining_refund_amount']  = $refunded_item['paid_price'] - $refunded_item['total_refunded'];
            $refunded_item['full_refunded']            = ($refunded_item['paid_price'] == $refunded_item['total_refunded']) ? 'yes' : 'no';

            $data['iyzico_transactions_refunds_data'][] = $refunded_item;
        }

        return $this->load->view('payment/iyzico_checkout_installment_order.tpl', $data);
    }

    /**
     * Cancel order API
     *
     */
    public function cancel()
    {
        error_reporting(0);

        $this->language->load('payment/iyzico_checkout_installment');
        $order_id = $this->request->post['order_id'];

        $data = array(
            'order_id' => $order_id,
            'success'  => 'false'
        );

        try {
            if (!$order_id) {
                throw new \Exception($this->language->get('error_invalid_order'));
            }

            $this->load->model('payment/iyzico_checkout_installment');

            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "iyzico_order` WHERE `order_id` = '{$order_id}' AND `request_type` = 'get_auth' ORDER BY `iyzico_order_id` DESC");

            if (!$query->row) {
                throw new \Exception($this->language->get('error_invalid_order'));
            }

            $transaction = $query->row;

            $auth_response = json_decode($transaction['api_response'], true);

            if (empty($auth_response)) {
                throw new \Exception($this->language->get('error_invalid_order'));
            }

            if ($auth_response['status'] == 'success' && $auth_response['paymentStatus'] == "SUCCESS") {
                $payment_id = $auth_response['paymentId'];
            } else {
                throw new \Exception($this->language->get('error_invalid_order'));
            }

            IyzipayBootstrap::init();

            $merchant_api_id     = $this->config->get('iyzico_checkout_installment_api_id_live');
            $merchant_secret_key = $this->config->get('iyzico_checkout_installment_secret_key_live');

            $configuration = new \Iyzipay\Client\Configuration\ClientConfiguration();
            $configuration->setApiKey($merchant_api_id);
            $configuration->setSecretKey($merchant_secret_key);
            $configuration->setBaseUrl($this->base_url);

            $client = \Iyzipay\Client\Service\EcomPaymentServiceClient::fromConfiguration($configuration);

            $request = new \Iyzipay\Client\Basic\Payment\Request\PaymentCancelRequest();
            $request->setLocale(\Iyzipay\Client\RequestLocaleType::TR);
            $request->setConversationId(uniqid($this->order_prefix) . "_cancel_" . $order_id);
            $request->setPaymentId($payment_id);
            $request->setIp($this->request->server['REMOTE_ADDR']);

            $save_data_array = array(
                'order_id'           => $order_id,
                'transaction_status' => 'in process',
                'date_created'       => date('Y-m-d H:i:s'),
                'date_modified'      => date('Y-m-d H:i:s'),
                'api_request'        => $request->toJsonString(),
                'api_response'       => '',
                'request_type'       => 'order_cancel',
                'note'               => ''
            );

            $cancel_transaction_id = $this->model_payment_iyzico_checkout_installment->createOrderEntry($save_data_array);

            $response = $client->cancel($request);

            $update_data_array = array(
                'date_modified'        => date('Y-m-d H:i:s'),
                'api_response'         => $response->getRawResult(),
                'transaction_status'   => $response->getStatus(),
                'processing_timestamp' => date('Y-m-d H:i:s', $response->getSystemTime() / 1000),
                'note'                 => ($response->getStatus() == "failure") ? $response->getErrorMessage() : $this->language->get('cancel_done_success')
            );

            $this->model_payment_iyzico_checkout_installment->updateOrderEntry($update_data_array, $cancel_transaction_id);

            if ($response->getStatus() == "failure") {
                throw new \Exception($response->getErrorMessage());
            }

            $this->_addhistory($order_id, $this->config->get('iyzico_checkout_installment_cancel_order_status_id'), $data['message']);

            $data['message'] = $this->language->get('cancel_done_success');
            $data['success'] = "true";
        } catch (\Exception $ex) {
            $data['message'] = $ex->getMessage();
        }

        echo json_encode($data);
    }

    /**
     * Full/Partial Refund API
     *
     */
    public function refund()
    {
        error_reporting(0);

        $this->language->load('payment/iyzico_checkout_installment');

        $order_id    = (int) $this->request->post['order_id'];
        $language_id = (int) $this->config->get('config_language_id');

        $data = array(
            'order_id' => $order_id,
            'success'  => 'false'
        );

        try {
            if (!$order_id) {
                throw new \Exception($this->language->get('error_invalid_order'));
            }

            $this->load->model('sale/order');
            $this->load->model('payment/iyzico_checkout_installment');

            $item_id       = (int) $this->request->post['item_id'];
            $amount        = (double) $this->request->post['amount'];
            $order_details = $this->model_sale_order->getOrder($order_id);

            if (!$order_details) {
                throw new \Exception($this->language->get('error_invalid_order'));
            }

            $refunded_transactions_query_string = "SELECT * FROM `" . DB_PREFIX . "iyzico_order_refunds` " . "WHERE `order_id` = '{$order_id}' AND `item_id` = '{$item_id}'";

            $refunded_transactions_query = $this->db->query($refunded_transactions_query_string);

            if (!$refunded_transactions_query->num_rows) {
                throw new \Exception($this->language->get('error_invalid_order'));
            }

            $refund_data      = $refunded_transactions_query->row;
            $remaining_amount = (double) $refund_data['paid_price'] - (double) $refund_data['total_refunded'];

            $diff = (string) $amount - (string) $remaining_amount;

            if ($diff > 0) {
                throw new \Exception(sprintf($this->language->get('request_amount_not_greater_than'), $remaining_amount));
            }

            IyzipayBootstrap::init();

            $merchant_api_id     = $this->config->get('iyzico_checkout_installment_api_id_live');
            $merchant_secret_key = $this->config->get('iyzico_checkout_installment_secret_key_live');

            $configuration = new \Iyzipay\Client\Configuration\ClientConfiguration();

            $configuration->setApiKey($merchant_api_id);
            $configuration->setSecretKey($merchant_secret_key);
            $configuration->setBaseUrl($this->base_url);

            $client = \Iyzipay\Client\Service\EcomPaymentServiceClient::fromConfiguration($configuration);

            $request = new \Iyzipay\Client\Basic\Payment\Request\PaymentRefundRequest();

            $request->setLocale(\Iyzipay\Client\RequestLocaleType::TR);
            $request->setConversationId(uniqid($this->order_prefix) . "_refund_{$order_id}_{$item_id}");
            $request->setPaymentTransactionId($refund_data['payment_transaction_id']);
            $request->setPrice($amount);
            $request->setIp($this->request->server['REMOTE_ADDR']);

            $product_data_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_description` " . "WHERE `product_id` = '{$item_id}' " . "AND `language_id` = '{$language_id}'");
            $product_data       = $product_data_query->row;

            $amount_formated        = $this->currency->format($amount, $order_details['currency_code'], false);
            $success_refund_message = sprintf($this->language->get('refund_done_success'), $item_id, $product_data['name'], $amount_formated);

            $save_data_array = array(
                'order_id'           => $order_id,
                'transaction_status' => 'in process',
                'date_created'       => date('Y-m-d H:i:s'),
                'date_modified'      => date('Y-m-d H:i:s'),
                'api_request'        => $request->toJsonString(),
                'api_response'       => '',
                'item_id'            => $item_id,
                'request_type'       => 'order_refund',
                'note'               => ''
            );

            $refund_transaction_id = $this->model_payment_iyzico_checkout_installment->createOrderEntry($save_data_array);

            $response = $client->refund($request);

            $update_data_array = array(
                'date_modified'        => date('Y-m-d H:i:s'),
                'api_response'         => $response->getRawResult(),
                'transaction_status'   => $response->getStatus(),
                'processing_timestamp' => date('Y-m-d H:i:s', $response->getSystemTime() / 1000),
                'note'                 => ($response->getStatus() == "failure") ? $response->getErrorMessage() : $success_refund_message
            );

            $this->model_payment_iyzico_checkout_installment->updateOrderEntry($update_data_array, $refund_transaction_id);

            if ($response->getStatus() == "failure") {
                throw new \Exception($response->getErrorMessage());
            }

            $new_refunded_total = (double) $refund_data['total_refunded'] + $amount;

            $update_refund_query_string = "UPDATE `" . DB_PREFIX . "iyzico_order_refunds` " . "SET `total_refunded` = '{$new_refunded_total}' " . "WHERE `order_id` = '{$order_id}' AND `item_id` = '{$item_id}'";

            $this->db->query($update_refund_query_string);

            $this->_addhistory($order_id, $order_details['order_status_id'], $success_refund_message);

            $data['message'] = $success_refund_message;
            $data['success'] = "true";
        } catch (\Exception $ex) {
            $data['message'] = $ex->getMessage();
        }

        echo json_encode($data);
    }

    /**
     * Validate admin settings form
     *
     */
    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/iyzico_checkout_installment')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post["iyzico_checkout_installment_api_id_live"])) {
            $this->error['api_id_live'] = $this->language->get("error_api_id_live");
        }

        if (empty($this->request->post["iyzico_checkout_installment_secret_key_live"])) {
            $this->error['secret_key_live'] = $this->language->get("error_secret_key_live");
        }

        return !$this->error;
    }

    /**
     * Add history after cancel and refund api execution.
     *
     */
    protected function _addhistory($order_id, $order_status_id, $comment)
    {
        error_reporting(0);

        $this->load->model('user/api');

        $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

        $api_key = ($api_info) ? $api_info['key'] : '';

        $site_url = $this->getSiteUrl();

        $api_login_response = $this->_curlCall(array(
                                                   'key' => $api_key
                                               ),
                                               $site_url . 'index.php?route=api/login'
        );

        if (empty($api_login_response)) {
            return false;
        }

        $api_login_data = json_decode($api_login_response, true);

        if (empty($api_login_data['token'])) {
            return false;
        }

        $token = $api_login_data['token'];

        $this->_curlCall(array(
                             'order_status_id' => $order_status_id,
                             'notify'          => 1,
                             'comment'         => $comment
                         ),
                         $site_url . "index.php?route=api/order/history&token={$token}&order_id={$order_id}"
        );

        return true;
    }

    /**
     * Get site url
     *
     */
    public function getSiteUrl()
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
            $site_url = HTTPS_CATALOG;
        } else {
            $site_url = HTTP_CATALOG;
        }

        return $site_url;
    }

    /**
     * Curl call to add history api calls
     *
     */
    protected function _curlCall($postFieldsArray, $apiUrl = null)
    {
        $fields_string = null;

        if (!empty($postFieldsArray)) {
            $fields_string = http_build_query($postFieldsArray);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, count($postFieldsArray));

        if (!is_null($fields_string)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }
}
