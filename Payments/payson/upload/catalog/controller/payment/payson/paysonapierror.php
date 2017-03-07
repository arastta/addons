<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class PaysonApiError {
    protected $errorId;
    protected $message;
    protected $parameter;

    public function __construct($errorId, $message, $parameter = null){
        $this->errorId = $errorId;
        $this->message = $message;
        $this->parameter = $parameter;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getParameter() {
        return $this->parameter;
    }

    public function getErrorId() {
        return $this->errorId;
    }

    public function __toString() {
        return "ErrorId: " . $this->getErrorId() .
               " Message: " . $this->getMessage() .
               " Parameter: " . $this->getParameter();
    }
}
