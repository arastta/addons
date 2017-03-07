<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

require_once "responseenvelope.php";

class PaymentUpdateResponse {
    protected $responseEnvelope;

    public function __construct($responseData) {
        $this->responseEnvelope = new ResponseEnvelope($responseData);
    }

    public function getResponseEnvelope() {
        return $this->responseEnvelope;
    }

    public function __toString() {
        return $this->responseEnvelope->__toString();
    }
}

