<?php
//insert permission for tool/opencart

$query = $this->db->query("SELECT permission FROM `" . DB_PREFIX . "user_group` WHERE `user_group_id` = 1");

$permission = unserialize($query->row['permission']);

$permission['access'][] = 'tool/opencart';
$permission['modify'][] = 'tool/opencart';

$permission = serialize($permission);

$this->db->query("UPDATE `" . DB_PREFIX . "user_group` SET `permission` = '" . $permission . "' WHERE `user_group_id` = 1");
		
?>		