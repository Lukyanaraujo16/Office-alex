<?php
function getCurl($url)
{
	try {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (SMART-TV; LINUX; Tizen 3.0) AppleWebKit/538.1 (KHTML, like Gecko) Version/3.0 TV Safari/538.1');
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$resp = curl_exec($ch);

		if (!curl_errno($ch)) {
			$info = curl_getinfo($ch);

			if ($info['http_code'] == 404) {
				die(json_encode([
					"setItem" => [
						"user_info" => json_encode([
							"auth" => 0
						])
					]
				]));
			}
		}

		curl_close($ch);
	}catch (Execption $e) {
		die(json_encode([
			"setItem" => [
				"user_info" => json_encode([
					"auth" => 0
				])
			]
		]));
	}

	return $resp;
}
