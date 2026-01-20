<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();

if (!isAdmin($logged_user) && !isPartner($logged_user) && !isUltra($logged_user) && !isMaster($logged_user)) {
	header('Location: ./index.php');
	exit();
}

$server_name = getServerProperty('server_name');
$fast_packages = json_decode(getServerProperty('fast_packages'), true);

if (isset($_POST['username']) && isset($_POST['password'])) {
	$username = preg_replace('/[^A-Za-z0-9\-]/', '', purifyHTML($_POST['username']));
	$password = preg_replace('/[^A-Za-z0-9\-]/', '', purifyHTML($_POST['password']));
	$notes = purifyHTML($_POST['notes']);
	$member_group = purifyHTML($_POST['member_group']);
	$email = purifyHTML($_POST['email']);
	$whatsapp = purifyHTML($_POST['full_phone']);
	$telegram = purifyHTML($_POST['telegram']);
	$client_price = purifyHTML($_POST['client_price']);
	$code_client_price = purifyHTML($_POST['code_client_price']);
	$binstream_client_price = purifyHTML($_POST['binstream_client_price']);

	$iptv_enabled = hasPermissionResource($logged_user['id'], "iptv") ? (isset($_POST['switchiptv']) ? "enabled" : "disabled") : 0;
	$codes_enabled = hasPermissionResource($logged_user['id'], "codes") ? (isset($_POST['switchcodes']) ? "enabled" : "disabled") : 0;
	$binstream_enabled = hasPermissionResource($logged_user['id'], "binstream") ? (isset($_POST['switchp2p']) ? "enabled" : "disabled") : 0;

	if (isAdmin($logged_user)) {
		$credits = $_POST['credits'];
	} else {
		$credits = 0;
	}
	if ((strlen($username) < 6) || (255 < strlen($username))) {
		header('location: ?result=invalid_username');
		exit();
	}

	if ((strlen($password) < 6) || (255 < strlen($password))) {
		header('location: ?result=invalid_password');
		exit();
	}

	if (500 < strlen($notes)) {
		header('location: ?result=invalid_notes');
		exit();
	}

	$group_settings = json_decode(getServerProperty('group_settings'), true);
	$logged_user_group = array_search($logged_user['member_group_id'], $group_settings);

	if ($logged_user_group !== false) {
		if (($logged_user_group == 'admin') || ($logged_user_group == 'partner') || (($logged_user_group == 'ultra') && (($logged_user_group == 'ultra') || ($member_group == 'master') || ($member_group == 'reseller'))) || (($logged_user_group == 'master') && ($member_group == 'reseller'))) {
			if (createReseller($logged_user['id'], $username, $password, $credits, $group_settings[$member_group], $email, $notes)) {
				$new_reseller = getUserByUsername($username);

				if ($new_reseller) {
					deleteAllUserProperty($new_reseller['id']);

					$result1 = addUserProperty($new_reseller['id'], 'whatsapp', $whatsapp);
					$result2 = addUserProperty($new_reseller['id'], 'telegram', $telegram);
					$result3 = addUserProperty($new_reseller['id'], 'fast_test_template', "");
					$result4 = addUserProperty($new_reseller['id'], 'client_price', trim(str_replace(array("R$", ","), array("", "."), $client_price)));
					$result4 = addUserProperty($new_reseller['id'], 'code_client_price', trim(str_replace(array("R$", ","), array("", "."), $code_client_price)));
					$result4 = addUserProperty($new_reseller['id'], 'binstream_client_price', trim(str_replace(array("R$", ","), array("", "."), $binstream_client_price)));
					$result5 = addUserProperty($new_reseller['id'], 'iptv_enabled', $iptv_enabled);
					$result6 = addUserProperty($new_reseller['id'], 'binstream_enabled', $binstream_enabled);
					$result7 = addUserProperty($new_reseller['id'], 'codes_enabled', $codes_enabled);
					if ($credits > 0) {
						addUserProperty($new_reseller['id'], "last_recharge", strtotime('now'));
					}

					if ($result1 && $result2 && $result3 && $result4 && $result5 && $result6) {
						header('location: /resellers');
						exit();
					}
					header('location: ?result=failed');
					exit();
				}
			} else {
				header('location: ?result=exist_reseller');
				exit();
			}
		}
		header('location: ?result=failed');
		exit();
	}
}
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
	<!-- overlayScrollbars -->
	<link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
	<link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
	<!-- International Telephone Input -->
	<link rel="stylesheet" href="/plugins/intlTelInput/build/css/intlTelInput.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="/dist/css/adminlte.min.css?<?php echo OFFICE_VERSION ?>">
	<!-- Google Font: Source Sans Pro -->
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed text-sm <?php if (DarkMode()) {
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
						case 'success':
							$result_message = 'O usuário foi criado com sucesso.';
							$result_type = 'success';
							break;
						case 'invalid_username':
							$result_message = 'O nome de usuário escolhido é invalido!, deve ter no mínimo 6 caracteres.';
							break;
						case 'invalid_password':
							$result_message = 'A senha escolhida é invalida!, deve ter no mnimo 6 caracteres.';
							break;
						case 'invalid_notes':
							$result_message = 'A observação escolhida é invalida!, deve ter no máximo 500 caracteres.';
							break;
						case 'exist_reseller':
							$result_message = 'Já existe um usuário com este nome de usuário, escolha outro.';
							break;
					} ?>

					<div class="alert alert-<?php echo $result_type; ?> alert-dismissible">
						<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
						<i class="icon fa fa-check"></i>
						<?php echo $result_message; ?>
					</div>

				<?php } ?>
				<div class="container-fluid">
					<div class="row mb-2">
						<div class="col-sm-6">
							<h1>Criar Revenda</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Criar Revenda</li>
							</ol>
						</div>
					</div>
				</div>
				<!-- /.container-fluid -->
			</section>
			<!-- Main content -->
			<section class="content">
				<div class="container-fluid">
					<!-- SELECT2 EXAMPLE -->
					<div class="card card-default">
						<div class="card-header">
							<h3 class="card-title">Dados do Revendedor</h3>
						</div>
						<!-- /.card-header -->
						<form autocomplete="off" action="#" method="post" name="frm1">
							<div class="card-body">
								<div class="row col-lg-6 col-md-12">
									<div class="form-group col-md-6">
										<label>Usuário</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fas fa-user"></i></span>
											</div>
											<input type="text" required="" autocomplete="off" name="username" data-minlength="6" minlength="6" class="form-control" placeholder="Usuário">
										</div>
									</div>
									<div class="form-group col-md-6">
										<label>Senha</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fas fa-lock"></i></span>
											</div>
											<input type="text" required="" autocomplete="off" name="password" data-minlength="6" minlength="6" class="form-control" placeholder="Senha">
										</div>
									</div>
									<div class="col-sm-6">
										<div class="form-group">
											<label>Grupo do Revendedor</label>
											<select id="member_group" name="member_group" class="form-control select2bs4" style="width: 100%;">
												<?php
												$group_settings = json_decode(getServerProperty('group_settings'), true);
												$partner_group = getGroupByID($group_settings['partner']);
												$ultra_group = getGroupByID($group_settings['ultra']);
												$master_group = getGroupByID($group_settings['master']);
												$reseller_group = getGroupByID($group_settings['reseller']);

												if (isAdmin($logged_user)) {
													if (!$partner_group['group_name'] == "") {
														echo '<option value=\'partner\'>' . $partner_group['group_name'] . '</option>';
													}
													echo '<option value=\'ultra\'>' . $ultra_group['group_name'] . '</option>';
													echo '<option value=\'master\'>' . $master_group['group_name'] . '</option>';
													echo '<option value=\'reseller\'>' . $reseller_group['group_name'] . '</option>';
												} else if (isPartner($logged_user)) {
													echo '<option value=\'ultra\'>' . $ultra_group['group_name'] . '</option>';
													echo '<option value=\'master\'>' . $master_group['group_name'] . '</option>';
													echo '<option value=\'reseller\'>' . $reseller_group['group_name'] . '</option>';
												} else if (isUltra($logged_user)) {
													echo '<option value=\'ultra\'>' . $ultra_group['group_name'] . '</option>';
													echo '<option value=\'master\'>' . $master_group['group_name'] . '</option>';
													echo '<option value=\'reseller\'>' . $reseller_group['group_name'] . '</option>';
												} else if (isMaster($logged_user)) {
													echo '<option value=\'reseller\'>' . $reseller_group['group_name'] . '</option>';
												}
												?>
											</select>
										</div>
									</div>
									<div class="form-group col-md-6">
										<label>E-mail</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fas fa-at"></i></span>
											</div>
											<input type="email" autocomplete="off" value="" required="" name="email" class="form-control" placeholder="E-mail">
										</div>
									</div>
									<div class="form-group col-md-6">
										<label>Telegram (Usuário)</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fab fa-telegram-plane"></i></span>
											</div>
											<input type="text" autocomplete="off" value="" name="telegram" class="form-control" placeholder="Telegram">
										</div>
									</div>
									<div class="form-group col-md-6">
										<label>WhatsApp</label>
										<div class="input-group mb-3">
											<input type="tel" autocomplete="off" name="phone_number" id="phone_number" class="form-control">
										</div>
									</div>
									<?php if (isAdmin($logged_user)) { ?>
										<div class="form-group col-md-6">
											<label>Créditos</label>
											<div class="input-group mb-3">
												<div class="input-group-prepend">
													<span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
												</div>
												<input type="number" autocomplete="off" value="" name="credits" class="form-control" placeholder="Créditos">
											</div>
										</div>
									<?php } ?>
									<div class="row col-12">
										<?php if ($permission['iptv']) { ?>
											<div class="form-group col-md-4">
												<label>Preço por Ativo <small>(/)</small></label>
												<div class="input-group mb-3">
													<div class="input-group-prepend">
														<span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
													</div>
													<input type="text" autocomplete="off" name="client_price" class="form-control price_per_active" data-inputmask="'alias': 'currency', 'prefix': 'R$ ', 'radixPoint': ',', 'groupSeparator': '.', 'numericInput': true" inputmode="decimal">
												</div>
											</div>
										<?php }
										if ($permission['codes']) { ?>
											<div class="form-group col-md-4">
												<label>Preço por Ativo <small>(Códigos)</small></label>
												<div class="input-group mb-3">
													<div class="input-group-prepend">
														<span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
													</div>
													<input type="text" autocomplete="off" name="code_client_price" class="form-control price_per_active" data-inputmask="'alias': 'currency', 'prefix': 'R$ ', 'radixPoint': ',', 'groupSeparator': '.', 'numericInput': true" inputmode="decimal">
												</div>
											</div>
										<?php }
										if ($permission['binstream']) { ?>
											<div class="form-group col-md-4">
												<label>Preço por Ativo <small>(BinStream)</small></label>
												<div class="input-group mb-3">
													<div class="input-group-prepend">
														<span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
													</div>
													<input type="text" autocomplete="off" name="binstream_client_price" class="form-control price_per_active" data-inputmask="'alias': 'currency', 'prefix': 'R$ ', 'radixPoint': ',', 'groupSeparator': '.', 'numericInput': true" inputmode="decimal">
												</div>
											</div>
										<?php } ?>
									</div>
									<div class="row col-12">
										<?php if ($permission['iptv']) { ?>
											<div class="form-group col-md-4">
												<div class="custom-control custom-switch">
													<input type="checkbox" class="custom-control-input" id="switchiptv" name="switchiptv" checked>
													<label class="custom-control-label" for="switchiptv">Pode criar CLIENTES</label>
												</div>
											</div>
										<?php }
										if ($permission['codes']) { ?>
											<div class="form-group  col-md-4">
												<div class="custom-control custom-switch">
													<input type="checkbox" class="custom-control-input" id="switchcodes" name="switchcodes" checked>
													<label class="custom-control-label" for="switchcodes">Pode criar Códigos P2P</label>
												</div>
											</div>
										<?php }
										if ($permission['binstream']) { ?>
											<div class="form-group col-md-4">
												<div class="custom-control custom-switch">
													<input type="checkbox" class="custom-control-input" id="switchp2p" name="switchp2p" checked>
													<label class="custom-control-label" for="switchp2p">Pode criar P2P BinStream</label>
												</div>
											</div>
										<?php } ?>
									</div>
									<div class="form-group col-md-12">
										<label>Notas</label>
										<textarea class="form-control" rows="3" value="" name="notes" placeholder="Informações..."></textarea>
									</div>

								</div>
							</div>
							<div class="card-footer">
								<button type="submit" class="btn btn-primary">Criar Revendedor</button>
							</div>
						</form>
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
	<!-- International Telephone Input -->
	<script src="/plugins/intlTelInput/build/js/intlTelInput.js"></script>
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.min.js?<?php echo OFFICE_VERSION ?>"></script>
	<!-- Page script -->
	<script>
		$(function() {
			//Initialize Select2 Elements
			$('.select2bs4').select2({
				theme: 'bootstrap4'
			})
			//Initialize InputMask Elements
			$('[data-inputmask]').inputmask()
		})

		var input = document.querySelector("#phone_number");
		window.intlTelInput(input, {
			allowDropdown: true,
			// autoHideDialCode: true,
			// autoPlaceholder: "off",
			// dropdownContainer: document.body,
			// excludeCountries: ["us"],
			// formatOnDisplay: true,
			hiddenInput: "full_phone",
			initialCountry: "br",
			// localizedCountries: { 'de': 'Deutschland' },
			// nationalMode: false,
			// onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
			// placeholderNumberType: "MOBILE",
			preferredCountries: ['br', 'us'],
			separateDialCode: false,
			utilsScript: "/plugins/intlTelInput/build/js/utils.js",
		});
	</script>
</body>

</html>