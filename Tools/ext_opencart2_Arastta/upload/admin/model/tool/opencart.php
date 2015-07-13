<?php
class ModelToolOpencart extends Model {

    public function createTables(){
        $sqlFile = file_get_contents(DIR_SYSTEM . '/helper/opencart.sql', true);

        if(DB_PREFIX == 'oc_') {
            $sqlFile = str_replace ( "`oc_", "`cs_", $sqlFile );
        }

        $sql = explode(";", $sqlFile);

        foreach ($sql as $key => $value) {
            if ($value != NULL) {

                $this->db->query($value);
            }
        }

        $this->removeFields();

    }

    protected function removeFields(){
        $dbprefix = 'oc_';
        if(DB_PREFIX == 'oc_') {
            $dbprefix = 'cs_';
        }

        $removes = array(
            $dbprefix .'address' => 'company_id',
            $dbprefix .'address' => 'tax_id',
            $dbprefix .'customer_group' => 'company_id_display',
            $dbprefix .'customer_group' => 'company_id_required',
            $dbprefix .'customer_group' => 'tax_id_display',
            $dbprefix .'customer_group' => 'tax_id_required',
            $dbprefix .'order' => 'payment_company_id',
            $dbprefix .'order' => 'payment_tax_id',
            $dbprefix .'language' => 'filename',
        );

        foreach($removes as $table => $field){
            $result = $this->db->query('SHOW COLUMNS FROM `' . $table . '`');

            foreach($result->rows as $row){
                if(in_array($field, $row)) {
                    $this->db->query('ALTER TABLE `' . $table .'` DROP `'. $field .'`;');
                }
            }

        }

        $result = $this->db->query('SHOW COLUMNS FROM `' . $dbprefix . 'setting`');

        if(in_array($field, $result->rows)) {
            $this->db->query('ALTER TABLE `' . $dbprefix . 'setting` CHANGE `group` `code` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;');

        }

    }

