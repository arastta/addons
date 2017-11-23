<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class ModelTotalPaymentFee extends Model
{
    public function getTotal(&$total_data, &$total, &$taxes)
    {
        if (isset($this->session->data['payment_method'])) {
            $this->load->language('total/payment_fee');

            $payment_methods = $this->config->get('payment_fee_payment_method');

            $value = 0;

            foreach ($payment_methods as $payment_method) {
                if ($this->session->data['payment_method']['code'] == $payment_method['method']) {
                    $value = $payment_method['fee'];
                }
            }

            $total_data[] = array(
                'code' => 'payment_fee',
                'title' => $this->language->get('text_payment_fee'),
                'value' => $value,
                'sort_order' => $this->config->get('payment_fee_sort_order')
            );

            $total += $value;
        }
    }
}
