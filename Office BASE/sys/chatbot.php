<?php
error_reporting(32767);
ini_set("display_errors", 1);

//get phpinput
$data = json_decode(file_get_contents('php://input'), true);
header('Content-Type: application/json');

include_once('./functions.php');
startSession();
if (!isset($_GET['key'])) {
	die(json_encode(["data" => [["message" =>  "Identificador inválido"]]]));
}
$key = $_GET['key'];
$result = getUserPropertyByValue('chatbot_token', $key);

if (!$result) {
	die(json_encode(["data" => [["message" =>  "Identificador inválido"]]]));
}

$reseller = getUserByID($result['userid']);

if (!$reseller) {
	die(json_encode(["data" => [["message" =>  "Identificador inválido"]]]));
}

$chatbotRules = getAllChatbotRulesByReseller($reseller['id']);
$userMessage = $data['senderMessage'];
if (!isset($data['senderMessage']) || empty(trim($data['senderMessage']))) {
	die();
}

foreach ($chatbotRules as $rule) {
	if ($rule["status"] !== 1) {
		continue; // Ignora a regra se ela não estiver ativa
	}
	foreach ($rule["messages"] as $message) {
		if ($rule["rule_type"] === "equals" && $message === $userMessage) {
			switch ($rule["rule_action"]) {
				case 'test_iptv':
					$response = ["data" => [["message" => json_decode(chatbotCreateTest($reseller, "iptv", $rule['id']))]]];
					break;
				case 'test_code':
					$response = ["data" => [["message" => json_decode(chatbotCreateTest($reseller, "code", $rule['id']))]]];
					break;
				case 'test_binstream':
					$response = ["data" => [["message" => json_decode(chatbotCreateTest($reseller, "binstream", $rule['id']))]]];
					break;
				default:
					$response = ["data" => [["message" =>  $rule["response"]]]];
					break;
			}
			incrementChatbotRuleRuns($rule["id"]);
			die(json_encode($response));
		} elseif ($rule["rule_type"] === "contains") {
			$pattern = "/\b" . preg_quote($message, "/") . "\b/i";
			if (preg_match($pattern, $userMessage)) {
				switch ($rule["rule_action"]) {
					case 'test_iptv':
						$response = ["data" => [["message" => json_decode(chatbotCreateTest($reseller, "iptv", $rule['id']))]]];
						break;
					case 'test_code':
						$response = ["data" => [["message" => json_decode(chatbotCreateTest($reseller, "code", $rule['id']))]]];
						break;
					case 'test_binstream':
						$response = ["data" => [["message" => json_decode(chatbotCreateTest($reseller, "binstream", $rule['id']))]]];
						break;
					default:
						$response = ["data" => [["message" =>  $rule["response"]]]];
						break;
				}
				incrementChatbotRuleRuns($rule["id"]);
				die(json_encode($response));
			}
		}
	}
}
