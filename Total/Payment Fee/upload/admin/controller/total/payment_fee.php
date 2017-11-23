<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class ControllerTotalPaymentFee extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('total/payment_fee');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_fee', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            if (isset($this->request->post['button']) and $this->request->post['button'] == 'save') {
                $this->response->redirect($this->url->link($this->request->get['route'], 'token=' . $this->session->data['token'], 'SSL'));
            }
            
            $this->response->redirect($this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL'));
        }

        #Get All Language Text
        $data = $this->language->all();

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['action'] = $this->url->link('total/payment_fee', 'token=' . $this->session->data['token'], 'SSL');

        $data['cancel'] = $this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['payment_fee_status'])) {
            $data['payment_fee_status'] = $this->request->post['payment_fee_status'];
        } else {
            $data['payment_fee_status'] = $this->config->get('payment_fee_status');
        }

        if (isset($this->request->post['payment_fee_sort_order'])) {
            $data['payment_fee_sort_order'] = $this->request->post['payment_fee_sort_order'];
        } else {
            $data['payment_fee_sort_order'] = $this->config->get('payment_fee_sort_order');
        }

        if (isset($this->request->post['payment_fee_payment_methods'])) {
            $data['payment_methods'] = $this->request->post['payment_fee_payment_method'];
        } else {
            $data['payment_methods'] = $this->config->get('payment_fee_payment_method', array());
        }

        $method_data = array();

        $this->load->model('extension/extension');

        $results = $this->model_extension_extension->getExtensions(array('filter_type' => 'payment'));

        foreach ($results as $result) {
            if ($this->config->get($result['code'] . '_status')) {
                $this->load->language('payment/' . $result['code']);

                $method_data[] = array(
                    'code' => $result['code'],
                    'name' => $this->language->get('heading_title')
                );
            }
        }

        /*$sort_order = array();

        foreach ($method_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $method_data);*/

        $data['methods'] = $method_data;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('total/payment_fee.tpl', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'total/payment_fee')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
