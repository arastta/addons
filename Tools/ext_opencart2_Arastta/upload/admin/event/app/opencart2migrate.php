<?php

class EventAppOpencart2migrate extends Event {

    public function preAdminMenuRender(&$menu) {
		$this->language->load('tool/opencart');
		
		$title = $this->language->get('heading_title');
		
		$link = $this->url->link('tool/opencart', 'token=' . $this->session->data['token'], 'SSL');
		
        $permission = $this->user->hasPermission('access', 'tool/opencart');

        $menu->addMenuItem('opencart2migrate', $title, $link, 'tools', $permission, '',  7);
	}
}