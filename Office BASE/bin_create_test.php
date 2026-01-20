<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();

if (!hasPermissionResource($logged_user['id'], "binstream")) {
	header('location: /dashboard');
	exit();
}

$server_name = getServerProperty('server_name');
$binstream_allowed_packages = json_decode(getServerProperty('binstream_allowed_packages'), true);
$test_time = getServerProperty('binstream_test_time');
include_once __DIR__ . '/sys/class/binstream.php';
$binstream = new BinStream();

if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['reseller_notes']) && isset($_POST['package'])) {
	if ((!isAdmin($logged_user) && !isPartner($logged_user)) && ($logged_user['credits'] < getServerProperty('test_min_credits', 0))) {
		header('location: ?result=no_min_credits');
		exit();
	}

	$username = preg_replace('/[^A-Za-z0-9\-]/', '', purifyHTML($_POST['username']));
	$password = preg_replace('/[^A-Za-z0-9\-]/', '', purifyHTML($_POST['password']));
	$package = preg_replace('/[^A-Za-z0-9\-]/', '', purifyHTML($_POST['package']));
	$reseller_notes = purifyHTML($_POST['reseller_notes']);

	$duration = ($test_time / 24);
	$phone = purifyHTML($_POST['full_phone']);
	$email = (isset($_POST['email']) ? $_POST['email'] : '');
	$send_email = isset($_POST['send_email']);

	if (!in_array($package, $binstream_allowed_packages)) {
		header('location: ?result=invalid_package');
		exit();
	}

	if ((strlen($username) < 6) || (255 < strlen($username))) {
		header('location: ?result=invalid_username');
		exit();
	}

	if ((strlen($password) < 6) || (255 < strlen($password))) {
		header('location: ?result=invalid_password');
		exit();
	}

	if (500 < strlen($reseller_notes)) {
		header('location: ?result=invalid_notes');
		exit();
	}

	if ($send_email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		header('location: ?result=invalid_email');
		exit();
	}

	$data = [
		'name' => OFFICE_CONFIG['panel_id'],
		'email' => $username . "@" . OFFICE_CONFIG['binstream']['email'],
		'password' => $password,
		'status' => -1,
		'type' => 0,
		'serviceTag' => $reseller_notes,
		'servicePeriod' => $duration,
		'productId' => $package,
		'exField1' => $logged_user['username'],
		'exField2' => $logged_user['id'],
		'exField3' => $password,
		'exField4' => json_encode(['email' => $email, 'phone' => $phone])
	];

	$new_test = $binstream->create($data);
	if ($new_test['id']) {

		insertRegUserLog($logged_user['id'], $username, $password, '<b>Novo Teste BinStream</b> | Pacote: ' . $package . ' | Créditos: <font color="green">' . $logged_user['credits'] . '</font> > <font color="red">' . $logged_user['credits'] . '</font> | Custo: 0 Crédito');

		if ($send_email) {
			$email_messages = json_decode(getServerProperty('email_messages'), true);
			$whatsapp = getUserProperty($logged_user['id'], 'whatsapp');
			$telegram = getUserProperty($logged_user['id'], 'telegram');
			$auto_test_subject = str_replace(array('#username#', '#password#', '#server_name#'), array($username, $password, $server_name), $email_messages['auto_test_subject']);
			$auto_test_message = str_replace(array('#username#', '#password#', '#server_name#', '#reseller_email#', '#whatsapp#', '#telegram#'), array($username, $password, $server_name, $logged_user['email'], $whatsapp, $telegram), $email_messages['auto_test_message']);

			if (smtpmailer($email, $auto_test_subject, $auto_test_message)) {
				header('location: ?result=success');
				exit();
			}

			header('location: ?result=cant_send_email');
			exit();
		}
		header('location: /p2p/clients/show/' . $new_test['id']);
		exit();
	} else {
		header('location: ?result=exist_client');
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
	<!-- overlayScrollbars -->
	<link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
	<link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
	<!-- International Telephone Input -->
	<link rel="stylesheet" href="/plugins/intlTelInput/build/css/intlTelInput.css">
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
							$result_message = 'O teste foi criado com sucesso.';
							$result_type = 'success';
							break;
						case 'invalid_username':
							$result_message = 'O nome de usuário escolhido é invalido!, deve ter no mínimo 6 caracteres.';
							break;
						case 'invalid_password':
							$result_message = 'A senha escolhida é invalida!, deve ter no mínimo 6 caracteres.';
							break;
						case 'invalid_notes':
							$result_message = 'A observaço escolhida é invalida!, deve ter no máximo 500 caracteres.';
							break;
						case 'invalid_bouquet':
							$result_message = 'Os pacotes escolhidos são inválidos.';
							break;
						case 'invalid_email':
							$result_message = 'O e-mail escolhido é invalido!';
							break;
						case 'cant_send_email':
							$result_message = 'O teste foi criado com sucesso, mas não foi possível enviar o e-mail com os dados de acesso.';
							break;
						case 'exist_client':
							$result_message = 'Já existe um usuário com este nome de usuário, escolha outro.';
							break;
						case 'no_min_credits':
							$result_message = 'Você não tem a quantidade minima de créditos para criar o teste.';
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
							<h1>Criar teste personalizado</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Teste Personalizado</li>
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
							<h3 class="card-title">Dados do Teste</h3>
						</div>
						<!-- /.card-header -->
						<form autocomplete="off" action="#" method="post" name="frm1">
							<div class="card-body">
								<div class="row col-lg-6 col-md-12">
									<div class="form-group col-md-6">
										<label>Usuário</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fal fa-user"></i></span>
											</div>
											<input type="text" required="" autocomplete="off" name="username" data-minlength="6" minlength="6" class="form-control" placeholder="Usuário">
											<div class="input-group-append">
												<button class="btn btn-outline-info generate" type="button" data-input="username"><i class="far fa-sync-alt"></i></button>
											</div>
										</div>
									</div>
									<div class="form-group col-md-6">
										<label>Senha</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fal fa-lock"></i></span>
											</div>
											<input type="text" required="" autocomplete="off" name="password" data-minlength="6" minlength="6" class="form-control" placeholder="Senha">
											<div class="input-group-append">
												<button class="btn btn-outline-info generate" type="button" data-input="password"><i class="far fa-sync-alt"></i></button>
											</div>
										</div>
									</div>
									<div class="form-group col-md-6">
										<label>E-mail</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fal fa-at"></i></span>
											</div>
											<input type="email" autocomplete="off" value="" name="email" class="form-control" placeholder="E-mail">
										</div>
										<div class="custom-control custom-switch">
											<input type="checkbox" name="send_email" class="custom-control-input" id="customSwitch1">
											<label class="custom-control-label" for="customSwitch1">Enviar dados do teste via e-mail</label>
										</div>
									</div>
									<div class="form-group col-md-6">
										<label>WhatsApp</label>
										<div class="input-group mb-3">
											<input type="tel" autocomplete="off" name="phone_number" id="phone_number" class="form-control">
										</div>
									</div>
									<div class="form-group col-md-12">
										<label>Notas</label>
										<textarea class="form-control" rows="3" name="reseller_notes" placeholder="Informações..."></textarea>
									</div>
									<div class="col-12">
										<div class="form-group">
											<label>Definir Pacote</label>
											<select name="package" id="package" class="select2" required="" data-placeholder="Selecione o Pacote" style="width: 100%;">
												<?php
												$packages = $binstream->getPackages();
												foreach ($binstream_allowed_packages as $package_id) {
													$package_key = array_search($package_id, array_column($packages, 'id'));
													if ($package_key !== false) {
														$current_package = $packages[$package_key]; ?>
														<option value="<?php echo $current_package['id']; ?>"><?php echo $current_package['name']; ?></option>
												<?php
													}
												} ?>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="card-footer">
								<button type="submit" class="btn btn-primary">Criar Teste</button>
							</div>
						</form>
					</div>
				</div>
			</section>
		</div>
		<?php include_once('footer.php'); ?>
	</div>
	<!-- ./wrapper -->
	<!-- jQuery -->
	<script src="/plugins/jquery/jquery.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="/plugins/select2/js/select2.full.min.js"></script>
	<!-- InputMask -->
	<script src="/plugins/moment/moment.min.js"></script>
	<script src="/plugins/inputmask/min/jquery.inputmask.bundle.min.js"></script>
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- International Telephone Input -->
	<script src="/plugins/intlTelInput/build/js/intlTelInput.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.min.js?<?php echo OFFICE_VERSION ?>"></script>
	<!-- Page script -->
	<script>
		$(function() {
			//Initialize Select2 Elements
			$('.select2').select2()
		})

		$(document).ready(function() {
			$('.generate').click(function() {
				var inputName = $(this).data('input');
				var inputField = $('input[name="' + inputName + '"]');
				var icon = $(this).find('i');
				icon.addClass('fa-spin');

				$.get("/sys/api.php?action=GenerateUserPass&type=binstream",
					function(data) {
						if (data.result === "success") {
							inputField.val(data.code);
						} else {
							toastr.warning("Não foi possível gerar o usuário/senha, tente novamente.");
						}
						icon.removeClass('fa-spin');
					});
			});
		});

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