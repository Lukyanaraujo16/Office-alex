<?php
header("Access-Control-Allow-Origin: *");

require '_app/Config.inc.php';
require 'src/functions/get-curl.php';
$url = $_SERVER['REQUEST_URI'];
$url = explode("/", $url);

$username = isset($url[3]) ? $url[3] : null;
$password = isset($url[4]) ? $url[4] : null;
$action = isset($url[5]) ? $url[5] : null;
$complement = isset($url[6]) ? $url[6] : null;

switch ($action) {
  case 'download_m3u':
    $file = "#EXTM3U size=\"Big\"\n";

    if (OFFICE_CONFIG['ssiptv']['live']['enabled']) {
      $file .= "#EXTINF:0 type=\"playlist\" tvg-logo=\"https://i.imgur.com/WmsgVaM.png\"," . OFFICE_CONFIG['ssiptv']['live']['name'] . "\n";
      $file .= "#EXTBG:" . OFFICE_CONFIG['ssiptv']['live']['image'] . "\n";
      $file .=  OFFICE_CONFIG['ssiptv_url'] . "/ssiptv/get/{$username}/{$password}/list_lives/\n";
    }

    if (OFFICE_CONFIG['ssiptv']['movie']['enabled']) {
      $file .= "#EXTINF:0 type=\"playlist\" tvg-logo=\"https://i.imgur.com/WmsgVaM.png\"," . OFFICE_CONFIG['ssiptv']['movie']['name'] . "\n";
      $file .= "#EXTBG:" . OFFICE_CONFIG['ssiptv']['movie']['image'] . "\n";
      $file .= OFFICE_CONFIG['ssiptv_url'] . "/ssiptv/get/{$username}/{$password}/movie_categories/\n";
    }

    if (OFFICE_CONFIG['ssiptv']['serie']['enabled']) {
      $file .= "#EXTINF:0 type=\"playlist\" tvg-logo=\"https://i.imgur.com/WmsgVaM.png\"," . OFFICE_CONFIG['ssiptv']['serie']['name'] . "\n";
      $file .= "#EXTBG:" . OFFICE_CONFIG['ssiptv']['serie']['image'] . "\n";
      $file .= OFFICE_CONFIG['ssiptv_url'] . "/ssiptv/get/{$username}/{$password}/serie_categories/\n";
    }
    break;

  case 'list_lives':
    $file = "#EXTM3U size=\"Medium\"\n";

    $categories = getCurl(DNS_URL . "/player_api.php?username={$username}&password={$password}&action=get_live_categories");
    $categories = str_replace('\"', "'", $categories);
    $categories = json_decode($categories, true);
    $cat = [];

    if ($categories) {
      foreach ($categories as $key => $value) {
        $cat[$value['category_id']] = $value['category_name'];
      }
    }

    $categories = $cat;

    $lives = getCurl(DNS_URL . "/player_api.php?username={$username}&password={$password}&action=get_live_streams");
    $lives = str_replace('\"', "'", $lives);
    $lives = json_decode($lives, true);

    if ($lives) {
      if (OFFICE_CONFIG['ssiptv']['output'] == 'mpegts') {
        foreach ($lives as $key => $value) {
          $file .= "#EXTINF:-1 type=\"stream\" tvg-id=\"{$value['name']}\" tvg-logo=\"{$value['stream_icon']}\" group-title=\"" . (isset($categories[$value['category_id']]) ? $categories[$value['category_id']] : "") . "\",{$value['name']}\n";
          $file .= DNS_URL . "/{$username}/{$password}/{$value['stream_id']}\n";
        }
      } else {
        foreach ($lives as $key => $value) {
          $file .= "#EXTINF:-1 type=\"stream\" tvg-id=\"{$value['name']}\" tvg-logo=\"{$value['stream_icon']}\" group-title=\"" . (isset($categories[$value['category_id']]) ? $categories[$value['category_id']] : "") . "\",{$value['name']}\n";
          $file .= DNS_URL . "/live/{$username}/{$password}/{$value['stream_id']}.m3u8\n";
        }
      }
    }
    break;

  case 'movie_categories':
    $file = "#EXTM3U size=\"Medium\"\n";

    $categories = getCurl(DNS_URL . "/player_api.php?username={$username}&password={$password}&action=get_vod_categories");

    $categories = str_replace('\"', "'", $categories);
    $categories = json_decode($categories, true);

    $file .= "#EXTINF:0 type=\"playlist\" tvg-logo=\"https://i.imgur.com/WmsgVaM.png\",VER TUDO\n";
    $file .= "#EXTBG: #11609e\n";
    $file .= OFFICE_CONFIG['ssiptv_url'] . "/ssiptv/get/{$username}/{$password}/list_movies/-1/\n";

    if ($categories) {
      foreach ($categories as $key => $value) {
        $file .= "#EXTINF:0 type=\"playlist\" tvg-logo=\"https://i.imgur.com/WmsgVaM.png\",{$value['category_name']}\n";
        $file .= "#EXTBG: #11609e\n";
        $file .= OFFICE_CONFIG['ssiptv_url'] . "/ssiptv/get/{$username}/{$password}/list_movies/{$value['category_id']}/\n";
      }
    }
    break;

  case 'serie_categories':
    $file = "#EXTM3U size=\"Medium\"\n";
    $file .= "#EXTINF:0 type=\"playlist\" tvg-logo=\"https://i.imgur.com/WmsgVaM.png\",VER TUDO\n";
    $file .= "#EXTBG: #11609e\n";
    $file .= OFFICE_CONFIG['ssiptv_url'] . "/ssiptv/get/{$username}/{$password}/list_series/-1/\n";
    break;

  case 'list_series':
    $file = "#EXTM3U size=\"Medium\"\n";

    $series = getCurl(DNS_URL . "/player_api.php?username={$username}&password={$password}&action=get_series");
    $series = str_replace('\"', "'", $series);
    $series = json_decode($series, true);

    if ($series) {
      foreach ($series as $key => $value) {
        $file .= "#EXTINF:-1 type=\"playlist\" tvg-id=\"{$value['name']}\" tvg-logo=\"{$value['cover']}\" description=\"\",{$value['name']}\n";
        $file .= OFFICE_CONFIG['ssiptv_url'] . "/ssiptv/get/{$username}/{$password}/list_episodes/{$value['series_id']}/\n";
      }
    }
    break;

  case 'list_episodes':
    $file = "#EXTM3U size=\"Medium\"\n";

    $episodes = getCurl(DNS_URL . "/player_api.php?username={$username}&password={$password}&action=get_series_info&series_id={$complement}");
    $episodes = str_replace('\"', "'", $episodes);
    $episodes = json_decode($episodes, true);

    if (!empty($episodes['seasons'])) {
      foreach ($episodes['seasons'] as $key => $value) {
        $seasons[$value['season_number']] = $value['name'];
      }
    }

    if (!empty($episodes['episodes'])) {
      foreach ($episodes['episodes'] as $key_season => $value_season) {
        foreach ($value_season as $key_ep => $value_ep) {
          $file .= "#EXTINF:-1 type=\"video\" tvg-id=\"{$value_ep['title']}\" group-title=\"" . (isset($seasons[$key_season]) ? $seasons[$key_season] : "") . "\" description=\"T{$key_season}E{$value_ep['episode_num']}\" tvg-logo=\"\",Episódio {$value_ep['episode_num']}\n";
          $file .= DNS_URL . "/series/{$username}/{$password}/{$value_ep['id']}.{$value_ep['container_extension']}\n";
        }
      }
    }
    break;

  case 'list_movies':
    $file = "#EXTM3U size=\"Medium\"\n";

    $movies = getCurl(DNS_URL . "/player_api.php?username={$username}&password={$password}&action=get_vod_streams&category_id={$complement}");
    $movies = str_replace('\"', "'", $movies);
    $movies = json_decode($movies, true);

    if ($movies) {
      foreach ($movies as $key => $value) {
        $file .= "#EXTINF:-1 type=\"video\" tvg-id=\"{$value['name']}\" tvg-logo=\"{$value['stream_icon']}\" description=\"\",{$value['name']}\n";
        $file .= DNS_URL . "/movie/{$username}/{$password}/{$value['stream_id']}.mp4\n";
      }
    }
    break;
}

if (isset($file)) {
  header('Content-Description: File Transfer');
  header('Content-Type: application/octet-stream');
  header('Expires: 0');
  header('cache-Control: must-revalidate');
  header('Pragma: public');
  header('Content-Disposition: attachment; filename="' . "{$username}-ssiptv.m3u" . '"');
  header('Content-Length: ' . strlen($file));
  echo $file;
}

// echo "<pre>{$file}</pre>";
