<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class ControllerModuleSagepayDirectCards extends Controller
{
    public function index()
    {
        if ($this->config->get('sagepay_direct_cards_status') && $this->config->get('sagepay_direct_status') && $this->customer->isLogged()) {
            $this->load->language('account/sagepay_direct_cards');

            $data['text_card'] = $this->language->get('text_card');
            $data['card']      = $this->url->link('account/sagepay_direct_cards', '', true);

            return $this->load->view('module/sagepay_direct_cards', $data);
        }
    }
}
