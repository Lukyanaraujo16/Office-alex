<?php
@ini_set('memory_limit', '-1');
ignore_user_abort(true);
set_time_limit(0);
header('Content-type: application/json');
$result = array('result' => 'failed');
include_once(__DIR__ . '/functions.php');
startSession();

if (isset($_SESSION['__l0gg3d_Client__'])) {
	if (isset($_GET['action'])) {
		$action = $_GET['action'];
		$logged_user_id = intval($_SESSION['__l0gg3d_Client__']);

		switch ($action) {
			case 'load_gateway':
				require_once dirname(__FILE__) . '/modules/gateways/load.php';
				break;
			case 'start_gateway':
				require_once dirname(__FILE__) . '/modules/gateways/start.php';
				break;
			case 'cb_gateway_mercadopago':
				require_once dirname(__FILE__) . '/modules/gateways/cb.mercadopago.php';
				break;

			case 'get_transactions':
				if (isset($_GET['start']) && isset($_GET['length']) && isset($_GET['search']) && isset($_GET['order'])) {
					$start = intval($_GET['start']);
					$length = intval($_GET['length']);
					$search = (isset($_GET['search']['value']) ? $_GET['search']['value'] : '');
					$order_column_index = (isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0);
					$order_type = (isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc');
					$draw = (isset($_GET['draw']) ? intval($_GET['draw']) : 1);
					$result = getClientTransactions($logged_user_id, $start, $length, $search, $order_column_index, $order_type);
					$result['draw'] = $draw;
				}

				break;
			case 'new_vods':
				require_once dirname(__FILE__) . '/api/newVods.php';
				break;
		}
	}
} else if (isset($_GET['action'])) {
	$action = $_GET['action'];
	switch ($action) {
		case 'cb_gateway_mercadopago':
			require_once dirname(__FILE__) . '/modules/gateways/cb.mercadopago.php';
			break;
		case 'cb_gateway_woovi':
			require_once dirname(__FILE__) . '/modules/gateways/cb.woovi.php';
			break;
	}
}

echo json_encode($result);
