<?php

set_time_limit(0);

class ModelPaymentHepsipay extends Model
{

    public function getInstallments()
    {
        $params = array(
            "type"      => 'Get',
            "get_param" => 'Installments',
            "language"  => 'tr',
            "client_ip" => $_SERVER['REMOTE_ADDR']
        );

        return $this->call($params);
    }

    public function getOneShotCommission()
    {
        $params = array(
            "type"                => 'Get',
            "get_param"           => 'Installments',
            "language"            => 'tr',
            "client_ip"           => $_SERVER['REMOTE_ADDR'],
            "one_shot_commission" => 1
        );

        return $this->call($params);
    }

    public function getOneShotTotal($total)
    {
        if ($this->config->get('hepsipay_installment_commission')) {
            $oneShotCommission = json_decode($this->getOneShotCommission(), true);
        } else {
            $oneShotCommission = 0;
        }

        if (!isset($oneShotCommission['data']['commission'])) {
            return $total;
        }

        $commission = $oneShotCommission['data']['commission'];
        $commission = str_replace('%', '', $commission);

        $total = $total + ($total * $commission / 100);

        return number_format($total, 2, '.', '');
    }

    public function getExtraInstallments()
    {
        //todo: hepsipay - extra inst
        $extraInstallmentsStatus = false;

        return json_encode([]);
    }

    public function get_card_info()
    {
        $params = array(
            "type"      => 'Get',
            "get_param" => 'Issuer',
            "bin"       => substr($this->request->post['cc_number'], 0, 6),//'435508',
            "language"  => 'tr',
            "client_ip" => $_SERVER['REMOTE_ADDR'],
        );

        return $this->call($params);
    }

    //save transaction history
    public function saveResponse($data)
    {
        if (!isset($data['transaction_id'])) {
            return;
        }

        $sql = "INSERT INTO `" . DB_PREFIX . "hepsipay_order` SET
          `order_id` = '" . $data['passive_data'] . "',
          `transaction_id` = '" . $data['transaction_id'] . "',
          `bank_id` = '" . $data['bank_id'] . "',
          `status` = '" . $data['status'] . "',
          `use3d` = '" . $data['use3d'] . "',
          `client_ip` = '" . $_SERVER['REMOTE_ADDR'] . "',
          `installments` = '" . $data['installments'] . "',
          `extra_installments` = '" . $data['extra_installments'] . "',
          `campaign_id` = '" . $data['campaign_id'] . "',
          `ErrorMSG` = '" . $data['ErrorMSG'] . "',
          `ErrorCode` = '" . $data['ErrorCode'] . "',
          `conversion_rate` = '" . $data['conversion_rate'] . "',
          `try_total` = '" . $data['total'] . "',
          `original` = '" . json_encode($data) . "',
          `date_added` = NOW()";

        $this->db->query($sql);
    }

    //send data to bank
    public function send()
    {
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $total = $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);

        if (isset($this->request->post['use3d'])) {
            $use3d = $this->request->post['use3d'];
        } else {
            $use3d = 0;
        }

        $force3D = $this->config->get('hepsipay_force_3dsecure_status');
        $use3d   = ($force3D) ? 1 : $use3d;

        if ($this->config->get('hepsipay_force_3dsecure_debit')) {
            $cardInfo = $this->get_card_info();
            $cardInfo = json_decode($cardInfo, true);

            if ($cardInfo['status']) {
                if ($cardInfo['data']['type'] != 'CREDIT') {
                    $use3d = true;
                }
            }
        }

        $bkmExist = (isset($this->request->post['useBKM']) && $this->request->post['useBKM']);

