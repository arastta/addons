<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

/* Redirect to the payment module page in the current directory that was requested */
$host  = $_SERVER['HTTP_HOST'];
$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$path  = 'index.php?route=payment/paytm/callback';
$query = "";
foreach($_POST as $key => $value) {
    $query .= '&' . $key . '=' . urlencode($value);
}
$query = rtrim($query, '&');
header("Location: http://$host$uri/$path$query");
exit;