    public function importTables($ocPrefix){
        $sqlFile = file_get_contents(DIR_UPLOAD . $this->request->post['path'] . '/install.sql', true);

        if(DB_PREFIX == 'oc_') {
            $sqlFile = str_replace ( '`' . $ocPrefix . '_', "`cs_" , $sqlFile );
        } else {
            $sqlFile = str_replace ( '`' . $ocPrefix . '_', "`oc_"  , $sqlFile );
        }


        $check = strpos($sqlFile, ";\n");

        if ($check !== false) {
            $sql = explode(";\n", $sqlFile);
        } else {
            $check = strpos($sqlFile, ";\r\n");
            if($check !== false) {
                $sql = explode(";\r\n", $sqlFile);
            }
        }

        foreach ($sql as $key => $value) {
            $value = trim($value);

            $check = strpos($value, "TRUNCATE TABLE `");

            if ($check !== false) {
                $tablename = explode("TRUNCATE TABLE `", $value);
                $tablename = explode("`", $tablename[1]);
                $tablename = $tablename[0];
            }

            if(!empty($tablename)) {
                $table_exists = $this->db->query("SHOW TABLES LIKE '" . $tablename . "'");
                if($table_exists->num_rows > 0) {
                    if ($value != NULL) {
                        $this->db->query($value);
                    }
                    $tablename = '';
                }
            } else {
                if ($value != NULL) {

                    $data_array = explode('(', $value);

                    $data_key = @explode(',', $data_array[1]);

                    for ($i = 0; $i < count($data_key); $i++) {
                        $check = strpos($data_key[$i], ")");
                        if ($check !== false) {
                            $var = explode(')', $data_key[$i]);
                            $data_key[$i] = $var[0];
                        }
                    }

                    $data_value = @explode(',', $data_array[2]);

                    for ($i = 0; $i < count($data_value); $i++) {
                        $check = strpos($data_value[$i], ")");

                        if ($check !== false) {
                            $var = @explode(')', $data_value[$i]);
                            $data_value[$i] = $var[0];
                        }
                    }

                    $is_address = strpos($data_array[0], "address`");
                    $is_customer_group = strpos($data_array[0], "customer_group`");
                    $is_customer_group_tax_rate = strpos($data_array[0], "tax_rate_to_customer_group`");

                    if($is_customer_group_tax_rate !== false) {
                        $is_customer_group = false;
                    }

                    $is_order = strpos($data_array[0], "order`");
                    $is_language = strpos($data_array[0], "language`");
                    $is_product_option = strpos($data_array[0], "product_option`");
                    $is_setting = strpos($data_array[0], "setting`");

                    if (($is_address !== false ) || ($is_customer_group !== false ) || ($is_order !== false ) || ($is_product_option !== false ) || ($is_setting !== false )) {
                        $keys = " ( ";
                        $values = "";
                        for ($i = 0; $i < count($data_key); $i++) {
                            if ($is_address !== false) {
                                if ($data_key[$i] != " `company_id`" && $data_key[$i] != " `tax_id`") {
                                    $keys .= $data_key[$i] . ",";
                                    $values .= $data_value[$i] . ",";
                                }
                            } else if ($is_customer_group !== false) {
                                if ($data_key[$i] != " `company_id_display`" && $data_key[$i] != " `company_id_required`" && $data_key[$i] != " `tax_id_display`" && $data_key[$i] != " `tax_id_required`") {
                                    $keys .= $data_key[$i] . ",";
                                    $values .= $data_value[$i] . ",";
                                }
                            } else if ($is_order !== false) {
                                if ($data_key[$i] != " `payment_company_id`" && $data_key[$i] != " `payment_tax_id`") {
                                    $keys .= $data_key[$i] . ",";
                                    $values .= $data_value[$i] . ",";
                                }
                            } else if ($is_product_option !== false) {
                                $keys .= str_replace("option_value", "value", $data_key[$i]) . ",";
                                $values .= $data_value[$i] . ",";
                            } else if ($is_setting !== false) {
                                if(isset($data_value[$i]) && $data_value[$i] != " 'config_robots'") {
                                    $_data = str_replace("',", "*", $data_array[2]);
                                    $_data = str_replace(",", "#", $_data);
                                    $_data = str_replace("*", "',", $_data);
                                    $_data = str_replace("\'", "", $_data);

                                    $data_value = explode(",", $_data);

                                    $keys .= str_replace("group", "code", $data_key[$i]) . ",";
                                    $values .= @str_replace("#", ",", $data_value[$i]) . ",";
                                } else {
                                    $none = true;
                                }
                            } else {
                                $keys .= $data_key[$i] . ",";
                                $values .= $data_value[$i] . ",";
                            }

                        }
                        if(empty($none)){
                            $value = $data_array[0] . rtrim($keys, ",") . " ) VALUES ( " . rtrim($values, ",") . " );";
                        } else {
                            $value = '';
                            unset($none);
                        }
                    } else if($is_language !== false) {
                        $keys = " (";
                        $values = "";

                        $_data = str_replace("',", "*", $data_array[2]);
                        $_data = str_replace(",", "#", $_data);
                        $_data = str_replace("*", "',", $_data);

                        $data_value = explode(",", $_data);

                        for ($i = 0; $i < count($data_key); $i++) {
                            if ($data_key[$i] != " `filename`") {
                                $keys .= $data_key[$i] . ",";

                                if ($data_key[$i] == " `directory`") {
                                    $values .= str_replace("english", "en-GB", $data_value[$i]) . ",";
                                } else {
                                    $values .= str_replace("#", ",", $data_value[$i]) . ",";
                                }
                            }
                        }

                        $value = $data_array[0] . rtrim($keys, ",") . "(" . rtrim($values, ",") . ";";
                    }

                    if(!empty($value)) {
                        $this->db->query($value);
                    }
                }
            }
        }
    }

    public function migrateTables(){
        $query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "`");

