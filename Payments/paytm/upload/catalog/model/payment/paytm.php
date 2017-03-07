<?php 
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class ModelPaymentPaytm extends Model {
    
      public function getMethod($address, $total) {
        $this->language->load('payment/paytm');
        
          $method_data = array( 
            'code'       => 'paytm',
            'title'      => $this->language->get('text_title'),
            'sort_order' => $this->config->get('paytm_sort_order'),
            'terms'      => ''
          );
        
        return $method_data;
      }
}