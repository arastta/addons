<?php

require_once "responseenvelope.php";
require_once "orderitem.php";
require_once "receiver.php";
require_once "paymentdetails.php";

class PaymentDetailsResponse {
    protected $responseEnvelope;
    protected $paymentDetails;

    public function __construct($responseData) {
        $this->responseEnvelope = new ResponseEnvelope($responseData);
        $this->paymentDetails = new PaymentDetails($responseData);
    }
    
    /**
     * 
     * @return ResponseEnvelope
     */
    public function getResponseEnvelope() {
        return $this->responseEnvelope;
    }

    /**
     * 
     * @return PaymentDetails
     */
    public function getPaymentDetails() {
        return $this->paymentDetails;
    }

    public function __toString() {
        return $this->paymentDetails->__toString();
    }
}

?>
