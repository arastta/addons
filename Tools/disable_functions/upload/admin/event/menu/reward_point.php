<?php
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
