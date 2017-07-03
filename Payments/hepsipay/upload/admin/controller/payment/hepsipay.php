<?php

class ControllerPaymentHepsipay extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('payment/hepsipay');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('hepsipay', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            if (isset($this->request->post['button']) and $this->request->post['button'] == 'save') {
                $this->response->redirect($this->url->link($this->request->get['route'], 'token=' . $this->session->data['token'], 'SSL'));
            }

            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }

        #Get All Language Text
        $data = $this->language->all();

        //todo: hepsipay - extra inst
        $data['entry_extra_installment_status'] = 0;

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['action'] = $this->url->link('payment/hepsipay', 'token=' . $this->session->data['token'], 'SSL');

        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['hepsipay_endpoint'])) {
            $data['hepsipay_endpoint'] = $this->request->post['hepsipay_endpoint'];
        } else {
            $data['hepsipay_endpoint'] = $this->config->get('hepsipay_endpoint');
        }

        $data['hepsipay_endpoint'] = 'https://pluginmanager.hepsipay.com/portal/web/api/v1';

        if (isset($this->request->post['hepsipay_3dsecure_status'])) {
            $data['hepsipay_3dsecure_status'] = $this->request->post['hepsipay_3dsecure_status'];
        } else {
            $data['hepsipay_3dsecure_status'] = $this->config->get('hepsipay_3dsecure_status');
        }

        if (isset($this->request->post['hepsipay_force_3dsecure_status'])) {
            $data['hepsipay_force_3dsecure_status'] = $this->request->post['hepsipay_force_3dsecure_status'];
        } else {
            $data['hepsipay_force_3dsecure_status'] = $this->config->get('hepsipay_force_3dsecure_status');
        }

        if (isset($this->request->post['hepsipay_force_3dsecure_debit'])) {
            $data['hepsipay_force_3dsecure_debit'] = $this->request->post['hepsipay_force_3dsecure_debit'];
        } else {
            $data['hepsipay_force_3dsecure_debit'] = $this->config->get('hepsipay_force_3dsecure_debit');
        }
        $data['hepsipay_force_3dsecure_debit'] = true;

        if (isset($this->request->post['hepsipay_installment_status'])) {
            $data['hepsipay_installment_status'] = $this->request->post['hepsipay_installment_status'];
        } else {
            $data['hepsipay_installment_status'] = $this->config->get('hepsipay_installment_status');
        }

        if (isset($this->request->post['hepsipay_installment_commission'])) {
            $data['hepsipay_installment_commission'] = $this->request->post['hepsipay_installment_commission'];
        } else {
            $data['hepsipay_installment_commission'] = $this->config->get('hepsipay_installment_commission');
        }

        //todo: hepsipay - extra inst
        $data['hepsipay_extra_installment_status'] = 0;

        if (isset($this->request->post['hepsipay_bkm_status'])) {
            $data['hepsipay_bkm_status'] = $this->request->post['hepsipay_bkm_status'];
        } else {
            $data['hepsipay_bkm_status'] = $this->config->get('hepsipay_bkm_status');
        }

        if (isset($this->request->post['hepsipay_username'])) {
            $data['hepsipay_username'] = $this->request->post['hepsipay_username'];
        } else {
            $data['hepsipay_username'] = $this->config->get('hepsipay_username');
        }

        if (isset($this->request->post['hepsipay_password'])) {
            $data['hepsipay_password'] = $this->request->post['hepsipay_password'];
        } else {
            $data['hepsipay_password'] = $this->config->get('hepsipay_password');
        }

        if (isset($this->request->post['hepsipay_total'])) {
            $data['hepsipay_total'] = $this->request->post['hepsipay_total'];
        } else {
            $data['hepsipay_total'] = $this->config->get('hepsipay_total');
        }

        if (isset($this->request->post['hepsipay_order_status_id'])) {
            $data['hepsipay_order_status_id'] = $this->request->post['hepsipay_order_status_id'];
        } else {
            $data['hepsipay_order_status_id'] = $this->config->get('hepsipay_order_status_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['hepsipay_geo_zone_id'])) {
            $data['hepsipay_geo_zone_id'] = $this->request->post['hepsipay_geo_zone_id'];
        } else {
            $data['hepsipay_geo_zone_id'] = $this->config->get('hepsipay_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['hepsipay_status'])) {
            $data['hepsipay_status'] = $this->request->post['hepsipay_status'];
        } else {
            $data['hepsipay_status'] = $this->config->get('hepsipay_status');
        }

        if (isset($this->request->post['hepsipay_sort_order'])) {
            $data['hepsipay_sort_order'] = $this->request->post['hepsipay_sort_order'];
        } else {
            $data['hepsipay_sort_order'] = $this->config->get('hepsipay_sort_order');
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/hepsipay.tpl', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/hepsipay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
