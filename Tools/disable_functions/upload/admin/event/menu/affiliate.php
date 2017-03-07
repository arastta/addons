<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class EventMenuAffiliate extends Event
{
    public function preAdminMenuRender(&$menu)
    {
        if ($this->config->get('config_affiliate_status')) {
            return true;
        }

        // Affilate
        $menu->removeMenuItem('affiliate', 'marketings');

        // Affilate
        $menu->removeMenuItem('affiliates', 'marketing');
        $menu->removeMenuItem('affiliate_activity', 'marketing');
    }
}
