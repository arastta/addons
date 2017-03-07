<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class PaymentUpdateData {
    protected $token;
    protected $method;

    public function __construct($token, $method) {
        $this->token = $token;
        $this->method = $method;
    }

    public function getOutput() {
        $output = array();

        $output["token"] = $this->token;
        $output["action"] = PaymentUpdateMethod::ConstantToString($this->method);

        return $output;
    }
}
