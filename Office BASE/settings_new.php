<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$server_name = getServerProperty('server_name');

$partner_allowed_pages = getAllowedPages($logged_user['id']);
$partner_allowed_pages = is_null($partner_allowed_pages) ? array() : $partner_allowed_pages;


if (isAdmin($logged_user) || (isPartner($logged_user) && in_array($_GET['page'], $partner_allowed_pages))) {
	//Allowed
} else {
	header("Location: /dashboard");
	exit;
}

if (isset($_POST['save_geral']) && isset($_POST['server_name'])) {
	$server_name = purifyHTML($_POST['server_name']);
	updateServerProperty('server_name', $server_name);

	$server_logo_small = purifyHTML($_POST['server_logo_small']);
	updateServerProperty('server_logo_small', $server_logo_small);

	$server_logo_big = purifyHTML($_POST['server_logo_big']);
	updateServerProperty('server_logo_big', $server_logo_big);

	$login_background = purifyHTML($_POST['login_background']);
	updateServerProperty('login_background', $login_background);

	$custom_dns = purifyHTML($_POST['custom_dns']);
	updateServerProperty('custom_dns', $custom_dns);

	$telegram = purifyHTML($_POST['telegram']);
	updateServerProperty('telegram', $telegram);

	$whatsapp = purifyHTML($_POST['whatsapp']);
	updateServerProperty('whatsapp', $whatsapp);

	header('location: ./geral&result=geral_settings_saved');
	exit();
}

if (isset($_POST['save_info']) && isset($_POST['fixed_informations'])) {
	$fixed_informations = purifyHTML($_POST['fixed_informations'], "br, p, b, i, u, ol, ul, li, h1, h2, h3, h4, h5, h6, a, img, table, thead, tbody, tr, th, td, span, div");
	$result1 = updateServerProperty('fixed_informations', $fixed_informations);

	if ($result1) {
		header('location: ./informations&result=fixed_informations_saved');
		exit();
	}

	header('location: ./informations&result=failed');
	exit();
}

if (isset($_POST['save_allowed_groups']) && isset($_POST['allowed_groups'])) {
	$admin_group = purifyHTML($_POST['admin_group']);
	$partner_group = purifyHTML($_POST['partner_group']);
	$ultra_group = purifyHTML($_POST['ultra_group']);
	$master_group = purifyHTML($_POST['master_group']);
	$reseller_group = purifyHTML($_POST['reseller_group']);
	$group_settings = json_encode(array('admin' => $admin_group, 'partner' => $partner_group, 'ultra' => $ultra_group, 'master' => $master_group, 'reseller' => $reseller_group));
	$result1 = updateServerProperty('group_settings', $group_settings);

	$allowed_groups = json_encode(purifyHTML($_POST['allowed_groups']));
	$result2 = updateServerProperty('allowed_groups', $allowed_groups);

	$partner_allowed_pages = json_encode(purifyHTML($_POST['partner_allowed_pages']));
	$result3 = updateServerProperty('partner_allowed_pages', $partner_allowed_pages);

	if ($result1 && $result2 && $result3) {
		header('location: ./allowed_groups&result=allowed_groups_saved');
		exit();
	}

	header('location: ./allowed_groups&result=failed');
	exit();
}

if (isset($_POST['save_recaptcha'])) {

	$recaptcha_enable = (isset($_POST['recaptcha_enable']) ? 1 : 0);
	$recaptcha_site_key = purifyHTML($_POST['recaptcha_site_key']);
	$recaptcha_secret_key = purifyHTML($_POST['recaptcha_secret_key']);

	$result1 = updateServerProperty('recaptcha_enable', $recaptcha_enable);
	$recaptcha_site_key = $_POST['recaptcha_site_key'];
	$result2 = updateServerProperty('recaptcha_site_key', $recaptcha_site_key);
	$recaptcha_secret_key = $_POST['recaptcha_secret_key'];
	$result3 = updateServerProperty('recaptcha_secret_key', $recaptcha_secret_key);

	if ($result1 && $result2 && $result3) {
		header('location: ./grupos&result=recaptcha_saved');
		exit();
	}

	header('location: ./grupos&result=failed');
	exit();
}

if (isset($_POST['save_allowed_bouquets']) && isset($_POST['allowed_bouquets'])) {
	$allowed_bouquets = json_encode(purifyHTML($_POST['allowed_bouquets']));
	$result1 = updateServerProperty('allowed_bouquets', $allowed_bouquets);

	if ($result1) {
		header('location: ./allowed_bouquets&result=allowed_bouquets_saved');
		exit();
	}

	header('location: ./allowed_bouquets&result=failed');
	exit();
}

if (isset($_POST['save_test_default_template']) && isset($_POST['template_iptv']) && isset($_POST['template_p2p'])) {

	$template_iptv = htmlspecialchars($_POST['template_iptv'], ENT_QUOTES, 'UTF-8');
	$result1 = updateServerProperty('default_test_template_iptv', $template_iptv);

	$template_p2p = $_POST['template_p2p'];
	$result2 = updateServerProperty('default_test_template_p2p', $template_p2p);

	$template_code = $_POST['template_code'];
	$result2 = updateServerProperty('default_test_template_code', $template_code);

	$template_exp_mesage = $_POST['template_exp_mesage'];
	$result3 = updateServerProperty('default_expiring_template', $template_exp_mesage);

	if ($result1 && $result2 && $result3) {
		header('location: ./fast_test_template&result=default_template_saved');
		exit();
	}
}

if (isset($_POST['save_fast_test_sidebar']) && isset($_POST['fast_packages']) && isset($_POST['test_time']) && isset($_POST['min_credits'])) {
	$fast_packages = json_encode(purifyHTML($_POST['fast_packages']));
	updateServerProperty('fast_packages', $fast_packages);
	$test_time = intval(purifyHTML($_POST['test_time']));
	updateServerProperty('test_time', $test_time);
	$min_credits = intval(purifyHTML($_POST['min_credits']));
	updateServerProperty('test_min_credits', $min_credits);

	header('location: ./fast_test_sidebar&result=fast_test_sidebar_saved');
	exit();
}

if (isset($_POST['save_automatic_test']) && isset($_POST['disabled_days_automatic_test']) && isset($_POST['automatic_test_packages']) && isset($_POST['automatic_test_min_credits'])) {
	$automatic_test = (isset($_POST['automatic_test']) ? 1 : 0);
	updateServerProperty('automatic_test', $automatic_test);

	$automatic_test_packages = json_encode(purifyHTML($_POST['automatic_test_packages']));
	updateServerProperty('automatic_test_packages', $automatic_test_packages);

	$binstream_automatic_test_packages = json_encode(purifyHTML($_POST['binstream_automatic_test_packages']));
	updateServerProperty('binstream_automatic_test_packages', $binstream_automatic_test_packages);

	$code_automatic_test_packages = json_encode(purifyHTML($_POST['code_automatic_test_packages']));
	updateServerProperty('code_automatic_test_packages', $code_automatic_test_packages);

	$random_name_automatic_test = (isset($_POST['random_name_automatic_test']) ? 1 : 0);
	updateServerProperty('random_name_automatic_test', $random_name_automatic_test);
	$only_valid_emails_automatic_test = (isset($_POST['only_valid_emails_automatic_test']) ? 1 : 0);
	updateServerProperty('only_valid_emails_automatic_test', $only_valid_emails_automatic_test);
	$disabled_days_automatic_test = purifyHTML($_POST['disabled_days_automatic_test']);
	updateServerProperty('disabled_days_automatic_test', $disabled_days_automatic_test);
	$automatic_test_min_credits = intval(purifyHTML($_POST['automatic_test_min_credits']));
	updateServerProperty('automatic_test_min_credits', $automatic_test_min_credits);
	header('location: ./geradorteste&result=automatic_test_saved');
	exit();
}

if (isset($_POST['save_dash_fast_test']) && isset($_POST['fast_test_package'])) {
	// $dash_fast_test = (isset($_POST['dash_fast_test']) ? 1 : 0);
	$dash_fast_test = 1;
	updateServerProperty('dash_fast_test', $dash_fast_test);

	$fast_test_package = intval(purifyHTML($_POST['fast_test_package']));
	updateServerProperty('fast_test_package', $fast_test_package);

	$code_fast_test_package = intval(purifyHTML($_POST['code_fast_test_package']));
	updateServerProperty('code_fast_test_package', $code_fast_test_package);

	$binstream_fast_test_package = (isset($_POST['binstream_fast_test_package']) ? purifyHTML($_POST['binstream_fast_test_package']) : "");
	updateServerProperty('binstream_fast_test_package', $binstream_fast_test_package);

	header('location: ./fast_test_dash&result=automatic_test_saved');
	exit();
}

if (isset($_POST['save_clients']) && isset($_POST['iptv_code_characters']) && isset($_POST['iptv_code_size']) && isset($_POST['binstream_user_char']) && isset($_POST['binstream_user_length']) && isset($_POST['code_user_char']) && isset($_POST['code_user_length']) && isset($_POST['code_default_pass'])) {

	$iptv_code_characters = purifyHTML($_POST['iptv_code_characters']);
	updateServerProperty('iptv_code_characters', $iptv_code_characters);

	$iptv_code_size = intval($_POST['iptv_code_size']);
	updateServerProperty('iptv_code_size', $iptv_code_size);

	$test_time_custom = json_encode(is_array($_POST['test_time_custom']) ? purifyHTML($_POST['test_time_custom']) : []);
	$result1 = updateServerProperty('test_time_custom', $test_time_custom);

	$iptv_migration_status = (isset($_POST['iptv_migration_status']) ? 1 : 0);
	updateServerProperty('iptv_migration_status', $iptv_migration_status);

	$iptv_migration_fee = (isset($_POST['iptv_migration_fee']) ? 1 : 0);
	updateServerProperty('iptv_migration_fee', $iptv_migration_fee);

	$iptv_trust_renew_status = (isset($_POST['iptv_trust_renew_status']) ? 1 : 0);
	updateServerProperty('iptv_trust_renew_status', $iptv_trust_renew_status);

	$iptv_trust_renew_time = intval($_POST['iptv_trust_renew_time']);
	updateServerProperty('iptv_trust_renew_time', $iptv_trust_renew_time);

	$iptv_max_connections_status = (isset($_POST['iptv_max_connections_status']) ? 1 : 0);
	updateServerProperty('iptv_max_connections_status', $iptv_max_connections_status);

	$iptv_max_connections = intval($_POST['iptv_max_connections']);
	updateServerProperty('iptv_max_connections', $iptv_max_connections);

	$iptv_show_online_clients = (isset($_POST['iptv_show_online_clients']) ? 1 : 0);
	updateServerProperty('iptv_show_online_clients', $iptv_show_online_clients);

	$iptv_show_m3u_link = (isset($_POST['iptv_show_m3u_link']) ? 1 : 0);
	updateServerProperty('iptv_show_m3u_link', $iptv_show_m3u_link);

	$binstream_user_char = $_POST['binstream_user_char'] ? purifyHTML($_POST['binstream_user_char']) : [];
	updateServerProperty('binstream_user_char', $binstream_user_char);

	$binstream_user_length = intval($_POST['binstream_user_length']);
	updateServerProperty('binstream_user_length', $binstream_user_length);

	$binstream_trust_renew_status = (isset($_POST['binstream_trust_renew_status']) ? 1 : 0);
	updateServerProperty('binstream_trust_renew_status', $binstream_trust_renew_status);

	$binstream_trust_renew_time = intval($_POST['binstream_trust_renew_time']);
	updateServerProperty('binstream_trust_renew_time', $binstream_trust_renew_time);

	$code_status = (isset($_POST['code_status']) ? 1 : 0);
	updateServerProperty('code_status', $code_status);

	$code_user_char = purifyHTML($_POST['code_user_char']);
	updateServerProperty('code_user_char', $code_user_char);

	$code_user_length = intval($_POST['code_user_length']);
	updateServerProperty('code_user_length', $code_user_length);

	$code_default_pass = purifyHTML($_POST['code_default_pass']);
	updateServerProperty('code_default_pass', $code_default_pass);

	$code_max_connections_status = (isset($_POST['code_max_connections_status']) ? 1 : 0);
	updateServerProperty('code_max_connections_status', $code_max_connections_status);

	$code_max_connections = intval($_POST['code_max_connections']);
	updateServerProperty('code_max_connections', $code_max_connections);

	header('location: ./clients&result=clients_saved');
	exit();
}

