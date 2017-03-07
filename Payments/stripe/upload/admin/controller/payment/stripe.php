<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @copyright   2016 Lancelot Hardel
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

use Arastta\Component\Form\Form as AForm;

class ControllerPaymentStripe extends Controller
{
    protected $error = array();

    public function install()
    {
        if ($this->user->hasPermission('modify', 'extension/payment')) {
            $this->load->model('payment/stripe');

            $this->model_payment_stripe->install();
        }
    }

    public function uninstall()
    {
        if ($this->user->hasPermission('modify', 'extension/payment')) {
            $this->load->model('payment/stripe');

            $this->model_payment_stripe->uninstall();
        }
    }

    public function index()
    {
        $this->load->language('payment/stripe');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('stripe', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            if (isset($this->request->post['button']) and $this->request->post['button'] == 'save') {
                $this->response->redirect($this->url->link($this->request->get['route'], 'token=' . $this->session->data['token'], 'SSL'));
            }

            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'] . '&filter_type=payment', true));
        }

        #Get All Language Text
        $data = $this->language->all();

        if (isset($this->error['warning'])) {
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

        $data['currencies'] = ['usd', 'eur'];

        if ($this->initStripe() == true) {
            $data['currencies'] = \Stripe\CountrySpec::retrieve("US")['supported_payment_currencies'];
        }

        $data['action'] = $this->url->link('payment/stripe', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'] . '&type=payment', true);

        if (isset($this->request->post['stripe_test_publishable_key'])) {
            $data['stripe_test_publishable_key'] = $this->request->post['stripe_test_publishable_key'];
        } elseif ($this->config->has('stripe_test_publishable_key')) {
            $data['stripe_test_publishable_key'] = $this->config->get('stripe_test_publishable_key');
        } else {
            $data['stripe_test_publishable_key'] = '';
        }

        if (isset($this->request->post['stripe_test_secret_key'])) {
            $data['stripe_test_secret_key'] = $this->request->post['stripe_test_secret_key'];
        } elseif ($this->config->has('stripe_test_secret_key')) {
            $data['stripe_test_secret_key'] = $this->config->get('stripe_test_secret_key');
        } else {
            $data['stripe_test_secret_key'] = '';
        }

        if (isset($this->request->post['stripe_live_publishable_key'])) {
            $data['stripe_live_publishable_key'] = $this->request->post['stripe_live_publishable_key'];
        } elseif ($this->config->has('stripe_live_publishable_key')) {
            $data['stripe_live_publishable_key'] = $this->config->get('stripe_live_publishable_key');
        } else {
            $data['stripe_live_publishable_key'] = '';
        }

        if (isset($this->request->post['stripe_live_secret_key'])) {
            $data['stripe_live_secret_key'] = $this->request->post['stripe_live_secret_key'];
        } elseif ($this->config->has('stripe_live_secret_key')) {
            $data['stripe_live_secret_key'] = $this->config->get('stripe_live_secret_key');
        } else {
            $data['stripe_live_secret_key'] = '';
        }

        if (isset($this->request->post['stripe_environment'])) {
            $data['stripe_environment'] = $this->request->post['stripe_environment'];
        } elseif ($this->config->has('stripe_environment')) {
            $data['stripe_environment'] = $this->config->get('stripe_environment');
        } else {
            $data['stripe_environment'] = 'test';
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['stripe_order_status_id'])) {
            $data['stripe_order_status_id'] = $this->request->post['stripe_order_status_id'];
        } else {
            $data['stripe_order_status_id'] = $this->config->get('stripe_order_status_id');
        }

        if (isset($this->request->post['stripe_currency'])) {
            $data['stripe_currency'] = $this->request->post['stripe_currency'];
        } elseif ($this->config->has('stripe_currency')) {
            $data['stripe_currency'] = $this->config->get('stripe_currency');
        } else {
            $data['stripe_currency'] = 'usd';
        }

        if (isset($this->request->post['stripe_store_cards'])) {
            $data['stripe_store_cards'] = $this->request->post['stripe_store_cards'];
        } elseif ($this->config->has('stripe_store_cards')) {
            $data['stripe_store_cards'] = $this->config->get('stripe_store_cards');
        } else {
            $data['stripe_store_cards'] = 0;
        }

        if (isset($this->request->post['stripe_status'])) {
            $data['stripe_status'] = $this->request->post['stripe_status'];
        } elseif ($this->config->has('stripe_status')) {
            $data['stripe_status'] = $this->config->get('stripe_status');
        } else {
            $data['stripe_status'] = 0;
        }

        if (isset($this->request->post['stripe_sort_order'])) {
            $data['stripe_sort_order'] = $this->request->post['stripe_sort_order'];
        } elseif ($this->config->has('stripe_sort_order')) {
            $data['stripe_sort_order'] = $this->config->get('stripe_sort_order');
        } else {
            $data['stripe_sort_order'] = '';
        }

        $data['token'] = $this->session->data['token'];

        $data['form_fields'] = $this->getFormFields($data['action']);

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/stripe.tpl', $data));
    }

    public function refund()
    {
        $this->load->language('payment/stripe');

        $this->initStripe();

        $json = array();

        $json['error'] = false;

        if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
            $this->load->model('payment/stripe');
            $this->load->model('user/user');

            $stripe_order = $this->model_payment_stripe->getOrder($this->request->post['order_id']);

            $user_info = $this->model_user_user->getUser($this->user->getId());

            $re = \Stripe\Refund::create(array(
                                             "charge"   => $stripe_order['stripe_order_id'],
                                             "amount"   => $this->request->post['amount'] * 100,
                                             "metadata" => array(
                                                 "opencart_user_username" => $user_info['username'],
                                                 "opencart_user_id"       => $this->user->getId()
                                             )
                                         ));
        } else {
            $json['error'] = true;
            $json['msg']   = 'Missing data';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function order()
    {
        if ($this->config->get('stripe_status')) {
            $this->load->language('payment/stripe');

            #Get All Language Text
            $data = $this->language->all();

            $data['order_id'] = $this->request->get['order_id'];

            $this->load->model('payment/stripe');

            $stripe_order = $this->model_payment_stripe->getOrder($this->request->get['order_id']);

            if ($stripe_order && $this->initStripe()) {
                $data['stripe_environment'] = $stripe_order['environment'];

                $data['charge']      = \Stripe\Charge::retrieve($stripe_order['stripe_order_id']);
                $data['transaction'] = \Stripe\BalanceTransaction::retrieve($data['charge']['balance_transaction']);

                $data['token'] = $this->request->get['token'];

                return $this->load->view('payment/stripe_order.tpl', $data);
            }
        }
    }

    protected function getFormFields($action)
    {
        $action = str_replace('amp;', '', $action);

        $option_text = array(
            'yes' => $this->language->get('text_enabled'),
            'no'  => $this->language->get('text_disabled')
        );

        $test_secret_key = array(
            'value' => $this->config->get('stripe_test_secret_key'),
            'placeholder' => $this->language->get('entry_test_api_key')
        );

        $test_publishable_key = array(
            'value' => $this->config->get('stripe_test_publishable_key'),
            'placeholder' => $this->language->get('entry_test_publishable_key')
        );

        $live_secret_key = array(
            'value' => $this->config->get('stripe_live_secret_key'),
            'required' => 'required',
            'placeholder' => $this->language->get('entry_live_api_key')
        );

        $live_publishable_key = array(
            'value' => $this->config->get('stripe_live_publishable_key'),
            'required' => 'required',
            'placeholder' => $this->language->get('entry_live_publishable_key')
        );

        $environment_option = array(
            'live' => $this->language->get('text_live'),
            'test' => $this->language->get('text_test')
        );

        $environment_text = array(
            'value'    => $this->config->get('stripe_environment', 'test'),
            'selected' => $this->config->get('stripe_environment', 'test'),
            'required' => 'required'
        );

        $environment_title = '<span data-toggle="tooltip" data-original-title="' . $this->language->get('help_test') . '">' . $this->language->get('entry_environment') . '</span>';

        $this->load->model('localisation/order_status');

        $order_option = array();

        $order_statuses = $this->model_localisation_order_status->getOrderStatuses();

        foreach ($order_statuses as $order_status) {
            $order_option[$order_status['order_status_id']] = $order_status['name'];
        }

        $order_text = array(
            'value'    => $this->config->get('stripe_order_status_id', 0),
            'selected' => $this->config->get('stripe_order_status_id', 0)
        );

        $currency_option = array(
            'usd' => $this->language->get('text_usd'),
            'eur' => $this->language->get('text_euro')
        );

        $currency_text = array(
            'value'    => $this->config->get('stripe_currency', 'usd'),
            'selected' => $this->config->get('stripe_currency', 'usd')
        );

        $currency_title = '<span data-toggle="tooltip" data-original-title="' . $this->language->get('help_currency') . '">' . $this->language->get('entry_currency') . '</span>';

        $store_cards = array(
            'value' => $this->config->get('stripe_status', 1),
            'labelclass' => 'radio-inline'
        );

        $status = array(
            'value' => $this->config->get('stripe_status', 1),
            'labelclass' => 'radio-inline'
        );

        $sort_order = array(
            'value' => $this->config->get('stripe_sort_order'),
            'placeholder' => $this->language->get('entry_sort_order')
        );

        $form = new AForm('form-stripe', $action);

        $form->addElement(new Arastta\Component\Form\Element\Textbox($this->language->get('entry_test_api_key'), 'stripe_test_secret_key', $test_secret_key));
        $form->addElement(new Arastta\Component\Form\Element\Textbox($this->language->get('entry_test_publishable_key'), 'stripe_test_publishable_key', $test_publishable_key));

        $form->addElement(new Arastta\Component\Form\Element\Textbox($this->language->get('entry_live_api_key'), 'stripe_live_secret_key', $live_secret_key));
        $form->addElement(new Arastta\Component\Form\Element\Textbox($this->language->get('entry_live_publishable_key'), 'stripe_live_publishable_key', $live_publishable_key));

        $form->addElement(new Arastta\Component\Form\Element\Select($environment_title, 'stripe_environment', $environment_option, $environment_text));
        $form->addElement(new Arastta\Component\Form\Element\Select($this->language->get('entry_order_status'), 'stripe_order_status_id', $order_option, $order_text));
        $form->addElement(new Arastta\Component\Form\Element\Select($currency_title, 'stripe_currency', $currency_option, $currency_text));

        $form->addElement(new Arastta\Component\Form\Element\YesNo($this->language->get('entry_card'), 'stripe_store_cards', $store_cards, $option_text));
        $form->addElement(new Arastta\Component\Form\Element\YesNo($this->language->get('entry_status'), 'stripe_status', $status, $option_text));
        $form->addElement(new Arastta\Component\Form\Element\Textbox($this->language->get('entry_sort_order'), 'stripe_sort_order', $sort_order));

        return $form->render(true);
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/stripe')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    protected function initStripe()
    {
        $this->load->library('stripe');

        if ($this->config->get('stripe_environment') == 'live') {
            $stripe_secret_key = $this->config->get('stripe_live_secret_key');
        } else {
            $stripe_secret_key = $this->config->get('stripe_test_secret_key');
        }

        if ($stripe_secret_key != '' && $stripe_secret_key != null && (strlen($stripe_secret_key) > 5)) {
            \Stripe\Stripe::setApiKey($stripe_secret_key);

            return true;
        }

        return false;
    }
}
