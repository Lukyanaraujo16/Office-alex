<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Inclui a classe PHP-MySQLi-Database-Class
require_once './functions.php';


// Verifica se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// Recebe o JSON do webhook e o decodifica
	$json = file_get_contents('php://input');
	$data = json_decode($json, true);

	// Extrai o uuid do revendedor da query da request
	$uuid = $_GET['uuid'];

	// Busca as respostas e gatilhos do revendedor no banco de dados usando a classe
	$databaseOffice->where('revendedores.uuid', $uuid);
	$databaseOffice->join('respostas', 'respostas.revendedor_id = revendedores.id', 'INNER');
	$result = $databaseOffice->get('revendedores', null, 'respostas.resposta, respostas.gatilho, respostas.tipo_gatilho, respostas.limite_porcentagem');

	// Verifica se a mensagem recebida do chatbot corresponde a algum dos gatilhos configurados pelo revendedor
	$mensagem = $data['senderMessage'];
	$resposta = '';
	$melhor_porcentagem = 0;
	foreach ($result as $row) {
		$gatilho = $row['gatilho'];
		$tipo_gatilho = $row['tipo_gatilho'];
		$limite_porcentagem = $row['limite_porcentagem'];
		$porcentagem = 0;
		if ($tipo_gatilho === 'exato' && $mensagem === $gatilho) {
			// Se encontrar um gatilho exato correspondente a uma resposta, seleciona a resposta correspondente
			$resposta = $row['resposta'];
			break;
		} else if ($tipo_gatilho === 'parecido') {
			// Se o gatilho for parecido, calcula a porcentagem de proximidade entre a mensagem e o gatilho
			similar_text($mensagem, $gatilho, $porcentagem);
			if ($porcentagem >= $limite_porcentagem && $porcentagem > $melhor_porcentagem) {
				// Se a porcentagem de proximidade for maior ou igual ao limite mínimo e maior do que a melhor porcentagem encontrada até agora, seleciona a resposta correspondente
				$resposta = $row['resposta'];
				$melhor_porcentagem = $porcentagem;
			}
		} else if ($tipo_gatilho === 'porcentagem') {
			// Se o gatilho for porcentagem, calcula a porcentagem de proximidade entre a mensagem e o gatilho
			similar_text($mensagem, $gatilho, $porcentagem);
			if ($porcentagem >= $limite_porcentagem) {
				// Se a porcentagem de proximidade for maior ou igual ao limite mínimo, seleciona a resposta correspondente
				$resposta = $row['resposta'];
				break;
			}
		}
	}

	// Retorna a resposta selecionada para o chatbot
	if ($resposta !== '') {
		$response = array('data' => array(array('message' => $resposta)));
		echo json_encode($response);
	}
}
