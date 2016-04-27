<?php
/**
 * @package        Arastta eCommerce
 * @copyright      Copyright (C) 2015-2016 Arastta Association. All rights reserved. (arastta.org)
 * @license        GNU General Public License version 3; see LICENSE.txt
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
