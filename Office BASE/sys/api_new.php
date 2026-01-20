<?php
@ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(0);
header('Content-type: application/json');
$result = array('result' => 'failed');
include_once(__DIR__ . '/functions.php');
startSession();

if (isset($_SESSION['__l0gg3d_us3r__'])) {
} else {
	# SECTION /api/login
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['route'] === 'login') {
		parse_str(file_get_contents("php://input"), $post_vars);
		$username = $post_vars['username'];
		$password = $post_vars['password'];
		$recaptcha = $post_vars['recaptcha'];

		if (getServerProperty('recaptcha_enable') && !isset($_COOKIE['username'])) {
			require_once __DIR__ . "/recaptchalib.php";
			$secret = getServerProperty('recaptcha_secret_key');
			$response = null;
			$reCaptcha = new ReCaptcha($secret);
			$response = $reCaptcha->verifyResponse($_SERVER["REMOTE_ADDR"], $recaptcha);

			if ($response == null || !$response->success) {
				echo json_encode(["success" => false, "message" => "captcha"]);
				die();
			}
		}
	}

	$result = loginUser($username, $password);

	switch ($result) {
		case 1:
			setcookie('username', $username, time() + (86400 * 30), "/");
			setcookie('password', $password, time() + (86400 * 30), "/");
			echo json_encode(["success" => true, "message" => "OK"]);
			break;
		case 2:
			echo json_encode(["success" => false, "message" => "cant_connect"]);
			break;
		case 3:
			echo json_encode(["success" => false, "message" => "invalid_user_or_pass"]);
			break;
		case 4:
			echo json_encode(["success" => false, "message" => "blocked"]);
			break;
		case 5:
			echo json_encode(["success" => false, "message" => "insufficient_permission"]);
			break;
	}
	die();
	## !SECTION
}
