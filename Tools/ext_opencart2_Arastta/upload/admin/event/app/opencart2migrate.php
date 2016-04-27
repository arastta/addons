<?php
/**
 * @package        Arastta eCommerce
 * @copyright      Copyright (C) 2015-2016 Arastta Association. All rights reserved. (arastta.org)
 * @license        GNU General Public License version 3; see LICENSE.txt
 */

class EventAppOpencart2migrate extends Event
{
    public function preAdminMenuRender(&$menu)
    {
        $this->language->load('tool/opencart');

        $title = $this->language->get('heading_title');

        $link = $this->url->link('tool/opencart', 'token=' . $this->session->data['token'], 'SSL');

        $permission = $this->user->hasPermission('access', 'tool/opencart');

        $menu->addMenuItem('opencart2migrate', $title, $link, 'tools', $permission, '', 7);
    }
}
