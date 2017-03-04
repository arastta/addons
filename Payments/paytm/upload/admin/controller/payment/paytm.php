<?php

require_once(DIR_SYSTEM . 'paytm/encdec_paytm.php');
require_once(DIR_SYSTEM . 'paytm/paytm_constants.php');

class ControllerPaymentPaytm extends Controller {
    
	private $error = array();
	//function executed at load of page
    
	public function index() {
		$this->language->load('payment/paytm');

		$this->document->setTitle($this->language->get('heading_title'));
		$arr = array();	
		
		foreach($this->request->post as $key => $value)
		{
			if($key == 'paytm_key')
			{
				 $arr[$key] = encrypt_e($value, $const1);
				continue;
			}
			$arr[$key] = $value;
		}
		
		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('paytm', $arr);

			$this->session->data['success'] = $this->language->get('text_success');
            
            if (isset($this->request->post['button']) && ($this->request->post['button'] == 'save')) {
                $this->response->redirect($this->url->link($this->request->get['route'], 'token=' . $this->session->data['token'], 'SSL'));
            }

			$this->response->redirect($this->url->link('extension/extension', 'filter_type=payment&token=' . $this->session->data['token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_live'] = $this->language->get('text_live');
		$data['text_successful'] = $this->language->get('text_successful');
		$data['text_fail'] = $this->language->get('text_fail');
		$data['text_env_production'] = $this->language->get('text_env_production');
		$data['text_env_test'] = $this->language->get('text_env_test');

		$data['entry_merchant'] = $this->language->get('entry_merchant');
		$data['entry_merchant_help'] = $this->language->get('entry_merchant_help');
		$data['entry_merchantkey'] = $this->language->get('entry_merchantkey');
		$data['entry_merchantkey_help'] = $this->language->get('entry_merchantkey_help');
		$data['entry_website'] = $this->language->get('entry_website');
		$data['entry_website_help'] = $this->language->get('entry_website_help');
		$data['entry_industry'] = $this->language->get('entry_industry');
		$data['entry_industry_help'] = $this->language->get('entry_industry_help');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['callbackurl_status'] = $this->language->get('callbackurl_status');
		$data['entry_checkstatus'] = $this->language->get('entry_checkstatus');
		$data['entry_checkstatus_help'] = $this->language->get('entry_checkstatus_help');
		$data['entry_environment'] = $this->language->get('entry_environment');
		$data['entry_environment_help'] = $this->language->get('entry_environment_help');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['merchant'])) {
			$data['error_merchant'] = $this->error['merchant'];
		} else {
			$data['error_merchant'] = '';
		}
		if (isset($this->error['key'])) {
			$data['error_key'] = $this->error['key'];
		} else {
			$data['error_key'] = '';
		}
		if (isset($this->error['website'])) {
			$data['error_website'] = $this->error['website'];
		} else {
			$data['error_website'] = '';
		}
		
		if (isset($this->error['industry'])) {
			$data['error_industry'] = $this->error['industry'];
		} else {
			$data['error_industry'] = '';
		}
		
		if (isset($this->request->post['paytm_order_status_id'])) {
			$data['paytm_order_status_id'] = $this->request->post['paytm_order_status_id'];
		} else {
			$data['paytm_order_status_id'] = $this->config->get('paytm_order_status_id');
		}
		
		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/paytm', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

		$data['action'] = $this->url->link('payment/paytm', 'token=' . $this->session->data['token'], 'SSL');

		$data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['paytm_merchant'])) {
			$data['paytm_merchant'] = $this->request->post['paytm_merchant'];
		} else {
			$data['paytm_merchant'] = $this->config->get('paytm_merchant');
		}
		if (isset($this->request->post['paytm_website'])) {
			$data['paytm_website'] = $this->request->post['paytm_website'];
		} else {
			$data['paytm_website'] = $this->config->get('paytm_website');
		}
		
		if (isset($this->request->post['paytm_industry'])) {
			$data['paytm_industry'] = $this->request->post['paytm_industry'];
		} else {
			$data['paytm_industry'] = $this->config->get('paytm_industry');
		}
		
		if (isset($this->request->post['paytm_key'])) {
		
			$data['paytm_key'] = $this->request->post['paytm_key'];
		} else {
			$data['paytm_key'] = $this->config->get('paytm_key');

			$data['paytm_key'] = "";
			if ($this->config->get('paytm_key') != "") {
				$data['paytm_key'] = htmlspecialchars_decode(decrypt_e($this->config->get('paytm_key'),$const1),ENT_NOQUOTES);
			}
			
		}

		
		if (isset($this->request->post['paytm_status'])) {
			$data['paytm_status'] = $this->request->post['paytm_status'];
		} else {
			$data['paytm_status'] = $this->config->get('paytm_status');
		}
		if (isset($this->request->post['paytm_callbackurl'])) {
			$data['paytm_callbackurl'] = $this->request->post['paytm_callbackurl'];
		} else {
			$data['paytm_callbackurl'] = $this->config->get('paytm_callbackurl');
		}
		if (isset($this->request->post['paytm_checkstatus'])) {
			$data['paytm_checkstatus'] = $this->request->post['paytm_checkstatus'];
		} else {
			$data['paytm_checkstatus'] = $this->config->get('paytm_checkstatus');
		}

		if (isset($this->request->post['paytm_environment'])) {
			$data['paytm_environment'] = $this->request->post['paytm_environment'];
		} else {
			$data['paytm_environment'] = $this->config->get('paytm_environment');
		}

		$this->template = 'payment/paytm.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$data['header'] = $this->load->controller('common/header');
				$data['column_left'] = $this->load->controller('common/column_left');

		$data['footer'] = $this->load->controller('common/footer');
		//$this->response->setOutput($this->render());
		$this->response->setOutput($this->load->view('payment/paytm.tpl', $data));
		
	}
	//validate function to ensure required fields are filled before proceeding
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/paytm')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['paytm_merchant']) {
			$this->error['merchant'] = $this->language->get('error_merchant');
		}
		if (!$this->request->post['paytm_key']) {
			$this->error['key'] = $this->language->get('error_key');
		}
		if (!$this->request->post['paytm_website']) {
			$this->error['website'] = $this->language->get('error_website');
		}
		if (!$this->request->post['paytm_industry']) {
			$this->error['industry'] = $this->language->get('error_industry');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
	public function orderAction() {
	}
}
?>