        foreach ($query->rows as $result) {
            if(DB_PREFIX == 'oc_') {
                if (utf8_substr($result['Tables_in_' . DB_DATABASE], 0, strlen('cs_')) == 'cs_') {
                    $prefix = 'cs_';
                    if (isset($result['Tables_in_' . DB_DATABASE])) {
                        $table_data[] = $result['Tables_in_' . DB_DATABASE];
                    }
                }
            } else {
                if (utf8_substr($result['Tables_in_' . DB_DATABASE], 0, strlen('oc')) == 'oc') {
                    $prefix = 'oc_';
                    if (isset($result['Tables_in_' . DB_DATABASE])) {
                        $table_data[] = $result['Tables_in_' . DB_DATABASE];
                    }
                }
            }

        }

        $privateTable = array($prefix . "manufacturer", $prefix . "setting", $prefix . "event");

        foreach ($table_data as $table) {
            if (!in_array($table, $privateTable)) {
                $this->_defaultMigrate($table);
            } else {
                $this->_privateTable($table);
            }
        }

    }

    protected function _defaultMigrate($table){

        if(DB_PREFIX == 'oc_') {
            $tname = explode('cs_',$table);
        } else {
            $tname = explode('oc_', $table);
        }
        $table_name = DB_PREFIX  . $tname[1];

        if ("category" == $tname[1]){
            $this->addCategoryMenu($table);
        }else if ("affiliate_transaction" == $tname[1]){
            $table_name = DB_PREFIX  . 'affiliate_commission';
        }else if("customer_transaction" == $tname[1]){
            $table_name = DB_PREFIX  . 'customer_credit';
        }

        $sql = "TRUNCATE TABLE `" . $table_name . "` ;";
        $this->db->query($sql);

        $result = $this->db->query("SELECT * FROM " .  $table);
        
        if ($result->num_rows > 0){
            foreach ($result->rows as $row) {
                $sql = 'INSERT INTO `' . $table_name . '` SET ';
                $count = count($row);
                $i = 1 ;

                foreach($row as $key => $value){
                    if($count > $i) {
                        $sql .= "`" . $key . "` = '" . $this->db->escape($value) . "', ";
                    } else {
                        $sql .= "`" . $key . "` = '" . $this->db->escape($value) . "'";
                    }
                    $i++;

                }

                if("order_status" ==  $tname[1]) {
                    $sql .= ", `message` = ''";
                }

                $sql .= " ;";
                $this->db->query($sql);
            }

        }

        if("user_group" == $tname[1]) {
            $query = $this->db->query("SELECT permission FROM `" . DB_PREFIX . "user_group` WHERE `user_group_id` = 1");

            $permission = unserialize($query->row['permission']);

            $permission['access'][] = 'appearance/customizer';
            $permission['modify'][] = 'appearance/customizer';

            $permission['access'][] = 'appearance/layout';
            $permission['modify'][] = 'appearance/layout';

            $permission['access'][] = 'appearance/menu';
            $permission['modify'][] = 'appearance/menu';

            $permission['access'][] = 'common/edit';
            $permission['modify'][] = 'common/edit';

            $permission['access'][] = 'common/update';
            $permission['modify'][] = 'common/update';

            $permission['access'][] = 'extension/marketplace';
            $permission['modify'][] = 'extension/marketplace';

            $permission['access'][] = 'module/categoryhome';
            $permission['modify'][] = 'module/categoryhome';

            $permission['access'][] = 'module/login';
            $permission['modify'][] = 'module/login';

            $permission['access'][] = 'module/manufacturer';
            $permission['modify'][] = 'module/manufacturer';

            $permission['access'][] = 'search/search';
            $permission['modify'][] = 'search/search';

            $permission['access'][] = 'system/email_template';
            $permission['modify'][] = 'system/email_template';

            $permission['access'][] = 'tool/export_import';
            $permission['modify'][] = 'tool/export_import';

            $permission['access'][] = 'tool/file_manager';
            $permission['modify'][] = 'tool/file_manager';

            $permission['access'][] = 'system/language_override';
            $permission['modify'][] = 'system/language_override';

            $permission['access'][] = 'tool/opencart';
            $permission['modify'][] = 'tool/opencart';

            $permission = serialize($permission);

            $this->db->query("UPDATE `" . DB_PREFIX . "user_group` SET `permission` = '" . $permission . "' WHERE `user_group_id` = 1");
        }
    }

    protected function _privateTable($table){
        if(DB_PREFIX == 'oc_') {
            $tname = explode('cs_',$table);
        } else {
            $tname = explode('oc_', $table);
        }
        $table_name = DB_PREFIX  . $tname[1];

        // Manufacturer
        if ("manufacturer" == $tname[1]){

            $sql = "TRUNCATE TABLE `{$table_name}` ;";
            $this->db->query($sql);

            $sql = "TRUNCATE TABLE `{$table_name}_description` ;";
            $this->db->query($sql);

            $sql = "SELECT * FROM {$table}";
            $result = $this->db->query($sql);

            if ($result->num_rows > 0){
                foreach ($result->rows as $key => $value) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer SET manufacturer_id = '" . (int)$value['manufacturer_id'] . "', sort_order = '" . (int)$value['sort_order'] . "', status = '1', date_modified = NOW(), date_added = NOW(), image = '" . $value['image'] . "'");

                    $manufacturer_id = $value['manufacturer_id'];

                    $this->load->model('localisation/language');

                    $language = $this->model_localisation_language->getLanguages();

                    foreach($language as $code =>$langValue){
                        $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_description SET manufacturer_id = '" . (int)$manufacturer_id . "', language_id = '" . (int)$langValue['language_id'] . "', name = '" . $this->db->escape($value['name']) . "', description = '', meta_title = '" . $this->db->escape($value['name']) . "', meta_description = '', meta_keyword = ''");
                    }
                }
            }
        }

        // Setting
        if ("setting" == $tname[1]){

            $sql = "SELECT * FROM " . $table_name;
            $result = $this->db->query($sql);

            if ($result->num_rows > 0){
                foreach ($result->rows as $value) {
                    $code = $value['code'];
                    $key  = $value['key'];

                    $sql = "SELECT * FROM " . $table . " WHERE code = '" . $code ."' AND `key` = '" . $key ."'";

                    $data = $this->db->query($sql);

                    if ($data->num_rows > 0){
                        foreach ($data->rows as $setValue) {
                            $sqlDEL = "DELETE FROM ". $table_name ." WHERE code = '" . $code ."' AND `key` = '" . $key ."' AND store_id = '" . (int)$setValue['store_id'] . "'";
                            $this->db->query($sqlDEL);
                            if(!empty($setValue['serialized'])) {
                                $setValue['value'] = unserialize($setValue['value']);
                            }

                            if (empty($setValue['serialized'])) {
                                $this->db->query("INSERT INTO " . $table_name . " SET store_id = '" . (int)$setValue['store_id'] . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($setValue['value']) . "'");
                            } else {
                                $this->db->query("INSERT INTO " . $table_name . " SET store_id = '" . (int)$setValue['store_id'] . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(serialize($setValue['value'])) . "', serialized = '1'");
                            }
                        }
                    }
                }
            }

        }
    }

    protected function addCategoryMenu($table) {
        // Menu Parent
        $sql = "SELECT * FROM {$table} WHERE parent_id = 0 and top = 1";

        $result = $this->db->query($sql);

        $sql = "TRUNCATE TABLE " . DB_PREFIX . "menu";
        $this->db->query($sql);
        $sql = "TRUNCATE TABLE " . DB_PREFIX . "menu_description";
        $this->db->query($sql);
        $sql = "TRUNCATE TABLE " . DB_PREFIX . "menu_to_store";
        $this->db->query($sql);
        $sql = "TRUNCATE TABLE " . DB_PREFIX . "menu_child";
        $this->db->query($sql);
        $sql = "TRUNCATE TABLE " . DB_PREFIX . "menu_child_description";
        $this->db->query($sql);
        $sql = "TRUNCATE TABLE " . DB_PREFIX . "menu_child_to_store";
        $this->db->query($sql);

        if ($result->num_rows > 0){
            foreach ($result->rows as $key => $value) {

                $this->db->query("INSERT INTO " . DB_PREFIX . "menu SET  sort_order= '" . $value['sort_order'] . "', columns = '" . $value['column'] . "', menu_type = 'category', status = '1'");
                $menu_id = $this->db->getLastId();

                // Menu Description
                $sql = "SELECT * FROM {$table}_description WHERE category_id = {$value['category_id']}";
                $data = $this->db->query($sql);

                if ($result->num_rows > 0) {
                    foreach ($data->rows as $descKey => $descValue) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "menu_description SET menu_id = '" . (int)$menu_id . "', language_id = '" . (int)$descValue['language_id'] . "', name = '" . $this->db->escape($descValue['name']) . "', link = '" . $value['category_id'] . "'");
                    }
                }

                // Menu Store
                $sql = "SELECT * FROM {$table}_to_store WHERE category_id = {$value['category_id']}";
                $data = $this->db->query($sql);

                if ($result->num_rows > 0) {
                    foreach ($data->rows as $storeKey => $storeValue) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "menu_to_store SET menu_id = '" . (int)$menu_id . "', store_id = '" . $storeValue['store_id'] . "'");
                    }
                }
            }
        }

        # Menu Child
        $sql = "SELECT * FROM {$table} WHERE parent_id > 0 and top = 1";
        $result = $this->db->query($sql);

        if ($result->num_rows > 0){
            foreach ($result->rows as $key => $value) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "menu_child SET menu_id = '" . $value['parent_id'] ."', sort_order = '" . (int)$value['sort_order'] . "', menu_type = 'category', status = '1'");
                $menu_child_id = $this->db->getLastId();

                // Menu Child Description

                $sql = "SELECT * FROM {$table}_description WHERE category_id = {$value['category_id']}";
                $data = $this->db->query($sql);

                if ($data->num_rows > 0) {
                    foreach ($data->rows as $descKey => $descValue) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "menu_child_description SET menu_id = '" . $value['parent_id'] . "', menu_child_id = '" . (int)$menu_child_id . "', language_id = '" . (int)$descValue['language_id'] . "', name = '" . $this->db->escape($descValue['name']) . "', link = '" . $this->db->escape($descValue['category_id']) . "'");
                    }
                }

                // Menu Child Store

                $sql = "SELECT * FROM {$table}_to_store WHERE category_id = {$value['category_id']}";
                $data = $this->db->query($sql);

                if ($data->num_rows > 0) {
                    foreach ($data->rows as $storeKey => $storeValue) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "menu_child_to_store SET menu_child_id = '" . (int)$menu_child_id . "', store_id = '" . $storeValue['store_id'] . "'");
                    }
                }
            }
        }
    }

    public function deleteTables() {
        $query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "`");

        foreach ($query->rows as $result) {
            if(DB_PREFIX == 'oc_') {
                if (utf8_substr($result['Tables_in_' . DB_DATABASE], 0, strlen('cs_')) == 'cs_') {
                    if (isset($result['Tables_in_' . DB_DATABASE])) {
                        $table_data[] = $result['Tables_in_' . DB_DATABASE];
                    }
                }
            } else {
                if (utf8_substr($result['Tables_in_' . DB_DATABASE], 0, strlen('oc')) == 'oc') {
                    if (isset($result['Tables_in_' . DB_DATABASE])) {
                        $table_data[] = $result['Tables_in_' . DB_DATABASE];
                    }
                }
            }

        }

        foreach ($table_data as $table) {
            $this->db->query("DROP TABLE " . $table);
        }
    }
}