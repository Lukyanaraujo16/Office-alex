<?php

require_once dirname(__FILE__) . '/../../functions.php';

$result = ['success' => false];
$gateways = ['pagseguro', 'mercadopago', 'woovi'];
if (
  isset($_POST['gateway']) && !empty($_POST['gateway']) &&
  isset($_GET['payment']) && !empty($_GET['payment'])
) {

  // Gateway name
  $gateway = mb_strtolower($_POST['gateway']);

  // Check if exist in list ($gateways)
  if (in_array(mb_strtolower($gateway), $gateways)) {
    $logged_user = getLoggedClient();
    $payment = getPaymentByID(intval($_GET['payment']));

    if ($payment['buyer_id'] == $logged_user['id']) {
      $updated = updatePayment(intval($_GET['payment']), [
        'gateway_name' => $gateway
      ]);

      $result = ['success' => boolval($updated)];
    }
  }
}
