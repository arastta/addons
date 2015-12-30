<?php
/**
 * @package        Arastta eCommerce
 * @copyright      Copyright (C) 2015 Arastta Association. All rights reserved. (arastta.org)
 * @credits        See CREDITS.txt for credits and other copyright notices.
 * @license        GNU General Public License version 3; see LICENSE.txt
 */
 
class ControllerPaymentTrPos extends Controller
{
    public function index()
    {
        $this->load->language('payment/trpos');

        $data = $this->language->all();

        $data['trpos_other_id']    = $this->config->get('trpos_other_id');

        $this->load->model('checkout/order');

        $order_total = $this->getTotal();

        $trpos_single_ratio = floatval($this->config->get('trpostotal_single_ratio'));

        if ($trpos_single_ratio > 0) {
            $trpos_single_title = $this->language->get('text_single_positive') . '(%' . $trpos_single_ratio . ')';
        } else {
            if ($trpos_single_ratio < 0) {
                $trpos_single_title = $this->language->get('text_single_negative') . '(%' . $trpos_single_ratio . ')';
            } else {
                $trpos_single_title = $this->language->get('text_no_commision') . '(%' . $trpos_single_ratio . ')';
            }
        }

        $trpos_total = $order_total + ($order_total * $trpos_single_ratio / 100);

        $data['single_order_total']  = $this->currency->format($trpos_total, $this->session->data['currency'], false, true);
        $data['trpos_single_title'] = $trpos_single_title;

        $data['banks'] = $this->config->get('trpos_banks_info');

        $new_banks = array();

        foreach ($data['banks'] as $bank) {

            if ($bank['status'] != 0) {

                $new_banks[$bank['bank_id']] = $bank;

                if (!empty($bank['instalment']) || $bank['instalment'] != '') {
                    $instalments = array();

                    $instalments = explode(';', $bank['instalment']);

                    foreach ($instalments as $instalment) {
                        $instalment_array = explode('=', $instalment);

                        $instalment_count = $instalment_array[0];
                        $instalment_ratio = $instalment_array[1];

                        $instalment_total = $order_total + ($order_total * $instalment_ratio) / 100;

                        if ($instalment_count != 0) {
                            $instalment_price = $instalment_total / $instalment_count;
                        } else {
                            $instalment_price = $order_total;
                        }

                        $instalment_total = $this->currency->format($instalment_total, $this->session->data['currency'], false, true);
                        $instalment_price = $this->currency->format($instalment_price, $this->session->data['currency'], false, true);

                        $new_banks[$bank['bank_id']]['instalments'][] = array('count' => $instalment_count,
                                                                              'ratio' => $instalment_ratio,
                                                                              'total' => $instalment_total,
                                                                              'price' => $instalment_price);
                    }
                }
            }
        }

        unset($data['banks']);

        $data['banks'] = $new_banks;

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/trpos_instalment.tpl')) {
            return $this->load->view($this->config->get('config_template') . '/template/payment/trpos_instalment.tpl', $data);
        } else {
            return $this->load->view('default/template/payment/trpos_instalment.tpl', $data);
        }
    }

    public function confirm()
    {
        if((isset($this->request->post['payment_method'])) && ($this->request->post['payment_method'] == 'trpos') && (isset($this->request->post['instalment']))) {
            $this->session->data['instalment'] = $this->request->post['instalment'];
            $bank_array = explode('_',$this->request->post['instalment']);
            $this->session->data['trpos_bank_id'] = $bank_array[0];
        }

        $this->load->language('payment/trpos');

        $data['text_credit_card'] = $this->language->get('text_credit_card');
        $data['text_loading']     = $this->language->get('text_loading');
        $data['text_3d_hosting']  = $this->language->get('text_3d_hosting');

        $data['entry_cc_owner']       = $this->language->get('entry_cc_owner');
        $data['entry_cc_number']      = $this->language->get('entry_cc_number');
        $data['entry_cc_expire_date'] = $this->language->get('entry_cc_expire_date');
        $data['entry_cc_cvv2']        = $this->language->get('entry_cc_cvv2');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back']    = $this->language->get('button_back');

        $data['months'] = array();

        for ($i = 1; $i <= 12; $i++) {
            $data['months'][] = array(
                'text'  => strftime('%B', mktime(0, 0, 0, $i, 1, 2000)),
                'value' => sprintf('%02d', $i)
            );
        }

        $today = getdate();

        $data['year_expire'] = array();

        for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
            $data['year_expire'][] = array(
                'text'  => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
                'value' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i))
            );
        }

        $data['cc_types'] = array();

        $data['cc_types'][] = array('text' => 'VISA', 'value' => '1');//VISA
        $data['cc_types'][] = array('text' => 'MasterCard', 'value' => '2');//MasterCard
        $data['cc_types'][] = array('text' => 'AMEX', 'value' => '3');//American Express

        $bank_id = $this->session->data['trpos_bank_id'];
        $bank = $this->getbank($bank_id);

        $data['payment_model'] = $bank['model'];

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/trpos.tpl')) {
            $this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/trpos.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view('default/template/payment/trpos.tpl', $data));
        }
    }

    public function helperload($helper)
    {
        $file       = DIR_SYSTEM . 'helper/trpos/adapter/' . $helper . '.php';
        $class_only = explode('/', $helper);
        $class      = preg_replace('/[^a-zA-Z0-9]/', '', $class_only[1]);

        if (file_exists($file)) {
            include_once($file);
            $this->registry->set('trpos_' . str_replace('/', '_', $class_only[1]), new $class($this->registry));
        } else {
            trigger_error('Error: Could not load trpos helper ' . $file . '!');
            exit();
        }
    }

    private function getbank($bank_id)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "trpos_bank WHERE bank_id = '" . (int) $bank_id . "'");

        return $query->row;
    }

    public function getTotal()
    {
        $order_total = 0;
        $order_data  = array();

        $order_data['totals'] = array();

        $total = 0;
        $taxes = $this->cart->getTaxes();

        $this->load->model('extension/extension');

        $sort_order = array();

        $results = $this->model_extension_extension->getExtensions('total');

        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            if ($this->config->get($result['code'] . '_status')) {
                $this->load->model('total/' . $result['code']);

                $this->{'model_total_' . $result['code']}->getTotal($order_data['totals'], $total, $taxes);
            }
        }

        $sort_order = array();

        foreach ($order_data['totals'] as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $order_data['totals']);

        foreach ($order_data['totals'] as $total) {
            if ($total['code'] == 'total') {
                $order_total = $total['value'];
            }
        }

        return $order_total;
    }

    public function send()
    {
        $this->load->model('checkout/order');
        $this->load->language('payment/trpos');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $banks   = $this->config->get('trpos_banks_info');
        $bank_id = $this->session->data['trpos_bank_id'];

        $trpos_bank  = array();
        $trpos_class = '';

        foreach ($banks as $bank) {
            if ($bank['bank_id'] == $bank_id) {
                $trpos_bank  = $bank;
                $trpos_class = $bank['method'] . '/' . $bank['method'] . $bank['model'];
            }
        }

        $this->helperload($trpos_class);

        if (isset($this->session->data['instalment'])) {
            $instalment_data  = explode('_', $this->session->data['instalment']);
            $instalment_array = explode('x', $instalment_data[1]);
            $instalment       = $instalment_array[0];
        } else {
            $instalment = 0;
        }

        $trpos_error = array();

        if ($trpos_bank['model'] != "3d_hosting" || $trpos_bank['model'] != "hosting") {
            $trpos_error = $this->validate();

            if (($this->request->server['REQUEST_METHOD'] == 'POST') && (empty($trpos_error))) {
                $trpos_bank['cc_owner']             = $this->request->post['cc_owner'];
                $trpos_bank['cc_number']            = $this->request->post['cc_number'];
                $trpos_bank['cc_cvv2']              = $this->request->post['cc_cvv2'];
                $trpos_bank['cc_expire_date_month'] = $this->request->post['cc_expire_date_month'];
                $trpos_bank['cc_expire_date_year']  = $this->request->post['cc_expire_date_year'];
                $trpos_bank['cc_type']              = $this->request->post['cc_type'];
            }
        }

        $json = array();

        if (!empty($trpos_error)) {
            $json['error'] = $this->language->get('error_fix') . PHP_EOL;

            foreach ($trpos_error as $error) {
                $json['error'] .= $error . PHP_EOL;
            }
        } else {
            $trpos_bank['customer_ip'] = $this->request->server['REMOTE_ADDR'];

            $trpos_bank['instalment'] = $instalment;

            if ($this->request->server['HTTPS']) {
                $trpos_bank['success_url'] = $this->url->link('payment/trpos/callback', '', 'SSL'); //bank will return here if payment successfully finishes;
                $trpos_bank['fail_url']    = $this->url->link('payment/trpos/callback', '', 'SSL'); //bank will return here if payment fails;
            } else {
                $trpos_bank['success_url'] = $this->url->link('payment/trpos/callback'); //bank will return here if payment successfully finishes;
                $trpos_bank['fail_url']    = $this->url->link('payment/trpos/callback'); //bank will return here if payment fails;
            }

            $trpos_bank['order_id']   = $this->session->data['order_id']; //unique order id
            $trpos_bank['total']      = $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);//total order amount
            $trpos_bank['mode']       = $this->config->get('trpos_mode');
            $trpos_bank['order_info'] = $order_info;
            $trpos_bank['products']   = $this->getOrderProducts();

            $method_response = array();
            $method_response = $this->{'trpos_' . $trpos_bank['method'] . $trpos_bank['model']}->methodResponse($trpos_bank);

            if (isset($method_response['form'])) {
                $json['form'] = $method_response['form'];
            } else {
                if (isset($method_response['redirect'])) {
                    $message = $method_response['message'];
                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('trpos_order_status_id'), $message, false);

                    $json['redirect'] = $this->url->link('checkout/success', '', 'SSL');

                    unset($this->session->data['instalment']);
                    unset($this->session->data['trpos_bank_id']);
                } else {
                    if (isset($method_response['error'])) {
                        $json['error'] = $method_response['error'];
                    } else {
                        if (isset($method_response['payu3d'])) {
                            $json['payu3d'] = $method_response['payu3d'];
                        }
                    }
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function callback()
    {
        $this->load->language('payment/trpos');

        $data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

        if (!$this->request->server['HTTPS']) {
            $data['base'] = $this->config->get('config_url');
        } else {
            $data['base'] = $this->config->get('config_ssl');
        }

        $data['language']  = $this->language->get('code');
        $data['direction'] = $this->language->get('direction');

        $data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

        $data['text_response']     = $this->language->get('text_response');
        $data['text_success']      = $this->language->get('text_success');
        $data['text_success_wait'] = sprintf($this->language->get('text_success_wait'), $this->url->link('checkout/success'));
        $data['text_failure']      = $this->language->get('text_failure');
        $data['text_failure_wait'] = sprintf($this->language->get('text_failure_wait'), $this->url->link('checkout/checkout', '', 'SSL'));

        $this->load->model('checkout/order');

        $bank_id = $this->session->data['trpos_bank_id'];
        $order_id = $this->session->data['order_id'];

        $bank_response = $this->request->post;
        $banks         = $this->config->get('trpos_banks_info');

        foreach ($banks as $bank) {
            if ($bank['bank_id'] == $bank_id) {
                $trpos_bank  = $bank;
                $trpos_class = $bank['method'] . '/' . $bank['method'] . $bank['model'];
            }
        }

        $this->helperload($trpos_class);

        $trpos_bank['order_info'] = $this->model_checkout_order->getOrder($order_id);

        $trpos_bank['products'] = $this->getOrderProducts();

        $method_response = array();
        $method_response = $this->{'trpos_' . $trpos_bank['method'] . $trpos_bank['model']}->bankResponse($bank_response, $trpos_bank);

        if ($method_response['result'] == 1) {
            $message = $method_response['message'] . $trpos_bank['name'];

            $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('trpos_order_status_id'), $message, false);

            unset($this->session->data['order_id']);
            unset($this->session->data['instalment']);
            unset($this->session->data['trpos_bank_id']);

            $data['continue'] = $this->url->link('checkout/success');
            $data['message']  = $method_response['message'];

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/trpos_success.tpl')) {
                $this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/trpos_success.tpl', $data));
            } else {
                $this->response->setOutput($this->load->view('default/template/payment/trpos_success.tpl', $data));
            }

        } else {
            unset($this->session->data['order_id']);
            unset($this->session->data['instalment']);
            unset($this->session->data['trpos_bank_id']);

            $data['continue'] = $this->url->link('checkout/checkout');
            $data['message']  = $method_response['message'];

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/trpos_failure.tpl')) {
                $this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/trpos_failure.tpl', $data));
            } else {
                $this->response->setOutput($this->load->view('default/template/payment/trpos_failure.tpl', $data));
            }
        }
    }

    public function getOrderProducts()
    {
        $order_data = array();

        foreach ($this->cart->getProducts() as $product) {
            $option_data = array();

            foreach ($product['option'] as $option) {
                $option_data[] = array(
                    'product_option_id'       => $option['product_option_id'],
                    'product_option_value_id' => $option['product_option_value_id'],
                    'option_id'               => $option['option_id'],
                    'option_value_id'         => $option['option_value_id'],
                    'name'                    => $option['name'],
                    'value'                   => $option['value'],
                    'type'                    => $option['type']
                );
            }

            $order_data['products'][] = array(
                'product_id' => $product['product_id'],
                'name'       => $product['name'],
                'model'      => $product['model'],
                'option'     => $option_data,
                'download'   => $product['download'],
                'quantity'   => $product['quantity'],
                'subtract'   => $product['subtract'],
                'price'      => $product['price'],
                'total'      => $product['total'],
                'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
                'reward'     => $product['reward']
            );
        }

        return $order_data;
    }

    protected function validate()
    {
        $this->load->language('payment/trpos');
        $trpos_error = array();

        if (utf8_strlen(trim($this->request->post['cc_owner'])) < 1) {
            $trpos_error['cc_owner'] = $this->language->get('error_cc_owner');
        }

        if ((utf8_strlen($this->request->post['cc_number']) < 15) || (utf8_strlen($this->request->post['cc_number']) > 16)) {
            $trpos_error['cc_number'] = $this->language->get('error_cc_number');
        }

        if (utf8_strlen($this->request->post['cc_cvv2']) != 3) {
            $trpos_error['cc_cvv2'] = $this->language->get('error_cc_cvv2');
        }

        $today = date("y-m-d H:i:s");
        $date  = $this->request->post['cc_expire_date_year'] . "-" . $this->request->post['cc_expire_date_month'] . "-31 00:00:00";

        if ($date < $today) {
            $trpos_error['cc_expire_date'] = $this->language->get('error_cc_expire_date');
        }

        if ($this->request->post['cc_type'] == 1 || $this->request->post['cc_type'] == 2) {
            $luhn = $this->is_valid_luhn($this->request->post['cc_number']);

            if ($luhn === false) {
                $trpos_error['cc_number_luhn'] = $this->language->get('error_cc_number_luhn');
            }
        }

        return $trpos_error;
    }

    protected function is_valid_luhn($number)
    {
        settype($number, 'string');

        $sumTable = array(
            array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
            array(0, 2, 4, 6, 8, 1, 3, 5, 7, 9)
        );

        $sum  = 0;
        $flip = 0;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $sum += $sumTable[$flip++ & 0x1][$number[$i]];
        }

        return ($sum % 10 === 0) ? true : false;
    }
}
