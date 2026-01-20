<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$server_name = getServerProperty('server_name');
$fast_packages = json_decode(getServerProperty('fast_packages'), true);

if (isset($_POST['save_email_settings']) && isset($_POST['encryption_type']) && isset($_POST['sender_name']) && isset($_POST['sender_email']) && isset($_POST['use_smtp']) && isset($_POST['smtp_server']) && isset($_POST['smtp_port']) && isset($_POST['smtp_username']) && isset($_POST['smtp_password'])) {
	$custom_smtp = (isset($_POST['custom_smtp']) ? 1 : 0);
	deleteUserProperty($logged_user["id"], 'custom_smtp');
	$result1 = addUserProperty($logged_user["id"], 'custom_smtp', $custom_smtp);

	$email_settings = $_POST;
	unset($email_settings['save_email_settings']);
	$email_settings = json_encode($email_settings);
	deleteUserProperty($logged_user['id'], 'email_settings');
	$result2 = addUserProperty($logged_user['id'], 'email_settings', $email_settings);

	if ($result1 && $result2) {
		if (!$custom_smtp) {
			header('location: ?email&result=custom_smtp_off');
			exit();
		}
		header('location: ?email&result=email_settings_saved');
		exit();
	}

	header('location: ?email&result=failed');
	exit();
}
$email_settings = json_decode(getUserProperty($logged_user['id'], 'email_settings'), true);

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
						case 'email_settings_saved':
							$result_message = 'Configurações de e-mail salvas com sucesso.';
							$result_type = 'success';
							break;
						case 'custom_smtp_off':
							$result_message = 'SMTP personalizado desativado.';
							$result_type = 'success';
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
							<h1>E-mail</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">E-mail</li>
								<li class="breadcrumb-item active">SMTP</li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
				<div class="container-fluid">
					<div class="card card-default">
						<form autocomplete="off" action="#" method="post">
							<div class="card-header">
								<h3 class="card-title">Configurações SMTP</h3>
							</div>
							<div class="card-body pad">
								<div class="row">
									<div class="col-md-12">
										<div class="form-group">

											<div class="custom-control custom_smtp custom-checkbox">
												<input class="custom-control-input" name="custom_smtp" type="checkbox" id="custom_smtp" <?php if (getUserProperty($logged_user['id'], 'custom_smtp')) {
																																																									echo 'checked';
																																																								} ?>>
												<label for="custom_smtp" class="custom-control-label">Usar SMTP personalizado</label>
											</div>
										</div>
									</div>
									<div class="all-fiels col-12" style="display:none">
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
												<label>Método de Envio</label>
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
												<label>Usuário SMTP</label>
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
								</div>
							</div>
							<div class="card-footer">
								<button type="submit" name="save_email_settings" class="btn btn-primary">Salvar</button>
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
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.min.js?<?php echo OFFICE_VERSION ?>"></script>
	<!-- Page script -->
	<script>
		$("#custom_smtp").on("change", function(e) {
			const $container = $(".all-fiels")

			if ($('#custom_smtp').prop('checked')) {
				$container.show();
			} else {
				$container.hide();
			}
		});
	</script>
	<?php if (getUserProperty($logged_user['id'], 'custom_smtp')) { ?>
		<script>
			const $container = $(".all-fiels")
			$container.show();
		</script>
	<?php } ?>
</body>

</html>