        if ($bkmExist) {
            $params = array(
                "type"               => 'Sale',
                "total"              => $total,
                "currency"           => $order_info['currency_code'],
                "language"           => 'tr',
                "client_ip"          => $_SERVER['REMOTE_ADDR'],
                "payment_title"      => 'Order #' . $order_info['order_id'],
                "return_url"         => $this->url->link('payment/hepsipay/callback'),
                "bank_id"            => 'BKMExpress',
                "customer_firstname" => $order_info['firstname'],
                "customer_lastname"  => $order_info['lastname'],
                "customer_email"     => $order_info['email'],
                "customer_phone"     => $order_info['telephone'],
                "passive_data"       => $order_info['order_id'],
            );
        } else {
            $params = array(
                "type"               => 'Sale',
                "total"              => $total,
                "cc_name"            => isset($this->request->post['cc_name']) ? $this->request->post['cc_name'] : '',
                "cc_number"          => isset($this->request->post['cc_number']) ? $this->request->post['cc_number'] : '',
                "cc_month"           => isset($this->request->post['cc_month']) ? $this->request->post['cc_month'] : '',
                "cc_year"            => isset($this->request->post['cc_year']) ? $this->request->post['cc_year'] : '',
                "cc_cvc"             => isset($this->request->post['cc_cvc']) ? $this->request->post['cc_cvc'] : '',
                "currency"           => $order_info['currency_code'],
                "language"           => 'tr',
                "client_ip"          => $_SERVER['REMOTE_ADDR'],
                "payment_title"      => 'Order #' . $order_info['order_id'],
                "use3d"              => $use3d,
                "return_url"         => $this->url->link('payment/hepsipay/callback'),
                "customer_firstname" => $order_info['firstname'],
                "customer_lastname"  => $order_info['lastname'],
                "customer_email"     => $order_info['email'],
                "customer_phone"     => $order_info['telephone'],
                "passive_data"       => $order_info['order_id'],
            );
        }

        if (isset($this->request->post['installments']) && !$bkmExist) {
            $params["installments"] = $this->request->post['installments'];
        } elseif (!$bkmExist) {
            $params["installments"] = 1;
        } else {
            $params["installments"] = ($this->config->get('hepsipay_installment_status')) ? 1 : 0;
        }

        if (isset($this->session->data['bank_id']) && $params["installments"] > 1) {
            $params["bank_id"] = $this->session->data['bank_id'];//[optional] set bank_id if the installments more than 1
        }

        if (isset($this->session->data['gateway']) && $params["installments"] > 1) {
            $params["gateway"] = $this->session->data['gateway'];//'160',//[optional] set bank_id if the installments more than 1
        }

        if (isset($this->request->post['campaign_id']) && $this->request->post['campaign_id'] != '' && $this->request->post['campaign_id']) {
            $params["campaign_id"] = $this->request->post['campaign_id'];//campaign_id for extra installments
        }

        if ($this->config->get('hepsipay_installment_commission')) {
            if (isset($this->session->data['installments'])) {
                $installmentsArr = $this->session->data['installments'];
            } else {
                $installmentsArr = [];
            }

            foreach ($installmentsArr as $installmentsItem) {
                if ($installmentsItem['count'] == $params["installments"] && isset($installmentsItem['commission']) && $params["installments"] > 1) {
                    $installmentsItem['commission'] = str_replace('%', '', $installmentsItem['commission']);

                    $params['total'] = $total + ($installmentsItem['commission'] * 0.01 * $params['total']);

                    $total = $this->currency->format($params['total'], $order_info['currency_code'], false, false);

                    break;
                }
            }
        }

        $params['total'] = number_format($params['total'], 2, '.', '');

        return $this->call($params);
    }

    public function call($params)
    {
        $merchantPassword = $this->config->get('hepsipay_password');

        $params["merchant"] = $this->config->get('hepsipay_username');//[mandatory]

        $api_url = $this->config->get('hepsipay_endpoint');

        //begin HASH calculation
        ksort($params);
        $hashString = "";

        foreach ($params as $key => $val) {
            $l = mb_strlen($val);
            if ($l) {
                $hashString .= $l . $val;
            }
        }

        $params["hash"] = hash_hmac("sha1", $hashString, $merchantPassword);
        //end HASH calculation

        $api_url = 'https://pluginmanager.hepsipay.com/portal/web/api/v1';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $response = curl_exec($ch);

        $curlerrcode = curl_errno($ch);
        $curlerr     = curl_error($ch);

        return $response;
    }

    public function getMethod($address, $total)
    {
        $this->load->language('payment/hepsipay');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('cod_geo_zone_id') . "' &&  country_id = '" . (int) $address['country_id'] . "' &&  (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('hepsipay_total') > 0 && $this->config->get('hepsipay_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('hepsipay_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'hepsipay',
                'title'      => $this->language->get('text_hepsipay'),
                'terms'      => '',
                'sort_order' => $this->config->get('hepsipay_sort_order')
            );
        }

        return $method_data;
    }
}
