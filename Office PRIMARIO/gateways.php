<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$logged_user_id = intval($_SESSION['__l0gg3d_us3r__']);
$server_name = getServerProperty('server_name');

if (isset($_POST['save_mp_credentials']) && isset($_POST['mercado_pago_public_key']) && isset($_POST['mercado_pago_access_token'])) {

	$mercado_pago = (isset($_POST['mercado_pago']) ? 1 : 0);
	$mercado_pago_public_key = purifyHTML($_POST['mercado_pago_public_key']);
	$mercado_pago_access_token = purifyHTML($_POST['mercado_pago_access_token']);

	if (empty($mercado_pago_public_key) or empty($mercado_pago_access_token)) {
		$mercado_pago = 0;
	}

	deleteUserProperty($logged_user_id, 'mercado_pago');
	deleteUserProperty($logged_user_id, 'mercado_pago_public_key');
	deleteUserProperty($logged_user_id, 'mercado_pago_access_token');

	$result1 = addUserProperty($logged_user_id, 'mercado_pago', $mercado_pago);
	$result2 = addUserProperty($logged_user_id, 'mercado_pago_public_key', $mercado_pago_public_key);
	$result3 = addUserProperty($logged_user_id, 'mercado_pago_access_token', $mercado_pago_access_token);

	if ($result1 && $result2 && $result3) {
		header('location: ?result=mp_saved');
		exit();
	}

	header('location: ?result=mp_error');
	exit();
}

if (isset($_POST['save_ps_credentials']) && isset($_POST['pag_seguro_email']) && isset($_POST['pag_seguro_token'])) {

	$pag_seguro 			= (isset($_POST['pag_seguro']) ? 1 : 0);
	$pag_seguro_email = purifyHTML($_POST['pag_seguro_email']);
	$pag_seguro_token = purifyHTML($_POST['pag_seguro_token']);

	if (empty($pag_seguro_email) or empty($pag_seguro_token)) {
		$pag_seguro = 0;
	}

	deleteUserProperty($logged_user_id, 'pag_seguro');
	deleteUserProperty($logged_user_id, 'pag_seguro_email');
	deleteUserProperty($logged_user_id, 'pag_seguro_token');

	$result1 = addUserProperty($logged_user_id, 'pag_seguro', $pag_seguro);
	$result2 = addUserProperty($logged_user_id, 'pag_seguro_email', $pag_seguro_email);
	$result3 = addUserProperty($logged_user_id, 'pag_seguro_token', $pag_seguro_token);

	if ($result1 && $result2 && $result3) {
		header('location: ?result=ps_saved');
		exit();
	}

	header('location: ?result=ps_error');
	exit();
}

if (isset($_POST['save_woovi_credentials']) && isset($_POST['woovi_token'])) {

	$woovi 			= (isset($_POST['woovi']) ? 1 : 0);
	$woovi_token = purifyHTML($_POST['woovi_token']);

	deleteUserProperty($logged_user_id, 'woovi');
	deleteUserProperty($logged_user_id, 'woovi_token');

	$result1 = addUserProperty($logged_user_id, 'woovi', $woovi);
	$result2 = addUserProperty($logged_user_id, 'woovi_token', $woovi_token);

	if ($result1 && $result2) {
		header('location: ?result=woovi_saved');
		exit();
	}

	header('location: ?result=woovi_error');
	exit();
}

$mercado_pago 							= getUserProperty($logged_user_id, 'mercado_pago');
$mercado_pago_public_key 		= getUserProperty($logged_user_id, 'mercado_pago_public_key');
$mercado_pago_access_token  = getUserProperty($logged_user_id, 'mercado_pago_access_token');

$pag_seguro 			= getUserProperty($logged_user_id, 'pag_seguro');
$pag_seguro_email = getUserProperty($logged_user_id, 'pag_seguro_email');
$pag_seguro_token = getUserProperty($logged_user_id, 'pag_seguro_token');

