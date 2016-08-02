<?php

class ControllerPaymentPaysondirect extends Controller
{
    private $error = array();
    private $data  = array();

    public function index()
    {
        $this->load->language('payment/paysondirect');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('paysondirect', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            if (isset($this->request->post['button']) and $this->request->post['button'] == 'save') {
                $this->response->redirect($this->url->link($this->request->get['route'], 'token=' . $this->session->data['token'], 'SSL'));
            }

            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $data = $this->language->all();

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['user_name'])) {
            $data['error_user_name'] = $this->error['user_name'];
        } else {
            $data['error_user_name'] = '';
        }

        if (isset($this->error['agent_id'])) {
            $data['error_agent_id'] = $this->error['agent_id'];
        } else {
            $data['error_agent_id'] = '';
        }

        if (isset($this->error['md5'])) {
            $data['error_md5'] = $this->error['md5'];
        } else {
            $data['error_md5'] = '';
        }

        if (isset($this->error['ignored_order_totals'])) {
            $data['error_ignored_order_totals'] = $this->error['ignored_order_totals'];
        } else {
            $data['error_ignored_order_totals'] = '';
        }

        $data['error_invoiceFeeError'] = (isset($this->error['invoiceFeeError']) ? $this->error['invoiceFeeError'] : '');

        $data['action'] = $this->url->link('payment/paysondirect', 'token=' . $this->session->data['token'], 'SSL');

        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['paysondirect_modul_version'])) {
            $data['paysondirect_modul_version'] = $this->request->post['paysondirect_modul_version'];
        } else {
            $data['paysondirect_modul_version'] = $this->config->get('paysondirect_modul_version');
        }

        if (isset($this->request->post['paysondirect_user_name'])) {
            $data['paysondirect_user_name'] = $this->request->post['paysondirect_user_name'];
        } else {
            $data['paysondirect_user_name'] = $this->config->get('paysondirect_user_name');
        }

        if (isset($this->request->post['paysondirect_agent_id'])) {
            $data['paysondirect_agent_id'] = $this->request->post['paysondirect_agent_id'];
        } else {
            $data['paysondirect_agent_id'] = $this->config->get('paysondirect_agent_id');
        }

        if (isset($this->request->post['paysondirect_md5'])) {
            $data['paysondirect_md5'] = $this->request->post['paysondirect_md5'];
        } else {
            $data['paysondirect_md5'] = $this->config->get('paysondirect_md5');
        }

        if (isset($this->request->post['paysondirect_mode'])) {
            $data['paysondirect_mode'] = $this->request->post['paysondirect_mode'];
        } else {
            $data['paysondirect_mode'] = $this->config->get('paysondirect_mode');
        }
        
        $data['paysoninvoice_fee_fee'] = (isset($this->request->post['paysoninvoice_fee_fee']) ? $this->request->post['paysoninvoice_fee_fee'] : $this->config->get('paysoninvoice_fee_fee'));

        if (isset($this->request->post['paysondirect_payment_method'])) {
            $data['paysondirect_payment_method'] = $this->request->post['paysondirect_payment_method'];
        } else {
            $data['paysondirect_payment_method'] = $this->config->get('paysondirect_payment_method');
        }

        if (isset($this->request->post['paysondirect_secure_word'])) {
            $data['paysondirect_secure_word'] = $this->request->post['paysondirect_secure_word'];
        } else {
            $data['paysondirect_secure_word'] = $this->config->get('paysondirect_secure_word');
        }

        if (isset($this->request->post['paysondirect_logg'])) {
            $data['paysondirect_logg'] = $this->request->post['paysondirect_logg'];
        } else {
            $data['paysondirect_logg'] = $this->config->get('paysondirect_logg');
        }

        if (isset($this->request->post['paysondirect_total'])) {
            $data['paysondirect_total'] = $this->request->post['paysondirect_total'];
        } else {
            $data['paysondirect_total'] = $this->config->get('paysondirect_total');
        }

        if (isset($this->request->post['paysondirect_order_status_id'])) {
            $data['paysondirect_order_status_id'] = $this->request->post['paysondirect_order_status_id'];
        } else {
            $data['paysondirect_order_status_id'] = $this->config->get('paysondirect_order_status_id');
        }

        $data['paysondirect_invoice_status_id'] = (isset($this->request->post['paysondirect_invoice_status_id']) ? $this->request->post['paysondirect_invoice_status_id'] : $this->config->get('paysondirect_invoice_status_id'));

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['paysondirect_geo_zone_id'])) {
            $data['paysondirect_geo_zone_id'] = $this->request->post['paysondirect_geo_zone_id'];
        } else {
            $data['paysondirect_geo_zone_id'] = $this->config->get('paysondirect_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['paysondirect_status'])) {
            $data['paysondirect_status'] = $this->request->post['paysondirect_status'];
        } else {
            $data['paysondirect_status'] = $this->config->get('paysondirect_status');
        }

        if (isset($this->request->post['paysondirect_sort_order'])) {
            $data['paysondirect_sort_order'] = $this->request->post['paysondirect_sort_order'];
        } else {
            $data['paysondirect_sort_order'] = $this->config->get('paysondirect_sort_order');
        }

        if (isset($this->request->post['paysondirect_receipt'])) {
            $data['paysondirect_receipt'] = $this->request->post['paysondirect_receipt'];
        } else {
            $data['paysondirect_receipt'] = $this->config->get('paysondirect_receipt');
        }

        if (isset($this->request->post['paysondirect_ignored_order_totals'])) {
            $data['paysondirect_ignored_order_totals'] = $this->request->post['paysondirect_ignored_order_totals'];
        } else {
            if ($this->config->get('paysondirect_ignored_order_totals') == null) {
                $data['paysondirect_ignored_order_totals'] = 'sub_total, total, taxes';
            } else {
                $data['paysondirect_ignored_order_totals'] = $this->config->get('paysondirect_ignored_order_totals');
            }
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/paysondirect.tpl', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/paysondirect')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ($this->request->post['paysondirect_mode'] != 0) {
            if (!isset($this->request->post['paysondirect_agent_id']) || !$this->request->post['paysondirect_agent_id']) {
                $this->error['agent_id'] = $this->language->get('error_agent_id');
            }

            if (!isset($this->request->post['paysondirect_user_name']) || !$this->request->post['paysondirect_user_name']) {
                $this->error['user_name'] = $this->language->get('error_user_name');
            }

            if (!isset($this->request->post['paysondirect_md5']) || !$this->request->post['paysondirect_md5']) {
                $this->error['md5'] = $this->language->get('error_md5');
            }
        }

        if (!$this->request->post['paysondirect_ignored_order_totals']) {
            $this->error['ignored_order_totals'] = $this->language->get('error_ignored_order_totals');
        }

        if (isset($this->request->post['paysoninvoice_fee_fee'])) {
            if (!is_numeric($this->request->post['paysoninvoice_fee_fee'])) {
                $this->error['invoiceFeeError'] = "Invoicefee must be a number";
            } else {
                if ($this->request->post['paysoninvoice_fee_fee'] < 0 || $this->request->post['paysoninvoice_fee_fee'] > 40) {
                    $this->error['invoiceFeeError'] = "Invoicefee must be between 0-40";
                }
            }
        }

        return !$this->error;
    }
}
