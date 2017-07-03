<?php
/**
 * @package        Arastta eCommerce
 * @copyright      Copyright (C) 2015-2016 Arastta Association. All rights reserved. (arastta.org)
 * @license        GNU General Public License version 3; see LICENSE.txt
 */

class EventMenuHepsiPay extends Event
{
    public function preAdminMenuRender(&$menu)
    {
        $this->language->load('payment/hepsipay');

        $title = $this->language->get('text_hepsipay_history');

        $link = $this->url->link('payment/hepsipay', 'token=' . $this->session->data['token'], 'SSL');

        $permission = $this->user->hasPermission('access', 'payment/hepsipay');

        $menu->addMenuItem('hepsipay', $title, $link, 'sale', $permission, '', 6);
    }
}
