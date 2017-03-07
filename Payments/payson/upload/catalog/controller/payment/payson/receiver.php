<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class Receiver {

    protected $email;
    protected $amount;
    protected $firstName;
    protected $lastName;
    protected $isPrimary;

    const FORMAT_STRING = "receiverList.receiver(%d).%s";

    public function __construct($email, $amount, $firstName = null, $lastName = null, $isPrimary = true)
    {
        $this->email = $email;
        $this->amount = $amount;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->isPrimary = $isPrimary;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public static function parseReceivers($data)
    {
        $receivers = array();

        $i = 0;
        while(isset($data[sprintf(self::FORMAT_STRING, $i, "email")])) {
            $receivers[$i] = new Receiver(
                $data[sprintf(self::FORMAT_STRING, $i, "email")],
                $data[sprintf(self::FORMAT_STRING, $i, "amount")]);
            $i++;
        }

        return $receivers;
    }

    public static function addReceiversToOutput($items, &$output)
    {
        $i = 0;
        foreach ($items as $item) {
            $output[sprintf(self::FORMAT_STRING, $i, "email")] = $item->getEmail();
            $output[sprintf(self::FORMAT_STRING, $i, "amount")] = number_format($item->getAmount(), 2, ".", ",");
            $i++;
        }
    }

    public function __toString()
    {
        return "email: " . $this->email . " amount: " . $this->amount;
    }
}
