<?php
/**
 * @package        Arastta eCommerce
 * @copyright      Copyright (C) 2015-2016 Arastta Association. All rights reserved. (arastta.org)
 * @credits        See CREDITS.txt for credits and other copyright notices.
 * @license        GNU General Public License version 3; see LICENSE.txt
 */

class ControllerPaymentTrPos extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('payment/trpos');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('trpos', $this->request->post);

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

        if (isset($this->error['login'])) {
            $data['error_login'] = $this->error['login'];
        } else {
            $data['error_login'] = '';
        }

        if (isset($this->error['key'])) {
            $data['error_key'] = $this->error['key'];
        } else {
            $data['error_key'] = '';
        }

        $data['action'] = $this->url->link('payment/trpos', 'token=' . $this->session->data['token'], 'SSL');
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        $this->load->model('payment/trpos');

        $filter_data = array(
            'sort' => 'bank_id'
        );

        $banks = $this->model_payment_trpos->getBanks($filter_data);

        $data['banks_info'] = array();

        if (isset($this->request->post['trpos_banks_info'])) {
            $data['banks_info'] = $this->request->post['trpos_banks_info'];
        } else {
            $data['banks_info'] = $this->config->get('trpos_banks_info');
        }

        $this->load->model('tool/image');

        foreach ($banks as $bank) {
            if (!empty($bank['image'])) {
                $image = $this->model_tool_image->resize($bank['image'], 120, 40);
            } else {
                $image = '';
            }

            $bank_id = $bank['bank_id'];

            $banks[$bank_id]['entries'] = array();
            $banks[$bank_id]['image']   = $image;

            $position = strlen($bank['method']);

            $entries = array();

            $entries = $this->getMethodEntries($data, $bank['method'], $position);

            foreach ($entries as $entry) {
                if (isset($data['banks_info'][$bank_id][$entry])) {
                    $banks[$bank_id]['entries'][$entry] = $data['banks_info'][$bank_id][$entry];
                } else {
                    $banks[$bank_id]['entries'][$entry] = '';
                }
            }

            if (isset($data['banks_info'][$bank_id]['instalment'])) {
                $banks[$bank_id]['entries']['instalment'] = $data['banks_info'][$bank_id]['instalment'];
            } else {
                $banks[$bank_id]['entries']['instalment'] = '';
            }
        }

        $data['banks'] = $banks;

        if (isset($this->request->post['trpos_mode'])) {
            $data['trpos_mode'] = $this->request->post['trpos_mode'];
        } else {
            $data['trpos_mode'] = $this->config->get('trpos_mode');
        }

        if (isset($this->request->post['trpos_other_id'])) {
            $data['trpos_other_id'] = $this->request->post['trpos_other_id'];
        } else {
            $data['trpos_other_id'] = $this->config->get('trpos_other_id');
        }

        if (isset($this->request->post['trpos_order_status_id'])) {
            $data['trpos_order_status_id'] = $this->request->post['trpos_order_status_id'];
        } else {
            $data['trpos_order_status_id'] = $this->config->get('trpos_order_status_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['trpos_geo_zone_id'])) {
            $data['trpos_geo_zone_id'] = $this->request->post['trpos_geo_zone_id'];
        } else {
            $data['trpos_geo_zone_id'] = $this->config->get('trpos_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['trpos_status'])) {
            $data['trpos_status'] = $this->request->post['trpos_status'];
        } else {
            $data['trpos_status'] = $this->config->get('trpos_status');
        }

        if (isset($this->request->post['trpos_total'])) {
            $data['trpos_total'] = $this->request->post['trpos_total'];
        } else {
            $data['trpos_total'] = $this->config->get('trpos_total');
        }

        if (isset($this->request->post['trpos_sort_order'])) {
            $data['trpos_sort_order'] = $this->request->post['trpos_sort_order'];
        } else {
            $data['trpos_sort_order'] = $this->config->get('trpos_sort_order');
        }

        $data['token'] = $this->session->data['token'];

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/trpos.tpl', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/trpos')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function getMethodEntries($data_array, $method, $position)
    {
        $entries  = array();
        $position = 6 + $position;

        foreach ($data_array as $key => $value) {
            if (substr($key, 0, $position) == "entry_" . $method) {
                $keywords  = explode("_", $key);
                $entries[] = $keywords[1] . '_' . $keywords[2] . '_' . $keywords[3];
            }
        }

        return $entries;
    }

    public function install()
    {
        if (!$this->user->hasPermission('modify', 'payment/trpos')) {
            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        } else {
            $this->load->model('payment/trpos');

            $this->model_payment_trpos->install();

            // $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }
    }

    public function uninstall()
    {
        if (!$this->user->hasPermission('modify', 'payment/trpos')) {
            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        } else {
            $this->load->model('payment/trpos');

            $this->model_payment_trpos->uninstall();

            // $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }
    }

    public function bank($raw = false)
    {
        $data = $this->language->all();

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        if (isset($this->error['method'])) {
            $data['error_method'] = $this->error['method'];
        } else {
            $data['error_method'] = '';
        }
        if (isset($this->error['model'])) {
            $data['error_model'] = $this->error['model'];
        } else {
            $data['error_model'] = '';
        }
        if (isset($this->error['short'])) {
            $data['error_short'] = $this->error['short'];
        } else {
            $data['error_short'] = '';
        }

        $data['token'] = $this->session->data['token'];

        $this->load->model('payment/trpos');

        if (isset($this->request->get['bank_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $bank_info = $this->model_payment_trpos->getBank($this->request->get['bank_id']);
        }

        if (isset($this->request->get['bank_id'])) {
            $data['bank_id'] = $this->request->get['bank_id'];
        } else {
            $data['bank_id'] = 0;
        }

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($bank_info)) {
            $data['name'] = $bank_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['image'])) {
            $data['image'] = $this->request->post['image'];
        } elseif (!empty($bank_info)) {
            $data['image'] = $bank_info['image'];
        } else {
            $data['image'] = '';
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($bank_info)) {
            $data['status'] = $bank_info['status'];
        } else {
            $data['status'] = true;
        }

        if (isset($this->request->post['method'])) {
            $data['method'] = $this->request->post['method'];
        } elseif (!empty($bank_info)) {
            $data['method'] = $bank_info['method'];
        } else {
            $data['method'] = '';
        }

        if (isset($this->request->post['model'])) {
            $data['model'] = $this->request->post['model'];
        } elseif (!empty($bank_info)) {
            $data['model'] = $bank_info['model'];
        } else {
            $data['model'] = '';
        }

        if (isset($this->request->post['short'])) {
            $data['short'] = $this->request->post['short'];
        } elseif (!empty($bank_info)) {
            $data['short'] = $bank_info['short'];
        } else {
            $data['short'] = '';
        }

        $this->load->model('tool/image');

        if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 120, 40);
        } elseif (!empty($bank_info) && is_file(DIR_IMAGE . $bank_info['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($bank_info['image'], 120, 40);
        } else {
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        if (!empty($this->request->get['bank_id'])) {
            $data['action']    = $this->url->link('payment/trpos/editBank', 'token=' . $this->session->data['token'], 'SSL');
            $data['button_id'] = 'edit-bank';
        } else {
            $data['action']    = $this->url->link('payment/trpos/addBank', 'token=' . $this->session->data['token'], 'SSL');
            $data['button_id'] = 'new-bank';
        }

        $data['cancel'] = $this->url->link('payment/trpos', 'token=' . $this->session->data['token'], 'SSL');

        if ($raw) {
            return $this->load->view('payment/trpos_bank.tpl', $data);
        }

        $this->response->setOutput($this->load->view('payment/trpos_bank.tpl', $data));
    }

    public function addBank()
    {
        $this->load->model('payment/trpos');

        $json = array();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_payment_trpos->addBank($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $json['redirect'] = str_replace('&amp;', '&', $this->url->link('payment/trpos', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $json['html'] = $this->bank(true);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function editBank()
    {
        $this->load->model('payment/trpos');

        $json = array();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_payment_trpos->editBank($this->request->post['bank_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $json['redirect'] = str_replace('&amp;', '&', $this->url->link('payment/trpos', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $json['html'] = $this->bank(true);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function deleteBank()
    {
        $this->load->model('payment/trpos');

        $json = array();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateDelete()) {
            $this->model_payment_trpos->deleteBank($this->request->post['bank_id']);

            $this->session->data['success'] = $this->language->get('text_success');

            $json['redirect'] = str_replace('&amp;', '&', $this->url->link('payment/trpos', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'payment/trpos')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        if (empty($this->request->post['method'])) {
            $this->error['method'] = $this->language->get('error_method');
        }
        if (empty($this->request->post['model'])) {
            $this->error['model'] = $this->language->get('error_model');
        }

        $short_check = $this->model_payment_trpos->checkShortName($this->request->post['short']);

        if (!empty($this->request->post['short']) && !empty($short_check)) {
            $this->error['short'] = $this->language->get('error_short');
        }

        return !$this->error;
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'payment/trpos')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
