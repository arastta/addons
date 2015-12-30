<?php
/**
 * @package        Arastta eCommerce
 * @copyright      Copyright (C) 2015 Arastta Association. All rights reserved. (arastta.org)
 * @credits        See CREDITS.txt for credits and other copyright notices.
 * @license        GNU General Public License version 3; see LICENSE.txt
 */
 
class ModelPaymentTrPos extends Model
{
    public function install()
    {
        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trposbank` (
				`bank_id` INT(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(64) NOT NULL,
				`image` varchar(64) NOT NULL,
				`method` varchar(64) NOT NULL,
				`model` varchar(64) NOT NULL,
				`short` varchar(64) NOT NULL,
				`status` tinyint(1) NOT NULL,
				PRIMARY KEY (`bank_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;"
        );
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trposbank`;");
    }
    
    public function addBank($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "trposbank SET name = '" . $this->db->escape($data['name']) . "', image = '" . $this->db->escape($data['image']) . "', method = '" . $this->db->escape($data['method']) . "', model = '" . $this->db->escape($data['model']) . "', short = '" . $this->db->escape($data['short']) . "', status = '" . (int) $data['status'] . "'");

        $bank_id = $this->db->getLastId();

        return $bank_id;
    }

    public function editBank($bank_id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "trposbank SET name = '" . $this->db->escape($data['name']) . "', image = '" . $this->db->escape($data['image']) . "', method = '" . $this->db->escape($data['method']) . "', model = '" . $this->db->escape($data['model']) . "', short = '" . $this->db->escape($data['short']) . "', status = '" . (int) $data['status'] . "' WHERE bank_id = '" . (int) $bank_id . "'");
    }

    public function deleteBank($bank_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "trposbank WHERE bank_id = '" . (int) $bank_id . "'");
    }

    public function getBank($bank_id)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "trposbank WHERE bank_id = '" . (int) $bank_id . "'");

        return $query->row;
    }

    public function getBanks($data = array())
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "trposbank";

        $sort_data = array(
            'name',
            'bank_id',
            'status'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data) && $data['sort'] != 'status') {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            if (isset($data['sort']) && ($data['sort'] == 'status')) {
                $sql .= " WHERE status = 1 ORDER BY name";
            } else {
                $sql .= " ORDER BY name";
            }
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }

        $query = $this->db->query($sql);

        $result = array();

        if ($query->num_rows) {
            foreach ($query->rows as $row) {
                $result[$row['bank_id']] = $row;
            }
        }


        return $result;
    }

    public function getTotalBanks()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "trposbank;");

        return $query->row['total'];
    }

    public function checkShortName($short)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trposbank WHERE short = '" . $this->db->escape($short) . "'");

        $bank = array();

        if ($query->num_rows) {
            $bank  = $query->row;

            if (!isset($this->request->request['bank_id'])) {
                return $bank;
            } else {
                if ($bank['bank_id'] == $this->request->request['bank_id']) {
                    return null;
                } else {
                    return $bank;
                }
            }
        }

        return $bank;
    }
}