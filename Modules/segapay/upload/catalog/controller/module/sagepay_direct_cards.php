<?php
/**
 * @package        Arastta eCommerce
 * @copyright      Copyright (C) 2015-2016 Arastta Association. All rights reserved. (arastta.org)
 * @credits        See CREDITS.txt for credits and other copyright notices.
 * @license        GNU General Public License version 3; see LICENSE.txt
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
