<?php

require_once dirname(__FILE__) . '/vendor/autoload.php';

use Curl\Curl;

class PagseguroGateway
{
  protected $email;
  protected $token;

  private $isTest;
  public $baseUrl = '';

  public function __construct($isTest = false)
  {
    $logged_user = getLoggedClient();

    $this->curl = new Curl();
    $this->curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');

    $email = getUserProperty($logged_user['member_id'], 'pag_seguro_email');
    $token = getUserProperty($logged_user['member_id'], 'pag_seguro_token');

    $this->email = (string) $email;
    $this->token = (string) $token;

    $this->baseUrl = "https://ws" . ($isTest ? '.sandbox' : '') . ".pagseguro.uol.com.br/v2/";
  }

  function setUrl($pathname)
  {
    $auth = [
      'email' => $this->email,
      'token' => $this->token
    ];

    $query = http_build_query($auth);

    return $this->baseUrl . ltrim($pathname, "/") . "?" . $query;
  }

  function getCodeCheckout($data)
  {
    $response = (array) $this->curl->post($this->setUrl("checkout/"), [
      'currency' => 'BRL',
      'itemId1' =>  $data['username'],
      'itemDescription1' => "Renovar: {$data['username']}",
      'itemAmount1' => (string) number_format($data['price'], 2, '.', ''),
      'itemQuantity1' => 1,
      'itemWeight1' => 0
    ]);

    $result = [
      'id' => $response['code']
    ];

    return $result;
  }
}
