<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class ControllerPaymentPaytrCheckout extends Controller
{

    private $_productList = array();
    protected $category_full = array();
    protected $category_installment = array();

    public function index()
    {
        $this->load->language('payment/paytr_checkout');

        $data['code'] = $this->language->get('code');
        $data['text_credit_card'] = $this->language->get('text_credit_card');

        $data['callback_ok'] = $this->config->get('paytr_checkout_callback_page');

        if ( file_exists(DIR_TEMPLATE . $this->config->get('config_template').'/template/payment/paytr_checkout.tpl') ) {
            return $this->load->view( $this->config->get('config_template').'/template/payment/paytr_checkout.tpl', $data);
        } else {
            return $this->load->view('default/template/payment/paytr_checkout.tpl', $data);
        } 
    }

    public function category_parser()
    {
        $cats = $this->db->query("SELECT c.category_id AS 'id',  c.parent_id AS 'parent_id', cd.name AS 'name' FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY c.sort_order, cd.name ASC");

        $cats = $cats->rows; 

        $cat_tree = array(); 

        foreach ($cats as $key => $item) { 
            $this->category_full[$item['id']] = $item['parent_id']; 
        }
    }

    public function cat_search( $category_id = 0 )
    {
        if (!empty($this->category_full[ $category_id ]) AND array_key_exists($this->category_full[ $category_id ], $this->category_installment)) {
            $return = $this->category_installment[$this->category_full[$category_id]];
        } else {
            foreach ($this->category_full as $id => $parent) {
                if ($category_id == $id) {
                    if ($parent == 0) { 
                        $return = 0;
                    } elseif (array_key_exists($parent, $this->category_installment)) { 
                        $return = $this->category_installment[$parent];
                    } else { 
                        $return = $this->cat_search($parent); 
                    }
                } else {
                    $return = 0;
                }
            }
        }

        return $return;
    }

    public function gettoken()
    {
        if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) { die('404 NOT FOUND'); }

        $this->load->language('payment/paytr_checkout');

        $data['code'] = $this->language->get('code');
        $data['text_credit_card'] = $this->language->get('text_credit_card');
        $data['text_start_date'] = $this->language->get('text_start_date');
        $data['text_issue'] = $this->language->get('text_issue');
        $data['text_wait'] = $this->language->get('text_wait');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back'] = $this->language->get('button_back');

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder( $this->session->data['order_id'] );

        $products = $this->cart->getProducts();

        $user_basket = array();

        if ( $this->config->get('paytr_checkout_installment_number') != 13 ) {

            foreach( $products as $pro ) {
                $user_basket[] = array( $pro['name'], $pro['total'], $pro['quantity'] );
            }

            $merchant['max_installment']    = in_array( $this->config->get('paytr_checkout_installment_number') , range( 0, 12 ) ) ? $this->config->get('paytr_checkout_installment_number') : 0; 

        } else {
            $installment = array();

            $this->category_installment = $this->config->get('paytr_checkout_category_installment');

            foreach( $products as $pro ) {
                $user_basket[] = array( $pro['name'], $pro['total'], $pro['quantity'] );

                $query  = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . $pro['product_id'] . "' ORDER BY category_id ASC");

                foreach ( $query->rows as $id => $item ) {
                    if ( array_key_exists( $item['category_id'], $this->category_installment ) ) {
                        $installment[ $item['category_id'] ] = $this->category_installment[ $item['category_id'] ];
                    } else {
                        $installment[ $item['category_id'] ] = $this->cat_search( $item['category_id'] );
                    }
                }
            }

            $installment = min( array_diff( $installment, array( 0 ) ) );
            $merchant['max_installment'] = $installment ? $installment : 0;
        }

        $merchant['no_installment']     = ( $merchant['max_installment'] == 1 ) ? 1 : 0;

        /* PAYTR Entegrasyonu için Token Oluşturma Safhası */
        $merchant['id']                 = $this->config->get('paytr_checkout_merchant_id');
        $merchant['key']                = $this->config->get('paytr_checkout_merchant_key');
        $merchant['salt']               = $this->config->get('paytr_checkout_merchant_salt');

        $merchant['user_ip']            = $this->GetIP();
        $merchant['oid']                = uniqid().'PAYTRARASTTA'.$order_info['order_id'];
        $merchant['email']              = $order_info['email'];

        $merchant['payment_amount']     = ( $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) * 100 );

        $merchant['user_basket']        = base64_encode(json_encode( $user_basket ));

        $merchant['user_name']          = $order_info['payment_firstname'].' '.$order_info['payment_lastname'];
        $merchant['user_address']       = $order_info['payment_address_1'] . ' ' . $order_info['payment_address_2'] . ' ' . $order_info['payment_postcode'] . ' ' . $order_info['payment_city'] . ' ' . $order_info['payment_zone'] . ' ' . $order_info['payment_iso_code_3'];
        $merchant['user_phone']         = $order_info['telephone'];

        $currency = strtoupper( $order_info['currency_code'] );

        $hash_str       = $merchant['id'] .$merchant['user_ip'] .$merchant['oid'] .$merchant['email'] .$merchant['payment_amount'] .$merchant['user_basket'] .$merchant['no_installment']. $merchant['max_installment']. $currency;
        $paytr_token    = base64_encode(hash_hmac('sha256',$hash_str.$merchant['salt'],$merchant['key'],true));

        $post_vals      = array(
            'merchant_id'       => $merchant['id'],
            'user_ip'           => $merchant['user_ip'],
            'merchant_oid'      => $merchant['oid'],
            'email'             => $merchant['email'],
            'payment_amount'    => $merchant['payment_amount'],
            'paytr_token'       => $paytr_token,
            'user_basket'       => $merchant['user_basket'],
            'debug_on'          => 1,
            'no_installment'    => $merchant['no_installment'],
            'max_installment'   => $merchant['max_installment'],
            'user_name'         => $merchant['user_name'],
            'user_address'      => $merchant['user_address'],
            'user_phone'        => $merchant['user_phone'],
            'currency'          => $currency,
            'merchant_ok_url'   => $this->getSiteUrl() . 'index.php?route=checkout/success',
            'merchant_fail_url' => $this->getSiteUrl() . 'index.php?route=checkout/cart'
        );

        if ($this->config->get('paytr_checkout_lang') == 0) {
            $lang_arr = array( 
                'tr',
                'tr-tr',
                'tr_tr',
                'turkish',
                'turk',
                'türkçe',
                'turkce',
                'try',
                'tl'
            );

            $post_vals['lang'] = (in_array(strtolower($this->session->data['language'] , $lang_arr) ? 'tr': 'en'));
        } else {
            $post_vals['lang'] = ($this->config->get('paytr_checkout_lang') == 2 ? 'en' : 'tr');
        }

        if (function_exists('curl_version')) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1) ;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);

            $result = @curl_exec($ch);

            if (curl_errno($ch)) {
                die( "PAYTR IFRAME connection error. err: " . curl_error($ch) );
            }

            curl_close($ch);

            $result = json_decode( $result, 1 );

            if ($result['status'] == 'success') {
                $token = $result['token'];
            } else {
                die( "PAYTR IFRAME failed. reason:" . $result['reason'] );
            }

            echo '<script src="https://www.paytr.com/js/iframeResizer.min.js"></script><iframe src="https://www.paytr.com/odeme/guvenli/'.$token.'" id="paytriframe" frameborder="0" scrolling="no" style="width: 100%;"></iframe>';
            ?> 
            <script type="text/javascript">
            setInterval(function(){ 
                iFrameResize({},'#paytriframe');
            }, 1000);
            </script>
        <?php
        } else {
            $data['error'] = $this->language->get("Error_message_curl");
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput( json_encode( $data ) );

        die;
    }

    public function GetIP()
    {
        ini_set('display_errors', 0); 

        error_reporting(0);

        if(isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];

            if (strstr($ip, ',')) {
                $tmp = explode (',', $ip);
                $ip = trim($tmp[0]);
            }
        } else {
          $ip = $_SERVER["REMOTE_ADDR"];
        }

        return $ip;
    }

    public function log()
    {
        $logFile = fopen('log.txt', 'w');

        fwrite($logFile, "IP : " . $this->GetIP() . PHP_EOL);
        fwrite($logFile, "Zaman : " . date('d-m-Y H:i:s') . PHP_EOL);

        $i = 0;

        foreach( $_POST as $key => $val ) {
            $i++;

            fwrite($logFile, $i . ") " . $key . " = " . $val . PHP_EOL);
        }

        fclose($logFile);
    }

    public function getSiteUrl()
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
            $siteUrl = HTTPS_SERVER;
        } else { 
            $siteUrl = HTTP_SERVER;
        }

        return $siteUrl;
    }

    public function callback()
    {
        #$this->log();
        ini_set('display_errors','0'); error_reporting(0);

        if (!isset( $_POST ) OR !isset($_POST['hash'])) {
            echo 'Hash Error !';
            exit;
        }

        $merchant['id']     = $this->config->get('paytr_checkout_merchant_id');
        $merchant['key']    = $this->config->get('paytr_checkout_merchant_key');
        $merchant['salt']   = $this->config->get('paytr_checkout_merchant_salt');

        $hash = base64_encode(hash_hmac('sha256', $_POST['merchant_oid'] . $merchant['salt']. $_POST['status']. $_POST['total_amount'], $merchant['key'], true));

        if ($hash != $_POST['hash']) {
            die('PAYTR notification failed: bad hash');
        } elseif (!isset($_POST['merchant_oid'])) {
            die('merchant_oid Not Found!');
        }

        $order_id = explode('PAYTRARASTTA', $_POST['merchant_oid']);

        $this->load->model('checkout/order');

        $getOrder = $this->model_checkout_order->getOrder( $order_id[1] );

        if ($getOrder) {
            if ($_POST['status'] == 'success' AND $getOrder['order_status_id'] == 0) {
                $total_amount = round( $_POST['total_amount'] / 100, 2 );
                $amount = $total_amount - $getOrder['total'];
                $amount = $amount > 0 ? $amount: 'YOK';

                $note = "Ödeme onaylandı.<br/><br/>## PAYTR SİSTEM NOTU ##<br/># Müşteri Ödeme Tutarı: ".$total_amount . "<br/># Vade Farkı: ".$amount."<br/># Sipariş numarası: ".$_POST['merchant_oid'];

                $this->model_checkout_order->addOrderHistory( $order_id[1], $this->config->get('paytr_checkout_order_completed_id'), $note, true );
            } elseif ($_POST['status'] == 'failed' AND array_key_exists( 'failed_reason_code', $_POST ) AND $_POST['failed_reason_code'] != 6) {
                $note = "Sipariş iptal edildi.<br/><br/>## PAYTR SİSTEM NOTU ##<br/># Sipariş Numarası: ".$_POST['merchant_oid']."<br/># Hata Mesajı: " . $_POST['failed_reason_msg'];

                $this->model_checkout_order->addOrderHistory($order_id[1], $this->config->get('paytr_checkout_order_canceled_id'), $note, true);
            }

            echo 'OK';
            exit;
        } else {
            echo 'Böyle bir sipariş bulunamadı.';
            exit;
        }
    }
}
