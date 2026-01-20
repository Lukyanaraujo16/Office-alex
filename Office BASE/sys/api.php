<?php
@ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(0);
header('Content-type: application/json');
$result = array('result' => 'failed');
include_once(__DIR__ . '/functions.php');
startSession();

// if (!isset($_SESSION['__l0gg3d_us3r__'])) {
// 	if (isset($_COOKIE['username']) && isset($_COOKIE['password'])) {
// 		$username = $_COOKIE['username'];
// 		$password = $_COOKIE['password'];
// 		$result = loginUser($username, $password);
// 	}
// }
if (isset($_SESSION['__l0gg3d_us3r__'])) {
	$logged_user_id = intval($_SESSION['__l0gg3d_us3r__']);

	if (isset($_GET['action'])) {
		$action = $_GET['action'];

		switch ($action) {
			case 'create_test':
				if (isset($_GET['package_id'])) {
					$package_id = $_GET['package_id'];
					$type = isset($_GET['type']) ? $_GET['type'] : "";
					$logged_user = getLoggedUser();

					if ($type == "binstream") {
						if (!hasPermissionResource($logged_user_id, "binstream")) {
							header('location: /dashboard');
							exit();
						}
					} elseif (!hasPermissionResource($logged_user_id, "iptv")) {
						header('location: /dashboard');
						exit();
					}

					if ($logged_user) {
						if ((!isAdmin($logged_user) && !isPartner($logged_user)) && ($logged_user['credits'] < getServerProperty('test_min_credits', 0))) {
							header('location: /iptv/clients?result=no_min_credits');
							exit();
						}
						$result = createFastTest($logged_user_id, $package_id, $type);
						if ($result) {
							if ($type == "binstream") {
								header('location: /p2p/clients/show/' . $result);
								die();
							} else {
								header('location: /iptv/clients/show/' . $result);
								die();
							}
							exit();
						}
					}
					if ($type == "binstream") {
						header('location: /p2p/clients?result=test_not_created');
						exit();
					} else {
						header('location: /iptv/clients?result=test_not_created');
						exit();
					}
				}
				break;

			case 'fast_test':
				if (isset($_GET['type'])) {
					$logged_user = getLoggedUser();
					$type = $_GET['type'];

					if (!hasPermissionResource($logged_user_id, $type)) {
						die(json_encode("Sem permissão, contate o seu revendedor!"));
					}

					if ($type == "code" && !getServerProperty('code_status', 1)) {
						die(json_encode("Código não disponível no momento!"));
					}

					if ($logged_user) {
						if ((!isAdmin($logged_user) && !isPartner($logged_user)) && ($logged_user['credits'] < getServerProperty('test_min_credits', 0))) {
							header('location: ../list_clients.php?result=no_min_credits');
							exit();
						}

						$result = createFastTestDash($logged_user_id, $type);
						if ($result != false) {
							header('Content-Type: application/json');
							echo htmlspecialchars_decode(json_encode(TemplateReplace($logged_user_id, $result['username'], $result['password'], $result['duration'], $type)), ENT_QUOTES);;
							die();
						}
						die(json_encode("Pacote de teste rápido não configurado, informe isso ao Administrador"));
					}
				}
				break;

			case 'fast_message':
				if (isset($_GET['type'])) {
					$logged_user = getLoggedUser();
					$type = $_GET['type'];
					$client_id = $_GET['client_id'];

					if ($type == "binstream") {
						if (!hasPermissionResource($logged_user_id, "binstream")) {
							$dados['result'] = "success";
							$dados['message'] = "Sem permissão, contate o seu revendedor!";
							echo json_encode($dados);
							die();
						}
					} elseif ($type == "code") {
						if (!hasPermissionResource($logged_user_id, "code")) {
							$dados['result'] = "success";
							$dados['message'] = "Sem permissão, contate o seu revendedor!";
							echo json_encode($dados);
							die();
						}
					} else {
						if (!hasPermissionResource($logged_user_id, "iptv")) {
							$dados['result'] = "success";
							$dados['message'] = "Sem permissão, contate o seu revendedor!";
							echo json_encode($dados);
							die();
						}
					}

					if ($logged_user) {
						if (!hasPermission($logged_user['id'], $client_id, $type)) {
							$result['result'] = 'no_permission';
							break;
						}
						if ($type == "binstream") {
							$client_data = getClientByID($client_id, "binstream");
						} else {
							$client_data = getClientByID($client_id, $type);
						}

						$vencimento = $type == "binstream" ? (is_null($client_data['endTime']) ? "Período não iniciado!" : strtotime($client_data['endTime'])) : $client_data['exp_date'];
						$username = $type == "binstream" ? explode("@", $client_data["email"])[0] : $client_data['username'];
						$password = $type == "binstream" ? $client_data['exField3'] : $client_data['password'];
						if ($client_data != false) {
							header('Content-Type: application/json');
							$dados['result'] = "success";
							$dados['message'] = TemplateReplace($logged_user_id, $username, $password, $vencimento, $type);
							echo json_encode($dados);
							die();
						}
						$dados['result'] = "success";
						$dados['message'] = "Pacote de teste rápido não configurado, informe isso ao Administrador";
						echo json_encode($dados);
						die();
					}
				}
				break;

			case 'exp_message':
				$logged_user = getLoggedUser();
				$client_id = $_GET['client_id'];
				$type = isset($_GET['type']) ? $_GET['type'] : "iptv";

				if ($logged_user) {
					$result = getClientByID($client_id, $type);
					$vencimento = $result['exp_date'];

					if ($result != false) {
						header('Content-Type: application/json');
						$dados['result'] = "success";
						if (strlen($result['phone']) > 8) {
							$dados['phone'] = $result['phone'];
						} else {

							$dados['phone'] = "";
						}
						$dados['message'] = TemplateReplace($logged_user_id, $result['username'], $result['password'], $vencimento, "exp_message");
						echo json_encode($dados);
						die;
						exit();
					}
					die(json_encode("Pacote de teste rápido não configurado, informe isso ao Administrador"));
				}

				break;

			case 'create_multi_codes':
				if (isset($_GET['amount']) && isset($_GET['duration']) && isset($_GET['connections']) && isset($_GET['package'])) {
					$logged_user = getLoggedUser();
					$amount = purifyHTML($_GET['amount']);
					$duration = purifyHTML($_GET['duration']);
					$connections = purifyHTML($_GET['connections']);
					$package = purifyHTML($_GET['package']);
					$reseller_notes = purifyHTML($_GET['reseller_notes']);
					$admin_notes = "Via Gerador de Multiplos Códigos";

					$exp_date = strtotime("+" . $duration . " month");
					$credits = $amount * $duration * $connections;

					if (!hasPermissionResource($logged_user_id, "code")) {
						die(json_encode("Sem permissão, contate o seu revendedor!"));
					}

					if ($logged_user) {
						$old_credits = $logged_user['credits'];
						if (addOrRemoveCredits($logged_user['id'], -$credits)) {
							$now_credits = getCreditsByUser($logged_user);
							$result = insertMultiCodes($logged_user_id, $exp_date, $admin_notes, $reseller_notes, $package, $connections, 0, $amount);
							if (isset($result)) {
								$package = getPackageByID($package);
								insertRegUserLog($logged_user['id'], "<u>" . $amount . " Códigos</u>", "", '<b>Novos Códigos</b> | Pacote: ' . $package['package_name'] . ' | Duração: ' . $duration . ' meses | Conexões: ' . $connections . ' | Créditos: <font color="green">' . $old_credits . '</font> > <font color="red">' . $now_credits . '</font> | Custo: ' . $credits . ' Créditos');
								$codes = "Lista de Códigos:\r\n";
								foreach ($result as $code) {
									$codes .= $code . "\r\n";
								}
								header('Content-Type: application/json');
								echo json_encode($codes);
								die;
								exit();
							}
							exit();
						}
						header('location: ../create_multi_code.php?result=no_min_credits');
						exit();
					}
				}
				header('location: ../create_multi_code.php?result=no_min_credits');
				exit();
				break;

			case 'convert':
				if (isset($_GET['user_id']) && isset($_GET['from'])) {
					$user_id = $_GET['user_id'];
					$from = $_GET['from'];
					$logged_user = getLoggedUser();

					if ((!hasPermissionResource($logged_user_id, "iptv")) || (!hasPermissionResource($logged_user_id, "binstream"))) {
						$result = array("result" => false, "message" => "IPTV ou BinStream não habilitado, contate o seu revendedor!");
						break;
					}

					if ($logged_user) {
						if ($from == "binstream") {
							$type = "binstream";
						} else {
							$type = "iptv";
						}
						if (hasPermission($logged_user_id, $user_id, $type)) {
							if ((!isAdmin($logged_user) && !isPartner($logged_user)) && ($logged_user['credits'] < getServerProperty('test_min_credits', 0))) {
								$result = array("result" => false, "message" => "Sem créditos suficientes para realizar a conversão");
								break;
							}
							if ($from == "iptv") {
								$response = iptvToP2P($user_id);
							} elseif ($from == "binstream") {
								$response = p2pToIPTV($user_id);
							}
							if (isset($response['error'])) {
								$result = array("result" => false, "message" => $response['error']);
								break;
							} else {
								$result = array("result" => "success", "message" => "Conversão realizada com sucesso!");
								break;
							}
						}
					}
				}
				break;

			case 'get_online_clients':
				if (!hasPermissionResource($logged_user_id, "iptv")) {
					die(json_encode("Sem permissão, contate o seu revendedor!"));
				}

				if (isset($_GET['start']) && isset($_GET['length']) && isset($_GET['search']) && isset($_GET['order'])) {
					$start = intval($_GET['start']);
					$length = intval($_GET['length']);
					$search = (isset($_GET['search']['value']) ? $_GET['search']['value'] : '');
					$order_column_index = (isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0);
					$order_type = (isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc');
					$draw = (isset($_GET['draw']) ? intval($_GET['draw']) : 1);
					$result = getAllOnlineClientsTable($logged_user_id, $start, $length, $search, $order_column_index, $order_type);
					$result['draw'] = $draw;
				}

				break;
			case 'get_online_codes':
				if (!hasPermissionResource($logged_user_id, "code")) {
					die(json_encode("Sem permissão, contate o seu revendedor!"));
				}

				if (isset($_GET['start']) && isset($_GET['length']) && isset($_GET['search']) && isset($_GET['order'])) {
					$start = intval($_GET['start']);
					$length = intval($_GET['length']);
					$search = (isset($_GET['search']['value']) ? $_GET['search']['value'] : '');
					$order_column_index = (isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0);
					$order_type = (isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc');
					$draw = (isset($_GET['draw']) ? intval($_GET['draw']) : 1);
					$p2p = True;
					$result = getAllOnlineClientsTable($logged_user_id, $start, $length, $search, $order_column_index, $order_type, $p2p);
					$result['draw'] = $draw;
				}

				break;
			case 'get_clients':
				if (!hasPermissionResource($logged_user_id, "iptv")) {
					die(json_encode("Sem permissão, contate o seu revendedor!"));
				}
				if (isset($_GET['start']) && isset($_GET['length']) && isset($_GET['search']) && isset($_GET['order'])) {
					$start = intval($_GET['start']);
					$length = intval($_GET['length']);
					$search = (isset($_GET['search']['value']) ? $_GET['search']['value'] : '');
					$order_column_index = (isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0);
					$order_type = (isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc');
					$draw = (isset($_GET['draw']) ? intval($_GET['draw']) : 1);
					$status = (isset($_GET['status']) ? $_GET['status'] : null);
					$reseller_id = (isset($_GET['reseller']) ? $_GET['reseller'] : null);
					$type = (isset($_GET['type']) ? $_GET['type'] : null);
					$result = getAllClientsTable($logged_user_id, $start, $length, $search, $order_column_index, $order_type, false, false, $status, $type, $reseller_id);
					$result['draw'] = $draw;
				}
				break;
			case 'get_binstream_clients':
				if (!hasPermissionResource($logged_user_id, "binstream")) {
					die(json_encode("Sem permissão, contate o seu revendedor!"));
				}
				if (isset($_GET['start']) && isset($_GET['length']) && isset($_GET['search']) && isset($_GET['order'])) {
					$start = intval($_GET['start']);
					$length = intval($_GET['length']);
					$search = (isset($_GET['search']['value']) ? $_GET['search']['value'] : '');
					$order_column_index = (isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0);
					$order_type = (isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc');
					$draw = (isset($_GET['draw']) ? intval($_GET['draw']) : 1);
					$status = (isset($_GET['status']) ? $_GET['status'] : null);
					$reseller_id = (isset($_GET['reseller']) ? $_GET['reseller'] : null);
					$type = (isset($_GET['type']) ? $_GET['type'] : null);
					$result = getAllBinstreamClientsTable($logged_user_id, $start, $length, $search, $order_column_index, $order_type, $status, $type, $reseller_id);
					$result['draw'] = $draw;
				}
				break;
			case 'get_clients_expiring':
				if (isset($_GET['type'])) {
					$type = $_GET['type'];
				} else {
					$type = "iptv";
				}
				if (!hasPermissionResource($logged_user_id, $type)) {
					die(json_encode("Sem permissão, contate o seu revendedor!"));
				}
				if (isset($_GET['start']) && isset($_GET['length']) && isset($_GET['search'])) {
					$start = intval($_GET['start']);
					$length = intval($_GET['length']);
					$search = (isset($_GET['search']['value']) ? $_GET['search']['value'] : '');
					$order_column_index = 3;
					$order_type = 'desc';
					$draw = (isset($_GET['draw']) ? intval($_GET['draw']) : 1);
					$tree = false;
					if ($type == "iptv") {
						$result = getExpiringClientsTable($logged_user_id, $start, $length, $search, $order_column_index, $order_type, $tree);
					} else {
						$result = getExpiringP2PClientsTable($logged_user_id, $start, $length, $search, $order_column_index, $order_type, $tree);
					}
					$result['draw'] = $draw;
				}
				break;
			case 'get_code_table':
				if (!hasPermissionResource($logged_user_id, "code")) {
					die(json_encode("Sem permissão, contate o seu revendedor!"));
				}
				if (isset($_GET['start']) && isset($_GET['length']) && isset($_GET['search']) && isset($_GET['order'])) {
					$start = intval($_GET['start']);
					$length = intval($_GET['length']);
					$search = (isset($_GET['search']['value']) ? $_GET['search']['value'] : '');
					$order_column_index = (isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0);
					$order_type = (isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc');
					$draw = (isset($_GET['draw']) ? intval($_GET['draw']) : 1);
					$status = (isset($_GET['status']) ? $_GET['status'] : null);
					$reseller_id = (isset($_GET['reseller']) ? $_GET['reseller'] : null);
					$type = (isset($_GET['type']) ? $_GET['type'] : null);
					$result = getAllClientsTable($logged_user_id, $start, $length, $search, $order_column_index, $order_type, true, false, $status, $type, $reseller_id);
					$result['draw'] = $draw;
				}

				break;
			case 'get_resellers':
				if (isset($_GET['start']) && isset($_GET['length']) && isset($_GET['search']) && isset($_GET['order'])) {
					$start = intval($_GET['start']);
					$length = intval($_GET['length']);
					$search = (isset($_GET['search']['value']) ? $_GET['search']['value'] : '');
					$order_column_index = (isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0);
					$order_type = (isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc');
					$draw = (isset($_GET['draw']) ? intval($_GET['draw']) : 1);
					$status = (isset($_GET['status']) ? $_GET['status'] : null);
					$reseller_id = (isset($_GET['reseller']) ? $_GET['reseller'] : null);
					$type = (isset($_GET['type']) ? $_GET['type'] : null);
					$result = getAllResellersTable($logged_user_id, $start, $length, $search, $order_column_index, $order_type, $status, $type, $reseller_id);
					$result['draw'] = $draw;
				}

				break;
			case 'get_chatbot_rules':
				if (isset($_GET['start']) && isset($_GET['length']) && isset($_GET['search']) && isset($_GET['order'])) {
					$start = intval($_GET['start']);
					$length = intval($_GET['length']);
					$search = (isset($_GET['search']['value']) ? $_GET['search']['value'] : '');
					$order_column_index = (isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0);
					$order_type = (isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc');
					$draw = (isset($_GET['draw']) ? intval($_GET['draw']) : 1);
					$status = (isset($_GET['status']) ? $_GET['status'] : null);
					$reseller_id = (isset($_GET['reseller']) ? $_GET['reseller'] : null);
					$type = (isset($_GET['type']) ? $_GET['type'] : null);
					$result = getChatbotRulesTable($logged_user_id, $start, $length, $search, $order_column_index, $order_type, $status, $type, $reseller_id);
					$result['draw'] = $draw;
				}

				break;
			case 'get_tickets':
				if (isset($_GET['start']) && isset($_GET['length']) && isset($_GET['search']) && isset($_GET['order'])) {
					$start = intval($_GET['start']);
					$length = intval($_GET['length']);
					$search = (isset($_GET['search']['value']) ? $_GET['search']['value'] : '');
					$order_column_index = (isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0);
					$order_type = (isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc');
					$draw = (isset($_GET['draw']) ? intval($_GET['draw']) : 1);
					$result = getTickets($logged_user_id, $start, $length, $search, $order_column_index, $order_type);
					$result['draw'] = $draw;
				}

				break;
			case 'get_transactions':
				if (isset($_GET['start']) && isset($_GET['length']) && isset($_GET['search']) && isset($_GET['order'])) {
					$start = intval($_GET['start']);
					$length = intval($_GET['length']);
					$search = (isset($_GET['search']['value']) ? $_GET['search']['value'] : '');
					$order_column_index = (isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0);
					$order_type = (isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc');
					$draw = (isset($_GET['draw']) ? intval($_GET['draw']) : 1);
					$result = getTransactions($logged_user_id, $start, $length, $search, $order_column_index, $order_type);
					$result['draw'] = $draw;
				}
				break;
			case 'get_credits_log':
				if (isset($_GET['start']) && isset($_GET['length']) && isset($_GET['search']) && isset($_GET['order'])) {
					$start = intval($_GET['start']);
					$length = intval($_GET['length']);
					$search = (isset($_GET['search']['value']) ? $_GET['search']['value'] : '');
					$order_column_index = (isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0);
					$order_type = (isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc');
					$draw = (isset($_GET['draw']) ? intval($_GET['draw']) : 1);
					$result = getCreditsLog($logged_user_id, $start, $length, $search, $order_column_index, $order_type);
					$result['draw'] = $draw;
				}
				break;

			case 'get_reseller_log':
				if (isset($_GET['start']) && isset($_GET['length']) && isset($_GET['search']) && isset($_GET['order'])) {
					$start = intval($_GET['start']);
					$length = intval($_GET['length']);
					$search = (isset($_GET['search']['value']) ? $_GET['search']['value'] : '');
					$order_column_index = (isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0);
					$order_type = (isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc');
					$draw = (isset($_GET['draw']) ? intval($_GET['draw']) : 1);
					$result = getResellerLog($logged_user_id, $start, $length, $search, $order_column_index, $order_type);
					$result['draw'] = $draw;
				}
				break;

			case 'get_active_clients_count':
				if (!hasPermissionResource($logged_user_id, "iptv")) {
					die(json_encode("Sem permissão, contate o seu revendedor!"));
				}
				if (isset($_GET['start']) && isset($_GET['length']) && isset($_GET['search']) && isset($_GET['order'])) {
					$start = intval($_GET['start']);
					$length = intval($_GET['length']);
					$search = (isset($_GET['search']['value']) ? $_GET['search']['value'] : '');
					$order_column_index = (isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0);
					$order_type = (isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc');
					$draw = (isset($_GET['draw']) ? intval($_GET['draw']) : 1);
					$result = getAllActiveClientsCountFromResellers($logged_user_id, $start, $length, $search, $order_column_index, $order_type);

					foreach ($result['data'] as $dataIndex => $reseller) {
						$totalData = totalScreenAndClientsInTree($reseller['id']);

						$active_clients = !$totalData['total_users'] ? '0' : $totalData['total_users'];
						$active_conns = !$totalData['total_max_connections'] ? '0' : $totalData['total_max_connections'];
						$client_price = getUserProperty($reseller['id'], "client_price");

						if (empty($client_price)) {
							$client_price = 0;
						}
						$estimated_cost = $active_conns * $client_price;

						$result['data'][$dataIndex]['active_clients'] = $active_clients;
						$result['data'][$dataIndex]['active_conns'] = $active_conns;
						$result['data'][$dataIndex]['estimated_cost'] = "R$ " . number_format($estimated_cost, 2, ',', '.');
					}

					$result['draw'] = $draw;
				}
				break;

			case 'get_bin_active_clients_count':
				if (!hasPermissionResource($logged_user_id, "binstream")) {
					die(json_encode("Sem permissão, contate o seu revendedor!"));
				}
				if (isset($_GET['start']) && isset($_GET['length']) && isset($_GET['search']) && isset($_GET['order'])) {
					$start = intval($_GET['start']);
					$length = intval($_GET['length']);
					$search = (isset($_GET['search']['value']) ? $_GET['search']['value'] : '');
					$order_column_index = (isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0);
					$order_type = (isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc');
					$draw = (isset($_GET['draw']) ? intval($_GET['draw']) : 1);
					$result = getAllActiveClientsCountFromResellers($logged_user_id, $start, $length, $search, $order_column_index, $order_type);

					include_once(__DIR__ . "/class/binstream.php");
					$binstream = new BinStream();

					// $resellers = getAllResellersIdByOwnerID($logged_user_id);
					// array_push($resellers, $logged_user_id);

					foreach ($result['data'] as $dataIndex => $reseller) {

						$active_clients = binCountResellerClients($reseller['id']);
						$client_price = getUserProperty($reseller['id'], "binstream_client_price");

						if (empty($client_price)) {
							$client_price = 0;
						}
						$estimated_cost = $active_clients * $client_price;

						$result['data'][$dataIndex]['active_clients'] = $active_clients;
						$result['data'][$dataIndex]['estimated_cost'] = "R$ " . number_format($estimated_cost, 2, ',', '.');
					}

					$result['draw'] = $draw;
				}
				break;

			case 'renew_client':
				if (isset($_GET['client_id'])) {
					$client_id = intval($_GET['client_id']);
					$client = getClientByID($client_id);

					if ($type == "binstream") {
						if (!hasPermissionResource($logged_user_id, "binstream")) {
							die(json_encode("Sem permissão, contate o seu revendedor!"));
						}
					} elseif (!hasPermissionResource($logged_user_id, "iptv")) {
						die(json_encode("Sem permissão, contate o seu revendedor!"));
					}

					if ($client) {
						$logged_user = getLoggedUser();

						if ($logged_user) {
							$credits = $client['max_connections'] * 1;

							if (hasPermission($logged_user_id, $client['id'])) {
								if (addOrRemoveCredits($logged_user_id, -$credits)) {
									if (renewClient($client['id'], 1)) {
										$old_credits = $logged_user['credits'];
										$logged_user = getLoggedUser();
										$now_credits = $logged_user['credits'];
										insertRegUserLog($logged_user['id'], $client['username'], $client['password'], '<b>Renovação</b> | Duração: 1 mês | Créditos: <font color="green">' . $old_credits . '</font> > <font color="red">' . $now_credits . '</font> | Custo: ' . $credits . ' Crédito(s)');
										$result['result'] = 'success';
										break;
									}
								}
								$result['result'] = 'no_credits';
							}
						}
					}
				}

				break;
			case 'renew_client_plus':
				if (isset($_GET['client_id']) && isset($_GET['months'])) {
					$type = isset($_GET['type']) ? $_GET['type'] : '';
					$months = intval($_GET['months']);

					if ($type == "binstream") {
						if (!hasPermissionResource($logged_user_id, "binstream")) {
							die(json_encode("Sem permissão, contate o seu revendedor!"));
						}
					} elseif (!hasPermissionResource($logged_user_id, "iptv")) {
						die(json_encode("Sem permissão, contate o seu revendedor!"));
					}

					if (0 < $months) {
						$client_id = intval($_GET['client_id']);
						if ($type == 'binstream') {
							include_once(__DIR__ . "/class/binstream.php");
							$binstream = new BinStream();
							$client = $binstream->getUser($client_id);
							$client['max_connections'] = 1;
							$client['username'] = explode("@", $client["email"])[0];
							$client['password'] = $client["exField3"];
						} else {
							$client = getClientByID($client_id);
						}

						if ($client) {
							$logged_user = getLoggedUser();

							if ($logged_user) {
								$credits = $client['max_connections'] * 1 * $months;

								if (hasPermission($logged_user_id, $client['id'], $type)) {
									if (addOrRemoveCredits($logged_user_id, -$credits)) {
										if (renewClient($client['id'], $months, $type)) {
											$old_credits = $logged_user['credits'];
											$logged_user = getLoggedUser();
											$now_credits = $logged_user['credits'];
											insertRegUserLog($logged_user['id'], $client['username'], $client['password'], '<b>Renovação</b> | Duração: ' . $months . ' meses | Créditos: <font color="green">' . $old_credits . '</font> > <font color="red">' . $now_credits . '</font> | Custo: ' . $credits . ' Crédito(s)');
											if ($type == 'binstream') {
												clearUserCache($logged_user_id, "clients_expiring_p2p");
											} else {
												clearUserCache($logged_user_id, "clients_expiring");
											}
											$result['result'] = 'success';
											break;
										}
									}
									$result['result'] = 'no_credits';
								}
							}
						}
					}
				}

				break;

			case 'trust_renew_client':
				if (isset($_GET['client_id'])) {
					$type = isset($_GET['type']) ? $_GET['type'] : '';
					$client_id = intval($_GET['client_id']);

					if ($type == "binstream") {
						if (!hasPermissionResource($logged_user_id, "binstream")) {
							$result = array("result" => false, "message" => "Sem permissão, contate o seu revendedor!");
							break;
						}
					} elseif (!hasPermissionResource($logged_user_id, "iptv")) {
						$result = array("result" => false, "message" => "Sem permissão, contate o seu revendedor!");
						break;
					}

					$client = getClientByID($client_id, $type);
					if ($client) {
						$logged_user = getLoggedUser();
						if ($logged_user) {
							if (hasPermission($logged_user_id, $client['id'], $type)) {
								$response = trustRenewClient($client['id'], $type);
								if (isset($response['error'])) {
									$result = array("result" => false, "message" => $response['error']);
									break;
								} else {
									$client['username'] = $type == "binstream" ? explode("@", $client["email"])[0] : $client["username"];
									$client['password'] = $type == "binstream" ? $client["exField3"] : $client["password"];
									insertRegUserLog($logged_user['id'], $client['username'], $client['password'], '<b>Renovação de confiança</b> | Duração: 1 mês | Créditos: <font color="green">' . $logged_user['credits'] . '</font> > <font color="red">' . $logged_user['credits'] . '</font> | Custo: 0 Crédito(s)');
									$result = array("result" => "success", "message" => "Renovação de confiança realizada com sucesso!");
									break;
								}
							}
						}
					}
				}

				break;
			case 'add_screen':
				if (isset($_GET['client_id'])) {
					$client_id = intval($_GET['client_id']);
					$type = isset($_GET['type']) ? $_GET['type'] : 'iptv';
					$logged_user = getLoggedUser();

					if (!hasPermissionResource($logged_user_id, $type)) {
						$result = array("result" => false, "message" => "Sem permissão, contate o seu revendedor!");
						break;
					}
					$client = getClientByID($client_id);
					if (getServerProperty($type . '_max_connections_status', 0)) {
						if ($client['max_connections'] >= getServerProperty($type . '_max_connections', 1)) {
							$result = array("result" => false, "message" => "O cliente já atingiu o limite de conexões!");
							break;
						}
					}
					if ($type == "code") {
						$client['password'] = "********";
					}

					if ($logged_user) {
						if (hasPermission($logged_user_id, $client_id)) {
							if (addOrRemoveCredits($logged_user_id, -1)) {
								if (addScreenClient($client_id, 1)) {
									$old_credits = $logged_user['credits'];
									$logged_user = getLoggedUser();
									$now_credits = $logged_user['credits'];
									insertRegUserLog($logged_user['id'], $client['username'], $client['password'], '<b>Adição de tela</b> | Créditos: <font color="green">' . $old_credits . '</font> > <font color="red">' . $now_credits . '</font> | Custo: 1 Crédito');

									$result['result'] = 'success';
								}
							}
						}
					}
				}

				break;
			case 'change_credits':
				if (isset($_GET['reseller_id']) && isset($_GET['credits'])) {
					$reseller_id = intval($_GET['reseller_id']);
					$credits = intval($_GET['credits']);
					$logged_user = getLoggedUser();

					if ($logged_user) {
						if (masterHasPermission($logged_user_id, $reseller_id)) {
							if (transferCredits($logged_user_id, $reseller_id, $credits)) {
								insertCreditsLog($reseller_id, $logged_user_id, $credits, "");
								if ($credits > 0) {
									deleteUserProperty($reseller_id, "last_recharge");
									addUserProperty($reseller_id, "last_recharge", strtotime('now'));
								}

								$result['result'] = 'success';
							}
						}
					}
				}

				break;
			case 'toggle_block_client':
				$type = isset($_GET['type']) ? $_GET['type'] : '';
				if (isset($_GET['user_id'])) {
					$user_id = intval($_GET['user_id']);

					if ($type == "binstream") {
						if (!hasPermissionResource($logged_user_id, "binstream")) {
							die(json_encode("Sem permissão, contate o seu revendedor!"));
						}
					} elseif (!hasPermissionResource($logged_user_id, "iptv")) {
						die(json_encode("Sem permissão, contate o seu revendedor!"));
					}

					if (hasPermission($logged_user_id, $user_id, $type)) {
						if (toggleClientBlock($user_id, $type)) {
							$result['result'] = 'success';
						}
					}
				}

				break;
			case 'toggle_block_reseller':
				if (isset($_GET['reseller_id'])) {
					$reseller_id = intval($_GET['reseller_id']);
					$allBelow = isset($_GET['all_below']) ? $_GET['all_below'] : false;
					$blockClients = isset($_GET['block_clients']) ? $_GET['block_clients'] : false;

					if ($allBelow == "false") {
						$allBelow = false;
					} else {
						$allBelow = true;
					}

					if ($blockClients == "false") {
						$blockClients = false;
					} else {
						$blockClients = true;
					}

					if (masterHasPermission($logged_user_id, $reseller_id)) {
						if (toggleBlock($reseller_id, $allBelow, $blockClients)) {
							$result['result'] = 'success';
						}
					}
				}

				break;
			case 'delete_client':
				if (isset($_GET['user_id'])) {
					$type = isset($_GET['type']) ? $_GET['type'] : '';
					$user_id = intval($_GET['user_id']);
					if ($type == "binstream") {
						if (!hasPermissionResource($logged_user_id, "binstream")) {
							die(json_encode("Sem permissão, contate o seu revendedor!"));
						}
					} elseif (!hasPermissionResource($logged_user_id, "iptv")) {
						die(json_encode("Sem permissão, contate o seu revendedor!"));
					}

					if (hasPermission($logged_user_id, $user_id, $type)) {
						$client = getClientByID($user_id, $type);

						if ($client) {
							$client['username'] = $type == "binstream" ? explode("@", $client["email"])[0] : $client["username"];
							$client['password'] = $type == "binstream" ? $client["exField3"] : $client["password"];
							if (deleteClient($user_id, $type)) {
								if ($type == "binstream") {
									insertRegUserLog($logged_user_id, $client['username'], $client['password'], '<b>Cliente BinStream Deletado</b>');
								} else {
									insertRegUserLog($logged_user_id, $client['username'], $client['password'], '<b>Cliente IPTV Deletado</b>');
								}
								$result['result'] = 'success';
							}
						}
					}
				}

				break;
			case 'delete_reseller':
				if (isset($_GET['reseller_id'])) {
					$reseller_id = intval($_GET['reseller_id']);

					if (masterHasPermission($logged_user_id, $reseller_id)) {
						if (deleteReseller($reseller_id)) {
							$result['result'] = 'success';
						}
					}
				}

				break;

			case 'btnresellerlogin':
				if (isset($_GET['reseller_id'])) {
					$reseller_id = intval($_GET['reseller_id']);
					$logged_user = getLoggedUser();

					if ($logged_user) {
						if (masterHasPermission($logged_user_id, $reseller_id)) {
							$reseller = loginAsReseller($reseller_id);
							if ($reseller) {
								// $_SESSION['logged_user'] = $reseller;
								$result['result'] = 'success';
							}
						}
					}
				}

				break;

			case 'toggle_ticket':
				if (isset($_GET['ticket_id'])) {
					$ticket_id = intval($_GET['ticket_id']);
					$ticket = getTicketById($ticket_id);
					$logged_user = getLoggedUser();
					if ($ticket && $logged_user) {
						if (($ticket['member_id'] === $logged_user['id']) || isAdmin($logged_user)) {
							if (toggleTicket($ticket_id)) {
								$result['result'] = 'success';
							}
						}
					}
				}

				break;
			case 'delete_ticket':
				if (isset($_GET['ticket_id'])) {
					$ticket_id = intval($_GET['ticket_id']);
					$logged_user = getLoggedUser();

					if ($logged_user) {
						if (isAdmin($logged_user)) {
							if (deleteTicket($ticket_id)) {
								$result['result'] = 'success';
							}
						}
					}
				}

				break;
			case 'toggle_dark_mode':
				if (toggleDarkMode()) {
					$result['result'] = 'success';
				}

				break;
			case 'get_streams':
				break;
			case 'create_update_list':
				break;

			case 'delete_client_plan':
				if (isset($_GET['plan_id'])) {
					$plan_id = $_GET['plan_id'];

					if (deleteClientPlan($logged_user_id, $plan_id)) {
						$result['result'] = 'success';
					}
				}
				break;
			case 'new_vods':
				require_once dirname(__FILE__) . '/api/newVods.php';
				break;

			case 'requestUpdate':
				//get the domain from the request
				$logged_user = getLoggedUser();

				$data = [
					'domain' => $_SERVER['HTTP_HOST'],
					'version' => OFFICE_VERSION,
					'username' => $logged_user['username'],
				];

				if ($logged_user) {
					if ((isAdmin($logged_user) || isPartner($logged_user))) {
						if (requestUpdate($data)) {
							$result['result'] = 'success';
						}
					}
				}
			case 'getDashStats':
				$result['result'] = 'success';
				if (hasPermissionResource($logged_user_id, "binstream") && binStreamEnabled()['success']) {
					include_once(__DIR__ . "/class/binstream.php");
					$binstream = new BinStream();


					$key = OFFICE_CONFIG['panel_id'] . "_userid_" . $logged_user_id . '_count_users_p2p';
					$cached_value = $redis->get($key);
					if ($cached_value !== false) {
						$cached_value = json_decode($cached_value, true);
						$result['p2p']['count']['all'] = $cached_value['all'];
						$result['p2p']['count']['active'] = $cached_value['active'];
						$result['p2p']['count']['trial'] = $cached_value['trial'];
						$result['p2p']['count']['new'] = $cached_value['new'];
					} else {
						$resellers = array($logged_user_id);
						$resellers = array_merge($resellers, getAllResellersIdByOwnerID($logged_user_id));

						$result['p2p']['count']['all'] = $binstream->countUsers($resellers);
						$result['p2p']['count']['active'] = $binstream->countUsers($resellers, "active");
						$result['p2p']['count']['trial'] = $binstream->countUsers($resellers, "trial");
						$result['p2p']['count']['new'] = $binstream->countUsers($resellers, "new");

						$cached_value = json_encode([
							'all' => $result['p2p']['count']['all'],
							'active' => $result['p2p']['count']['active'],
							'trial' => $result['p2p']['count']['trial'],
							'new' => $result['p2p']['count']['new'],
						]);
						$redis->setex($key, 300, $cached_value);
					}
				}

				if (hasPermissionResource($logged_user_id, "iptv")) {
					$key = OFFICE_CONFIG['panel_id'] . "_userid_" . $logged_user_id . '_count_users_iptv';
					$cached_value = $redis->get($key);
					if ($cached_value !== false) {
						$cached_value = json_decode($cached_value, true);
						$result['iptv']['count']['all'] = $cached_value['all'];
						$result['iptv']['count']['active'] = $cached_value['active'];
						$result['iptv']['count']['trial'] = $cached_value['trial'];
						$result['iptv']['count']['new'] = $cached_value['new'];
					} else {
						$logged_user = getLoggedUser();
						$result['iptv']['count']['all'] = getClientsCount($logged_user);
						$result['iptv']['count']['active'] = getActiveCount($logged_user);
						$result['iptv']['count']['trial'] = getTrialClientsCount($logged_user);
						$result['iptv']['count']['new'] = getNewClientsCount($logged_user);

						$cached_value = json_encode([
							'all' => $result['iptv']['count']['all'],
							'active' => $result['iptv']['count']['active'],
							'trial' => $result['iptv']['count']['trial'],
							'new' => $result['iptv']['count']['new'],
						]);
						$redis->setex($key, 300, $cached_value);
					}
				}
				break;

			case 'getResellerStats':
				if (isset($_GET['reseller_id'])) {
					$result['result'] = 'success';
					$reseller_id = intval($_GET['reseller_id']);
					$d1 = (int) getUserProperty($reseller_id, "last_recharge");
					$days = (strtotime("now") - $d1) / 86400;
					$result['last_recharge'] = !empty($d1) ? date("d/m/Y", $d1) . " (" . intval($days) . " Dias)" : "Sem recarga recente";

					$active_clients = totalScreenAndClientsInTree($reseller_id);
					$active_clients_iptv = "<i>Clientes:</i> " . $active_clients['total_users'] . " | <i>Conexões:</i> " . $active_clients['total_max_connections'];

					$result['active_clients_iptv'] = $active_clients_iptv;
					$result['active_clients_code'] = "No momento, está somado com IPTV";
					if (hasPermissionResource($logged_user_id, "binstream") && binStreamEnabled()['success']) {
						$result['active_clients_binstream'] = binCountResellerClients($reseller_id);
					}
				}
				break;

			case 'GenerateUserPass':
				if (isset($_GET['type'])) {
					$type = $_GET['type'];

					if ($type == "binstream") {
						$user_length = getServerProperty('binstream_user_length', 8);
						$user_char = getServerProperty('binstream_user_char', '1');
					} elseif ($type == "iptv") {
						$user_length = getServerProperty('iptv_code_size', 8);
						$user_char = getServerProperty('iptv_code_characters', '1');
					} elseif ($type == "code") {
						$user_length = getServerProperty('code_user_length', 8);
						$user_char = getServerProperty('code_user_char', '1');
					}
					$result['result'] = 'success';
					$length = getServerProperty('password_length');
					$result['code'] = CodeGenerator($user_length, $user_char);
					break;
				}
			case 'delete_chatbot_rule':
				if (isset($_GET['rule_id'])) {
					$chatbot_id = intval($_GET['rule_id']);

					if (deleteChatbotRuleById($chatbot_id, $logged_user_id)) {
						$result['result'] = 'success';
					}
				}

				break;
			case 'togle_chatbot_rule':
				if (isset($_GET['rule_id'])) {
					$chatbot_id = intval($_GET['rule_id']);

					if (toggleChatbotRuleStatus($chatbot_id, $logged_user_id)) {
						$result['result'] = 'success';
					}
				}

				break;

			case 'get_resellers_simple':
				if (isset($_GET['search'])) {

					$search = (isset($_GET['search']) ? $_GET['search'] : '');
					$result = getSimpleResellerList($logged_user_id, $search);
				}

				break;
		}
	} elseif (isset($_POST['action'])) {
		if (isset($_POST['action'])) {
			$action = $_POST['action'];
			parse_str(file_get_contents("php://input"), $post_vars);
			switch ($action) {

				case 'add_chatbot_rule':
					if (isset($_POST['rule_type']) && isset($_POST['rule_action']) && isset($_POST['response'])) {
						if (empty($_POST['rule_type']) || empty($_POST['rule_action']) || empty($_POST['response'])) {
							$result = ["success" => false, "message" => "Um ou mais campos vazios"];
							break;
						}

						$rule_type = purifyHTML($_POST['rule_type']);
						$rule_action = purifyHTML($_POST['rule_action']);
						$response = purifyHTML($_POST['response']);
						$messages = purifyHTML($_POST['messages']);

						foreach ($messages as $message) {
							if (empty($message)) {
								$result = ["success" => false, "message" => "Adicione pelo menos uma mensagem"];
								break;
							}
						}

						$rule_id = addChatbotRule($logged_user_id, $rule_type, $rule_action, $response, $messages);
						if ($rule_id) {
							$result = ["success" => true, "message" => "success"];
						} else {
							$result = ["success" => false, "message" => "Falha ao criar a regra"];
						}
					}
					break;

				case 'edit_chatbot_rule':
					if (isset($_POST['rule_type']) && isset($_POST['rule_action']) && isset($_POST['response'])) {
						if (empty($_POST['rule_type']) || empty($_POST['rule_action']) || empty($_POST['response'])) {
							$result = ["success" => false, "message" => "Um ou mais campos vazios"];
							break;
						}

						if (empty($_POST['rule_id'])) {
							$result = ["success" => false, "message" => "ID da regra não encontrado"];
							break;
						}

						$chatbot_id = purifyHTML($_POST['rule_id']);

						$rule_type = purifyHTML($_POST['rule_type']);
						$rule_action = purifyHTML($_POST['rule_action']);
						$response = purifyHTML($_POST['response']);
						$messages = purifyHTML($_POST['messages']);

						foreach ($messages as $message) {
							if (empty($message)) {
								$result = ["success" => false, "message" => "Adicione pelo menos uma mensagem"];
								break;
							}
						}

						$rule_id = updateChatBotRule($logged_user_id, $chatbot_id, $rule_type, $rule_action, $response, $messages);
						if ($rule_id) {
							$result = ["success" => true, "message" => "success"];
						} else {
							$result = ["success" => false, "message" => "Falha ao atualizar a regra"];
						}
					}
					break;
			}
			echo json_encode($result);
			die();
		}
	}

	// if (isset($_GET['action']) && isset($_GET['t'])) {
	// if (MANAGEMENT_TOKEN === $_GET['t']) {
	// 	$action = $_GET['action'];
	// 	switch ($action) {
	// 		case 'install_db':
	// 			$result = array('result' => importSQL());
	// 			break;

	// 		case 'change_admin_pass':
	// 			if (isset($_POST['admin_user']) && isset($_POST['admin_pass'])) {

	// 				if (!empty($_POST['admin_user']) || !empty($_POST['admin_pass'])) {
	// 					deleteServerProperty("admin_data");
	// 					$admin_data = ["username" => $_POST['admin_user'], "password" => $_POST['admin_pass']];
	// 					$result = array('result' => addServerProperty("admin_data", json_encode($admin_data)));
	// 					break;
	// 				}
	// 			}
	// 			$result = array("result" => false, "message" => "Username/Password can't be empty");
	// 			break;

	// 		case 'set_db_info':
	// 			if (isset($_POST['hostname']) && isset($_POST['username']) && isset($_POST['database']) && isset($_POST['password']) && isset($_POST['port']) && isset($_POST['uuid'])) {
	// 				$DB_INFO = json_decode(file_get_contents(__DIR__ . "/../../dbinfo.json"), true);
	// 				$DB_INFO['office_db'] = [
	// 					"hostname" => $_POST['hostname'],
	// 					"username" => $_POST['username'],
	// 					"database" => $_POST['database'],
	// 					"password" => $_POST['password'],
	// 					"port" => $_POST['port']
	// 				];
	// 				$DB_INFO['panel_id'] = $_POST['uuid'];
	// 				$DB_INFO['shorten_url'] = "https://" . $_POST['shorten_url'];
	// 				$DB_INFO['ssiptv_url'] = "https://" . $_POST['ssiptv_url'];
	// 				if (file_put_contents(__DIR__ . "/../../dbinfo.json", json_encode($DB_INFO))) {
	// 					$result = array("result" => true);
	// 					break;
	// 				}
	// 			}
	// 			$result = array("result" => false, "message" => "Username/Password can't be empty");
	// 			//exit;
	// 			break;
	// 		case 'status':
	// 			$PDO = getConnection();
	// 			if ($PDO !== NULL) {
	// 				$result = array("result" => true);
	// 				break;
	// 			}
	// case 'get_panel_info':
	// 	$DB_INFO = json_decode(file_get_contents(__DIR__ . "/../../dbinfo.json"), true);
	// 	$result = array("result" => true, "data" => $DB_INFO);
	// 	break;
	// 	}
	// }
	// }
} else {
	if (isset($_POST['action'])) {
		$action = $_POST['action'];
		parse_str(file_get_contents("php://input"), $post_vars);
		switch ($action) {
			case 'login':
				$username = $post_vars['username'];
				$password = $post_vars['password'];
				if (empty($username) || empty($password)) {
					$result = ["success" => false, "message" => "empty_user_or_pass"];
					break;
				}

				$recaptcha = isset($post_vars['recaptcha']) ? $post_vars['recaptcha'] : null;
				if (getServerProperty('recaptcha_enable') && !isset($_COOKIE['username'])) {
					require_once __DIR__ . "/recaptchalib.php";
					$secret = getServerProperty('recaptcha_secret_key');
					$response = null;
					$reCaptcha = new ReCaptcha($secret);
					$response = $reCaptcha->verifyResponse($_SERVER["REMOTE_ADDR"], $recaptcha);

					if ($response == null || !$response->success) {
						$result = ["success" => false, "message" => "captcha"];
						break;
					}
				}

				$result_login = loginUser($username, $password);


				switch ($result_login) {
					case 1:
						$user = getLoggedUser();
						$maintenance = maintenanceEnabled();
						if (!isAdmin($user) && $maintenance['status']) {
							$result = ["success" => false, "message" => "maintenance", "text" => $maintenance['message'], "button_text" => $maintenance['button_text'], "button_link" => $maintenance['button_link']];
							break;
						}

						$result = ["success" => true, "message" => "success"];
						break;
					case 2:
						$result = ["success" => false, "message" => "cant_connect"];
						break;
					case 3:
						$result = ["success" => false, "message" => "invalid_user_or_pass"];
						break;
					case 4:
						$result = ["success" => false, "message" => "blocked"];
						break;
					case 5:
						$result = ["success" => false, "message" => "insufficient_permission"];
						break;
				}
				break;
		}
	}
}

echo json_encode($result);
