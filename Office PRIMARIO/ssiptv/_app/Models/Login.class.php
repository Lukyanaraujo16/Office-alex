<?php

/**
* Login.class[ MODEL ]
* Responsavel por checar usuario e logar.
* @copyright (c) 2022, By:Zed
*/
class Login {
  private $User;
  private $Senha;
  private $Error;
  private $Result;

  public function ExeLogin(array $UserData) {
    $this->User = (string) trim($UserData['username']);
    $this->Senha = (string) trim($UserData['password']);
    $this->setLogin();
  }

  function getResult() {
    return $this->Result;
  }

  function getError() {
    return $this->Error;
  }

  public function CheckLogin() {
    if(empty($_SESSION['user_info']) || empty($_SESSION['token'])):
      unset($_SESSION['user_info']);
      unset($_SESSION['token']);
      unset($_SESSION['favorites']);
      return false;
    else:
      $Headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $_SESSION['token']
      ];

      $url = API_URI . "/login.php";
      $Curl = new Curl($url, 'GET', null, $Headers);

      if ($Curl->getResponse()['info']['http_code'] == 200) {
        return true;
      }else {
        return false;
      }

      return true;
    endif;
  }

  //////////////////////////////////////////////
  ///////////////////PRIVATE////////////////////
  //////////////////////////////////////////////

  private function setLogin() {
    if (!$this->User || !$this->Senha) {
      $this->Error = 'Informe seu usuario e senha para efetuar o login';
      $this->Result = false;
    }else {
      $this->getUser();
    }
  }

  private function getUser() {
    $Read = new Read;
    $Update = new Update;
    $Create = new Create;

    $ipAddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])){
      $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    }else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
      $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else if(isset($_SERVER['HTTP_X_FORWARDED'])){
      $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
    }else if(isset($_SERVER['HTTP_FORWARDED_FOR'])){
      $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
    }else if(isset($_SERVER['HTTP_FORWARDED'])){
      $ipAddress = $_SERVER['HTTP_FORWARDED'];
    }else if(isset($_SERVER['REMOTE_ADDR'])){
      $ipAddress = $_SERVER['REMOTE_ADDR'];
    }else{
      $ipAddress = 'UNKNOWN';
    }

    $Headers = [
      'Content-Type: application/json'
    ];

    $Data = [
      "user" => $this->User,
      "password" => $this->Senha
    ];

    $url = API_URI . "/login.php";
    $Curl = new Curl($url, 'POST', json_encode($Data), $Headers);

    if ($Curl->getResponse()['info']['http_code'] == 200) {
      if (!session_id()):
        session_start();
      endif;

      $_SESSION['user_info'] = $Curl->getResponse()['response']['user'];
      $_SESSION['token'] = $Curl->getResponse()['response']['token'];

      $this->setFavorites();

      $this->Error = "Ola seja bem vindo. Aguade Redirecionamento!";
      $this->Result = true;
    }else {
      if (isset($Curl->getResponse()['response']['data'][0]['tag']) && $Curl->getResponse()['response']['data'][0]['tag'] == "LOGIN_EXPIRED") {
        $this->Error = 'Acesso expirado';
        $this->Result = false;
      }else {
        $this->Error = 'Usuário não encontrado';
        $this->Result = false;
      }
    }
  }

  private function setFavorites() {
    $Headers = [
      'Content-Type: application/json',
      'Authorization: Bearer ' . $_SESSION['token']
    ];

    $url = API_URI . "/favorites.php";
    $Curl = new Curl($url, 'GET', null, $Headers);

    if ($Curl->getResponse()['info']['http_code'] == 200) {
      if (!empty($Curl->getResponse()['response'])) {
        foreach ($Curl->getResponse()['response'] as $key => $value) {
          $_SESSION['favorites'][$value['stream_id']] = true;
        }
      }
    }
  }
}
