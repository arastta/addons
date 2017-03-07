<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

//insert permission for tool/opencart

$query = $this->db->query("SELECT permission FROM `" . DB_PREFIX . "user_group` WHERE `user_group_id` = 1");

$permission = unserialize($query->row['permission']);

$permission['access'][] = 'tool/opencart';
$permission['modify'][] = 'tool/opencart';

$permission = serialize($permission);

$this->db->query("UPDATE `" . DB_PREFIX . "user_group` SET `permission` = '" . $permission . "' WHERE `user_group_id` = 1");
