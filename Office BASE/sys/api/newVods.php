<?php

if (!function_exists('isLogged')) {
  header('Location: ../../index.php');
  exit;
}

header("Content-Type: application/json");

$categories = getAllCategories();

$last_channels = getNewChannels(10);
$last_movies = getNewMovies(10);
$last_series = getNewSeries(10);

$html['channels'] = "";
$html['channels_copy'] = "🆕 Novos Canais 🆕\r\n\r\n";
$html['movies'] = "";
$html['movies_copy'] = "🆕 Novos Filmes 🆕\r\n\r\n";
$html['tvshows'] = "";
$html['tvshows_copy'] = "🆕 Novas Séries 🆕\r\n\r\n";

foreach ($last_channels as $current_channel) {
  $html['channels'] .= "<tr style='height: 90px;'>";
  $html['channels'] .= "<td>";
  $html['channels'] .= "<ul class=\"list-inline\">";
  $html['channels'] .= "<li class=\"list-inline-item\">";
  $channel_image = $current_channel["stream_icon"];

  if (empty($channel_image)) {
    $channel_image = "/dist/img/no_logo.png";
  } else {
    $channel_image = "/sys/api/get_img.php?url=" . $channel_image;
  }

  $html['channels'] .= '<img alt="Logo" style="display: inline; width: 2.5rem;" src="' . $channel_image . '">';
  $html['channels'] .= "</li>";
  $html['channels'] .= "</ul>";
  $html['channels'] .= "</td>";
  $html['channels'] .= "<td>";
  $html['channels'] .= '<a>' . $current_channel["stream_display_name"] . '</a><br>';
  $html['channels'] .= "<span class=\"badge badge-info\">";
  if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
    $category_array = str_replace("[", "", $current_channel['category_id']);
    $category_array = str_replace("]", "", $category_array);

    $category_array = explode(",", $category_array);
    $category_id = $category_array[0];
  } else {
    $category_id = $current_channel['category_id'];
  }
  $categoryName = getCategoryNameById($category_id, $categories);
  $html['channels'] .= $categoryName ? purifyHTML($categoryName) : 'Desconhecido';
  $html['channels'] .= "</span>";
  $html['channels'] .= "</td>";
  $html['channels'] .= "<td class=\"project-state\">";
  $html['channels'] .= "<span class=\"badge badge-info\">";
  $html['channels'] .= date('d/m/Y', $current_channel["added"]);
  $html['channels'] .= "</span>";
  $html['channels'] .= "</td>";
  $html['channels'] .= "</tr>";
  $html['channels_copy'] .= $current_channel["stream_display_name"] . "\r\n";
}

foreach ($last_movies as $current_movie) {
  $html['movies'] .= "<tr style='height: 90px;'>";
  $html['movies'] .= "<td>";
  $html['movies'] .= "<ul class=\"list-inline\">";
  $html['movies'] .= "<li class=\"list-inline-item\">";
  if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
    $movie_image = json_decode($current_movie["movie_properties"], true);
  } else {
    $movie_image = json_decode($current_movie["movie_propeties"], true);
  }
  $movie_image = $movie_image["movie_image"];

  if (empty($movie_image) || $movie_image == "https://image.tmdb.org/t/p/w600_and_h900_bestv2") {
    $movie_image = "/dist/img/no_logo.png";
  } else {
    if (substr($movie_image, 0, 2) === "s:") {
      $server_id = substr($movie_image, 2, 1);
      $domain = explode(",", getServerDNS($server_id))[0];

      $movie_image = "/sys/api/get_img.php?url=" . $domain . substr($movie_image, 4);
    } else {
      $movie_image = "/sys/api/get_img.php?url=" . $movie_image;
    }
  }

  $html['movies'] .= '<img alt="Logo" style="display: inline; width: 2.5rem;" src="' . $movie_image . '">';
  $html['movies'] .= "</li>";
  $html['movies'] .= "</ul>";
  $html['movies'] .= "</td>";
  $html['movies'] .= "<td>";
  $html['movies'] .= '<a>' . $current_movie["stream_display_name"] . '</a><br>';
  $html['movies'] .= "<span class=\"badge badge-info\">";
  if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
    $category_array = str_replace("[", "", $current_movie['category_id']);
    $category_array = str_replace("]", "", $category_array);

    $category_array = explode(",", $category_array);
    $category_id = $category_array[0];
  } else {
    $category_id = $current_movie['category_id'];
  }
  $categoryName = getCategoryNameById($category_id, $categories);
  $html['movies'] .= $categoryName ? purifyHTML($categoryName) : 'Desconhecido';
  $html['movies'] .= "</span>";
  $html['movies'] .= "</td>";
  $html['movies'] .= "<td class=\"project-state\">";
  $html['movies'] .= "<span class=\"badge badge-info\">";
  $html['movies'] .= date('d/m/Y', $current_movie["added"]);
  $html['movies'] .= "</span>";
  $html['movies'] .= "</td>";
  $html['movies'] .= "</tr>";
  $html['movies_copy'] .= $current_movie["stream_display_name"] . "\r\n";
}

foreach ($last_series as $current_serie) {
  $html['tvshows'] .= "<tr style='height: 90px;'>";
  $html['tvshows'] .= "<td>";
  $html['tvshows'] .= "<ul class=\"list-inline\">";
  $html['tvshows'] .= "<li class=\"list-inline-item\">";
  $serie_image = $current_serie["cover"];

  if (empty($serie_image) || $serie_image == "https://image.tmdb.org/t/p/w600_and_h900_bestv2") {
    $serie_image = "/dist/img/no_logo.png";
  } else {
    if (substr($serie_image, 0, 2) === "s:") {
      $server_id = substr($serie_image, 2, 1);
      $domain = explode(",", getServerDNS($server_id))[0];

      $serie_image = "/sys/api/get_img.php?url=" . $domain . substr($serie_image, 4);
    } else {
      $serie_image = "/sys/api/get_img.php?url=" . $serie_image;
    }
  }

  $html['tvshows'] .= '<img alt="Logo" style="display: inline; width: 2.5rem;" src="' . $serie_image . '">';
  $html['tvshows'] .= "</li>";
  $html['tvshows'] .= "</ul>";
  $html['tvshows'] .= "</td>";
  $html['tvshows'] .= "<td>";
  $html['tvshows'] .= '<a>' . $current_serie["title"] . '</a><br>';
  $html['tvshows'] .= "<span class=\"badge badge-info\">";
  if (OFFICE_CONFIG['remote_db']['panel_type'] == "XUI") {
    $category_array = str_replace("[", "", $current_serie['category_id']);
    $category_array = str_replace("]", "", $category_array);

    $category_array = explode(",", $category_array);
    $category_id = $category_array[0];
  } else {
    $category_id = $current_serie['category_id'];
  }
  $categoryName = getCategoryNameById($category_id, $categories);
  $html['tvshows'] .= $categoryName ? purifyHTML($categoryName) : 'Desconhecido';
  $html['tvshows'] .= "</span>";
  $html['tvshows'] .= "</td>";
  $html['tvshows'] .= "<td class=\"project-state\">";
  $html['tvshows'] .= "<span class=\"badge badge-info\">";
  $html['tvshows'] .= date('d/m/Y', $current_serie["last_modified"]);
  $html['tvshows'] .= "</span>";
  $html['tvshows'] .= "</td>";
  $html['tvshows'] .= "</tr>";
  $html['tvshows_copy'] .= $current_serie["title"] . "\r\n";
}

// tv show

$result = $html;
