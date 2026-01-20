<?php

require_once dirname(__FILE__) . '/../../functions.php';
require_once dirname(__FILE__) . '/mercadopago/mercadopago.php';
require_once dirname(__FILE__) . '/pagseguro/pagseguro.php';

$logged_user = getLoggedClient();
$result = [];

if (isset($_POST['planId']) && !empty($_POST['planId'])) {
  $plan = getClientPlanByID($logged_user['member_id'], strip_tags($_POST['planId']));

  if ($plan) {
    $connections = intval($logged_user["max_connections"]);
    $pricePlan = intval($plan['price']);
    $priceValue = $connections * $pricePlan;

    $payment_id = createPayment([
      'plan_type' => 'client_renew',
      'plan_id' => $plan['id'],
      'seller_id' => $logged_user['member_id'],
      'buyer_id' => $logged_user['id'],
      'amount' => doubleval(strip_tags($priceValue))
    ]);

    $result['payment'] = $payment_id;

    // Mercadopago
    if ((getUserProperty($logged_user['member_id'], "mercado_pago")) && (strlen(getUserProperty($logged_user['member_id'], "mercado_pago_public_key")) > 30) && (strlen(getUserProperty($logged_user['member_id'], "mercado_pago_access_token")) > 60)) {

      $mercadopagoGateway = new MercadopagoGateway();
      $mercadopago = $mercadopagoGateway->getCodeCheckout([
        'id' => strip_tags($payment_id),
        'user_id' => $logged_user['member_id'],
        'username' => strip_tags($logged_user['username']),
        'price' => strip_tags($priceValue)
      ]);

      $result['mercadopago'] = $mercadopago['id'];
    }

    // Pagseguro
    if ((getUserProperty($logged_user['member_id'], "pag_seguro")) && (filter_var(getUserProperty($logged_user['member_id'], "pag_seguro_email"), FILTER_VALIDATE_EMAIL) != false) && (strlen(getUserProperty($logged_user['member_id'], "pag_seguro_token")) > 20)) {
      $pagseguroGateway = new PagseguroGateway(false);
      $pagseguro = $pagseguroGateway->getCodeCheckout([
        'id' => strip_tags($payment_id),
        'username' => strip_tags($logged_user['username']),
        'price' => strip_tags($priceValue)
      ]);

      $result['pagseguro'] = $pagseguro['id'];
    }

    // Woovi
    if ((getUserProperty($logged_user['member_id'], "woovi")) && (strlen(getUserProperty($logged_user['member_id'], "woovi_token")) > 20)) {
      include_once dirname(__FILE__) . '/../../class/CurlRequest.php';

      $data = [
        'correlationID' => strip_tags($payment_id),
        'value' => number_format(strip_tags($priceValue), 2, '', ''),
        'comment' => "Renovação: " . $logged_user['username'],
      ];
      $request_url = "https://api.openpix.com.br/api/v1/charge";
      $header = [
        "Authorization: " . getUserProperty($logged_user['member_id'], "woovi_token"),
      ];
      $CurlRequest = new CurlRequest($request_url, "POST", $data, $header);
      $response = $CurlRequest->makeRequest();

      if ($response['http_code'] == 200) {
        $response = json_decode($response['response'], true);
        $result['woovi'] = $response['charge'];
      } else {
        $result['woovi'] = false;
      }
    }
  }
}
