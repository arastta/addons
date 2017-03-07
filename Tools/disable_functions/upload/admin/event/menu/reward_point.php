<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class EventMenuRewardPoint extends Event
{
    public function preAdminMenuRender(&$menu)
    {
        if ($this->config->get('config_reward_point_status')) {
            return true;
        }

        // Remove Reward Point
        $menu->removeMenuItem('reward_points', 'customers');
    }
}
