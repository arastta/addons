<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class ControllerPaymentPaysoninvoice extends Controller
{
    private $error = array();
    private $data  = array();

    public function index()
    {
        $this->load->language('payment/paysoninvoice');

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('paysoninvoice', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            if (isset($this->request->post['button']) and $this->request->post['button'] == 'save') {
                $this->response->redirect($this->url->link($this->request->get['route'], 'token=' . $this->session->data['token'], 'SSL'));
            }

            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }
        
        $data = $this->language->all();

        $data['link_to_paysondirect'] = $this->url->link('payment/paysondirect', 'token=' . $this->session->data['token'], 'SSL');

        $data['action'] = $this->url->link('payment/paysoninvoice', 'token=' . $this->session->data['token'], 'SSL');

        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['paysoninvoice_status'])) {
            $data['paysoninvoice_status'] = $this->request->post['paysoninvoice_status'];
        } else {
            $data['paysoninvoice_status'] = $this->config->get('paysoninvoice_status');
        }

        if (isset($this->request->post['paysoninvoice_sort_order'])) {
            $data['paysoninvoice_sort_order'] = $this->request->post['paysoninvoice_sort_order'];
        } else {
            $data['paysoninvoice_sort_order'] = $this->config->get('paysoninvoice_sort_order');
        }

        if (isset($this->request->post['paysoninvoice_order_status_id'])) {
            $data['paysoninvoice_order_status_id'] = $this->request->post['paysoninvoice_order_status_id'];
        } else {
            $data['paysoninvoice_order_status_id'] = $this->config->get('paysoninvoice_order_status_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['paysoninvoice_geo_zone_id'])) {
            $data['paysoninvoice_geo_zone_id'] = $this->request->post['paysoninvoice_geo_zone_id'];
        } else {
            $data['paysoninvoice_geo_zone_id'] = $this->config->get('paysoninvoice_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/paysoninvoice.tpl', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/paysoninvoice')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
