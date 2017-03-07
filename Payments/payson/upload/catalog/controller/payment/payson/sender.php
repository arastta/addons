<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class Sender {
    protected $email;
    protected $firstName;
    protected $lastName;

    public function __construct($email, $firstName, $lastName){
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function getEmail(){
        return $this->email;
    }

    public function getFirstName(){
        return $this->firstName;
    }

    public function getLastName(){
        return $this->lastName;
    }

    public function addSenderToOutput(&$output){
        $output["senderEmail"] = $this->getEmail();
        $output["senderFirstName"] = $this->getFirstName();
        $output["senderLastName"] = $this->getLastName();
    }
}
