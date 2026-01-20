<?php
error_reporting(32767);
ini_set("display_errors", 1);

//get phpinput
$data = json_decode(file_get_contents('php://input'), true);

// $data = $_REQUEST;

// $data['receiveMessageAppId'];
// $data['receiveMessagePattern'];
// $data['senderName'];
// $data['groupName'];
// $data['senderMesage'];
// $data['isMessageFromGroup'];


include_once('./functions.php');
startSession();

if (!isset($_GET['key'])) {
	echo ('Erro ao criar teste');
}
$key = $_GET['key'];
$result = getUserPropertyByValue('chatbot_token', $key);

if (!$result) {
	echo ('token inválido');
}

$reseller = getUserByID($result['userid']);

if (!$reseller) {
	echo ('Usuário não encontrado');
}
die();

if ($data['senderMessage'] == 5) {
	$type = "iptv";
} elseif ($data['senderMesage'] == "6") {
	$type = "code";
} else {
	exit();
}


if (!isAdmin($reseller) && ($reseller['credits'] < getServerProperty('automatic_test_min_credits', 0))) {
	exit();
}


$package = getPackageByID(getServerProperty("fast_test_package"));


if ($package && $package['is_trial']) {
	$duration = $package['trial_duration'] . ' ' . $package['trial_duration_in'];
	$username = CodeGenerator();
	if ($type == "code") {
		// $password = getServerTestPass();
		$password = "jhonnyégay";
	} else {
		$password = CodeGenerator();
	}

	$phone = "";
	$new_test = createClient($result['userid'], $username, $password, $phone, "", $duration, $package['bouquets'], 'Nome: ' . $data['senderName'], 1);

	if ($new_test) {
		insertRegUserLog($reseller['id'], $username, $password, '<b>Novo Teste (Auto)</b> | Pacote: ' . $package['package_name'] . ' | Créditos: <font color="green">' . $reseller['credits'] . '</font> > <font color="red">' . $reseller['credits'] . '</font> | Custo: 0 Crédito');
		$list_link = GetList($username, $password);
		$link_short = ShortenList("$list_link");
		$testdata = [
			"username" => $username,
			"password" => $password,
			"link" => $list_link,
			"link_short" => $link_short,
			"duration" => $package['trial_duration'],
			"server_name" => getServerProperty('server_name'),
		];
		// echo message($type, $testdata);

		echo json_encode(array("data" => array(
			array("message" =>  message($type, $testdata)),
			// array("message" => "Success ✅")
		)));
	} else {
		die('Erro ao criar teste');
	}
}


function message($type, $testdata)
{
	if ($type == 'iptv') {
		return
			'Seu teste iptv foi criado com sucesso!

Segue seus dados de acesso:
Usuario: ' . $testdata['username'] . '
Senha: ' . $testdata['password'] . '
Vencimento: ' . $testdata['duration'] . ' horas

Lista M3U: ' . $testdata['link_short'] . '

Att. ' . $testdata['server_name'] . '.';
	} elseif ($type == "code") {
		return "Seu teste iptv foi criado com sucesso!

	Segue seus dados de acesso:
	Usuario: {USERNAME}
	Senha: {PASSWORD}

	Codigo: {CODE}
	";
	}
}
//verifica se função existe
if (!function_exists('CodeGenerator')) {

	function CodeGenerator(int $length = 8, string $type = "1")
	{

		$lmin = 'abcdefghjkmnpqrstuvwxyz';
		$lmai = 'ABCDEFGHJKMNPQRSTUVWXYZ';
		$num = '123456789';
		$symb = '-';

		$characters = '';

		switch ($type) {
			case '1':
				$characters .= $num;
				break;
			case '1a':
				$characters .= $num;
				$characters .= $lmin;
				break;
			case '1aA':
				$characters .= $num;
				$characters .= $lmin;
				$characters .= $lmai;
				break;
			case '1aA-':
				$characters .= $num;
				$characters .= $lmin;
				$characters .= $lmai;
				$characters .= $symb;
				break;
		}
		$code = "";

		$len = strlen($characters);
		for ($n = 1; $n <= $length; $n++) {
			$rand = mt_rand(1, $len);
			$code .= $characters[$rand - 1];
		}
		return $code;
	}
}
