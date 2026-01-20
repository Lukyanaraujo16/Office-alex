<?php

if (!defined('OFFICE_CONFIG')) {
  $DB_INFO = json_decode(file_get_contents(__DIR__ . "/../../dbinfo.json"), true);
  define("OFFICE_CONFIG", $DB_INFO);
}
include_once __DIR__ . "/CurlRequest.php";

class BinStream
{
  protected $api_url;
  protected $api_token;
  protected $api_email;

  public function __construct()
  {
    $this->api_url = OFFICE_CONFIG['binstream']['url'];
    $this->api_token = OFFICE_CONFIG['binstream']['token'];
    $this->api_email = OFFICE_CONFIG['binstream']['email'];
  }

  public function create(array $data)
  {
    $url = $this->api_url . 'user?t=' . $this->api_token;
    $CurlRequest = new CurlRequest($url, "POST", $data);
    $response = $CurlRequest->makeRequest();
    return json_decode($response['response'], true);
  }

  public function getusers($owner, string $sort_by = "-_id", $filter = [], $search = '')
  {
    $panel_id = OFFICE_CONFIG['panel_id'];
    $where = "&name=" . $panel_id;

    $where = $where . "&" . http_build_query($filter);

    $url = $this->api_url . 'user?t=' . $this->api_token . '&sort=' . $sort_by . $where;

    if (is_array($owner)) {
      $CurlRequest = new CurlRequest($url, "GET");
      $response = $CurlRequest->makeRequest();
      $all_users = json_decode($response['response'], true);

      // filtrar os usuários pelo dono
      $filtered_users = array_filter($all_users, function ($user) use ($owner) {
        return in_array($user['exField2'], $owner);
      });

      return array_values($filtered_users);
    } else {
      switch (gettype($owner)) {
        case 'integer':
          $where .= "&exField2=" . $owner;
          break;
        case 'string':
          if ($owner !== 'all' && $owner !== "") {
            $where .= "&exField2=" . $owner;
          }
          break;
        default:
          break;
      }

      $CurlRequest = new CurlRequest($url, "GET");
      $response = $CurlRequest->makeRequest();
      return json_decode($response['response'], true);
    }
  }

  public function getuser($id)
  {
    $url = $this->api_url . 'user/' . $id . '?t=' . $this->api_token;
    $CurlRequest = new CurlRequest($url, "GET");
    $response = $CurlRequest->makeRequest();
    return json_decode($response['response'], true);
  }

  public function updateUser($id, $data)
  {
    $url = $this->api_url . 'user/' . $id . '?t=' . $this->api_token;
    $CurlRequest = new CurlRequest($url, "PUT", $data);
    $response = $CurlRequest->makeRequest();
    return json_decode($response['response'], true);
  }

  public function deleteUser($id)
  {
    $url = $this->api_url . 'user/' . $id . '?t=' . $this->api_token;
    $CurlRequest = new CurlRequest($url, "DELETE");
    $response = $CurlRequest->makeRequest();

    if ($response['http_code'] == 204) {
      return true;
    } else {
      return $response;
    }
  }

  public function getPackages()
  {
    $url = $this->api_url . 'product?t=' . $this->api_token . "&select=id%20name";

    $CurlRequest = new CurlRequest($url, "GET");
    $response = $CurlRequest->makeRequest();

    return json_decode($response['response'], true);
  }

  public function countUsers($owner, $type = "")
  {
    $where = "&name=" . OFFICE_CONFIG['panel_id'];

    switch ($type) {
      case 'active':
        $where .= "&type=1&endTime__gt=" . gmdate("Y-m-d\TH:i:s\Z");
        // $where .= "&type=1&status=1";
        break;
      case 'trial':
        $where .= "&type=0&status=-1";
        break;
      case 'new':
        $where .= "&type=1&status=1&regTime__gt=" . gmdate("Y-m-d\TH:i:s\Z", strtotime("-30 days"));
        break;
    }

    $url = $this->api_url . 'user?t=' . $this->api_token . $where . "&select=id&select=exField2";
    $CurlRequest = new CurlRequest($url, "GET");
    $response = $CurlRequest->makeRequest();

    $users = json_decode($response['response'], true);
    $filteredUsers = array_filter($users, function ($user) use ($owner) {
      if (is_array($owner)) {
        return in_array($user['exField2'], $owner);
      } elseif (is_integer($owner)) {
        return $user['exField2'] == $owner;
      } elseif (is_string($owner) && $owner !== 'all' && $owner !== '') {
        return $user['exField2'] === $owner;
      }
      return false;
    });

    return count($filteredUsers);
  }
}
