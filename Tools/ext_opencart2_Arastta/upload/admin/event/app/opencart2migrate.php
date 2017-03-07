<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
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
