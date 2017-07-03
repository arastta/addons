<?php

class ControllerSaleHepsipay extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('sale/hepsipay');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/hepsipay');

        $this->getList();
    }

    public function delete()
    {
        $this->load->language('sale/hepsipay');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/hepsipay');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $hepsipay_order_id) {
                $this->model_sale_hepsipay->deleteHepsipay($hepsipay_order_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['filter_hepsipay_order_id'])) {
                $url .= '&filter_hepsipay_order_id=' . $this->request->get['filter_hepsipay_order_id'];
            }

            if (isset($this->request->get['filter_order_id'])) {
                $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
            }

            if (isset($this->request->get['filter_transaction_id'])) {
                $url .= '&filter_transaction_id=' . $this->request->get['filter_transaction_id'];
            }

            if (isset($this->request->get['filter_bank_id'])) {
                $url .= '&filter_bank_id=' . $this->request->get['filter_bank_id'];
            }

            if (isset($this->request->get['filter_status'])) {
                $url .= '&filter_status=' . $this->request->get['filter_status'];
            }

            if (isset($this->request->get['filter_use3d'])) {
                $url .= '&filter_use3d=' . $this->request->get['filter_use3d'];
            }

            if (isset($this->request->get['filter_client_ip'])) {
                $url .= '&filter_client_ip=' . $this->request->get['filter_client_ip'];
            }

            if (isset($this->request->get['filter_installments'])) {
                $url .= '&filter_installments=' . $this->request->get['filter_installments'];
            }

            if (isset($this->request->get['filter_date_added'])) {
                $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('sale/hepsipay', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }

        $this->getList();
    }

    protected function getList()
    {
        if (isset($this->request->get['filter_hepsipay_order_id'])) {
            $filter_hepsipay_order_id = $this->request->get['filter_hepsipay_order_id'];
        } else {
            $filter_hepsipay_order_id = null;
        }

        if (isset($this->request->get['filter_order_id'])) {
            $filter_order_id = $this->request->get['filter_order_id'];
        } else {
            $filter_order_id = null;
        }

        if (isset($this->request->get['filter_transaction_id'])) {
            $filter_transaction_id = $this->request->get['filter_transaction_id'];
        } else {
            $filter_transaction_id = null;
        }

        if (isset($this->request->get['filter_bank_id'])) {
            $filter_bank_id = $this->request->get['filter_bank_id'];
        } else {
            $filter_bank_id = null;
        }

        if (isset($this->request->get['filter_status'])) {
            $filter_status = $this->request->get['filter_status'];
        } else {
            $filter_status = null;
        }

        if (isset($this->request->get['filter_use3d'])) {
            $filter_use3d = $this->request->get['filter_use3d'];
        } else {
            $filter_use3d = null;
        }

        if (isset($this->request->get['filter_client_ip'])) {
            $filter_client_ip = $this->request->get['filter_client_ip'];
        } else {
            $filter_client_ip = null;
        }

        if (isset($this->request->get['filter_installments'])) {
            $filter_installments = $this->request->get['filter_installments'];
        } else {
            $filter_installments = null;
        }

        if (isset($this->request->get['filter_date_added'])) {
            $filter_date_added = $this->request->get['filter_date_added'];
        } else {
            $filter_date_added = null;
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'po.order_id';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'DESC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['filter_hepsipay_order_id'])) {
            $url .= '&filter_hepsipay_order_id=' . $this->request->get['filter_hepsipay_order_id'];
        }

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_transaction_id'])) {
            $url .= '&filter_transaction_id=' . $this->request->get['filter_transaction_id'];
        }

        if (isset($this->request->get['filter_bank_id'])) {
            $url .= '&filter_bank_id=' . $this->request->get['filter_bank_id'];
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }

        if (isset($this->request->get['filter_use3d'])) {
            $url .= '&filter_use3d=' . $this->request->get['filter_use3d'];
        }

        if (isset($this->request->get['filter_client_ip'])) {
            $url .= '&filter_client_ip=' . $this->request->get['filter_client_ip'];
        }

        if (isset($this->request->get['filter_installments'])) {
            $url .= '&filter_installments=' . $this->request->get['filter_installments'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        #Get All Language Text
        $data = $this->language->all();

        $data['delete'] = $this->url->link('sale/hepsipay/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

        $data['transactions'] = array();

        $filter_data = array(
            'filter_hepsipay_order_id' => $filter_hepsipay_order_id,
            'filter_order_id'          => $filter_order_id,
            'filter_transaction_id'    => $filter_transaction_id,
            'filter_bank_id'           => $filter_bank_id,
            'filter_status'            => $filter_status,
            'filter_use3d'             => $filter_use3d,
            'filter_client_ip'         => $filter_client_ip,
            'filter_installments'      => $filter_installments,
            'filter_date_added'        => $filter_date_added,
            'sort'                     => $sort,
            'order'                    => $order,
            'start'                    => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit'                    => $this->config->get('config_limit_admin')
        );

        $hepsipay_total = $this->model_sale_hepsipay->getTotalHepsipays($filter_data);

        $results = $this->model_sale_hepsipay->getHepsipays($filter_data);

        foreach ($results as $result) {
            if (isset($result['extra_installments']) && $result['extra_installments']) {
                $result['installments'] .= ' (+' . $result['extra_installments'] . ')';
            }

            $data['transactions'][] = array(
                'hepsipay_order_id' => $result['hepsipay_order_id'],
                'order_id'          => $result['order_id'],
                'transaction_id'    => $result['transaction_id'],
                'total'             => $result['total'],
                'try_total'         => $result['try_total'],
                'conversion_rate'   => $result['conversion_rate'],
                'bank_id'           => $result['bank_id'],
                'use3d'             => $result['use3d'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
                'client_ip'         => $result['client_ip'],
                'installments'      => $result['installments'],
                'status'            => $result['status'] ? $this->language->get('text_complete') : $this->language->get('text_failed'),
                'date_added'        => date($this->language->get('date_format_short'), strtotime($result['date_added']))
            );
        }

        $data['token'] = $this->session->data['token'];

        if (isset($this->session->data['error'])) {
            $data['error_warning'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } elseif (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array) $this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';

        if (isset($this->request->get['filter_hepsipay_order_id'])) {
            $url .= '&filter_hepsipay_order_id=' . $this->request->get['filter_hepsipay_order_id'];
        }

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_transaction_id'])) {
            $url .= '&filter_transaction_id=' . $this->request->get['filter_transaction_id'];
        }

        if (isset($this->request->get['filter_bank_id'])) {
            $url .= '&filter_bank_id=' . $this->request->get['filter_bank_id'];
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }

        if (isset($this->request->get['filter_use3d'])) {
            $url .= '&filter_use3d=' . $this->request->get['filter_use3d'];
        }

        if (isset($this->request->get['filter_client_ip'])) {
            $url .= '&filter_client_ip=' . $this->request->get['filter_client_ip'];
        }

        if (isset($this->request->get['filter_installments'])) {
            $url .= '&filter_installments=' . $this->request->get['filter_installments'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_hepsipay_order_id'] = $this->url->link('sale/hepsipay', 'token=' . $this->session->data['token'] . '&sort=po.hepsipay_order_id' . $url, 'SSL');

        $data['sort_order_id'] = $this->url->link('sale/hepsipay', 'token=' . $this->session->data['token'] . '&sort=po.order_id' . $url, 'SSL');

        $data['sort_transaction_id'] = $this->url->link('sale/hepsipay', 'token=' . $this->session->data['token'] . '&sort=po.transaction_id' . $url, 'SSL');

        $data['sort_total'] = $this->url->link('sale/hepsipay', 'token=' . $this->session->data['token'] . '&sort=o.total' . $url, 'SSL');

        $data['sort_try_total'] = $this->url->link('sale/hepsipay', 'token=' . $this->session->data['token'] . '&sort=po.try_total' . $url, 'SSL');

        $data['sort_conversion_rate'] = $this->url->link('sale/hepsipay', 'token=' . $this->session->data['token'] . '&sort=po.conversion_rate' . $url, 'SSL');

        $data['sort_date_added'] = $this->url->link('sale/hepsipay', 'token=' . $this->session->data['token'] . '&sort=po.date_added' . $url, 'SSL');

        $data['sort_bank_id'] = $this->url->link('sale/hepsipay', 'token=' . $this->session->data['token'] . '&sort=po.bank_id' . $url, 'SSL');

        $data['sort_status'] = $this->url->link('sale/hepsipay', 'token=' . $this->session->data['token'] . '&sort=po.status' . $url, 'SSL');

        $data['sort_use3d'] = $this->url->link('sale/hepsipay', 'token=' . $this->session->data['token'] . '&sort=po.use3d' . $url, 'SSL');

        $data['sort_client_ip'] = $this->url->link('sale/hepsipay', 'token=' . $this->session->data['token'] . '&sort=po.client_ip' . $url, 'SSL');

        $data['sort_installments'] = $this->url->link('sale/hepsipay', 'token=' . $this->session->data['token'] . '&sort=po.installments' . $url, 'SSL');

        $url = '';

        if (isset($this->request->get['filter_hepsipay_order_id'])) {
            $url .= '&filter_hepsipay_order_id=' . $this->request->get['filter_hepsipay_order_id'];
        }

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_transaction_id'])) {
            $url .= '&filter_transaction_id=' . $this->request->get['filter_transaction_id'];
        }

        if (isset($this->request->get['filter_bank_id'])) {
            $url .= '&filter_bank_id=' . $this->request->get['filter_bank_id'];
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }

        if (isset($this->request->get['filter_use3d'])) {
            $url .= '&filter_use3d=' . $this->request->get['filter_use3d'];
        }

        if (isset($this->request->get['filter_client_ip'])) {
            $url .= '&filter_client_ip=' . $this->request->get['filter_client_ip'];
        }

        if (isset($this->request->get['filter_installments'])) {
            $url .= '&filter_installments=' . $this->request->get['filter_installments'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();

        $pagination->total = $hepsipay_total;
        $pagination->page  = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url   = $this->url->link('sale/hepsipay', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($hepsipay_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($hepsipay_total - $this->config->get('config_limit_admin'))) ? $hepsipay_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $hepsipay_total, ceil($hepsipay_total / $this->config->get('config_limit_admin')));

        $data['filter_hepsipay_order_id'] = $filter_hepsipay_order_id;
        $data['filter_order_id']          = $filter_order_id;
        $data['filter_transaction_id']    = $filter_transaction_id;
        $data['filter_bank_id']           = $filter_bank_id;
        $data['filter_status']            = $filter_status;
        $data['filter_use3d']             = $filter_use3d;
        $data['filter_client_ip']         = $filter_client_ip;
        $data['filter_installments']      = $filter_installments;
        $data['filter_date_added']        = $filter_date_added;

        $data['sort']  = $sort;
        $data['order'] = $order;

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('sale/hepsipay_list.tpl', $data));
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'sale/hepsipay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
