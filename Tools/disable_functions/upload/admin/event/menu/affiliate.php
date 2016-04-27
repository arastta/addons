<?php
/**
 * @package        Arastta eCommerce
 * @copyright      Copyright (C) 2015 Arastta Association. All rights reserved. (arastta.org)
 * @license        GNU General Public License version 3; see LICENSE.txt
 */

class EventMenuAffiliate extends Event
{
    public function preAdminMenuRender(&$menu)
    {
        if ($this->config->get('config_affiliate_status')) {
            //return true;
        }

        // Affilate
        $menu->removeMenuItem('affiliate', 'marketings');

        // Affilate
        $menu->removeMenuItem('affiliates', 'marketing');
        $menu->removeMenuItem('affiliate_activity', 'marketing');
    }
}
