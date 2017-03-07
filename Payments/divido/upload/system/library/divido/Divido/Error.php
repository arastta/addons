<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class Divido_Error extends Exception
{
  public function __construct($message, $httpStatus=null,
      $httpBody=null, $jsonBody=null
  )
  {
    parent::__construct($message);
    $this->httpStatus = $httpStatus;
    $this->httpBody = $httpBody;
    $this->jsonBody = $jsonBody;
  }

  public function getHttpStatus()
  {
    return $this->httpStatus;
  }

  public function getHttpBody()
  {
    return $this->httpBody;
  }

  public function getJsonBody()
  {
    return $this->jsonBody;
  }
}
