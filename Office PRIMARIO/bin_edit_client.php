<?php
if (!isset($_GET['client_id'])) {
	header('location: /p2p/clients');
	exit();
}

include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();

if (!hasPermissionResource($logged_user['id'], "binstream")) {
	header('location: /dashboard');
	exit();
}

$server_name = getServerProperty('server_name');
$binstream_allowed_packages = json_decode(getServerProperty('binstream_allowed_packages'), true);
$client_id = intval($_GET['client_id']);

include_once __DIR__ . '/sys/class/binstream.php';
$binstream = new BinStream();
$client = $binstream->getuser($client_id);

if (!$client) {
	header('location: /p2p/clients');
	exit();
}

if (!hasPermission($logged_user['id'], $client['id'], "binstream")) {
	header('location: /p2p/clients');
	exit();
}

$server_dns = getServerDNS();

if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['reseller_notes']) && isset($_POST['package'])) {
	$username = preg_replace('/[^A-Za-z0-9\-]/', '', purifyHTML($_POST['username']));
	$password = preg_replace('/[^A-Za-z0-9\-]/', '', purifyHTML($_POST['password']));
	$package = preg_replace('/[^A-Za-z0-9\-]/', '', purifyHTML($_POST['package']));
	$reseller_notes = purifyHTML($_POST['reseller_notes']);
	$email = purifyHTML($_POST['email']);
	$phone = purifyHTML($_POST['full_phone']);

	if (!in_array($package, $binstream_allowed_packages)) {
		header('location: ?result=invalid_package');
		exit();
	}

	if ((strlen($username) < 6) || (255 < strlen($username))) {
		header('location: ?client_id=' . $client_id . '&result=invalid_username');
		exit();
	}

	if ((strlen($password) < 6) || (255 < strlen($password))) {
		header('location: ?client_id=' . $client_id . '&result=invalid_password');
		exit();
	}

	if (500 < strlen($reseller_notes)) {
		header('location: ?client_id=' . $client_id . '&result=invalid_notes');
		exit();
	}

	$exField4 = json_decode($client['exField4'], true);
	$exField4['email'] = $email;
	$exField4['phone'] = $phone;

	$data = array(
		'email' => $username . '@' . explode("@", $client["email"])[1],
		'password' => $password,
		'productId' => $package,
		'exField3' => $password,
		'exField4' => json_encode($exField4),
		'serviceTag' => $reseller_notes
	);

	if (isAdmin($logged_user) || isPartner($logged_user)) {
		$dateObj = DateTime::createFromFormat('d/m/Y H:i', $_POST['exp_date']);
		$exp_date = $dateObj->getTimestamp();
		$data['endTime'] = gmdate("Y-m-d\TH:i:s\Z", $exp_date);
	}

	if ($binstream->updateUser($client_id, $data)) {
		insertRegUserLog($logged_user['id'], explode("@", $client["email"])[0], $client['exField3'], '<b>Cliente Binstream Editado</b>');
		// header('location: ./?result=success');
		// exit();
		$message = "success";
		$client = $binstream->getuser($client_id);
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
	<!-- daterange picker -->
	<link rel="stylesheet" href="/plugins/daterangepicker/daterangepicker.css">
	<!-- Tempusdominus Bootstrap 4 -->
	<link rel="stylesheet" href="/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
	<link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
	<!-- International Telephone Input -->
	<link rel="stylesheet" href="/plugins/intlTelInput/build/css/intlTelInput.css">
	<!-- Bootstrap4 Duallistbox -->
	<link rel="stylesheet" href="/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
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
				if (isset($_GET['result']) || isset($message)) {
					$result = isset($_GET['result']) ? $_GET['result'] : $message;
					$result_message = 'Aconteceu um problema, tente novamente mais tarde!';
					$result_type = 'warning';

					switch ($result) {
						case 'success':
							$result_message = 'As alterações foram salvas com sucesso.';
							$result_type = 'success';
							break;
						case 'invalid_username':
							$result_message = 'O usuário escolhido é invalido, deve ter no mínimo 6 caracteres.';
							break;
						case 'invalid_password':
							$result_message = 'A senha escolhida é invalida, deve ter no mínimo 6 caracteres.';
							break;
						case 'invalid_notes':
							$result_message = 'A observação escolhida é invalida, deve ter no máximo 500 caracteres.';
							break;
						case 'invalid_package':
							$result_message = 'O pacote escolhido é inválido.';
							break;
						case 'email_error':
							$result_message = 'Não foi possível salvar o e-mail.';
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
							<h1>Editar Cliente</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Editar Cliente</li>
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
							<h3 class="card-title">Dados do Cliente</h3>
						</div>
						<!-- /.card-header -->
						<form autocomplete="off" action="#" method="post" name="frm1">
							<input type="hidden" name="action" value="create_custom_test">
							<div class="card-body">
								<div class="row col-lg-6">
									<!-- <div class=""> -->
									<div class="form-group col-md-6">
										<label>Usuário</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fal fa-user"></i></span>
											</div>
											<input type="text" required="" autocomplete="off" name="username" data-minlength="6" minlength="6" value="<?php echo explode("@", $client["email"])[0]; ?>" class="form-control">
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
											<input type="text" required="" autocomplete="off" name="password" data-minlength="6" minlength="6" value="<?php echo $client['exField3']; ?>" class="form-control">
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
											<input type="email" autocomplete="off" name="email" class="form-control" placeholder="E-mail" value="<?php echo json_decode($client['exField4'], true)['email']; ?>">
										</div>
									</div>
									<div class="form-group col-md-6">
										<label>Vencimento</label>
										<div class="input-group mb-3 date" id="reservationdatetime" data-target-input="nearest">
											<div class="input-group-prepend" data-target="#reservationdatetime" data-toggle="datetimepicker">
												<span class="input-group-text"><i class="fal fa-clock"></i></span>
											</div>
											<input type="text" <?php if (!isAdmin($logged_user) && !isPartner($logged_user)) {
																						echo 'readonly=""';
																					} ?> autocomplete="off" name="exp_date" id="exp_date" data-target="#reservationdatetime" value="<?php echo !empty($client["endTime"]) ? date("d/m/Y H:i", strtotime($client["endTime"])) : "Período não iniciado!"; ?>" class="form-control datetimepicker-input">
										</div>
									</div>
									<div class="form-group col-md-6">
										<label>WhatsApp</label>
										<div class="input-group mb-3">
											<input type="tel" autocomplete="off" name="phone_number" id="phone_number" class="form-control phone_number" value="<?php echo json_decode($client['exField4'], true)['phone']; ?>">
										</div>
									</div>
									<div class="form-group col-md-12">
										<label>Notas</label>
										<textarea class="form-control" rows="3" name="reseller_notes" placeholder="Informações..."><?php echo $client['serviceTag']; ?></textarea>
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
														$current_package = $packages[$package_key];
												?>
														<option <?php echo $client['productId'] == $current_package['id'] ? "selected" : ""; ?> value="<?php echo $current_package['id']; ?>"><?php echo $current_package['name']; ?></option>
												<?php
													}
												} ?>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="card-footer">
								<button type="submit" class="btn btn-primary">Editar Usuário</button>
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
	<!-- date-range-picker -->
	<script src="/plugins/daterangepicker/daterangepicker.js"></script>
	<!-- Tempusdominus Bootstrap 4 -->
	<script src="/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
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

			//Date and time picker
			$('#reservationdatetime').datetimepicker({
				format: 'DD/MM/YYYY HH:mm',
				icons: {
					time: 'far fa-clock'
				}
			});
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

		var input = document.querySelector(".phone_number");
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
			utilsScript: "https://<?php echo $_SERVER['HTTP_HOST']; ?>/plugins/intlTelInput/build/js/utils.js",
		});
	</script>
</body>

</html>