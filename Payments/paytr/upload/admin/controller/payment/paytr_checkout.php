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

    private $error = array();

    public function index()
    {
        $this->load->language('payment/paytr_checkout');
        $this->document->setTitle( $this->language->get('heading_title') );

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('paytr_checkout', $this->request->post);

            $this->session->data['success'] = '<strong>PayTR</strong> modül ayarları kaydedildi.!';

            if (isset($this->request->post['button']) and $this->request->post['button'] == 'save') {
                $this->response->redirect($this->url->link($this->request->get['route'], 'token=' . $this->session->data['token'], 'SSL'));
            }

            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], true));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');

        $data['help_total'] = $this->language->get('help_total');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['errors_message'] = array(
            'warning'                               => $this->language->get('error_warning'),
            'paytr_checkout_merchant_id'            => $this->language->get('error_paytr_checkout_merchant_id'),
            'paytr_checkout_merchant_key'           => $this->language->get('error_paytr_checkout_merchant_key'),
            'paytr_checkout_merchant_salt'          => $this->language->get('error_paytr_checkout_merchant_salt'),
            'paytr_checkout_order_completed_id'     => $this->language->get('error_paytr_checkout_order_completed_id'),
            'paytr_checkout_order_canceled_id'      => $this->language->get('error_paytr_checkout_order_canceled_id'),
            'paytr_checkout_order_status_general'   => $this->language->get('error_paytr_checkout_order_status_general'),
            'paytr_checkout_merchant_general'       => $this->language->get('error_paytr_checkout_merchant_general')
        );

        $data['action'] = $this->url->link('payment/paytr_checkout', 'token=' . $this->session->data['token'], 'SSL');

        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['paytr_checkout_merchant_id'])) {
            $data['paytr_checkout_merchant_id'] = trim( $this->request->post['paytr_checkout_merchant_id'] );
        } else {
            $data['paytr_checkout_merchant_id'] = $this->config->get('paytr_checkout_merchant_id');
        }

        if (isset($this->request->post['paytr_checkout_merchant_key'])) {
            $data['paytr_checkout_merchant_key'] = trim( $this->request->post['paytr_checkout_merchant_key'] );
        } else {
            $data['paytr_checkout_merchant_key'] = $this->config->get('paytr_checkout_merchant_key');
        }

        if (isset($this->request->post['paytr_checkout_merchant_salt'])) {
            $data['paytr_checkout_merchant_salt'] = trim( $this->request->post['paytr_checkout_merchant_salt'] );
        } else {
            $data['paytr_checkout_merchant_salt'] = $this->config->get('paytr_checkout_merchant_salt');
        }

        if (isset($this->request->post['paytr_checkout_status'])) {
            $data['paytr_checkout_status'] = $this->request->post['paytr_checkout_status'];
        } else {
            $data['paytr_checkout_status'] = $this->config->get('paytr_checkout_status');
        }

        if (isset($this->request->post['paytr_checkout_installment_number'])) {
            $data['paytr_checkout_installment_number'] = $this->request->post['paytr_checkout_installment_number'];
        } else {
            if ( !$this->config->get('paytr_checkout_installment_number') ) {
                $data['paytr_checkout_installment_number'] = 0;
            } else {
                $data['paytr_checkout_installment_number'] = $this->config->get('paytr_checkout_installment_number');    
            }
        }

        $data['installment_arr'] = array(
            0 => 'Tüm Taksit Seçenekleri',
            1 => 'Tek Çekim (Taksit Yok)',
            2 => '2 Taksit\'e kadar',
            3=> '3 Taksit\'e kadar',
            4 => '4 Taksit\'e kadar',
            5 => '5 Taksit\'e kadar',
            6=> '6 Taksit\'e kadar',
            7 => '7 Taksit\'e kadar',
            8 => '8 Taksit\'e kadar',
            9 => '9 Taksit\'e kadar',
            10 => '10 Taksit\'e kadar',
            11 => '11 Taksit\'e kadar',
            12 => '12 Taksit\'e kadar',
            13 => 'KATEGORİ BAZLI'
        );

        $data['language_arr'] = array( 
            0 => 'Otomatik',
            1 => 'Türkçe',
            2 => 'İngilizce'
        );

        if (isset($this->request->post['paytr_checkout_lang'])) {
            $data['paytr_checkout_lang'] = $this->request->post['paytr_checkout_lang'];
        } else {
            $data['paytr_checkout_lang'] = $this->config->get('paytr_checkout_lang');    
        }

        if (isset($this->request->post['paytr_checkout_order_completed_id'])) {
            $data['paytr_checkout_order_completed_id'] = $this->request->post['paytr_checkout_order_completed_id'];
        } else {
            $data['paytr_checkout_order_completed_id'] = $this->config->get('paytr_checkout_order_completed_id');    
        }

        if (isset($this->request->post['paytr_checkout_order_canceled_id'])) {
            $data['paytr_checkout_order_canceled_id'] = $this->request->post['paytr_checkout_order_canceled_id'];
        } else {
            $data['paytr_checkout_order_canceled_id'] = $this->config->get('paytr_checkout_order_canceled_id');    
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if ( !$this->config->get('paytr_checkout_merchant_id') OR !$this->config->get('paytr_checkout_merchant_key') OR !$this->config->get('paytr_checkout_merchant_salt') ) {
            $this->error['paytr_checkout_merchant_general'] = 1;
        }

        $tree = $this->category_parser(); 

        $finish = array(); 

        $this->category_parser_clear($tree, 0, array(), $finish); 

        $data['paytr_checkout_category_list'] = $finish;

        if ( isset($this->request->post['paytr_checkout_category_installment']) ) {
            $data['paytr_checkout_category_installment'] = $this->request->post['paytr_checkout_category_installment'];
        } else {
            $data['paytr_checkout_category_installment'] = $this->config->get('paytr_checkout_category_installment');
        }

        $data['errors'] = $this->error;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/paytr_checkout.tpl', $data));
    }

    public function category_parser()
    {
        $cats = $this->db->query("SELECT c.category_id AS 'id',  c.parent_id AS 'parent_id', cd.name AS 'name' FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY c.sort_order, cd.name ASC");

        $cats = $cats->rows; $cat_tree = array();

        foreach ($cats as $key => $item) {
            if ($item['parent_id'] == 0) {
                $cat_tree[$item['id']] = array( 
                    'id'    => $item['id'],
                    'name'  => $item['name']
                );

                $this->parent_category_parser($cats, $cat_tree[ $item['id'] ]);
            }
        }

        return $cat_tree;
    }

    public function parent_category_parser(&$cats = array(), &$cat_tree = array())
    {
        foreach ($cats as $key => $item) {
            if ($item['parent_id'] == $cat_tree['id']) {
                $cat_tree['parent'][$item['id']] = array( 
                    'id'    => $item['id'],
                    'name'  => $item['name']
                );

                $this->parent_category_parser($cats, $cat_tree['parent'][$item['id']]);
            }
        }
    }

    public function category_parser_clear( $tree, $level = 0, $arr = array(), &$finish_him = array() )
    {
        foreach ($tree as $id => $item) {
            if ($level == 0) { 
                unset($arr); 

                $arr = array(); 

                $arr[] = $item['name'];
            } elseif ($level == 1 OR $level == 2) {
                if (count($arr) == ($level + 1)) {
                    $deleted = array_pop($arr);
                }

                $arr[] = $item['name'];
            }

            if ($level < 3) {
                $nav = null;

                foreach ($arr as $key => $val) {
                    $nav .= $val.($level != 0 ? ' > ' : null);
                }

                $finish_him[$item['id']] = rtrim($nav,' > ').'<br>';

                if (!empty($item['parent'])) {
                    $this->category_parser_clear( $item['parent'], $level + 1, $arr, $finish_him );
                }
            }
        }
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/paytr_checkout')) {
            $this->error['warning'] = 1;
        }

        if (!$this->request->post['paytr_checkout_merchant_id']) {
            $this->error['paytr_checkout_merchant_id'] = 1;
        }

        if (!$this->request->post['paytr_checkout_merchant_key']) {
            $this->error['paytr_checkout_merchant_key'] = 1;
        }

        if (!$this->request->post['paytr_checkout_merchant_salt']) {
            $this->error['paytr_checkout_merchant_salt'] = 1;
        }

        if (!$this->request->post['paytr_checkout_order_completed_id']) {
            $this->error['paytr_checkout_order_completed_id'] = 1;
        }

        if (!$this->request->post['paytr_checkout_order_canceled_id']) {
            $this->error['paytr_checkout_order_canceled_id'] = 1;
        }

        return !$this->error;
    }
}