$woovi 			= getUserProperty($logged_user_id, 'woovi');
$woovi_token = getUserProperty($logged_user_id, 'woovi_token');

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
	<link rel="stylesheet" href="plugins/fontawesome-pro/css/all.min.css">
	<!-- Ionicons -->
	<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<!-- iCheck for checkboxes and radio inputs -->
	<link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
	<!-- overlayScrollbars -->
	<link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="plugins/select2/css/select2.min.css">
	<!-- summernote -->
	<link rel="stylesheet" href="plugins/summernote/summernote-bs4.css">
	<!-- SweetAlert2 -->
	<link rel="stylesheet" href="plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
	<!-- Bootstrap Switch -->
	<script src="plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
	<!-- Theme style -->
	<link rel="stylesheet" href="dist/css/adminlte.min.css">
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
						case 'mp_saved':
							$result_message = 'Configurações do MercadoPago Salvas!';
							$result_type = 'success';
							break;

						case 'ps_saved':
							$result_message = 'Configurações do PagSeguro Salvas!';
							$result_type = 'success';
							break;
						case 'woovi_saved':
							$result_message = 'Configurações do Woovi Salvas!';
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
							<h1>Configurações de Gateways</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item">Configurações</li>
								<li class="breadcrumb-item active">Gateways</li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
				<div class="container-fluid row">
					<div class="col-md-6">
						<div class="card card-default ">
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">MercadoPago</h3>
									<div class="card-tools">
										<input type="checkbox" name="mercado_pago" <?php echo $mercado_pago ? 'checked' : ''; ?> data-bootstrap-switch data-off-color="danger" data-on-color="success" />
									</div>
								</div>
								<div class="card-body pad">
									<div class="form-group col-md-12">
										<label>Public Key</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fas fa-key"></i></span>
											</div>
											<input type="text" autocomplete="off" value="<?php echo $mercado_pago_public_key; ?>" name="mercado_pago_public_key" class="form-control">
										</div>
									</div>
									<div class="form-group col-md-12">
										<label>Access Token</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fas fa-key"></i></span>
											</div>
											<input type="text" autocomplete="off" value="<?php echo $mercado_pago_access_token; ?>" name="mercado_pago_access_token" class="form-control">
										</div>
										<div class="col-md-12">
											<p><strong>Acesse <a href="https://www.mercadopago.com.br/settings/account/credentials" target="_blank">https://www.mercadopago.com.br/settings/account/credentials</a> para gerar suas Credenciais.</strong></p>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_mp_credentials" class="btn btn-primary">Salvar</button>
								</div>
							</form>
						</div>
					</div>
					<div class="col-md-6">
						<div class="card card-default">
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">PagSeguro</h3>
									<div class="card-tools">
										<input type="checkbox" name="pag_seguro" <?php if ($pag_seguro) {
																																echo 'checked';
																															} ?> data-bootstrap-switch data-off-color="danger" data-on-color="success">
									</div>
								</div>
								<div class="card-body pad">
									<div class="form-group col-md-12">
										<label>E-Mail</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fas fa-at"></i></span>
											</div>
											<input type="text" autocomplete="off" value="<?php echo $pag_seguro_email; ?>" name="pag_seguro_email" class="form-control">
										</div>
									</div>
									<div class="form-group col-md-12">
										<label>Token</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fas fa-key"></i></span>
											</div>
											<input type="text" autocomplete="off" value="<?php echo $pag_seguro_token; ?>" name="pag_seguro_token" class="form-control">
										</div>
									</div>
									<div class="col-md-12">
										<p><strong>Acesse <a href="https://pagseguro.uol.com.br/preferencias/integracoes.jhtml" target="_blank">https://pagseguro.uol.com.br/preferencias/integracoes.jhtml</a> para gerar seu Token.</strong></p>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_ps_credentials" class="btn btn-primary">Salvar</button>
								</div>
							</form>
						</div>
					</div>
					<div class="col-md-6">
						<div class="card card-default">
							<form autocomplete="off" action="#" method="post">
								<div class="card-header">
									<h3 class="card-title">Woovi</h3>
									<div class="card-tools">
										<input type="checkbox" name="woovi" <?php if ($woovi) {
																													echo 'checked';
																												} ?> data-bootstrap-switch data-off-color="danger" data-on-color="success">
									</div>
								</div>
								<div class="card-body pad">
									<div class="form-group col-md-12">
										<label>Token</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fas fa-key"></i></span>
											</div>
											<input type="text" autocomplete="off" value="<?php echo $woovi_token; ?>" name="woovi_token" class="form-control">
										</div>
									</div>
									<div class="col-md-12">
										<p><strong>Acesse <a href="https://app.woovi.com/home/applications/add" target="_blank">https://app.woovi.com/home/applications/add</a> para gerar seu Token.</strong></p>
										<p><strong>URL de Callback:</strong> <?php echo getBaseURL() . "gateway/woovi" ?><br><strong>Evento: </strong>Transação Pix Recebida</p>
										<p>Tutorial: <a href="https://developers.openpix.com.br/docs/webhook/platform/webhook-platform-api" target="_blank">Clique Aqui</a></p>
									</div>
								</div>
								<div class="card-footer">
									<button type="submit" name="save_woovi_credentials" class="btn btn-primary">Salvar</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</section>
		</div>
		<?php include_once('footer.php'); ?>
	</div>
	<!-- jQuery -->
	<script src="plugins/jquery/jquery.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- Select2 -->
	<script src="plugins/select2/js/select2.full.min.js"></script>
	<!-- InputMask -->
	<script src="plugins/moment/moment.min.js"></script>
	<script src="plugins/inputmask/min/jquery.inputmask.bundle.min.js"></script>
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- AdminLTE App -->
	<script src="dist/js/adminlte.min.js"></script>
	<!-- Clipboard -->
	<script src="bower_components/clipboard.min.js"></script>
	<!-- SweetAlert2 -->
	<script src="plugins/sweetalert2/sweetalert2.min.js"></script>
	<!-- Bootstrap Switch -->
	<script src="plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
	<script>
		$(function() {

			//Initialize Bootstrap Switch
			$("input[data-bootstrap-switch]").each(function() {
				$(this).bootstrapSwitch('state', $(this).prop('checked'));
			});

			//Initialize Select2 Elements
			$('.select2').select2()

			new ClipboardJS('.btn');
			const Toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 3000
			});

			$('.btn-copy-mp').click(function() {
				Toast.fire({
					type: 'success',
					title: 'Link IPN copiado!'
				})
			});
			$('.btn-test-iptv').click(function() {
				Toast.fire({
					type: 'success',
					title: 'Link de teste automático copdo!'
				})
			});
			$('.btn-modal-test').click(function() {
				Toast.fire({
					type: 'success',
					title: 'Dados do teste copiados!'
				})
			});
		});
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