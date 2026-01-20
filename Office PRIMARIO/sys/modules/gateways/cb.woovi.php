<?php
require_once dirname(__FILE__) . '/../../functions.php';

// Get the JSON input data
$input = file_get_contents('php://input');

// Decode the JSON data into an associative array
$data = json_decode($input, true);
if (!isset($data['charge'])) {
  die('Invalid data');
}

$charge = $data['charge'];

if ($charge['status'] != 'COMPLETED') {
  die('Invalid status');
}

$payment = getPaymentByID(intval($charge['correlationID']));

if (!$payment) {
  die('Payment not found');
}

$woovi_token = getUserProperty($payment['seller_id'], 'woovi_token');

// Check if the payment was approved
include_once dirname(__FILE__) . '/../../class/CurlRequest.php';

$request_url = "https://api.openpix.com.br/api/v1/charge/" . $charge['correlationID'];
$header = [
  "Authorization: " . $woovi_token,
];
$CurlRequest = new CurlRequest($request_url, "GET", $data, $header);
$response = $CurlRequest->makeRequest();

if ($response['http_code'] == 200) {
  $response = json_decode($response['response'], true);
  if ($response['charge']['status'] != 'COMPLETED') {
    die('Fraud detected');
  }
  setActionPayment('success', intval($payment['id']));
  die('Payment approved');
} else {
  die('Invalid response');
}
