<?php
require_once dirname(__FILE__) . '/../../functions.php';
require_once dirname(__FILE__) . '/mercadopago/mercadopago.php';

if (!isset($_GET['seller_id']) && isset($_SESSION["__l0gg3d_Client__"])) {
  $logged_user = getLoggedClient();
  $member_id = $logged_user['member_id'];
} else if (isset($_GET['seller_id'])) {
  $member_id = intval(strip_tags($_GET['seller_id']));
}

if (!empty($member_id)) {
  $mercado_pago_access_token = getUserProperty($member_id, 'mercado_pago_access_token');
  MercadoPago\SDK::setAccessToken($mercado_pago_access_token);
  $merchant_order = null;

  if (isset($_GET["topic"]) && !empty($_GET["topic"])) {
    switch ($_GET["topic"]) {
      case "payment":
        $payment = MercadoPago\Payment::find_by_id($_GET["id"]);
        $merchant_order = MercadoPago\MerchantOrder::find_by_id($payment->order->id);
        break;
      case "merchant_order":
        $merchant_order = MercadoPago\MerchantOrder::find_by_id($_GET["id"]);
        break;
    }

    $payment_id = $payment->additional_info->items[0]->id;
    setActionPayment($payment->status, intval($payment_id));

    $result = ['success' => true];
  }
}
