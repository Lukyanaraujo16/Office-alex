<?php
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

$code = CodeGenerator();

if (isset($_POST['usercode']) && isset($_POST['reseller_notes']) && isset($_POST['package'])) {
	$username = preg_replace('/[^A-Za-z0-9\-]/', '', strip_tags($_POST['usercode']));
	$password = preg_replace('/[^A-Za-z0-9\-]/', '', strip_tags(getServerProperty('code_default_pass', 0)));
	$package_id = preg_replace('/[^A-Za-z0-9\-]/', '', strip_tags($_POST['package']));

	$reseller_notes = $_POST['reseller_notes'];
	if ($package_id == "custom") {
		$package['package_name'] = "Personalizado";
		$bouquet = $_POST['bouquet'];
	} else {
		$package = getPackageByID($package_id);
		$bouquet = json_decode($package["bouquets"]);
	}

	$email = (isset($_POST['email']) ? $_POST['email'] : '');
	$send_email = isset($_POST['send_email']);

	$phone = $_POST['full_phone'];

	if (is_array($bouquet)) {
		foreach ($bouquet as $a => $b) {
			if (!in_array($b, $allowed_bouquets)) {
				header('location: ?result=invalid_bouquet');
				exit();
			}
		}

		if ((strlen($username) < 6) || (255 < strlen($username))) {
			header('location: ?result=invalid_username');
			exit();
		}

		if ((strlen($password) < 6) || (255 < strlen($password))) {
			header('location: ?result=error_P462');
			exit();
		}

		if (500 < strlen($reseller_notes)) {
			header('location: ?result=invalid_notes');
			exit();
		}

		$bouquet = json_encode($bouquet);
		if ($send_email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			header('location: ?key=' . $key . '&result=invalid_email');
			exit();
		}

		if (addOrRemoveCredits($logged_user['id'], -1)) {
			$new_code = createP2P($logged_user['id'], $username, $password, $phone, $email, '1 months', $bouquet, $reseller_notes, 0);
			if ($new_code) {

				$old_credits = $logged_user['credits'];
				$logged_user = getLoggedUser();
				$now_credits = $logged_user['credits'];
				insertRegUserLog($logged_user['id'], $username, $password, '<b>Novo Código</b> | Pacote: ' . $package['package_name'] . ' | Créditos: <font color="green">' . $old_credits . '</font> > <font color="red">' . $now_credits . '</font> | Custo: 1 Crédito');

				if ($send_email) {
					$list_link = GetList($username, $password);
					$email_messages = json_decode(getServerProperty('email_messages'), true);
					$whatsapp = getUserProperty($logged_user['id'], 'whatsapp');
					$telegram = getUserProperty($logged_user['id'], 'telegram');
					$auto_test_subject = str_replace(array('#username#', '#password#', '#server_name#'), array($username, $password, $server_name), $email_messages['auto_test_subject_code']);
					$auto_test_message = str_replace(array('#username#', '#password#', '#m3u_link#', '#server_name#', '#reseller_email#', '#whatsapp#', '#telegram#'), array($username, $password, $list_link, $server_name, $logged_user['email'], $whatsapp, $telegram), $email_messages['auto_test_message_code']);

					if (smtpmailer($email, $auto_test_subject, $auto_test_message)) {
						header('location: ?key=' . $key . '&result=success');
						exit();
					}

					header('location: ?result=cant_send_email');
					exit();
				}
				header('location: /codes/clients/show/' . $new_code);
				//header('location: ?result=success');
				exit();
			} else {
				addOrRemoveCredits($logged_user['id'], 1);
				header('location: ?result=exist_client');
				exit();
			}
		} else {
			header('location: ?result=insufficient_credits');
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
	<!-- daterange picker -->
	<link rel="stylesheet" href="/plugins/daterangepicker/daterangepicker.css">
	<!-- iCheck for checkboxes and radio inputs -->
	<link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
	<!-- Bootstrap Color Picker -->
	<link rel="stylesheet" href="/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">
	<!-- Tempusdominus Bootstrap 4 -->
	<link rel="stylesheet" href="/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
	<!-- International Telephone Input -->
	<link rel="stylesheet" href="/plugins/intlTelInput/build/css/intlTelInput.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
	<link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
	<!-- Toastr -->
	<link rel="stylesheet" href="/plugins/toastr/toastr.min.css">
	<!-- Bootstrap4 Duallistbox -->
	<link rel="stylesheet" href="/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="/dist/css/adminlte.min.css">
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
						case 'success':
							$result_message = 'O usuário foi criado com sucesso.';
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
						case 'invalid_email':
							$result_message = 'O e-mail escolhido é invalido!';
							break;
						case 'insufficient_credits':
							$result_message = 'Você não tem créditos suficiente, recarregue seu painel.';
							break;
						case 'exist_client':
							$result_message = 'Já existe um usuário com este nome de usuário, escolha outro.';
							break;
						case 'cant_send_email':
							$result_message = 'O usuário foi criado com sucesso, mas não foi possível enviar o e-mail com os dados de acesso.';
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
							<h1>Criar Cliente</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Criar Cliente</li>
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
							<div class="card-body">
								<div class="row col-lg-6 col-md-12">
									<div class="form-group col-md-12">
										<label>Código</label>
										<div class="input-group mb-6">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fal fa-user"></i></span>
											</div>
											<input type="text" required="" autocomplete="off" name="usercode" id="usercode" data-minlength="6" minlength="6" readonly class="form-control" placeholder="" value="<?php echo $code; ?>">
											<div class="input-group-append">
												<button class="btn btn-outline-info generate" type="button" data-input="usercode"><i class="far fa-sync-alt"></i></button>
											</div>
											<div class="input-group-append">
												<button type="button" class="btn btn-info copyusercode" data-clipboard-target="#usercode">Copiar</button>
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
											<label>Selecionar Pacote</label>
											<select name="package" id="package" class="select2" required="" data-placeholder="Selecione os Pacotes" style="width: 100%;">

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
												<option value="custom">Personalizar</option>
											</select>
										</div>
									</div>
									<div class="col-12">
										<div class="form-group boquet-container" style="display:none">
											<label>Definir Buquês</label>
											<select name="bouquet[]" class="select2" multiple="multiple" data-placeholder="Selecione os Buquês" style="width: 100%; display: none">
												<?php
												$bouquets = getBouquets();
												foreach ($bouquets as $bouquet) {
													if (in_array($bouquet['id'], $allowed_bouquets)) { ?>
														<option value="<?php echo $bouquet['id']; ?>"><?php echo $bouquet['bouquet_name']; ?></option>
												<?php }
												} ?>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="card-footer">
								<button type="submit" class="btn btn-primary">Criar Usuário</button>
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
	<!-- Clipboard -->
	<script src="/bower_components/clipboard.min.js"></script>
	<!-- Tempusdominus Bootstrap 4 -->
	<script src="/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
	<!-- International Telephone Input -->
	<script src="/plugins/intlTelInput/build/js/intlTelInput.js"></script>
	<!-- Toastr -->
	<script src="/plugins/toastr/toastr.min.js"></script>
	<!-- Bootstrap Switch -->
	<script src="/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.min.js"></script>
	<!-- Page script -->
	<script>
		$(function() {
			$('.select2').select2();

			toastr.options = {
				"closeButton": false,
				"debug": false,
				"newestOnTop": true,
				"progressBar": true,
				"positionClass": "toast-top-right",
				"preventDuplicates": false,
				"onclick": null,
				"showDuration": "300",
				"hideDuration": "1000",
				"timeOut": "5000",
				"extendedTimeOut": "1000",
				"showEasing": "swing",
				"hideEasing": "linear",
				"showMethod": "fadeIn",
				"hideMethod": "fadeOut"
			}
		})

		$(document).ready(function() {
			$('.generate').click(function() {
				var inputName = $(this).data('input');
				var inputField = $('input[name="' + inputName + '"]');
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

		$("#package").on("change", function(e) {
			const value = $(this).val();
			const $container = $(".boquet-container")

			if (value == 'custom') {
				$container.show();
			} else {
				$container.hide();
			}
		});
	</script>
	<script type="text/javascript">
		$(function() {
			new ClipboardJS('.copyusercode');

			$('.copyusercode').click(function() {
				toastr.success('Código copiado!');

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