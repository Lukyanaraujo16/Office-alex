<?php
if (!isset($_GET['reseller_id'])) {
	header('Location: /resellers');
	exit();
}

include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();

if (!isAdmin($logged_user) && !isPartner($logged_user) && !isUltra($logged_user) && !isMaster($logged_user)) {
	header('Location: ./index.php');
	exit();
}

$server_name = getServerProperty('server_name');
$fast_packages = json_decode(getServerProperty('fast_packages'), true);
$reseller_id = intval($_GET['reseller_id']);
$reseller = getUserByID($reseller_id);

if (!$reseller) {
	exit();
}

if (!masterHasPermission($logged_user['id'], $reseller['id'])) {
	exit();
}

$owner_id = $reseller['owner_id'];
$owner = NULL;

if ($owner_id) {
	$owner = getUserByID($owner_id);
}

if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email']) && isset($_POST['member_group']) && isset($_POST['notes']) && isset($_POST['full_phone']) && isset($_POST['telegram'])) {

	$username = preg_replace('/[^A-Za-z0-9\-]/', '', purifyHTML($_POST['username']));
	$password = preg_replace('/[^A-Za-z0-9\-]/', '', purifyHTML($_POST['password']));
	$email = purifyHTML($_POST['email']);
	$member_group = purifyHTML($_POST['member_group']);
	$notes = purifyHTML($_POST['notes']);
	$whatsapp = purifyHTML($_POST['full_phone']);
	$telegram = purifyHTML($_POST['telegram']);
	$client_price = purifyHTML($_POST['client_price']);
	$code_client_price = purifyHTML($_POST['code_client_price']);
	$binstream_client_price = purifyHTML($_POST['binstream_client_price']);



	$iptv_enabled = hasPermissionResource($logged_user['id'], "iptv") ? (isset($_POST['switchiptv']) ? "enabled" : "disabled") : 0;
	$codes_enabled = hasPermissionResource($logged_user['id'], "codes") ? (isset($_POST['switchcodes']) ? "enabled" : "disabled") : 0;
	$binstream_enabled = hasPermissionResource($logged_user['id'], "binstream") ? (isset($_POST['switchp2p']) ? "enabled" : "disabled") : 0;


	if ((strlen($username) < 6) || (255 < strlen($username))) {
		header('location: ?reseller_id=' . $reseller_id . '&result=invalid_username');
		exit();
	}

	if (!empty($password) && ((strlen($password) < 6) || (255 < strlen($password)))) {
		header('location: ?reseller_id=' . $reseller_id . '&result=invalid_password');
		exit();
	}

	if ((strlen($email) < 6) || (255 < strlen($email))) {
		header('location: ?reseller_id=' . $reseller_id . '&result=invalid_email');
		exit();
	}

	$group_settings = json_decode(getServerProperty('group_settings'), true);
	$logged_user_group = array_search($logged_user['member_group_id'], $group_settings);
	if (($logged_user_group == 'admin') || ($logged_user_group == 'partner') || (($logged_user_group == 'ultra') && (($logged_user_group == 'ultra') || ($member_group == 'master') || ($member_group == 'reseller'))) || (($logged_user_group == 'master') && ($member_group == 'reseller'))) {
		$group_id = (isset($group_settings[$member_group]) ? $group_settings[$member_group] : false);

		if (!$group_id) {
			header('location: ?reseller_id=' . $reseller_id . '&result=error');
			exit();
		}

		if (500 < strlen($notes)) {
			header('location: ?reseller_id=' . $reseller_id . '&result=invalid_notes');
			exit();
		}

		if (updateUser($reseller_id, $username, $password, $email, $group_id, $notes)) {
			$result1 = updateUserProperty($reseller_id, 'whatsapp', $whatsapp);
			$result2 = updateUserProperty($reseller_id, 'telegram', $telegram);
			$result3 = updateUserProperty($reseller_id, 'client_price', trim(str_replace(array("R$", ","), array("", "."), $client_price)));
			$result3 = updateUserProperty($reseller_id, 'code_client_price', trim(str_replace(array("R$", ","), array("", "."), $code_client_price)));
			$result3 = updateUserProperty($reseller_id, 'binstream_client_price', trim(str_replace(array("R$", ","), array("", "."), $binstream_client_price)));
			$result4 = updateUserProperty($reseller_id, 'iptv_enabled', $iptv_enabled);
			$result5 = updateUserProperty($reseller_id, 'codes_enabled', $codes_enabled);
			$result6 = updateUserProperty($reseller_id, 'binstream_enabled', $binstream_enabled);

			if ($result1 && $result2 && $result3 && $result4 && $result5 && $result6) {
				header('location: ' . $reseller_id . '&result=success');
				exit();
			}
		}
	}

	header('location: &result=error');
	exit();
}
$whatsapp = getUserProperty($reseller['id'], 'whatsapp');
$telegram = getUserProperty($reseller['id'], 'telegram');
$client_price = getUserProperty($reseller['id'], 'client_price');
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
							$result_message = 'As alterações foram salvas com sucesso.';
							$result_type = 'success';
							break;
						case 'invalid_username':
							$result_message = 'O nome de usuário escolhido é invalido!, deve ter no mínimo 6 caracteres.';
							break;
						case 'invalid_password':
							$result_message = 'A senha escolhida é invalida!, deve ter no mínimo 6 caracteres.';
							break;
						case 'invalid_email':
							$result_message = 'O e-mail escolhido é invalido!';
							break;
						case 'invalid_notes':
							$result_message = 'A observação escolhida é invalida!, deve ter no máximo 500 caracteres.';
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
							<h1>Editar Revenda</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Editar Revenda</li>
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
							<h3 class="card-title">Dados do Revendedor</h3><br>
						</div>
						<!-- /.card-header -->
						<form autocomplete="off" action="#" method="post" name="frm1">
							<div class="card-body">
								<div class="row">
									<div class="col-lg-6 col-md-12">
										<div class="row">
											<div class="form-group col-md-6">
												<label>Usuário</label>
												<div class="input-group mb-3">
													<div class="input-group-prepend">
														<span class="input-group-text"><i class="fas fa-user"></i></span>
													</div>
													<input type="text" required="" autocomplete="off" name="username" data-minlength="6" minlength="6" class="form-control" placeholder="Usuário" value="<?php echo $reseller['username']; ?>">
												</div>
											</div>
											<div class="form-group col-md-6">
												<label>Senha</label>
												<div class="input-group mb-3">
													<div class="input-group-prepend">
														<span class="input-group-text"><i class="fas fa-lock"></i></span>
													</div>
													<input type="text" autocomplete="off" name="password" data-minlength="6" minlength="6" class="form-control" placeholder="*********">
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
																$selected = (isPartner($reseller) ? 'selected' : '');
																echo '<option value="partner" ' . $selected . ' >' . $partner_group['group_name'] . '</option>';
															}
															$selected = (isUltra($reseller) ? 'selected' : '');
															echo '<option value="ultra" ' . $selected . ' >' . $ultra_group['group_name'] . '</option>';
															$selected = (isMaster($reseller) ? 'selected' : '');
															echo '<option value="master" ' . $selected . ' >' . $master_group['group_name'] . '</option>';
															$selected = (isReseller($reseller) ? 'selected' : '');
															echo '<option value="reseller" ' . $selected . ' >' . $reseller_group['group_name'] . '</option>';
														} else if (isPartner($logged_user)) {
															$selected = (isUltra($reseller) ? 'selected' : '');
															echo '<option value="ultra" ' . $selected . ' >' . $ultra_group['group_name'] . '</option>';
															$selected = (isMaster($reseller) ? 'selected' : '');
															echo '<option value="master" ' . $selected . ' >' . $master_group['group_name'] . '</option>';
															$selected = (isReseller($reseller) ? 'selected' : '');
															echo '<option value="reseller" ' . $selected . ' >' . $reseller_group['group_name'] . '</option>';
														} else if (isUltra($logged_user)) {
															if (!$owner || ($owner && (isAdmin($owner) || isUltra($owner)))) {
																$selected = (isUltra($reseller) ? 'selected' : '');
																echo '<option value="ultra" ' . $selected . ' >' . $ultra_group['group_name'] . '</option>';
																$selected = (isMaster($reseller) ? 'selected' : '');
																echo '<option value="master" ' . $selected . ' >' . $master_group['group_name'] . '</option>';
															}


															$selected = (isReseller($reseller) ? 'selected' : '');
															echo '<option value="reseller" ' . $selected . ' >' . $reseller_group['group_name'] . '</option>';
														} else if (isMaster($logged_user)) {
															$selected = (isReseller($reseller) ? 'selected' : '');
															echo '<option value="reseller" ' . $selected . ' >' . $reseller_group['group_name'] . '</option>';
														};
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
													<input type="email" autocomplete="off" required="" name="email" class="form-control" placeholder="E-mail" value="<?php echo $reseller['email']; ?>">
												</div>
											</div>
											<div class="form-group col-md-4">
												<label>WhatsApp</label>
												<div class="input-group mb-3">
													<input type="tel" autocomplete="off" name="phone_number" id="phone_number" class="form-control">
												</div>
											</div>
											<div class="form-group col-md-4">
												<label>Telegram (Usuário)</label>
												<div class="input-group mb-3">
													<div class="input-group-prepend">
														<span class="input-group-text"><i class="fab fa-telegram-plane"></i></span>
													</div>
													<input type="text" autocomplete="off" name="telegram" class="form-control" placeholder="Telegram" value="<?php echo getUserProperty($reseller_id, 'telegram'); ?>">
												</div>
											</div>
											<div class="form-group col-md-4">
												<label>Créditos</label>
												<div class="input-group mb-3">
													<div class="input-group-prepend">
														<span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
													</div>
													<input type="text" autocomplete="off" readonly="" name="credits" class="form-control" value="<?php echo $reseller['credits'] ?>">
												</div>
											</div>
											<div class="col-12 row">
												<?php if ($permission['iptv']) { ?>
													<div class="form-group col-md-4">
														<label>Preço por Ativo <small>(/)</small></label>
														<div class="input-group mb-3">
															<div class="input-group-prepend">
																<span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
															</div>
															<input type="text" autocomplete="off" name="client_price" class="form-control price_per_active" data-inputmask="'alias': 'currency', 'prefix': 'R$ ', 'radixPoint': ',', 'groupSeparator': '.', 'numericInput': true" inputmode="decimal" value="<?php echo getUserProperty($reseller_id, 'client_price', "", true); ?>">
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
															<input type="text" autocomplete="off" name="code_client_price" class="form-control price_per_active" data-inputmask="'alias': 'currency', 'prefix': 'R$ ', 'radixPoint': ',', 'groupSeparator': '.', 'numericInput': true" inputmode="decimal" value="<?php echo getUserProperty($reseller_id, 'code_client_price', "", true); ?>">
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
															<input type="text" autocomplete="off" name="binstream_client_price" class="form-control price_per_active" data-inputmask="'alias': 'currency', 'prefix': 'R$ ', 'radixPoint': ',', 'groupSeparator': '.', 'numericInput': true" inputmode="decimal" value="<?php echo getUserProperty($reseller_id, 'binstream_client_price', "", true); ?>">
														</div>
													</div>
												<?php } ?>
											</div>
											<div class="col-12 row">
												<?php if ($permission['iptv']) { ?>
													<div class="form-group col-lg-4">
														<div class="custom-control custom-switch">
															<input type="checkbox" class="custom-control-input" id="switchiptv" name="switchiptv" <?php if (hasPermissionResource($reseller_id, "iptv")) {
																																																											echo "checked";
																																																										} ?>>
															<label class="custom-control-label" for="switchiptv">Pode criar CLIENTES</label>
														</div>
													</div>
												<?php }
												if ($permission['codes']) { ?>
													<div class="form-group col-lg-4">
														<div class="custom-control custom-switch">
															<input type="checkbox" class="custom-control-input" id="switchcodes" name="switchcodes" <?php if (hasPermissionResource($reseller_id, "codes")) {
																																																												echo "checked";
																																																											} ?>>
															<label class="custom-control-label" for="switchcodes">Pode criar Códigos</label>
														</div>
													</div>
												<?php }
												if ($permission['binstream']) { ?>
													<div class="form-group col-lg-4">
														<div class="custom-control custom-switch">
															<input type="checkbox" class="custom-control-input" id="switchp2p" name="switchp2p" <?php if (hasPermissionResource($reseller_id, "binstream")) {
																																																										echo "checked";
																																																									} ?>>
															<label class="custom-control-label" for="switchp2p">Pode criar P2P BinStream</label>
														</div>
													</div>
												<?php } ?>
											</div>
											<div class="form-group col-md-12">
												<label>Notas</label>
												<textarea class="form-control" rows="3" name="notes" placeholder="Informaões..."><?php echo $reseller['notes']; ?></textarea>
											</div>

										</div>
									</div>
									<div class="col-lg-6 col-md-12">
										<div class="callout callout-info">
											<dl class="row">
												<dt class="col-sm-6">Criado em</dt>
												<dd class="col-sm-6"><?php echo date('d/m/Y H:i', $reseller['date_registered']); ?></dd>
												<dt class="col-sm-6">Última Recarga</dt>
												<dd class="col-sm-6 stats-last_recharge"><i class="fad fa-spinner fa-spin"></i></dd>
												<dt class="col-sm-6">Clientes Ativos <small>(/)</small></dt>
												<dd class="col-sm-6 stats-active_clients_iptv"><i class="fad fa-spinner fa-spin"></i></dd>
												<dt class="col-sm-6">Clientes Ativos <small>(Código)</small></dt>
												<dd class="col-sm-6 stats-active_clients_code"><i class="fad fa-spinner fa-spin"></i></dd>
												<?php
												if (hasPermissionResource($logged_user['id'], "binstream") && binStreamEnabled() && binStreamEnabled()['success']) { ?>
													<dt class="col-sm-6">Clientes Ativos <small>(BinStream)</small></dt>
													<dd class="col-sm-6 stats-active_clients_binstream"><i class="fad fa-spinner fa-spin"></i></dd>
												<?php } ?>
											</dl>
										</div>
									</div>
								</div>
							</div>
							<div class="card-footer">
								<button type="submit" class="btn btn-primary">Editar Revendedor</button>
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

		getResellerStats()

		function getResellerStats() {
			$.get('/sys/api.php?action=getResellerStats&reseller_id=<?php echo $reseller_id; ?>', function(data) {
				if (data.result == 'success') {
					$('.stats-last_recharge').html(data.last_recharge);
					$('.stats-active_clients_iptv').html(data.active_clients_iptv);
					$('.stats-active_clients_code').html(data.active_clients_code);
					<?php if (hasPermissionResource($logged_user['id'], "binstream") && binStreamEnabled() && binStreamEnabled()['success']) { ?>
						$('.stats-active_clients_binstream').html(data.active_clients_binstream);
					<?php } ?>
				}
			});
		}
	</script>
</body>

</html>