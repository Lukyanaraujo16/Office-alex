<?php
if (!isset($_GET['client_id'])) {
	header('location: /codes/clients');
	exit();
}

include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
if (!hasPermissionResource($logged_user['id'], "codes") || !getServerProperty('code_status', 1)) {
	header('location: /dashboard');
	exit();
}
$server_name = getServerProperty('server_name');
$allowed_bouquets = json_decode(getServerProperty('allowed_bouquets'), true);
$fast_packages = json_decode(getServerProperty('fast_packages'), true);
$client_id = intval($_GET['client_id']);
$client = getClientByID($client_id);

if (!$client) {
	header('location: /codes/clients');
	exit();
}

if (!hasPermission($logged_user['id'], $client['id'])) {
	header('location: /codes/clients');
	exit();
}

$user_bouquets = json_decode($client['bouquet'], true);
$server_dns = getServerDNS();

if (isset($_POST['usercode']) && isset($_POST['reseller_notes']) && isset($_POST['bouquet'])) {
	$username = preg_replace('/[^A-Za-z0-9\-]/', '', purifyHTML($_POST['usercode']));
	$password = preg_replace('/[^A-Za-z0-9\-]/', '', purifyHTML(getServerProperty('code_default_pass', 0)));

	$reseller_notes = purifyHTML($_POST['reseller_notes']);
	$bouquet = $_POST['bouquet'];
	$email = purifyHTML($_POST['email']);
	$phone = purifyHTML($_POST['full_phone']);

	if (is_array($bouquet)) {
		foreach ($bouquet as $a => $b) {
			if (!in_array($b, $allowed_bouquets)) {
				header('location: ?client_id=' . $client_id . '&result=invalid_bouquet');
				exit();
			}
		}

		if ((strlen($username) < 6) || (255 < strlen($username))) {
			header('location: ?client_id=' . $client_id . '&result=invalid_username');
			exit();
		}

		if ((strlen($password) < 6) || (255 < strlen($password))) {
			header('location: ?client_id=' . $client_id . '&result=error_P462');
			exit();
		}

		if (500 < strlen($reseller_notes)) {
			header('location: ?client_id=' . $client_id . '&result=invalid_notes');
			exit();
		}

		$bouquet = json_encode($bouquet);

		if (isAdmin($logged_user) || isPartner($logged_user)) {
			$max_connections = intval($_POST['max_connections']);
			$dateObj = DateTime::createFromFormat('d/m/Y H:i', $_POST['exp_date']);
			$exp_date = $dateObj->getTimestamp();
		} else {
			$max_connections = "";
			$exp_date = "";
		}

		if (updateClient($client_id, $username, $password, $phone, $email, $reseller_notes, $bouquet, $max_connections, $exp_date)) {
			$result = insertRegUserLog($logged_user['id'], $client['username'], $client['password'], '<b>Código Editado</b>');
			if ($result) {
				header('location: ?client_id=' . $client_id . '&result=success');
			} else {
				header('location: ?client_id=' . $client_id . '&result=email_error');
			}
			exit();
		}
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
						case 'error_P462':
							$result_message = 'Ocorreu um erro interno, por favor nos informe sobre isso!';
							break;
						case 'invalid_notes':
							$result_message = 'A observação escolhida é invalida!, deve ter no máximo 500 caracteres.';
							break;
						case 'invalid_bouquet':
							$result_message = 'Os pacotes escolhidos são inválidos.';
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
							<h1>Editar P2P</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Editar P2P</li>
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
							<h3 class="card-title">Dados do P2P</h3>
						</div>
						<!-- /.card-header -->
						<form autocomplete="off" action="#" method="post" name="frm1">
							<input type="hidden" name="action" value="create_custom_test">
							<div class="card-body">
								<div class="row col-lg-6">
									<div class="form-group col-md-6">
										<label>Código</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fas fa-user"></i></span>
											</div>
											<input type="text" required="" readonly="" autocomplete="off" name="usercode" data-minlength="6" minlength="6" value="<?php echo $client['username']; ?>" class="form-control">
											<div class="input-group-append">
												<button class="btn btn-outline-info generate" type="button" data-input="code"><i class="far fa-sync-alt"></i></button>
											</div>
										</div>
									</div>
									<div class="form-group col-md-6">
										<label>E-mail</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fas fa-at"></i></span>
											</div>
											<input type="email" autocomplete="off" name="email" class="form-control" placeholder="E-mail" value="<?php echo $client['email']; ?>">
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
																					} ?> autocomplete="off" name="exp_date" id="exp_date" data-target="#reservationdatetime" value="<?php echo date('d/m/Y H:i', $client['exp_date']); ?>" class="form-control datetimepicker-input">
										</div>
									</div>
									<div class="form-group col-md-6">
										<label>Conexões</label>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fas fa-user"></i></span>
											</div>
											<input type="text" readonly="" name="max_connections" value="<?php echo $client['max_connections']; ?>" class="form-control">
										</div>
									</div>
									<div class="form-group col-md-6">
										<label>WhatsApp</label>
										<div class="input-group mb-3">
											<input type="tel" autocomplete="off" name="phone_number" id="phone_number" class="form-control" value="<?php echo $client['phone']; ?>">
										</div>
									</div>
									<div class="form-group col-md-12">
										<label>Notas</label>
										<textarea class="form-control" rows="3" name="reseller_notes" placeholder="Informações..."><?php echo $client['reseller_notes']; ?></textarea>
									</div>
									<div class="col-12">
										<div class="form-group">
											<label>Definir Pacotes</label>
											<select name="bouquet[]" class="select2" required="" multiple="multiple" data-placeholder="Selecione os Pacotes" style="width: 100%;">
												<?php
												$bouquets = getBouquets();
												foreach ($bouquets as $bouquet) {
													if (in_array($bouquet['id'], $allowed_bouquets)) {
														if (in_array($bouquet['id'], $user_bouquets)) { ?>
															<option value="<?php echo $bouquet['id']; ?>" selected><?php echo $bouquet['bouquet_name']; ?></option>
														<?php } else { ?>
															<option value="<?php echo $bouquet['id']; ?>"><?php echo $bouquet['bouquet_name']; ?></option>
												<?php }
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
				var inputField = $('input[name="usercode"]');
				var icon = $(this).find('i');
				icon.addClass('fa-spin');

				$.get("/sys/api.php?action=GenerateUserPass&type=code",
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