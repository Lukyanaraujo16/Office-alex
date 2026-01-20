<?php
/**
* @copyright (c) 2022, By:Zed
*/

class Curl{
  private $URL;
  private $Request;
  private $Json;
  private $Params;
  private $ParamsString;
  private $Headers;
  private $Curl;

  private $Response = array();

  function __construct($url, $request, $json = null, array $headers = array(), $params = null){
    $this->URL = $url;
    $this->Request = $request;
    $this->Json = $json;
    $this->Headers = $headers;
    $this->Params = $params;
    $this->setParams();
    $this->initCurl();
  }

  private function initCurl(){
    $this->Curl = curl_init($this->URL . $this->ParamsString); // Initialise cURL
    curl_setopt($this->Curl, CURLOPT_CUSTOMREQUEST, $this->Request);
    curl_setopt($this->Curl, CURLOPT_POSTFIELDS, $this->Json);
    curl_setopt($this->Curl, CURLOPT_HTTPHEADER, $this->Headers); // Inject the token into the header
    curl_setopt($this->Curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->Curl, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
    curl_setopt($this->Curl, CURLOPT_SSL_VERIFYPEER, false);

    $this->Response['response'] = json_decode(str_replace("\xEF\xBB\xBF",'', trim(curl_exec($this->Curl))), true);
    $this->Response['info'] = curl_getinfo($this->Curl);
    curl_close($this->Curl); // Close the cURL connection
  }

  private function setParams(){
    if (!empty($this->Params)) {
      $this->ParamsString = '?';
      foreach ($this->Params as $key => $value) {
        $this->ParamsString .= (($this->ParamsString == '?') ? '' : '&') . $key . "=" . $value;
      }
    }else {
      $this->ParamsString = '';
    }
  }

  public function getResponse(){
    return $this->Response;
  }
}

?>