if (isset($_POST['save_p2p_binstream'])) {
	$DB_INFO = OFFICE_CONFIG;

	$binstream_status = (isset($_POST['binstream_status']) ? true : false);
	$binstream_url = trim(purifyHTML($_POST['bin_api_url']));
	$binstream_token = trim(purifyHTML($_POST['bin_api_token']));
	$binstream_domain = trim(purifyHTML($_POST['bin_domain']));

	if (empty($binstream_token)) {
		$binstream_token = $DB_INFO['binstream']['token'];
	}

	$resultCheck = checkBinStreamConfig($binstream_url, $binstream_token);
	if ($resultCheck['success'] === true) {
		$binstream_status = $binstream_status;

		//verifica se $_POST['binstream_allowed_packages'] é nulo
		if (empty($_POST['binstream_allowed_packages'])) {
			$_POST['binstream_allowed_packages'] = [];
		}
		$binstream_allowed_packages = json_encode($_POST['binstream_allowed_packages']);
		$result1 = updateServerProperty('binstream_allowed_packages', $binstream_allowed_packages);

		$binstream_test_time = intval($_POST['binstream_test_time']);
		$result2 = updateServerProperty('binstream_test_time', $binstream_test_time);

		$location = './p2p_binstream&result=binstream_saved';
	} else {
		$binstream_status = false;
		$location = './p2p_binstream&result=binstream_error&message=' . urlencode($resultCheck['error']);
	}

	$DB_INFO = OFFICE_CONFIG;

	$DB_INFO['binstream']['enabled'] = $binstream_status;

	if (!empty($binstream_url)) {
		$DB_INFO['binstream']['url'] = $binstream_url;
	}
	if (!empty($binstream_domain)) {
		$DB_INFO['binstream']['email'] = $binstream_domain;
	}
	if (!empty($binstream_token)) {
		$DB_INFO['binstream']['token'] = $binstream_token;
	}

	if (file_put_contents(__DIR__ . "/../dbinfo.json", json_encode($DB_INFO))) {
		header('location: ' . $location);
	}

	exit();
}

if (isset($_POST['save_ssiptv']) && isset($_POST['ssiptv_live_name']) && isset($_POST['ssiptv_live_image']) && isset($_POST['ssiptv_movie_name']) && isset($_POST['ssiptv_movie_image']) && isset($_POST['ssiptv_serie_name']) && isset($_POST['ssiptv_serie_image'])) {
	$output = (isset($_POST['ssiptv_output']) ? "mpegts" : "hls");
	$live_enabled = (isset($_POST['ssiptv_live_enabled']) ? 1 : 0);
	$movie_enabled = (isset($_POST['ssiptv_movie_enabled']) ? 1 : 0);
	$serie_enabled = (isset($_POST['ssiptv_serie_enabled']) ? 1 : 0);

	$DB_Data = OFFICE_CONFIG;

	$DB_INFO['ssiptv'] = [
		"output" => $output,
		"live" => ["enabled" => $live_enabled, "name" => purifyHTML($_POST['ssiptv_live_name']), "image" => purifyHTML($_POST['ssiptv_live_image'])],
		"movie" => ["enabled" => $movie_enabled, "name" => purifyHTML($_POST['ssiptv_movie_name']), "image" => purifyHTML($_POST['ssiptv_movie_image'])],
		"serie" => ["enabled" => $serie_enabled, "name" => purifyHTML($_POST['ssiptv_serie_name']), "image" => purifyHTML($_POST['ssiptv_serie_image'])]
	];
	if (file_put_contents(__DIR__ . "/../dbinfo.json", json_encode($DB_INFO))) {
		header('location: ./ssiptv&result=ssiptv_saved');
	}

	exit();
}

if (isset($_POST['change_resellers']) && isset($_POST['selected_resellers']) && isset($_POST['new_owner']) && isset($_POST['new_group_name'])) {
	$selected_resellers = purifyHTML($_POST['selected_resellers']);
	$new_owner = intval($_POST['new_owner']);
	$new_group = purifyHTML($_POST['new_group_name']);

	if (is_array($selected_resellers)) {
		$group_settings = json_decode(getServerProperty('group_settings'), true);
		$group_id = (isset($group_settings[$new_group]) ? $group_settings[$new_group] : 0);

		if (transferResellers($selected_resellers, $new_owner, $group_id)) {
			header('location: ./tools&result=resellers_changed');
			exit();
		}
	}

	header('location: ./tools&result=failed');
	exit();
}

if (isset($_POST['update_tables'])) {

	if (updateTables()) {
		header('location: ./tools&result=tables_updated');
		exit();
	}

	header('location: ./tools&result=update_failed');
	exit();
}

if (isset($_POST['save_email_settings']) && isset($_POST['encryption_type']) && isset($_POST['sender_name']) && isset($_POST['sender_email']) && isset($_POST['use_smtp']) && isset($_POST['smtp_server']) && isset($_POST['smtp_port']) && isset($_POST['smtp_username']) && isset($_POST['smtp_password'])) {
	$email_settings = purifyHTML($_POST);
	unset($email_settings['save_email_settings']);
	$email_settings = json_encode($email_settings);
	$result1 = updateServerProperty('email_settings', $email_settings);

	if ($result1) {
		header('location: ./email_config&result=email_settings_saved');
		exit();
	}

	header('location: ./email_config&result=failed');
	exit();
}

if (isset($_POST['save_email_messages']) && isset($_POST['auto_test_subject']) && isset($_POST['auto_test_message']) && isset($_POST['pass_recovery_subject']) && isset($_POST['pass_recovery_message'])) {
	$email_messages = $_POST;
	unset($email_messages['save_email_messages']);
	$email_messages = json_encode($email_messages);
	$result1 = updateServerProperty('email_messages', $email_messages);

	if ($result1) {
		header('location: ./email_template&result=email_messages_saved');
		exit();
	}

	header('location: ./email_template&result=failed');
	exit();
}

if (isset($_POST['save_maintenance'])) {

	$maintenance_status = (isset($_POST['maintenance_status']) ? 1 : 0);
	$maintenance_message = purifyHTML($_POST['maintenance_message'], "br, b, i, u, ol, ul, li, h1, h2, h3, h4, h5, h6, a, img, span, div");
	$maintenance_button_text = purifyHTML($_POST['maintenance_button_text']);
	$maintenance_button_link = purifyHTML($_POST['maintenance_button_link']);

	$maintenance = json_encode(array('status' => $maintenance_status, 'message' => $maintenance_message, 'button_text' => $maintenance_button_text, 'button_link' => $maintenance_button_link));
	$result = updateServerProperty('maintenance', $maintenance);

	if ($result) {
		header('location: ./maintenance&result=maintenance_settings_saved');
		exit();
	} else {
		header('location: ./maintenance&result=failed');
		exit();
	}
}

$settings = getServerProperties();
$fixed_informations = $settings['fixed_informations'];
$allowed_groups = (isset($settings['allowed_groups']) ? json_decode($settings['allowed_groups'], true) : array());
$allowed_bouquets = (isset($settings['allowed_bouquets']) ? json_decode($settings['allowed_bouquets'], true) : array());
$fast_packages = (isset($settings['fast_packages']) ? json_decode($settings['fast_packages'], true) : array());

$automatic_test_packages = (isset($settings['automatic_test_packages']) ? json_decode($settings['automatic_test_packages'], true) : array());
$code_automatic_test_packages = (isset($settings['code_automatic_test_packages']) ? json_decode($settings['code_automatic_test_packages'], true) : array());
$binstream_automatic_test_packages = (isset($settings['binstream_automatic_test_packages']) ? json_decode($settings['binstream_automatic_test_packages'], true) : array());

$automatic_test_packages = is_null($automatic_test_packages) ? array() : $automatic_test_packages;
$code_automatic_test_packages = is_null($code_automatic_test_packages) ? array() : $code_automatic_test_packages;
$binstream_automatic_test_packages = is_null($binstream_automatic_test_packages) ? array() : $binstream_automatic_test_packages;

$binstream_fast_test_package = (isset($settings['binstream_fast_test_package']) ? json_decode($settings['binstream_fast_test_package'], true) : array());

if (isset($settings['test_time_custom']) && !is_null($settings['test_time_custom'])) {
	$test_time_custom = json_decode($settings['test_time_custom'], true);
} else {
	$test_time_custom = array("4");
}

asort($test_time_custom);

$email_settings = json_decode($settings['email_settings'], true);
$email_messages = json_decode($settings['email_messages'], true);
$binstream_allowed_packages = (!empty($settings['binstream_allowed_packages']) ? json_decode($settings['binstream_allowed_packages'], true) : array());

