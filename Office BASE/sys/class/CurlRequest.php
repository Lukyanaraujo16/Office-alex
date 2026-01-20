<?php

class CurlRequest
{
  private $url;
  private $requestType;
  private $data;
  private $headers = [];

  public function __construct($url, $requestType, $data = [], $headers = [])
  {
    $this->url = $url;
    $this->requestType = $requestType;
    $this->data = $data;
    $this->headers = $headers;
  }

  public function makeRequest()
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($this->requestType === "POST") {
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));
    } elseif ($this->requestType === "PUT") {
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));
    } elseif ($this->requestType === "DELETE") {
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));
    }
    if (!empty($this->headers)) {
      curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
    }
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
      $response = 'Curl error: ' . curl_error($ch);
    }

    curl_close($ch);
    return array("response" => $response, "http_code" => $http_code);
  }
}
