<?php

class ControllerTotalPaysoninvoiceFee extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('total/paysoninvoice_fee');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
            $this->model_setting_setting->editSetting('paysoninvoice_fee', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');
            
            if (isset($this->request->post['button']) and $this->request->post['button'] == 'save') {
                $this->response->redirect($this->url->link($this->request->get['route'], 'token=' . $this->session->data['token'], 'SSL'));
            }
            
            $this->response->redirect($this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $data = $this->language->all();

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['action'] = $this->url->link('total/paysoninvoice_fee', 'token=' . $this->session->data['token'], 'SSL');

        $data['cancel'] = $this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['paysoninvoice_fee_fee'])) {
            $data['paysoninvoice_fee_fee'] = $this->request->post['paysoninvoice_fee_fee'];
        } else {
            $data['paysoninvoice_fee_fee'] = $this->config->get('paysoninvoice_fee_fee');
        }

        if (isset($this->request->post['paysoninvoice_fee_tax_class_id'])) {
            $data['paysoninvoice_fee_tax_class_id'] = $this->request->post['paysoninvoice_fee_tax_class_id'];
        } else {
            $data['paysoninvoice_fee_tax_class_id'] = $this->config->get('paysoninvoice_fee_tax_class_id');
        }

        if (isset($this->request->post['paysoninvoice_fee_status'])) {
            $data['paysoninvoice_fee_status'] = $this->request->post['paysoninvoice_fee_status'];
        } else {
            $data['paysoninvoice_fee_status'] = $this->config->get('paysoninvoice_fee_status');
        }

        if (isset($this->request->post['paysoninvoice_fee_sort_order'])) {
            $data['paysoninvoice_fee_sort_order'] = $this->request->post['paysoninvoice_fee_sort_order'];
        } else {
            $data['paysoninvoice_fee_sort_order'] = $this->config->get('paysoninvoice_fee_sort_order');
        }

        $this->load->model('localisation/tax_class');

        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('total/paysoninvoice_fee.tpl', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'total/paysoninvoice_fee')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