clearServerCache();
?>
<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?php echo $server_name; ?></title>
	<!-- Tell the browser to be responsive to screen width -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="/plugins/fontawesome-pro/css/all.min.css">
	<!-- Ionicons -->
	<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<!-- iCheck for checkboxes and radio inputs -->
	<link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
	<!-- daterange picker -->
	<link rel="stylesheet" href="/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
	<!-- summernote -->
	<link rel="stylesheet" href="/plugins/summernote/summernote-bs4.css">
	<!-- Bootstrap Switch -->
	<!-- <script src="/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script> -->
	<!-- Theme style -->
	<link rel="stylesheet" href="/dist/css/adminlte.min.css?<?php echo OFFICE_VERSION ?>">
	<!-- Google Font: Source Sans Pro -->
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini text-sm layout-footer-fixed <?php if (DarkMode()) {
																																				echo "dark-mode";
																																			} ?>">
	<div class="wrapper">
		<?php include_once('sidebar.php'); ?>
		<!-- Content Wrapper. Contains page content -->
		<div class="content-wrapper">
			<!-- Content Header (Page header) -->
			<section class="content-header">
				<?php
				if (isset($_GET['result'])) {
					$result = $_GET['result'];
					$result_message = 'Aconteceu um problema, tente novamente mais tarde!';
					$result_type = 'warning';

					switch ($result) {
						case 'geral_settings_saved':
							$result_message = 'Configurações gerais salvas com sucesso.';
							$result_type = 'success';
							break;
						case 'fixed_informations_saved':
							$result_message = 'Informaes Fixas salvas com sucesso.';
							$result_type = 'success';
							break;
						case 'allowed_groups_saved':
							$result_message = 'Grupos permitidos salvos com sucesso.';
							$result_type = 'success';
							break;
						case 'allowed_bouquets_saved':
							$result_message = 'Listas permitidas salvas com sucesso.';
							$result_type = 'success';
							break;
						case 'fast_test_sidebar_saved':
							$result_message = 'As configurações de teste da sidebar foram salvas com sucesso.';
							$result_type = 'success';
							break;
						case 'automatic_test_saved':
							$result_message = 'As configurações do gerador de teste automático foram salvas com sucesso.';
							$result_type = 'success';
							break;
						case 'resellers_changed':
							$result_message = 'Os revendedores foram transferidos com sucesso.';
							$result_type = 'success';
							break;
						case 'tables_updated':
							$result_message = 'Os revendedores foram transferidos com sucesso.';
							$result_type = 'success';
							break;
						case 'email_messages_saved':
							$result_message = 'Mensagens de email salvas com sucesso.';
							$result_type = 'success';
							break;
						case 'email_settings_saved':
							$result_message = 'Configurações de email salvas com sucesso.';
							$result_type = 'success';
							break;
						case 'clients_saved':
							$result_message = 'Configurações salvas com sucesso.';
							$result_type = 'success';
							break;
						case 'default_template_saved':
							$result_message = 'Configurações do template salvas com sucesso.';
							$result_type = 'success';
							break;
						case 'recaptcha_saved':
							$result_message = 'Configurações reCAPTCHA salvas com sucesso.';
							$result_type = 'success';
							break;
						case 'binstream_saved':
							$result_message = 'Configurações BinStream salvas com sucesso. Conexão bem sucedida!';
							$result_type = 'success';
							break;
						case 'ssiptv_saved':
							$result_message = 'Configurações SSIPTV salvas com sucesso.';
							$result_type = 'success';
							break;
						case 'binstream_error':
							$result_message = urldecode($_GET['message']);
							$result_type = 'warning';
							break;
						case 'maintenance_settings_saved':
							$result_message = 'Configurações de manutenção salvas com sucesso.';
							$result_type = 'success';
							break;
					} ?>
					<div class="alert alert-<?php echo $result_type; ?>">
						<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
						<?php
						switch ($result_type) {
							case 'success':
								$icon = "fas fa-check-circle";
								break;
							case 'warning':
								$icon = "fas fa-exclamation-circle";
								break;
							case 'danger':
								$icon = "fas fa-exclamation-triangle";
								break;

							default:
								$icon = "fas fa-check-circle";
								break;
						} ?>
						<i class="icon <?php echo $icon ?>"></i>
						<?php echo $result_message; ?>
					</div>
				<?php } ?>
				<div class="container-fluid">
					<div class="row mb-2">
						<div class="col-sm-6">
							<h1>Configurações</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Configurações</li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
				<div class="container-fluid">
					<div class="card card-primary card-tabs">
						<div class="card-header p-0 pt-1">
							<ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
								<?php
								$nav_itens = [
									"Geral" => "geral",
									"Informações" => "informations",
									"Grupos Permitidos" => "allowed_groups",
									"Buquês Permitidos <span class='badge badge-info'>IPTV</span>" => "allowed_bouquets",
									"Clientes" => "clients",
									"P2P <span class='badge badge-info'>BinStream</span>" => "p2p_binstream",
									"SSIPTV" => "ssiptv",
									"Template Mensagem Rápida" => "fast_test_template",
									"Teste Rápido <span class='badge badge-info'>Menu Lateral</span>" => "fast_test_sidebar",
									"Teste Rápido <span class='badge badge-info'>Botão Topo</span>" => "fast_test_top_button",
									"Gerador de testes <span class='badge badge-info'>Link</span>" => "geradorteste",
									"E-mail <span class='badge badge-info'>Config</span>" => "email_config",
									"E-mail <span class='badge badge-info'>Template</span>" => "email_template",
									"Ferramentas" => "tools",
									"Captcha <span class='badge badge-info'>Seguraça</span>" => "captcha",
									"Manutenção" => "maintenance"
								];
								//insere caracter de espaço
								$nav_itens = str_replace(' ', '&nbsp;', $nav_itens);
								foreach ($nav_itens as $key => $value) {

									if ($_GET['page'] == $value) {
										$active = 'active';
										$aria_selected = 'true';
									} else {
										$active = '';
										$aria_selected = 'false';
									}
									if (isAdmin($logged_user) || (isPartner($logged_user) && in_array($value, $partner_allowed_pages))) {

								?>
										<li class="nav-item">
											<a class="nav-link settings <?php echo $active; ?>" id="custom-tabs-one-home-tab" href="<?php echo './' . $value; ?>" role="tab" aria-controls="custom-tabs-one-home" aria-selected="<?php echo $aria_selected; ?>"><?php echo $key ?></a>
										</li>
								<?php }
								} ?>
							</ul>
						</div>
						<?php if ($_GET['page'] == "geral") { ?>
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Geral</h3>
								</div>
								<div class="card-body pad">
									<div class="row col-lg-6 col-md-12">
										<div class="col-md-12">
											<label>Identificador do Painel</label>
											<div class="form-group input-group">
												<input type="text" class="form-control" readonly value="<?php echo OFFICE_CONFIG['panel_id']; ?>" id="panel_uuid" name="panel_uuid">
												<div class="input-group-append"> <button type="button" class="btn btn-sm btn-primary bg-gradient waves-effect waves-light copy_panel_uuid" data-clipboard-target="#panel_uuid">COPIAR</button> </div>
											</div>
										</div>
										<div class="col-md-12">
											<label>Nome do Servidor</label>
											<div class="form-group">
												<input type="text" class="form-control" required="" value="<?php echo $settings['server_name']; ?>" autocomplete="off" id="server_name" name="server_name" placeholder="Server Name">
											</div>
										</div>
										<div class="col-md-12">
											<label>Logo Pequena</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo $settings['server_logo_small']; ?>" autocomplete="off" id="server_logo_small" name="server_logo_small" placeholder="https://i.imgur.com/image.png">
											</div>
										</div>
										<div class="col-md-12">
											<label>Logo Grande</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo $settings['server_logo_big']; ?>" autocomplete="off" id="server_logo_big" name="server_logo_big" placeholder="https://i.imgur.com/image.png">
											</div>
										</div>
										<div class="col-md-12">
											<label>Login Backgound</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo $settings['login_background']; ?>" autocomplete="off" id="login_background" name="login_background" placeholder="https://i.imgur.com/image.png">
											</div>
										</div>
										<div class="col-md-12">
											<label>Domnio da lista m3u</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo $settings['custom_dns']; ?>" autocomplete="off" id="custom_dns" name="custom_dns" placeholder="http://server.xyz:80">
												<span>Deixe em Branco para manter o padrão do Main Server</span>
											</div>
										</div>
										<div class="col-md-12">
											<label>Telegram de suporte</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo $settings['telegram']; ?>" autocomplete="off" id="telegram" name="telegram" placeholder="https://t.me/hostmk2">
												<span>Deixe em Branco para Ocultar</span>
											</div>
										
										</div>
										</div>
										<div class="col-md-12">
											<label>link loja de aplicativos</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo $settings['whatsapp']; ?>" autocomplete="off" id="whatsapp" name="whatsapp" placeholder="https://api.whatsapp.com/send?phone=5561996098925&text=Olá">
												<span>Deixe em Branco para Ocultar</span>
											</div>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_geral" class="btn btn-primary">Salvar</button>
								</div>
							</form>
							<!-- Clipboard -->
							<script src="/bower_components/clipboard.min.js"></script>
							<script>
								new ClipboardJS(".copy_panel_uuid");
							</script>
						<?php } elseif ($_GET['page'] == "informations") { ?>
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Informaçes</h3>
								</div>
								<div class="card-body pad">
									<p class="text-sm mb-0">
										Escreva informaçes importantes e úteis para serem exibidas no painel
									</p>
									<br>
									<div class="mb-3">
										<textarea class="textarea" id="fixed_informations" name="fixed_informations" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;">
										<?php echo $fixed_informations; ?>
										</textarea>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_info" class="btn btn-primary">Salvar informaçes</button>
								</div>
							</form>
						<?php } elseif ($_GET['page'] == "allowed_groups") { ?>
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Configurações de grupos de Acesso</h3>
								</div>
								<div class="card-body pad">
									<div class="col-md-6">
										<label>Selecione o grupo dos Administradores</label>
										<div class="form-group">
											<?php

											$grupos = json_decode($settings['group_settings'], true);
											?>
											<select class="select2" id="admin_group" name="admin_group" style="width: 100%;">
												<?php

												foreach (getAllGroups() as $group) {
													if ($group['group_id'] == $grupos['admin']) {
														echo '<option value="';
														echo $group['group_id'];
														echo '" selected>';
														echo $group['group_name'];
														echo '</option>';
													} else {
														echo '<option value="';
														echo $group['group_id'];
														echo '">';
														echo $group['group_name'];
														echo '</option>';
													}
												} ?>
											</select>
										</div>
									</div>
									<div class="col-md-6">
										<label>Selecione o grupo dos Fraqueados</label>
										<div class="form-group">
											<?php
											?>
											<select class="select2" id="partner_group" name="partner_group" style="width: 100%;">
												<option value="0" <?php if ($group['group_id'] == $grupos['partner']) {
																						echo "selected";
																					} ?>>Desativado</option>
												<?php

												foreach (getAllGroups() as $group) {
													if ($group['group_id'] == $grupos['partner']) {
														echo '<option value="';
														echo $group['group_id'];
														echo '" selected>';
														echo $group['group_name'];
														echo '</option>';
													} else {
														echo '<option value="';
														echo $group['group_id'];
														echo '">';
														echo $group['group_name'];
														echo '</option>';
													}
												} ?>
											</select>
										</div>
									</div>
									<div class="col-md-6">
										<label>Selecione o grupo dos Ultra</label>
										<div class="form-group">
											<select class="select2" id="ultra_group" name="ultra_group" style="width: 100%;">
												<?php

												foreach (getAllGroups() as $group) {
													if ($group['group_id'] == $grupos['ultra']) {
														echo '<option value="';
														echo $group['group_id'];
														echo '" selected>';
														echo $group['group_name'];
														echo '</option>';
													} else {
														echo '<option value="';
														echo $group['group_id'];
														echo '">';
														echo $group['group_name'];
														echo '</option>';
													}
												} ?>
											</select>
										</div>
									</div>
									<div class="col-md-6">
										<label>Selecione o grupo dos Master</label>
										<div class="form-group">
											<select class="select2" id="master_group" name="master_group" style="width: 100%;">
												<?php

												foreach (getAllGroups() as $group) {
													if ($group['group_id'] == $grupos['master']) {
														echo '<option value="';
														echo $group['group_id'];
														echo '" selected>';
														echo $group['group_name'];
														echo '</option>';
													} else {
														echo '<option value="';
														echo $group['group_id'];
														echo '">';
														echo $group['group_name'];
														echo '</option>';
													}
												} ?>
											</select>
										</div>
									</div>
									<div class="col-md-6">
										<label>Selecione o grupo dos Revendedores comuns</label>
										<div class="form-group">
											<select class="select2" id="reseller_group" name="reseller_group" style="width: 100%;">
												<?php

												foreach (getAllGroups() as $group) {
													if ($group['group_id'] == $grupos['reseller']) {
														echo '<option value="';
														echo $group['group_id'];
														echo '" selected>';
														echo $group['group_name'];
														echo '</option>';
													} else {
														echo '<option value="';
														echo $group['group_id'];
														echo '">';
														echo $group['group_name'];
														echo '</option>';
													}
												} ?>
											</select>
										</div>
									</div>
									<div class="col-md-6 pt-5">
										<label>Grupos com permissão para acessar o Office</label>
										<div class="form-group">
											<select class="select2" multiple="multiple" id="allowed_groups" name="allowed_groups[]" data-placeholder="Selecione os Grupos" style="width: 100%;">
												<?php
												foreach (getAllGroups() as $group) {
													if (in_array($group['group_id'], $allowed_groups)) { ?>
														<option value="<?php echo $group['group_id']; ?>" selected><?php echo $group['group_name']; ?></option>
													<?php } else { ?>
														<option value="<?php echo $group['group_id']; ?>"><?php echo $group['group_name']; ?></option>
												<?php }
												} ?>
											</select>
										</div>
									</div>
									<div class="col-md-6 pt-5">
										<label>Páginas permitidas Franqueado</label>
										<div class="form-group">
											<select class="select2" multiple="multiple" id="partner_allowed_pages" name="partner_allowed_pages[]" data-placeholder="Selecione os Grupos" style="width: 100%;">
												<?php
												foreach ($nav_itens as $key => $value) {
													if (in_array($value, $partner_allowed_pages)) { ?>
														<option value="<?php echo $value; ?>" selected><?php echo $key; ?></option>
													<?php } else { ?>
														<option value="<?php echo $value; ?>"><?php echo $key; ?></option>
												<?php }
												} ?>
											</select>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_allowed_groups" class="btn btn-primary">Salvar</button>
								</div>
							</form>
						<?php } elseif ($_GET['page'] == "allowed_bouquets") { ?>
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Buques Permitidos</h3>
								</div>
								<div class="card-body pad">
									<p class="text-sm mb-0">
										Selecione os buquês permitidos para o painel office.
									</p>
									<br>
									<div class="col-md-6">
										<div class="form-group">
											<select class="select2" multiple="multiple" id="allowed_bouquets" name="allowed_bouquets[]" data-placeholder="Selecione os Buquês" style="width: 100%;">
												<?php
												foreach (getBouquets() as $bouquet) {
													if (in_array($bouquet['id'], $allowed_bouquets)) { ?>
														<option value="<?php echo $bouquet['id']; ?>" selected><?php echo $bouquet['bouquet_name']; ?></option>
													<?php } else { ?>
														<option value="<?php echo $bouquet['id']; ?>"><?php echo $bouquet['bouquet_name']; ?></option>
												<?php }
												} ?>
											</select>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_allowed_bouquets" class="btn btn-primary">Salvar</button>
								</div>
							</form>
						<?php } elseif ($_GET['page'] == "fast_test_template") { ?>
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Template mensagem rápida</h3>
								</div>
								<div class="card-body pad">
									<div class="accordion" id="accordionExample">
										<button class="btn btn-block btn-secondary text-left mb-3 templates" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
											<h6> Template mensagem rápida IPTV</h6>
											<i class="fal fa-arrow-alt-from-top"></i>
										</button>
										<div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
											<div class="row col-lg-12">
												<div class="col-lg-6" id="div-iptv">
													<div class="input-group mb-3">
														<textarea class="form-control" id="template_iptv" name="template_iptv" style="width: 100%; min-height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" placeholder=""><?php echo getServerProperty('default_test_template_iptv', "", true); ?></textarea>
													</div>
												</div>
												<div class="col-lg-6 callout callout-info">
													<h5>Variáveis para Substituição <i class="fad fa-question-circle text-teal" data-toggle="tooltip" data-original-title="Clique na variável para inserir no template rapidamente"></i></h5>
													<p>Use as variáveis abaixo para inserir as informaçes como usuário e senha ao seu template<br><strong>Dica:</strong> Clique na variável para inserir no template</p>
													<table class="table table-bordered table-sm">
														<thead>
															<tr>
																<!--th style="width: 10px">#</th-->
																<th class="text-center">Variável</th>
																<th class="text-center">Informaço inserida</th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#username#</strong></td>
																<td class="text-center">Usuário do cliente criado</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#password#</strong></td>
																<td class="text-center">Senha do cliente criado</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#m3u_link#</strong></td>
																<td class="text-center">Link m3u encurtado</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#m3u_link_hls#</strong></td>
																<td class="text-center">Link m3u HLS completo</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#m3u_link_mpegts#</strong></td>
																<td class="text-center">Link m3u MPEGTS completo</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#ssiptv_link#</strong></td>
																<td class="text-center">Link SSIPTV encurtado</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#exp_info#</strong></td>
																<td class="text-center">Informação de vencimento. Ex: expira amanhã</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#exp_date#</strong></td>
																<td class="text-center">Informação de vencimento.Ex: <?php echo date("d/m/Y", strtotime("+5 day")) ?></td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#server_name#</strong></td>
																<td class="text-center">Nome do servidor. (<b><?php echo $server_name ?></b>)</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#whatsapp#</strong></td>
																<td class="text-center">Seu WhatsApp. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#telegram#</strong></td>
																<td class="text-center">Seu Telegram. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
															</tr>
														</tbody>
													</table>
												</div>
											</div>
										</div>
										<button class="btn btn-block btn-secondary text-left mb-3 templates" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
											<h6>Template mensagem rpida P2P BinStream</h6>
											<i class="fal fa-arrow-alt-from-top"></i>
										</button>
										<div id="collapseTwo" class="collapse" aria-labelledby="collapseTwo" data-parent="#accordionExample">
											<div class="row col-lg-12">
												<div class="col-lg-6" id="div-binstream">
													<div class="input-group mb-3">
														<textarea class="form-control" id="template_p2p" name="template_p2p" style="width: 100%; min-height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" placeholder=""><?php echo getServerProperty('default_test_template_p2p', "", true); ?></textarea>
													</div>
												</div>
												<div class="col-lg-6 callout callout-info">
													<h5>Variáveis para Substituição <i class="fad fa-question-circle text-teal" data-toggle="tooltip" data-original-title="Clique na variável para inserir no template rapidamente"></i></h5>
													<p>Use as variáveis abaixo para inserir as informaçes como usurio e senha ao seu template</p>
													<table class="table table-bordered table-sm">
														<thead>
															<tr>
																<th class="text-center">Variável</th>
																<th class="text-center">Informação inserida</th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_p2p',this)">#username#</strong></td>
																<td class="text-center">Usuário do cliente criado</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_p2p',this)">#password#</strong></td>
																<td class="text-center">Senha do cliente criado</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_p2p',this)">#exp_info#</strong></td>
																<td class="text-center">Informação de vencimento. Ex: expira amanhã</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_p2p',this)">#exp_date#</strong></td>
																<td class="text-center">Informação de vencimento.Ex: <?php echo date("d/m/Y", strtotime("+5 day")) ?></td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_p2p',this)">#server_name#</strong></td>
																<td class="text-center">Nome do servidor. (<b><?php echo $server_name ?></b>)</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_p2p',this)">#whatsapp#</strong></td>
																<td class="text-center">Seu WhatsApp. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_p2p',this)">#telegram#</strong></td>
																<td class="text-center">Seu Telegram. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
															</tr>
														</tbody>
													</table>
												</div>
											</div>
										</div>
										<button class="btn btn-block btn-secondary text-left mb-3 templates" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="true" aria-controls="collapseThree">
											<h6>Template mensagem rápida Cdigo IPTV</h6>
											<i class="fal fa-arrow-alt-from-top"></i>
										</button>
										<div id="collapseThree" class="collapse" aria-labelledby="collapseThree" data-parent="#accordionExample">
											<div class="row col-lg-12">
												<div class="col-lg-6" id="div-code">
													<div class="input-group mb-3">
														<textarea class="form-control" id="template_code" name="template_code" style="width: 100%; min-height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" placeholder=""><?php echo getServerProperty('default_test_template_code', "", true); ?></textarea>
													</div>
												</div>
												<div class="col-lg-6 callout callout-info">
													<h5>Variáveis para Substituiço <i class="fad fa-question-circle text-teal" data-toggle="tooltip" data-original-title="Clique na variável para inserir no template rapidamente"></i></h5>
													<p>Use as variveis abaixo para inserir as informações como usuário e senha ao seu template</p>
													<table class="table table-bordered table-sm">
														<thead>
															<tr>
																<th class="text-center">Variável</th>
																<th class="text-center">Informação inserida</th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_code',this)">#username#</strong></td>
																<td class="text-center">Usuário do cliente criado</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_code',this)">#exp_info#</strong></td>
																<td class="text-center">Informação de vencimento. Ex: expira amanhã</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_code',this)">#exp_date#</strong></td>
																<td class="text-center">Informação de vencimento.Ex: <?php echo date("d/m/Y", strtotime("+5 day")) ?></td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_code',this)">#server_name#</strong></td>
																<td class="text-center">Nome do servidor. (<b><?php echo $server_name ?></b>)</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_code',this)">#whatsapp#</strong></td>
																<td class="text-center">Seu WhatsApp. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_code',this)">#telegram#</strong></td>
																<td class="text-center">Seu Telegram. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
															</tr>
														</tbody>
													</table>
												</div>
											</div>
										</div>
										<button class="btn btn-block btn-secondary text-left mb-3 templates" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="true" aria-controls="collapseFour">
											<h6>Template mensagem Expiração</h6>
											<i class="fal fa-arrow-alt-from-top"></i>
										</button>
										<div id="collapseFour" class="collapse" aria-labelledby="collapseFour" data-parent="#accordionExample">
											<div class="row col-lg-12" id="div-expiration">
												<div class="col-lg-6">
													<div class="input-group mb-3">
														<textarea class="form-control" id="template_exp_mesage" name="template_exp_mesage" style="width: 100%; min-height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" placeholder=""><?php echo getServerProperty('default_expiring_template', "", true); ?></textarea>
													</div>
												</div>
												<div class="col-lg-6 callout callout-info">
													<h5>Variáveis para Substituição <i class="fad fa-question-circle text-teal" data-toggle="tooltip" data-original-title="Clique na variável para inserir no template rapidamente"></i></h5>
													<p>Use as variáveis abaixo para inserir as informações como usuário e senha ao seu template</p>
													<table class="table table-bordered table-sm">
														<thead>
															<tr>
																<th class="text-center">Variável</th>
																<th class="text-center">Informaão inserida</th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_exp_mesage',this)">#username#</strong></td>
																<td class="text-center">Usuário do cliente criado</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_exp_mesage',this)">#exp_info#</strong></td>
																<td class="text-center">Informaço de vencimento. Ex: expira amanhã</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_exp_mesage',this)">#exp_date#</strong></td>
																<td class="text-center">Informação de vencimento.Ex: <?php echo date("d/m/Y", strtotime("+5 day")) ?></td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_exp_mesage',this)">#server_name#</strong></td>
																<td class="text-center">Nome do servidor. (<b><?php echo $server_name ?></b>)</td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_exp_mesage',this)">#whatsapp#</strong></td>
																<td class="text-center">Seu WhatsApp. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
															</tr>
															<tr>
																<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_exp_mesage',this)">#telegram#</strong></td>
																<td class="text-center">Seu Telegram. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
															</tr>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_test_default_template" class="btn btn-primary">Salvar informações</button>
								</div>
							</form>
							<script>
								window.onload = function() {
									$('[data-toggle="tooltip"]').tooltip();
								}

								function pasteVar(textarea, element) {
									// Obtenha o texto predefinido
									var texto = element.textContent;

									// Obtenha a textarea
									var textarea = document.getElementById(textarea);

									// Obtenha a posição do cursor na textarea
									var posicaoCursor = textarea.selectionStart;

									// Obtenha o texto antes e depois da posição do cursor
									var textoAntes = textarea.value.substring(0, posicaoCursor);
									var textoDepois = textarea.value.substring(posicaoCursor, textarea.value.length);

									// Concatene o texto antes, o texto predefinido e o texto depois
									var novoTexto = textoAntes + texto + textoDepois;

									// Insira o novo texto na textarea
									textarea.value = novoTexto;

									// Reposicione o cursor para após o texto predefinido
									var novaPosicaoCursor = posicaoCursor + texto.length;
									textarea.setSelectionRange(novaPosicaoCursor, novaPosicaoCursor);

									// Defina o foco na textarea
									textarea.focus();
								}
							</script>
						<?php } elseif ($_GET['page'] == "fast_test_sidebar") { ?>
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Teste Rápido</h3>
								</div>
								<div class="card-body pad">
									<div class="callout callout-info">
										<p><i class="fal fa-info pr-2"></i>Defina os pacotes do teste da Sidebar <i>(Menu Lateral)</i></p>
									</div>
									<div class="row col-lg-6 col-md-12">
										<h4>IPTV</h4>
										<div class="col-12">
											<label>Selecione os pacotes para criação de teste <i>IPTV</i>.</label>
											<div class="form-group">
												<select class="select2" multiple="multiple" id="fast_packages" name="fast_packages[]" data-placeholder="Selecione os Pacotes" style="width: 100%;">
													<?php
													foreach (getPackages() as $package) {
														if ($package['is_trial']) {
															if (in_array($package['id'], $fast_packages)) { ?>
																<option value="<?php echo $package['id']; ?>" selected><?php echo $package['package_name']; ?></option>
															<?php } else { ?>
																<option value="<?php echo $package['id']; ?>"><?php echo $package['package_name']; ?></option>
													<?php }
														}
													} ?>
												</select>
											</div>
											<label>Defina o tempo em horas do teste customizado.</label>
											<div class="form-group">
												<input type="number" class="form-control" required="" value="<?php echo getServerProperty('test_time', 1, true); ?>" data-minlength="0" minlength="0" autocomplete="off" id="test_time" name="test_time">
											</div>

											<label>Defina o mínimo de créditos para a criaão de testes.</label>
											<div class="form-group">
												<input type="number" class="form-control" required="" value="<?php echo getServerProperty('test_min_credits', 0, true); ?>" data-minlength="0" minlength="0" autocomplete="off" id="min_credits" name="min_credits">
											</div>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_fast_test_sidebar" class="btn btn-primary">Salvar</button>
								</div>
							</form>
						<?php } elseif ($_GET['page'] == "fast_test_top_button") { ?>
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Teste Rápido</h3>
								</div>
								<div class="card-body pad">
									<div class="row col-lg-6 col-md-12">
										<div class="col-12 callout callout-info">
											<p><i class="fal fa-info pr-2"></i> Defina o pacote padrão do <b>Teste Rápido</b></p>
										</div>
										<h4>IPTV</h4>
										<div class="col-12">
											<label>Selecione o pacote padrão para o gerador de teste <i>IPTV</i>.</label>
											<div class="form-group">
												<select class="select2" id="fast_test_package" name="fast_test_package" data-placeholder="Selecione os Pacotes" style="width: 100%;">
													<?php
													foreach (getPackages() as $package) {
														if ($package['is_trial']) {
															if ($package['id'] == getServerProperty("fast_test_package", "", true)) { ?>
																<option value="<?php echo $package['id']; ?>" selected><?php echo $package['package_name']; ?></option>
															<?php } else { ?>
																<option value="<?php echo $package['id']; ?>"><?php echo $package['package_name']; ?></option>
													<?php }
														}
													} ?>
												</select>
											</div>
										</div>
										<?php if (binStreamEnabled(true)['success']) { ?>
											<hr>
											<h4>P2P BinStream</h4>
											<div class="col-12">
												<label>Selecione o pacote padrão para o gerador de teste <i>P2P</i>.</label>
												<div class="form-group">
													<select name="binstream_fast_test_package" id="binstream_fast_test_package" class="select2" required="" data-placeholder="Selecione o Pacote" style="width: 100%;">
														<?php
														include_once(__DIR__ . "/sys/class/binstream.php");

														$binstream = new BinStream();
														$packages = $binstream->getPackages();
														foreach ($binstream_allowed_packages as $package_id) {
															$package_key = array_search($package_id, array_column($packages, 'id'));
															if ($package_key !== false) {
																$current_package = $packages[$package_key];
														?>
																<option <?php echo getServerProperty("binstream_fast_test_package", "", true) == $current_package['id'] ? "selected" : ""; ?> value="<?php echo $current_package['id']; ?>"><?php echo $current_package['name']; ?></option>
														<?php
															}
														} ?>
													</select>
												</div>
											</div>
										<?php } ?>
										<h4>Código IPTV</h4>
										<div class="col-12">
											<label>Selecione o pacote padrão para o gerador de teste de <i>Código IPTV</i>.</label>
											<div class="form-group">
												<select class="select2" id="code_fast_test_package" name="code_fast_test_package" data-placeholder="Selecione os Pacotes" style="width: 100%;">
													<?php
													foreach (getPackages() as $package) {
														if ($package['is_trial']) {
															if ($package['id'] == getServerProperty("code_fast_test_package", "", true)) { ?>
																<option value="<?php echo $package['id']; ?>" selected><?php echo $package['package_name']; ?></option>
															<?php } else { ?>
																<option value="<?php echo $package['id']; ?>"><?php echo $package['package_name']; ?></option>
													<?php }
														}
													} ?>
												</select>
											</div>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_dash_fast_test" class="btn btn-primary">Salvar</button>
								</div>
							</form>
						<?php } elseif ($_GET['page'] == "geradorteste") { ?>
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Gerador Teste</h3>
									<div class="card-tools">
										<input type="checkbox" name="automatic_test" <?php if (getServerProperty('automatic_test', 0, true)) {
																																		echo 'checked';
																																	} ?> data-bootstrap-switch data-off-color="danger" data-on-color="success">
									</div>
								</div>
								<div class="card-body pad">
									<div class="row col-lg-6 col-md-12">
										<h4>IPTV</h4>
										<div class="col-12">
											<label>Selecione os pacotes para o gerador de teste automático.</label>
											<div class="form-group">
												<select class="select2" multiple="multiple" id="automatic_test_packages" name="automatic_test_packages[]" data-placeholder="Selecione os Pacotes" style="width: 100%;">
													<?php
													foreach (getPackages() as $package) {
														if ($package['is_trial']) {
															if (in_array($package['id'], $automatic_test_packages)) { ?>
																<option value="<?php echo $package['id']; ?>" selected><?php echo $package['package_name']; ?></option>
															<?php } else { ?>
																<option value="<?php echo $package['id']; ?>"><?php echo $package['package_name']; ?></option>
													<?php }
														}
													} ?>
												</select>
											</div>
										</div>
										<h4>P2P BinStream</h4>
										<div class="col-12">
											<label>Selecione os pacotes para o gerador de teste automático.</label>
											<div class="form-group">
												<select class="select2" multiple="multiple" id="binstream_automatic_test_packages" name="binstream_automatic_test_packages[]" data-placeholder="Selecione os Pacotes" style="width: 100%;">
													<?php
													if (binStreamEnabled(true)['success'] === true) {
														include_once(__DIR__ . "/sys/class/binstream.php");
														$binstream = new Binstream();
														foreach ($binstream->getPackages() as $package) {
															if (in_array($package['id'], $binstream_allowed_packages)) {
																if (in_array($package['id'], $binstream_automatic_test_packages)) { ?>
																	<option value="<?php echo $package['id']; ?>" selected><?php echo $package['name']; ?></option>
																<?php } else { ?>
																	<option value="<?php echo $package['id']; ?>"><?php echo $package['name']; ?></option>
													<?php }
															}
														}
													} ?>
												</select>
											</div>
										</div>
										<h4>Código IPTV</h4>
										<div class="col-12">
											<label>Selecione os pacotes para o gerador de teste automático.</label>
											<div class="form-group">
												<select class="select2" multiple="multiple" id="code_automatic_test_packages" name="code_automatic_test_packages[]" data-placeholder="Selecione os Pacotes" style="width: 100%;">
													<?php
													foreach (getPackages() as $package) {
														if ($package['is_trial']) {
															if (in_array($package['id'], $code_automatic_test_packages)) { ?>
																<option value="<?php echo $package['id']; ?>" selected><?php echo $package['package_name']; ?></option>
															<?php } else { ?>
																<option value="<?php echo $package['id']; ?>"><?php echo $package['package_name']; ?></option>
													<?php }
														}
													} ?>
												</select>
											</div>
										</div>
									</div>
									<h4>Configurações Gerais</h4>
									<div class="col-md-6">
										<label>Defina o minimo de créditos para a utilização do gerador de teste automático.</label>
										<div class="form-group">
											<input type="number" class="form-control" required="" value="<?php echo getServerProperty('test_min_credits', 0); ?>" data-minlength="0" minlength="0" autocomplete="off" id="automatic_test_min_credits" name="automatic_test_min_credits">
										</div>
									</div>
									<label>Selecione dias para deixar o gerador de teste desativado.</label>
									<div class="input-group col-md-6">
										<div class="input-group-prepend">
											<span class="input-group-text">
												<i class="far fa-calendar-alt"></i>
											</span>
										</div>
										<input type="text" class="form-control float-right" id="datepicker" name="disabled_days_automatic_test" value="<?php echo getServerProperty('disabled_days_automatic_test', ''); ?>">
									</div>
									<br>
									<div class="input-group col-md-6">
										<div class="form-group">
											<div class="custom-control custom-checkbox">
												<input class="custom-control-input" name="random_name_automatic_test" type="checkbox" id="random_name_automatic_test" <?php if (getServerProperty('random_name_automatic_test', 0)) {
																																																																								echo 'checked';
																																																																							} ?>>
												<label for="random_name_automatic_test" class="custom-control-label">Gerar nome de usuário aleatório.</label>
											</div>
										</div>
									</div>
									<div class="input-group col-md-6">
										<div class="form-group">
											<div class="custom-control custom-checkbox">
												<input class="custom-control-input" name="only_valid_emails_automatic_test" type="checkbox" id="only_valid_emails_automatic_test" <?php if (getServerProperty('only_valid_emails_automatic_test', 0)) {
																																																																														echo 'checked';
																																																																													} ?>>
												<label for="only_valid_emails_automatic_test" class="custom-control-label">Permitir apenas e-mails válidos.</label>
											</div>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_automatic_test" class="btn btn-primary">Salvar</button>
								</div>
							</form>
						<?php } elseif ($_GET['page'] == "email_config") { ?>
							<?php $email_settings = json_decode($settings['email_settings'], true); ?>
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Configurações de E-mail</h3>
								</div>
								<div class="card-body pad">
									<div class="row">
										<div class="col-md-6">
											<label>Nome do remetente</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo $email_settings['sender_name']; ?>" data-minlength="1" minlength="1" autocomplete="off" id="sender_name" name="sender_name" placeholder="Nome do remetente">
											</div>
										</div>
										<div class="col-md-6">
											<label>E-mail do remetente</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo $email_settings['sender_email']; ?>" data-minlength="4" minlength="4" autocomplete="off" id="sender_email" name="sender_email" placeholder="E-mail do remetente">
											</div>
										</div>
										<div class="col-md-12">
											<label>Mtodo de Envio</label>
											<div class="form-group">
												<div class="custom-control custom-radio">
													<input class="custom-control-input" type="radio" id="direto" name="use_smtp" value="0" <?php if ($email_settings['use_smtp'] == 0) {
																																																										echo 'checked';
																																																									} ?>>
													<label for="direto" class="custom-control-label">Direto do Servidor</label>
												</div>
												<div class="custom-control custom-radio">
													<input class="custom-control-input" type="radio" id="smtpserver" name="use_smtp" value="1" <?php if ($email_settings['use_smtp'] == 1) {
																																																												echo 'checked';
																																																											} ?>>
													<label for="smtpserver" class="custom-control-label">STMP Server</label>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<label>SMTP Server</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo $email_settings['smtp_server']; ?>" data-minlength="0" minlength="0" maxlength="255" autocomplete="off" id="smtp_server" name="smtp_server" placeholder="SMTP Server">
											</div>
										</div>
										<div class="col-md-6">
											<label>SMTP Port</label>
											<div class="form-group">
												<input type="number" class="form-control" value="<?php echo $email_settings['smtp_port']; ?>" data-minlength="0" minlength="1" maxlength="5" autocomplete="off" id="smtp_port" name="smtp_port" placeholder="465">
											</div>
										</div>
										<div class="col-md-6">
											<label>Usurio SMTP</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo $email_settings['smtp_username']; ?>" data-minlength="0" minlength="0" maxlength="100" autocomplete="off" id="smtp_username" name="smtp_username" placeholder="SMTP Username">
											</div>
										</div>
										<div class="col-md-6">
											<label>Senha SMTP</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo $email_settings['smtp_password']; ?>" data-minlength="0" minlength="0" maxlength="100" autocomplete="off" id="smtp_password" name="smtp_password" placeholder="SMTP Password">
											</div>
										</div>
										<div class="col-md-6">
											<label>Método de Segurança</label>
											<div class="form-group">
												<div class="custom-control custom-radio">
													<input class="custom-control-input" type="radio" id="nenhum" name="encryption_type" value="" <?php if ($email_settings['encryption_type'] === '') {
																																																													echo 'checked';
																																																												} ?>>
													<label for="nenhum" class="custom-control-label">Nenhum</label>
												</div>
												<div class="custom-control custom-radio">
													<input class="custom-control-input" type="radio" id="tls" name="encryption_type" value="TLS" <?php if ($email_settings['encryption_type'] === 'TLS') {
																																																													echo 'checked';
																																																												} ?>>
													<label for="tls" class="custom-control-label">TLS</label>
												</div>
												<div class="custom-control custom-radio">
													<input class="custom-control-input" type="radio" id="ssl" name="encryption_type" value="SSL" <?php if ($email_settings['encryption_type'] === 'SSL') {
																																																													echo 'checked';
																																																												} ?>>
													<label for="ssl" class="custom-control-label">SSL</label>
												</div>

											</div>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_email_settings" class="btn btn-primary">Salvar</button>
								</div>
							</form>
						<?php } elseif ($_GET['page'] == "email_template") { ?>
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Templates de E-mail</h3>
								</div>
								<div class="card-body pad">
									<div class="row">
										<div class="col-lg-6 col-md-12">
											<div class="callout callout-info">
												<div class="col-md-12">
													<h5>Template de teste automático <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#modal-var-auto-testuser">Ver variáveis disponveis</button></h5>
													<label>Assunto</label>
													<div class="form-group">
														<input type="text" class="form-control" value="<?php echo $email_messages['auto_test_subject']; ?>" data-minlength="1" minlength="1" autocomplete="off" id="auto_test_subject" name="auto_test_subject" placeholder="Assunto do e-mail">
													</div>
												</div>
												<div class="col-md-12 mb-3">
													<label>Mensagem de teste automático </label>
													<textarea class="textarea" id="auto_test_message" name="auto_test_message" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;">
														<?php echo $email_messages['auto_test_message']; ?>
													</textarea>
												</div>
											</div>
										</div>
										<div class="col-lg-6 col-md-12">
											<div class="callout callout-info">
												<div class="col-md-12">
													<h5>Template de teste automático (Código) <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#modal-var-auto-testcode">Ver variáveis disponíveis</button></h5>
													<label>Assunto</label>
													<div class="form-group">
														<input type="text" class="form-control" value="<?php echo $email_messages['auto_test_subject_code']; ?>" data-minlength="1" minlength="1" autocomplete="off" id="auto_test_subject_code" name="auto_test_subject_code" placeholder="Assunto do e-mail">
													</div>
												</div>
												<div class="col-md-12 mb-3">
													<label>Mensagem de teste automático</label>
													<textarea class="textarea" id="auto_test_message_code" name="auto_test_message_code" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;">
														<?php echo $email_messages['auto_test_message_code']; ?>
													</textarea>
												</div>
											</div>
										</div>
										<div class="col-lg-6 col-md-12">
											<div class="callout callout-info">
												<div class="col-md-12">
													<h5>Template de recuperação de senha <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#modal-var-reset">Ver variveis disponíveis</button></h5>
													<label>Assunto</label>
													<div class="form-group">
														<input type="text" class="form-control" value="<?php echo $email_messages['pass_recovery_subject']; ?>" data-minlength="1" minlength="1" autocomplete="off" id="pass_recovery_subject" name="pass_recovery_subject" placeholder="Assunto do e-mail">
													</div>
												</div>
												<div class="col-md-12 mb-3">
													<label>Mensagem de teste automático</label>
													<textarea class="textarea" id="pass_recovery_message" name="pass_recovery_message" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;">
														<?php echo $email_messages['pass_recovery_message']; ?>
													</textarea>
												</div>
											</div>
										</div>
										<div class="col-lg-6 col-md-12">
											<div class="callout callout-info">
												<div class="col-md-12">
													<h5>Template de clientes próximo do vencimento <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#modal-var-expiring-message">Ver variáveis disponíveis</button></h5>
													<label>Assunto</label>
													<div class="form-group">
														<input type="text" class="form-control" value="<?php echo $email_messages['expiring_subject']; ?>" data-minlength="1" minlength="1" autocomplete="off" id="expiring_subject" name="expiring_subject" placeholder="Assunto do e-mail">
													</div>
												</div>
												<div class="col-md-12 mb-3">
													<label>Mensagem de teste automático</label>
													<textarea class="textarea" id="expiring_message" name="expiring_message" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;">
														<?php echo $email_messages['expiring_message']; ?>
													</textarea>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_email_messages" class="btn btn-primary">Salvar</button>
								</div>
							</form>
							<div class="modal fade" id="modal-var-auto-testuser">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<h5 class="modal-title">Variáveis para Substituição</h5>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
										<div class="modal-body">
											<p>Use as variáveis abaixo para inserir as informações como usuário e senha do teste gerado</p><br>
											<table class="table table-bordered">
												<thead>
													<tr>
														<!--th style="width: 10px">#</th-->
														<th>Variável</th>
														<th>Informação inserida</th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td><strong>#username#</strong></td>
														<td>Usuário do teste criado</td>
													</tr>
													<tr>
														<td><strong>#password#</strong></td>
														<td>Senha do teste criado</td>
													</tr>
													<tr>
														<td><strong>#m3u_link#</strong></td>
														<td>Link m3u do teste</td>
													</tr>
													<tr>
														<td><strong>#ssiptv_link#</strong></td>
														<td>Link SSIPTV do teste</td>
													</tr>
													<tr>
														<td><strong>#duration#</strong></td>
														<td>Tempo de duração do teste</td>
													</tr>
													<tr>
														<td><strong>#server_name#</strong></td>
														<td>Nome do servidor</td>
													</tr>
													<tr>
														<td><strong>#reseller_email#</strong></td>
														<td>E-mail do revendedor</td>
													</tr>
													<tr>
														<td><strong>#whatsapp#</strong></td>
														<td>WhatsApp do revendedor</td>
													</tr>
													<tr>
														<td><strong>#telegram#</strong></td>
														<td>Telegram do revendedor</td>
													</tr>
												</tbody>
											</table>
										</div>
										<div class="modal-footer justify-content-between">
											<button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button>
										</div>
									</div>
									<!-- /.modal-content -->
								</div>
								<!-- /.modal-dialog -->
							</div>
							<div class="modal fade" id="modal-var-auto-testcode">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<h5 class="modal-title">Variáveis para Substituição</h5>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
										<div class="modal-body">
											<p>Use as variáveis abaixo para inserir as informações como código do teste gerado</p><br>
											<table class="table table-bordered">
												<thead>
													<tr>
														<!--th style="width: 10px">#</th-->
														<th>Variável</th>
														<th>Informação inserida</th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td><strong>#usercode#</strong></td>
														<td>Código do teste criado</td>
													</tr>
													<tr>
														<td><strong>#duration#</strong></td>
														<td>Tempo de duração do teste</td>
													</tr>
													<tr>
														<td><strong>#server_name#</strong></td>
														<td>Nome do servidor</td>
													</tr>
													<tr>
														<td><strong>#reseller_email#</strong></td>
														<td>E-mail do revendedor</td>
													</tr>
													<tr>
														<td><strong>#whatsapp#</strong></td>
														<td>WhatsApp do revendedor</td>
													</tr>
													<tr>
														<td><strong>#telegram#</strong></td>
														<td>Telegram do revendedor</td>
													</tr>
												</tbody>
											</table>
										</div>
										<div class="modal-footer justify-content-between">
											<button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button>
										</div>
									</div>
									<!-- /.modal-content -->
								</div>
								<!-- /.modal-dialog -->
							</div>
							<div class="modal fade" id="modal-var-reset">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<h5 class="modal-title">Variáveis para Substituião</h5>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
										<div class="modal-body">
											<p>Use as variáveis abaixo para inserir as informaçes como usuário do revendedor</p><br>
											<table class="table table-bordered">
												<thead>
													<tr>
														<!--th style="width: 10px">#</th-->
														<th>Variável</th>
														<th>Informação inserida</th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td><strong>#username#</strong></td>
														<td>Código do teste criado</td>
													</tr>
													<tr>
														<td><strong>#server_name#</strong></td>
														<td>Nome do servidor</td>
													</tr>
													<tr>
														<td><strong>#reset_link#</strong></td>
														<td>Link para resetar a senha</td>
													</tr>
												</tbody>
											</table>
										</div>
										<div class="modal-footer justify-content-between">
											<button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button>
										</div>
									</div>
									<!-- /.modal-content -->
								</div>
								<!-- /.modal-dialog -->
							</div>
							<div class="modal fade" id="modal-var-expiring-message">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<h5 class="modal-title">Variáveis para Substituião</h5>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
										<div class="modal-body">
											<p>Use as variveis abaixo para inserir as informações como usuário que está próximo do vencimento</p><br>
											<table class="table table-bordered">
												<thead>
													<tr>
														<!--th style="width: 10px">#</th-->
														<th>Variável</th>
														<th>Informaão inserida</th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td><strong>#username#</strong></td>
														<td>Usuário a vencer</td>
													</tr>
													<tr>
														<td><strong>#exp_date#</strong></td>
														<td>Data que o cliente expira (dd/mm/aaaa)</td>
													</tr>
													<tr>
														<td><strong>#exp_info#</strong></td>
														<td>Informação de quando a linha expira, exemplos:<br> expira em 3 dias<br> expira em 1 dia<br> expirou a 3 dias</td>
													</tr>
													<tr>
														<td><strong>#client_email#</strong></td>
														<td>E-mail do cliente</td>
													</tr>
													<tr>
														<td><strong>#reseller_email#</strong></td>
														<td>E-mail do revendedor</td>
													</tr>
													<tr>
														<td><strong>#whatsapp#</strong></td>
														<td>WhatsApp do revendedor</td>
													</tr>
													<tr>
														<td><strong>#telegram#</strong></td>
														<td>Telegram do revendedor</td>
													</tr>
													<tr>
														<td><strong>#server_name#</strong></td>
														<td>Nome do servidor</td>
													</tr>
												</tbody>
											</table>
										</div>
										<div class="modal-footer justify-content-between">
											<button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button>
										</div>
									</div>
									<!-- /.modal-content -->
								</div>
								<!-- /.modal-dialog -->
							</div>
						<?php } elseif ($_GET['page'] == "clients") { ?>
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Clientes</h3>
								</div>
								<div class="card-body pad">
									<div class="row col-lg-6 col-md-12">
										<h4>🔸IPTV</h4>
										<div class="col-12">
											<label>Geração de Usuário/Senha <i>IPTV</i></label>
											<div class="form-group">
												<?php
												$iptv_code_characters = getServerProperty('iptv_code_characters', 0, true);
												$binstream_user_char = getServerProperty('binstream_user_char', 0, true);
												$code_user_char = getServerProperty('code_user_char', 0, true);
												?>
												<select class="custom-select" id="iptv_code_characters" name="iptv_code_characters" style="width: 100%;">
													<option value="1" <?php if ($iptv_code_characters == "1") {
																							echo "selected";
																						} ?>>Apenas Números</option>
													<option value="1a" <?php if ($iptv_code_characters == "1a") {
																								echo "selected";
																							} ?>>Números e Letras Minsculas</option>
													<option value="1aA" <?php if ($iptv_code_characters == "1aA") {
																								echo "selected";
																							} ?>>Números, Letras Minúsculas e Maisculas</option>
													<option value="1aA-" <?php if ($iptv_code_characters == "1aA-") {
																									echo "selected";
																								} ?>>Nmeros, Letras Minsculas, Maisculas e Simbolos</option>
												</select>
											</div>
											<label>Tamanho do Usurio/Senha</label>
											<div class="form-group">
												<input type="number" class="form-control" required="" value="<?php echo getServerProperty('iptv_code_size', 0, true); ?>" data-minlength="0" minlength="0" autocomplete="off" id="iptv_code_size" name="iptv_code_size">
											</div>
											<label>Tempo do teste personalizado. <small>(Em Horas)</small></label>
											<div class="form-group">
												<select class="form-control select2" multiple="multiple" id="test_time_custom" name="test_time_custom[]" style="width: 100%;">
													<?php
													foreach ($test_time_custom as $time) { ?>
														<option value="<?php echo $time; ?>" <?php if (in_array($time, $test_time_custom)) {
																																		echo "selected";
																																	} ?>><?php echo $time; ?></option>
													<?php } ?>
												</select>
												<p><small class="text-muted">Multiplos valores são aceitos! Digite os períodos desejados</small></p>

											</div>
											<div id="iptv_trust_renew">
												<label>Renovação de confiança</label>
												<div class="form-group">
													<div class="custom-control custom-switch">
														<input type="checkbox" class="custom-control-input" id="iptv_trust_renew_status" name="iptv_trust_renew_status" <?php if (getServerProperty('iptv_trust_renew_status', 0, true)) {
																																																																							echo "checked";
																																																																						} ?>>
														<label class="custom-control-label" for="iptv_trust_renew_status">Habilitar</label>
													</div>
												</div>
												<div class="form-group iptvtrustrenewtime">
													<label>Defina o tempo da Renovação de confiança</label>
													<div class="form-group">
														<div class="input-group col-lg-4 col-md-4 col-sm-6 pl-0">
															<input type="number" class="form-control" required="" value="<?php echo getServerProperty('iptv_trust_renew_time', 3, true); ?>" data-minlength="1" minlength="1" autocomplete="off" id="iptv_trust_renew_time" name="iptv_trust_renew_time">
															<div class="input-group-append">
																<span class="input-group-text">dias</span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div id="iptv_migration">
												<label>Migração de clientes</label>
												<div class="form-group">
													<div class="custom-control custom-switch">
														<input type="checkbox" class="custom-control-input" id="iptv_migration_status" name="iptv_migration_status" <?php if (getServerProperty('iptv_migration_status', 1, true)) {
																																																																					echo "checked";
																																																																				} ?>>
														<label class="custom-control-label" for="iptv_migration_status">Habilitar</label>
													</div>
												</div>
												<div class="form-group iptvmigrationfee">
													<label>Cobrar pela migração? <small></small></label>
													<div class="custom-control custom-switch">
														<input type="checkbox" class="custom-control-input" id="iptv_migration_fee" name="iptv_migration_fee" <?php if (getServerProperty('iptv_migration_fee', 1, true)) {
																																																																		echo "checked";
																																																																	} ?>>
														<label class="custom-control-label" for="iptv_migration_fee">Habilitar</label>
													</div>
													<p><small class="text-muted">Se ativado será cobrado o valor proporcional aos dias restantes até o vencimento.</small></p>
												</div>
											</div>
											<div id="iptv_conns_limit">
												<label>Limitar mximo de conexões</label>
												<div class="form-group">
													<div class="custom-control custom-switch">
														<input type="checkbox" class="custom-control-input" id="iptv_max_connections_status" name="iptv_max_connections_status" <?php if (getServerProperty('iptv_max_connections_status', 0, true)) {
																																																																											echo "checked";
																																																																										} ?>>
														<label class="custom-control-label" for="iptv_max_connections_status">Habilitar</label>
													</div>
												</div>
												<div class="form-group iptvmaxconnections">
													<label>Defina o máximo de conexes</label>
													<div class="form-group">
														<div class="input-group col-lg-4 col-md-4 col-sm-6 pl-0">
															<input type="number" class="form-control" required="" value="<?php echo getServerProperty('iptv_max_connections', 3, true); ?>" data-minlength="1" minlength="1" autocomplete="off" id="iptv_max_connections" name="iptv_max_connections">
															<div class="input-group-append">
																<span class="input-group-text">Conexões</span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<label>Exibir "Clientes Online"</label>
											<div class="form-group">
												<div class="custom-control custom-switch">
													<input type="checkbox" class="custom-control-input" id="iptv_show_online_clients" name="iptv_show_online_clients" <?php if (getServerProperty('iptv_show_online_clients', 1, true)) {
																																																																							echo "checked";
																																																																						} ?>>
													<label class="custom-control-label" for="iptv_show_online_clients">Habilitar</label>
												</div>
											</div>
											<label>Exibir Boto "Gerar Link" na listagem de Clientes</label>
											<div class="form-group">
												<div class="custom-control custom-switch">
													<input type="checkbox" class="custom-control-input" id="iptv_show_m3u_link" name="iptv_show_m3u_link" <?php if (getServerProperty('iptv_show_m3u_link', 1, true)) {
																																																																	echo "checked";
																																																																} ?>>
													<label class="custom-control-label" for="iptv_show_m3u_link">Habilitar</label>
												</div>
											</div>
										</div>
										<hr>
										<h4>P2P BinStream</h4>
										<div class="col-12">
											<label>Geração de Usuário/Senha <i>P2P BinStream</i></label>
											<div class="form-group">
												<select class="custom-select" id="binstream_user_char" name="binstream_user_char" style="width: 100%;">
													<option value="1" <?php if ($binstream_user_char == "1") {
																							echo "selected";
																						} ?>>Apenas Números</option>
													<option value="1a" <?php if ($binstream_user_char == "1a") {
																								echo "selected";
																							} ?>>Números e Letras Minúsculas</option>
													<option value="1aA" <?php if ($binstream_user_char == "1aA") {
																								echo "selected";
																							} ?>>Números, Letras Minúsculas e Maiúsculas</option>
													<option value="1aA-" <?php if ($binstream_user_char == "1aA-") {
																									echo "selected";
																								} ?>>Nmeros, Letras Minúsculas, Maisculas e Simbolos</option>
												</select>
											</div>
											<label>Tamanho do Usuário/Senha</label>
											<div class="form-group">
												<input type="number" class="form-control" required="" value="<?php echo getServerProperty('binstream_user_length', 0, true); ?>" data-minlength="0" minlength="0" autocomplete="off" id="binstream_user_length" name="binstream_user_length">
											</div>
											<div id="binstream_trust_renew">
												<label>Renovação de confiança</label>
												<div class="form-group">
													<div class="custom-control custom-switch">
														<input type="checkbox" class="custom-control-input" id="binstream_trust_renew_status" name="binstream_trust_renew_status" <?php if (getServerProperty('binstream_trust_renew_status', 0, true)) {
																																																																												echo "checked";
																																																																											} ?>>
														<label class="custom-control-label" for="binstream_trust_renew_status">Habilitar</label>
													</div>
												</div>
												<div class="form-group binstreamtrustrenewtime">
													<label>Defina o tempo da Renovação de confiança</label>
													<div class="form-group">
														<div class="input-group col-lg-4 col-md-4 col-sm-6 pl-0">
															<input type="number" class="form-control" required="" value="<?php echo getServerProperty('binstream_trust_renew_time', 3, true); ?>" data-minlength="1" minlength="1" autocomplete="off" id="binstream_trust_renew_time" name="binstream_trust_renew_time">
															<div class="input-group-append">
																<span class="input-group-text">dias</span>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<hr>
										<h4>🔸Cdigo P2P</h4>
										<div class="col-12" id="code_div">
											<label>Códigos</label>
											<div class="form-group">
												<div class="custom-control custom-switch">
													<input type="checkbox" class="custom-control-input" id="code_status" name="code_status" <?php if (getServerProperty('code_status', 1, true)) {
																																																										echo "checked";
																																																									} ?>>
													<label class="custom-control-label" for="code_status">Habilitar</label>
												</div>
												<p><small class="text-muted">Se desativado, os clientes de código já exitentes seram exibidos como IPTV</small></p>

											</div>
											<div id="code_options">
												<label>Geração de Código</label>
												<div class="form-group">
													<select class="custom-select" id="code_user_char" name="code_user_char" style="width: 100%;">
														<option value="1" <?php if ($code_user_char == "1") {
																								echo "selected";
																							} ?>>Apenas Números</option>
														<option value="1a" <?php if ($code_user_char == "1a") {
																									echo "selected";
																								} ?>>Números e Letras Minsculas</option>
														<option value="1aA" <?php if ($code_user_char == "1aA") {
																									echo "selected";
																								} ?>>Nmeros, Letras Minúsculas e Maiúsculas</option>
														<option value="1aA-" <?php if ($code_user_char == "1aA-") {
																										echo "selected";
																									} ?>>Números, Letras Minúsculas, Maiúsculas e Simbolos</option>
													</select>
												</div>
												<label>Tamanho do Usuário/Senha</label>
												<div class="form-group">
													<input type="number" class="form-control" required="" value="<?php echo getServerProperty('code_user_length', 0, true); ?>" data-minlength="0" minlength="0" autocomplete="off" id="code_user_length" name="code_user_length">
												</div>
												<label>Senha Padro do Aplicativo</label>
												<div class="form-group">
													<input type="text" class="form-control" required="" value="<?php echo getServerProperty('code_default_pass', "VeryStonksP2P", true); ?>" data-minlength="6" minlength="6" autocomplete="off" id="code_default_pass" name="code_default_pass">
												</div>
												<div id="code_conns_limit">
													<label>Limitar máximo de conexões</label>
													<div class="form-group">
														<div class="custom-control custom-switch">
															<input type="checkbox" class="custom-control-input" id="code_max_connections_status" name="code_max_connections_status" <?php if (getServerProperty('code_max_connections_status', 0, true)) {
																																																																												echo "checked";
																																																																											} ?>>
															<label class="custom-control-label" for="code_max_connections_status">Habilitar</label>
														</div>
													</div>
													<div class="form-group codemaxconnections">
														<label>Defina o máximo de conexões</label>
														<div class="form-group">
															<div class="input-group col-lg-4 col-md-4 col-sm-6 pl-0">
																<input type="number" class="form-control" required="" value="<?php echo getServerProperty('code_max_connections', 3, true); ?>" data-minlength="1" minlength="1" autocomplete="off" id="code_max_connections" name="code_max_connections">
																<div class="input-group-append">
																	<span class="input-group-text">Conexões</span>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_clients" class="btn btn-primary">Salvar</button>
								</div>
							</form>
							<script>
								$(document).on('keypress', '.select2-search__field', function() {
									$(this).val($(this).val().replace(/[^\d].+/, ""));
									if ((event.which < 48 || event.which > 57)) {
										event.preventDefault();
									}
								});

								$(document).ready(function() {
									$('#iptv_migration_status').change(function() {
										if ($(this).is(':checked')) {
											$('.iptvmigrationfee').show();
										} else {
											$('.iptvmigrationfee').hide();
										}
									});

									$('#iptv_trust_renew_status').change(function() {
										if ($(this).is(':checked')) {
											$('.iptvtrustrenewtime').show();
										} else {
											$('.iptvtrustrenewtime').hide();
										}
									});

									$('#iptv_max_connections_status').change(function() {
										if ($(this).is(':checked')) {
											$('.iptvmaxconnections').show();
										} else {
											$('.iptvmaxconnections').hide();
										}
									});

									$('#binstream_trust_renew_status').change(function() {
										if ($(this).is(':checked')) {
											$('.binstreamtrustrenewtime').show();
										} else {
											$('.binstreamtrustrenewtime').hide();
										}
									});

									$('#code_status').change(function() {
										if ($(this).is(':checked')) {
											$('#code_options').show();
										} else {
											$('#code_options').hide();
										}
									});

									$('#code_max_connections_status').change(function() {
										if ($(this).is(':checked')) {
											$('.codemaxconnections').show();
										} else {
											$('.codemaxconnections').hide();
										}
									});
								});

								<?php if (!getServerProperty('iptv_migration_status', 0, true)) { ?>
									$('.iptvmigrationfee').hide();
								<?php }
								if (!getServerProperty('iptv_trust_renew_status', 0, true)) { ?>
									$('.iptvtrustrenewtime').hide();
								<?php }
								if (!getServerProperty('iptv_max_connections_status', 0, true)) { ?>
									$('.iptvmaxconnections').hide();
								<?php }
								if (!getServerProperty('binstream_trust_renew_status', 0, true)) { ?>
									$('.binstreamtrustrenewtime').hide();
								<?php }
								if (!getServerProperty('code_status', 0, true)) { ?>
									$('#code_options').hide();
								<?php }
								if (!getServerProperty('code_max_connections_status', 0, true)) { ?>
									$('.codemaxconnections').hide();
								<?php } ?>
							</script>
						<?php } elseif ($_GET['page'] == "p2p_binstream") { ?>
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Binstream</h3>
								</div>
								<div class="card-body pad">
									<div class="row">
										<div class="col-lg-6 col-md-12">
											<label>Binstream</label>
											<div class="form-group">
												<div class="custom-control custom-switch">
													<input type="checkbox" class="custom-control-input" id="binstream_status" name="binstream_status" <?php if (OFFICE_CONFIG['binstream']['enabled']) {
																																																															echo "checked";
																																																														} ?>>
													<label class="custom-control-label" for="binstream_status">Habilitar</label>
												</div>
											</div>
											<label>API URL</label>
											<div class="form-group">
												<input type="text" class="form-control" required="" value="<?php echo OFFICE_CONFIG['binstream']['url']; ?>" data-minlength="0" minlength="0" autocomplete="off" id="bin_api_url" name="bin_api_url" placeholder="https://api1.hostmk.com.br/api/v1in/">
											</div>
											<label>Domnio</label>
											<div class="form-group">
												<input type="text" class="form-control" required="" value="<?php echo OFFICE_CONFIG['binstream']['email']; ?>" data-minlength="0" minlength="0" autocomplete="off" id="bin_domain" name="bin_domain" placeholder="hostmk.com.br">
											</div>
											<label>Token</label>
											<?php
											if (empty(OFFICE_CONFIG['binstream']['token'])) {
												$placeholder = '&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;';
												$required = 'required';
											} else {
												$placeholder = 'J preenchido, deixe em branco para manter a senha atual';
												$required = '';
											}
											?>
											<div class="form-group">
												<input type="text" class="form-control" <?php echo $required ?> value="" data-minlength="0" minlength="0" autocomplete="off" id="bin_api_token" name="bin_api_token" placeholder="<?php echo $placeholder ?>">
											</div>
											<div class="form-group">
												<label>Pacotes Permitidos Binstream</label>
												<select class="select2" multiple="multiple" id="binstream_allowed_packages" name="binstream_allowed_packages[]" data-placeholder="Selecione os Pacotes" style="width: 100%;">
													<?php
													if (checkBinStreamConfig(OFFICE_CONFIG['binstream']['url'], OFFICE_CONFIG['binstream']['token'])['success'] === true) {
														include_once(__DIR__ . "/sys/class/binstream.php");
														$binstream = new Binstream();
														foreach ($binstream->getPackages() as $package) {
															if (in_array($package['id'], $binstream_allowed_packages)) { ?>
																<option value="<?php echo $package['id']; ?>" selected><?php echo $package['name']; ?></option>
															<?php } else { ?>
																<option value="<?php echo $package['id']; ?>"><?php echo $package['name']; ?></option>
													<?php }
														}
													} ?>
												</select>
											</div>
											<label>Defina o tempo do teste customizado.</label>
											<div class="form-group">
												<div class="input-group col-lg-4 col-md-4 col-sm-6 pl-0">
													<input type="number" class="form-control" required="" value="<?php echo getServerProperty('binstream_test_time', 4, true); ?>" data-minlength="1" minlength="1" autocomplete="off" id="binstream_test_time" name="binstream_test_time">
													<div class="input-group-append">
														<span class="input-group-text">horas</span>
													</div>
												</div>
											</div>
											<label>Listagem dos Clientes</label>
											<div class="form-group">
												<select class="custom-select" id="binstream_show_clients" name="binstream_show_clients" style="width: 100%;">
													<option value="1" <?php if ($binstream_show_clients == "by_uuid") {
																							echo "selected";
																						} ?>>Listar apenas clientes criados nesse painel</option>
													<option value="1a" <?php if ($binstream_show_clients == "all") {
																								echo "selected";
																							} ?>>Listar todos os clientes</option>
												</select>
											</div>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_p2p_binstream" class="btn btn-primary">Salvar</button>
								</div>
							</form>
						<?php } elseif ($_GET['page'] == "ssiptv") { ?>
							<form autocomplete="off" action="#" method="post">
								<?php

								?>
								<div class="card-header">
									<h3 class="card-title">SSIPTV <i>(Gerador de Lista)</i></h3>
								</div>
								<div class="card-body pad">
									<div class="row">
										<div class="col-md-6">
											<h4>Tipo da lista</h4>
											<input type="checkbox" name="ssiptv_output" <?php if (OFFICE_CONFIG['ssiptv']['output'] == 'mpegts') {
																																		echo 'checked';
																																	} ?> data-bootstrap-switch data-on-color="success" data-off-color="info" data-on-text="MPEGTS" data-off-text="HLS">

											<br>
											<hr>
											<h4>Canais</h4>

											<div class="form-group">
												<div class="custom-control custom-switch">
													<input type="checkbox" class="custom-control-input" id="ssiptv_live_enabled" name="ssiptv_live_enabled" <?php if (OFFICE_CONFIG['ssiptv']['live']['enabled']) {
																																																																		echo "checked";
																																																																	} ?>>
													<label class="custom-control-label" for="ssiptv_live_enabled">Habilitar</label>
												</div>
											</div>
											<label>Nome</label>
											<div class="form-group">
												<input type="text" class="form-control" id="ssiptv_live_name" name="ssiptv_live_name" value="<?php echo OFFICE_CONFIG['ssiptv']['live']['name']; ?>" data-minlength="0" minlength="0" autocomplete="off" placeholder="Canais">
											</div>
											<label>Imagem</label>
											<div class="form-group">
												<input type="text" class="form-control" id="ssiptv_live_image" name="ssiptv_live_image" value="<?php echo OFFICE_CONFIG['ssiptv']['live']['image']; ?>" data-minlength="0" minlength="0" autocomplete="off" placeholder="https://i.imgur.com/image.png">
											</div>
											<hr>
											<h4>Filmes</h4>
											<div class="col-md-12">
												<div class="form-group">
													<div class="custom-control custom-switch">
														<input type="checkbox" class="custom-control-input" id="ssiptv_movie_enabled" name="ssiptv_movie_enabled" <?php if (OFFICE_CONFIG['ssiptv']['movie']['enabled']) {
																																																																				echo "checked";
																																																																			} ?>>
														<label class="custom-control-label" for="ssiptv_movie_enabled">Habilitar</label>
													</div>
												</div>
											</div>
											<label>Nome</label>
											<div class="form-group">
												<input type="text" class="form-control" required="" value="<?php echo OFFICE_CONFIG['ssiptv']['movie']['name']; ?>" data-minlength="0" minlength="0" autocomplete="off" id="ssiptv_movie_name" name="ssiptv_movie_name" placeholder="Filmes">
											</div>
											<label>Imagem</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo OFFICE_CONFIG['ssiptv']['movie']['image']; ?>" data-minlength="0" minlength="0" autocomplete="off" id="ssiptv_movie_image" name="ssiptv_movie_image" placeholder="https://i.imgur.com/image.png">
											</div>
											<hr>
											<h4>Sries</h4>
											<div class="form-group">
												<div class="custom-control custom-switch">
													<input type="checkbox" class="custom-control-input" id="ssiptv_serie_enabled" name="ssiptv_serie_enabled" <?php if (OFFICE_CONFIG['ssiptv']['serie']['enabled']) {
																																																																			echo "checked";
																																																																		} ?>>
													<label class="custom-control-label" for="ssiptv_serie_enabled">Habilitar</label>
												</div>
											</div>
											<label>Nome</label>
											<div class="form-group">
												<input type="text" class="form-control" required="" value="<?php echo OFFICE_CONFIG['ssiptv']['serie']['name']; ?>" data-minlength="0" minlength="0" autocomplete="off" id="ssiptv_serie_name" name="ssiptv_serie_name" placeholder="Séries">
											</div>
											<label>Imagem</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo OFFICE_CONFIG['ssiptv']['serie']['image']; ?>" data-minlength="0" minlength="0" autocomplete="off" id="ssiptv_serie_image" name="ssiptv_serie_image" placeholder="https://i.imgur.com/image.png">
											</div>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_ssiptv" class="btn btn-primary">Salvar</button>
								</div>
							</form>
						<?php } elseif ($_GET['page'] == "tools") { ?>
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Transferir revendedores</h3>
								</div>
								<div class="card-body pad">
									<div class="col-md-6">
										<label>Selecione os revendedores que de deseja transferir</label>
										<div class="form-group">
											<select class="select2" multiple="multiple" id="selected_resellers" name="selected_resellers[]" data-placeholder="Selecione os revendedores" style="width: 100%;">
												<?php
												$all_users = getAllUsers();
												foreach ($all_users as $user) {
													$owner_name = '-';
													if ($user['owner_id']) {
														$user_key = array_search($user['owner_id'], array_column($all_users, 'id'));
														if ($user_key !== false) {
															$owner_name = $all_users[$user_key]['username'];
														}
													} ?>
													<option value="<?php echo $user['id']; ?>"><?php echo $user['username'] . ' (' . $owner_name . ')'; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="col-md-6">
										<label>Selecione o novo dono</label>
										<div class="form-group">
											<select class="select2" id="new_owner" name="new_owner" data-placeholder="Selecione o novo dono" style="width: 100%;">
												<?php
												$all_users = getAllUsers();
												foreach ($all_users as $user) { ?>
													<option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="col-md-6">
										<label>Selecione o novo Grupo</label>
										<div class="form-group">
											<select class="select2" id="new_group_name" name="new_group_name" data-placeholder="Selecione o novo Grupo" style="width: 100%;">
												<?php
												$group_settings = json_decode(getServerProperty('group_settings', "", true), true);
												$ultra_group = getGroupByID($group_settings['ultra']);
												$master_group = getGroupByID($group_settings['master']);
												$reseller_group = getGroupByID($group_settings['reseller']);
												?>
												<option value="" selected>Não Alterar Grupo</option>
												<option value="ultra"><?php echo $ultra_group['group_name']; ?></option>
												<option value="master"><?php echo $master_group['group_name']; ?></option>
												<option value="reseller"><?php echo $reseller_group['group_name']; ?></option>
											</select>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="change_resellers" class="btn btn-primary">Salvar</button>
								</div>
							</form>
							<!-- <div class="card card-default">
								<div class="card-header">
									<h3 class="card-title">Atualizar Tabelas</h3>
								</div>
								<form autocomplete="off" action="#" method="post">
									<div class="card-body pad">
										<p class="text-sm mb-0">
											Clique no botão abaixo caso estejam ocorrendo erros na criação ou listagem de clientes.
										</p>
										<br>
									</div>
									<div class="card-footer">
										<button type="submit" name="update_tables" class="btn btn-warning">Atualizar Tabelas</button>
									</div>
								</form>
							</div> -->
						<?php } elseif ($_GET['page'] == "captcha") { ?>
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Configuraões reCAPTCHA</h3>
									<div class="card-tools">
										<input type="checkbox" name="recaptcha_enable" <?php if (getServerProperty('recaptcha_enable', 0, true)) {
																																			echo 'checked';
																																		} ?> data-bootstrap-switch data-off-color="danger" data-on-color="success">
									</div>
								</div>
								<div class="card-body pad">
									<div class="col-md-6">
										<label>Chave de Site</label>
										<div class="form-group">
											<input type="text" class="form-control" value="<?php echo getServerProperty('recaptcha_site_key', 0, true); ?>" data-minlength="0" minlength="0" autocomplete="off" id="recaptcha_site_key" name="recaptcha_site_key">
										</div>
									</div>
									<div class="col-md-6">
										<label>Chave Secreta</label>
										<div class="form-group">
											<input type="text" class="form-control" value="<?php echo getServerProperty('recaptcha_secret_key', 0, true); ?>" data-minlength="0" minlength="0" autocomplete="off" id="recaptcha_secret_key" name="recaptcha_secret_key">
										</div>
									</div>
									<div class="col-md-6">
										<p><strong>Acesse <a href="https://www.google.com/recaptcha/admin" target="_blank">https://www.google.com/recaptcha/admin</a> para obter esses dados.</strong></p>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_recaptcha" class="btn btn-primary">Salvar</button>
								</div>
							</form>
						<?php } elseif ($_GET['page'] == "maintenance") {
							$maintenance = json_decode(getServerProperty('maintenance', '{"status":0,"message":"","button_text":"","button_link":""}', true), true);
						?>
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Manutenão</h3>
								</div>
								<div class="card-body pad">
									<div class="callout callout-info">
										<p><i class="fal fa-info pr-2"></i>Ao ativar o modo de manutenço a mensagem definida abaixo exibida quando os revendedores e clientes tentarem fazer login no painel</p>
									</div>
									<div class="row col-lg-6 col-md-12">
										<div class="col-md-12">
											<label>Modo Manutenço</label>
											<div class="form-group">
												<div class="custom-control custom-switch">
													<input type="checkbox" class="custom-control-input" id="maintenance_status" name="maintenance_status" <?php if ($maintenance['status']) {
																																																																	echo "checked";
																																																																} ?>>
													<label class="custom-control-label" for="maintenance_status">Habilitar</label>
												</div>
											</div>
										</div>
										<div class="col-md-12">
											<label>Mensagem</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo $maintenance['message']; ?>" autocomplete="off" id="maintenance_message" name="maintenance_message" placeholder="Estamos passando por uma manutenção!">
											</div>
										</div>
										<div class="col-md-12">
											<label>Texto botão</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo $maintenance['button_text']; ?>" autocomplete="off" id="maintenance_button_text" name="maintenance_button_text" placeholder="OK">
											</div>
										</div>
										<div class="col-md-12">
											<label>Link Botão</label>
											<div class="form-group">
												<input type="text" class="form-control" value="<?php echo $maintenance['button_link']; ?>" autocomplete="off" id="maintenance_button_link" name="maintenance_button_link" placeholder="#">
											</div>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_maintenance" class="btn btn-primary">Salvar</button>
								</div>
							</form>
						<?php } ?>
					</div>
				</div>
			</section>
		</div>
		<?php include_once('footer.php'); ?>
	</div>
	<!-- jQuery -->
	<script src="/plugins/jquery/jquery.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- Select2 -->
	<script src="/plugins/select2/js/select2.full.min.js"></script>
	<!-- InputMask -->
	<script src="/plugins/moment/moment.min.js"></script>
	<script src="/plugins/inputmask/min/jquery.inputmask.bundle.min.js"></script>
	<!-- bootstrap datepicker -->
	<script src="/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.min.js?<?php echo OFFICE_VERSION ?>"></script>
	<!-- Bootstrap Switch -->
	<script src="/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
	<!-- Summernote -->
	<script src="/plugins/summernote/summernote-bs4.min.js"></script>
	<script src="/plugins/summernote/lang/summernote-pt-BR.js"></script>
	<script>
		$(function() {
			//Initialize Bootstrap Datepicker
			$("#datepicker").datepicker({
				multidate: true,
				showOtherMonths: true,
				selectOtherMonths: true
			});

			//Initialize Bootstrap Switch
			$("input[data-bootstrap-switch]").each(function() {
				$(this).bootstrapSwitch('state', $(this).prop('checked'));
			});

			//Initialize Select2 Elements
			$('.select2').select2()
			$("#test_time_custom").select2({
				tags: true,
				// tokenSeparators: [',', ' ']
			})


			//Initialize Summernote
			$('.textarea').summernote({
				lang: 'pt-BR'
			})
		})
	</script>
	<script type="text/javascript">
		$(".alert").delay(3000).slideUp(200, function() {
			$(this).alert('close');
		});

		$('.use_smtp').on('ifChecked', function(event) {
			if ($('.use_smtp:checked').val() === '0') {
				$(".smtp_form :input").attr("readonly", true);
			} else {
				$(".smtp_form :input").removeAttr("readonly");
			}
		});
	</script>
</body>

</html>