<?php
@ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(0);
header('Content-type: application/json');
$result = array('result' => false);
include_once(__DIR__ . '/functions.php');
startSession();

if (!isset($_GET["key"])) {
	$result["message"] = "No key provided";
	echo json_encode($result);
	die();
}

if (!isset($_GET['action'])) {
	$result["message"] = "No action provided";
	echo json_encode($result);
	die();
}

$result_propertty = getUserPropertyByValue('test_key', strip_tags($_GET["key"]));
$reseller = getUserByID($result_propertty['userid']);

if (!$result || !$reseller) {
	$result["message"] = "Invalid key";
	echo json_encode($result);
	die();
}

$action = $_GET['action'];

switch ($action) {
	case 'new_test':
		$data = json_decode(file_get_contents('php://input'), true);

		$ip = $data['ip'];

		if (!isset($data['package_id'])) {
			$result["message"] = "No package_id provided";
			echo json_encode($result);
			die();
		}

		$package_id = intval($data['package_id']);
		$package = getPackageByID($package_id);

		if (!isset($data['email'])) {
			$result["message"] = "No email provided";
			echo json_encode($result);
			die();
		}

		$email = strip_tags($data['email']);

		if (!filter_var($email, FILTER_VALIDATE_EMAIL) || (mb_strpos($email, '+') !== false)) {
			$result["message"] = "Invalid email";
			echo json_encode($result);
			die();
		}

		# remove o caracter . do email caso seja gmail
		if (mb_strpos($email, '@gmail.com') !== false) {
			$email = explode("@", $email);
			$email[0] = str_replace('.', '', $email[0]);
			$email = $email[0] . "@" . $email[1];
		}

		if (getServerProperty('only_valid_emails_automatic_test', 0)) {
			if ((strpos($email, '@hotmail') === false) && (strpos($email, '@outlook') === false) && (strpos($email, '@gmail') === false) && (strpos($email, '@icloud') === false)) {
				$result["message"] = "Invalid email provider";
				echo json_encode($result);
				die();
			}
		}

		if (!in_array($email, unserialize(ALLOWED_EMAILS)) && existTest($email)) {
			$result["message"] = "Used email";
			echo json_encode($result);
			die();
		}

		// bloqueiar se ip já foi usado
		if (existTestIP($ip)) {
			$result["message"] = "IP already used";
			echo json_encode($result);
			die();
		}
		if ((!isAdmin($reseller) && !isPartner($reseller)) && ($reseller['credits'] < getServerProperty('test_min_credits', 0))) {
			$result["message"] = "Not enough credits";
			echo json_encode($result);
			die();
		}

		if (!$package || !$package['is_trial']) {
			$result["message"] = "Invalid package";
			echo json_encode($result);
			die();
		}

		if (!insertTest($email, $ip, $user_agent)) {
			$result["message"] = "Error creating test";
			echo json_encode($result);
			die();
		}

		$duration = $package['trial_duration'] . ' ' . $package['trial_duration_in'];

		if (isset($data['username'])) {
			$username = $data['username'];
		} else {
			$username = CodeGenerator(8, "1");
		}

		if (isset($data['password'])) {
			$password = $data['password'];
		} else {
			$password = CodeGenerator(8, "1");
		}

		if (isset($data['notes'])) {
			$notes = $data['notes'];
		} else {
			$notes = "";
		}

		if (isset($data['phone'])) {
			$phone = $data['phone'];
		} else {
			$phone = "";
		}

		$new_test = createClient($reseller['id'], $username, $password, $phone, $email, $duration, $package['bouquets'], $notes, 1);

		if (!$new_test) {
			$result["message"] = "Error creating test";
			echo json_encode($result);
			die();
		}

		insertRegUserLog($reseller['id'], $username, $password, '<b>Novo Teste (Auto)</b> | Pacote: ' . $package['package_name'] . ' | Créditos: <font color="green">' . $reseller['credits'] . '</font> > <font color="red">' . $reseller['credits'] . '</font> | Custo: 0 Crédito');
		$list_link = GetList($username, $password);
		// $link_short = ShortenList("$list_link");
		$ssiptv_link = GetList($username, $password, "ssiptv");

		$custom_template = getUserProperty($reseller['id'], 'custom_template');

		if ($custom_template) {
			$email_messages = json_decode(getUserProperty($reseller['id'], 'email_messages'), true);
		} else {
			$email_messages = json_decode(getServerProperty('email_messages'), true);
		}

		$whatsapp = getUserProperty($result['userid'], 'whatsapp');
		$telegram = getUserProperty($result['userid'], 'telegram');

		$auto_test_subject = str_replace(array('#username#', '#password#', '#server_name#'), array($username, $password, $server_name), $email_messages['auto_test_subject']);
		$auto_test_message = str_replace(
			array('#username#', '#password#', '#m3u_link#', '#ssiptv_link#', '#server_name#', '#reseller_email#', '#whatsapp#', '#telegram#', '#duration#'),
			array($username, $password, $list_link, $ssiptv_link, $server_name, $reseller['email'], $whatsapp, $telegram, $duration),
			$email_messages['auto_test_message']
		);
		// $auto_test_message =  TemplateReplace($reseller['id'], $username, $password, $duration);

		$custom_smtp = getUserProperty($reseller['id'], 'custom_smtp');

		if (smtpmailer($email, $auto_test_subject, $auto_test_message, $custom_smtp, $reseller['id'])) {
			$result = ["result" => true, "message" => "Test created"];
			echo json_encode($result);
			die();
		} else {
			$result["message"] = "Error sending email";
			echo json_encode($result);
			die();
		}

		break;

	case 'get_packages':
		$fast_packages = json_decode(getServerProperty('fast_packages'), true);
		$packages = getPackages();
		$packages_array = [];
		foreach ($fast_packages as $package_id) {
			$package_key = array_search($package_id, array_column($packages, 'id'));

			if ($package_key !== false) {
				$current_package = $packages[$package_key];
				$packages_array = array_merge($packages_array, [$current_package]);
			}
		}

		$result = ["result" => true, "packages" => $packages_array];
		echo json_encode($result);
		die();
		break;
}
