<?php

require_once dirname(__FILE__) . '/../../../functions.php';
require_once dirname(__FILE__) . '/vendor/autoload.php';

class MercadopagoGateway
{
  protected $preference;

  public function __construct()
  {
    $logged_user = getLoggedClient();

    $mercado_pago_access_token = getUserProperty($logged_user['member_id'], 'mercado_pago_access_token');
    MercadoPago\SDK::setAccessToken($mercado_pago_access_token);

    $this->preference = new MercadoPago\Preference();
  }

  function getCodeCheckout($data)
  {
    $item = new MercadoPago\Item();
    $item->id = $data['id'];
    $item->title = "Renovação: {$data['username']}";
    $item->quantity = "1";
    $item->unit_price = (string) number_format($data['price'], 2, '.', '');

    $this->preference = new MercadoPago\Preference();
    $this->preference->items = array($item);

    $homeUrl = rtrim(str_replace([
      dirname($_SERVER["REQUEST_URI"]),
      "/sys",
      "client_area"
    ], "", getBaseURL()), "/");

    $back_urls = "{$homeUrl}/client_area/dashboard.php?gateway=mercadopago&payment={$data['id']}";

    $this->preference->notification_url = "{$homeUrl}/gateway/mercadopago/{$data['user_id']}?source_news=ipn&id={$data['id']}";
    $this->preference->back_urls = array(
      "success" => "{$back_urls}&status=success",
      "failure" => "{$back_urls}&status=failure",
      "pending" => "{$back_urls}&status=pending"
    );

    $this->preference->auto_return = "approved";

    $this->preference->save();

    $result = array(
      'id' => $this->preference->id,
    );

    return $result;
  }
}
