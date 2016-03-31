<?php
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
