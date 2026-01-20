<?php
include_once('./sys/functions.php');
startSession();

if (!getServerProperty('automatic_test', 0)) {
	exit();
}

if (!isset($_GET['key'])) {
	exit();
}

$code = boolval(isset($_GET['c']));
$key = $_GET['key'];
$result = getUserPropertyByValue('test_key', $key);

if (!$result) {
	exit();
}


$reseller = getUserByID($result['userid']);

if (!$reseller) {
	exit();
}

if (!isAdmin($reseller) && ($reseller['credits'] < getServerProperty('automatic_test_min_credits', 0))) {
	exit();
}

$server_name = getServerProperty('server_name');
$automatic_test_packages = json_decode(getServerProperty('automatic_test_packages', json_encode(array())), true);
$random_name = getServerProperty('random_name_automatic_test', 0);

if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['package_id'])) {
	$ip = getIP();
	$user_agent = $_SERVER['HTTP_USER_AGENT'];

	$data = array(
		'secret' => "0x9356f0FEBF4D7800769E906c06A41c46dC71c6E4",
		'response' => $_POST['h-captcha-response']
	);
	$verify = curl_init();
	curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
	curl_setopt($verify, CURLOPT_POST, true);
	curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($verify);
	$responseData = json_decode($response, true);
	if (!$responseData['success']) {
		header('location: ?key=' . $key . '&result=captcha_error');
		exit();
	}


	$username = random_str(6);
	$name = $_POST['name'];

	if (!$random_name) {
		if (!isset($_POST['username'])) {
			header('location: ?key=' . $key . '&result=invalid_username');
			exit();
		}

		$username = $_POST['username'];
		$username = str_replace(' ', '_', $username);
		if ((strlen($username) < 6) || (255 < strlen($username))) {
			header('location: ?key=' . $key . '&result=invalid_username');
			exit();
		}
	}

	$email = $_POST['email'];
	$package_id = intval($_POST['package_id']);
	$package = getPackageByID($package_id);

	if (!filter_var($email, FILTER_VALIDATE_EMAIL) || (mb_strpos($email, '+') !== false)) {
		header('location: ?key=' . $key . '&result=invalid_email');
		exit();
	}

	# remove o caracter . do email caso seja gmail
	if (mb_strpos($email, '@gmail.com') !== false) {
		$email = explode("@", $email);
		$email[0] = str_replace('.', '', $email[0]);
		$email = $email[0] . "@" . $email[1];
	}


	if (getServerProperty('only_valid_emails_automatic_test', 0)) {
		if ((strpos($email, '@hotmail') === false) && (strpos($email, '@outlook') === false) && (strpos($email, '@gmail') === false) && (strpos($email, '@icloud') === false)) {
			header('location: ?key=' . $key . '&result=just_verified_emails');
			exit();
		}
	}

	if ($package && $package['is_trial']) {
		if (!in_array($email, unserialize(ALLOWED_EMAILS)) && existTest($email)) {
			header('location: ?key=' . $key . '&result=used_email');
			exit();
		}

		// bloqueiar se ip já foi usado
		if (existTestIP($ip)) {
			header('location: ?key=' . $key . '&result=used_ip');
			exit();
		}

		if (insertTest($email, $ip, $user_agent)) {
			$duration = $package['trial_duration'] . ' ' . $package['trial_duration_in'];
			if ($code) {
				$username = CodeGenerator();
				$password = getServerProperty('code_default_pass');
			} else {
				$password = random_str(6);
			}
			$phone = "";

			$new_test = createClient($result['userid'], $username, $password, $phone, $email, $duration, $package['bouquets'], 'Nome: ' . $name, 1);
			if ($new_test) {
				insertRegUserLog($reseller['id'], $username, $password, '<b>Novo Teste (Auto)</b> | Pacote: ' . $package['package_name'] . ' | Créditos: <font color="green">' . $reseller['credits'] . '</font> > <font color="red">' . $reseller['credits'] . '</font> | Custo: 0 Crédito');
				$list_link = GetList($username, $password);
				$link_short = ShortenList("$list_link");
				$custom_template = getUserProperty($reseller['id'], 'custom_template');
				if ($custom_template) {
					$email_messages = json_decode(getUserProperty($reseller['id'], 'email_messages'), true);
				} else {
					$email_messages = json_decode(getServerProperty('email_messages'), true);
				}
				$whatsapp = getUserProperty($result['userid'], 'whatsapp');
				$telegram = getUserProperty($result['userid'], 'telegram');
				if ($code) {
					$auto_test_subject = str_replace(array('#usercode#', '#server_name#'), array($username, $server_name), $email_messages['auto_test_subject_code']);
					$auto_test_message = str_replace(array('#usercode#', '#server_name#', '#reseller_email#', '#whatsapp#', '#telegram#', '#duration#'), array($username, $server_name, $reseller['email'], $whatsapp, $telegram, $duration), $email_messages['auto_test_message_code']);
				} else {
					$auto_test_subject = str_replace(array('#username#', '#password#', '#server_name#'), array($username, $password, $server_name), $email_messages['auto_test_subject']);
					$auto_test_message = str_replace(array('#username#', '#password#', '#m3u_link#', '#server_name#', '#reseller_email#', '#whatsapp#', '#telegram#', '#duration#'), array($username, $password, $link_short, $server_name, $reseller['email'], $whatsapp, $telegram, $duration), $email_messages['auto_test_message']);
				}
				$custom_smtp = getUserProperty($reseller['id'], 'custom_smtp');
				if (smtpmailer($email, $auto_test_subject, $auto_test_message, $custom_smtp, $reseller['id'])) {
					if ($code) {
						header('location: ?c&key=' . $key . '&result=success');
					} else {
						header('location: ?key=' . $key . '&result=success');
					}
				}
			} else {
				header('location: ?key=' . $key . '&result=exist_user');
			}
		}
	}
}
$background = getServerProperty('login_background');

?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?php echo $server_name; ?> :: Gerador de teste</title>
	<!-- Tell the browser to be responsive to screen width -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="plugins/fontawesome-pro/css/all.min.css">
	<!-- Ionicons -->
	<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<!-- icheck bootstrap -->
	<link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="dist/css/adminlte.min.css">
	<!-- Google Font: Source Sans Pro -->
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

</head>

<body class="hold-transition login-page <?php if (DarkMode()) {
																					echo "dark-mode";
																				} ?>">
	<div class="login-box" style="background-image: url('<?php echo $background ?>');">
		<div class="login-logo">
			<b><?php echo $server_name; ?></b>
		</div>
		<!-- /.login-logo -->
		<div class="card">
			<div class="card-body login-card-body">
				<p class="login-box-msg">Crie seu teste gratuito agora mesmo!</p>
				<form method="post" onsubmit="return checkForm(this);">
					<input type="hidden" name="key" value="<?php echo $key; ?>">
					<label>Nome</label>
					<div class="input-group mb-3">
						<input type="text" class="form-control" name="name" maxlength="255" required="" placeholder="Digite seu nome">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fa fa-user"></span>
							</div>
						</div>
					</div>
					<label>E-mail</label>
					<div class="input-group mb-3">
						<input type="text" class="form-control" name="email" maxlength="255" required="" placeholder="Digite seu e-mail">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fa fa-envelope"></span>
							</div>
						</div>
					</div>
					<?php if (!$random_name) { ?>
						<label>Usuário</label>
						<div class="input-group mb-3">
							<input type="text" class="form-control" name="username" maxlength="255" required="" placeholder="Usuário">
							<div class="input-group-append">
								<div class="input-group-text">
									<span class="fa fa-user"></span>
								</div>
							</div>
						</div>
					<?php } ?>
					<div class="form-group">
						<label>Pacote</label>
						<select class="form-control" name="package_id" required="">
							<?php
							$packages = getPackages();

							foreach ($packages as $current_package) {
								if (in_array($current_package['id'], $automatic_test_packages)) { ?>
									<option value="<?php echo $current_package['id']; ?>"><?php echo $current_package['package_name']; ?></option>
							<?php }
							} ?>
						</select>
					</div>
					<center>
						<div class="h-captcha" data-theme="<?php if (DarkMode()) {
																									echo "dark";
																								} ?>" data-sitekey="f8946ba7-124f-4c46-85cb-d3d548221242"></div>

					</center>
					</br>

					<div class="row">
						<div class="col-12">
							<button type="submit" name="formsubmit" class="btn btn-primary btn-block">Gerar Teste</button>
						</div>
						<!-- /.col -->
					</div>
				</form>
			</div>
			<!-- /.login-card-body -->
		</div>
	</div>
	<!-- /.login-box -->
	<!-- jQuery -->
	<script src="plugins/jquery/jquery.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- AdminLTE App -->
	<script src="dist/js/adminlte.min.js"></script>
	<script src="https://js.hcaptcha.com/1/api.js" async defer></script>

	<?php

	if (isset($_GET['result'])) {
		$result = $_GET['result'];
		$result_title = 'Ops...';
		$result_message = 'Aconteceu um problema, tente novamente mais tarde!';
		$result_type = 'error';

		switch ($result) {
			case 'success':
				$result_type = 'success';
				$result_title = 'Feito!';
				$result_message = 'Teste criado com sucesso, verifique seu e-mail!';
				break;
			case 'invalid_email':
				$result_type = 'warning';
				$result_title = 'Ops...';
				$result_message = 'O e-mail escolhido é invalido!';
				break;
			case 'just_verified_emails':
				$result_type = 'warning';
				$result_title = 'Ops...';
				$result_message = 'Apenas e-mails válidos (@gmail, @hotmail, @outlook e @icloud)!';
				break;
			case 'used_email':
				$result_type = 'error';
				$result_title = 'Ops...';
				$result_message = 'Você só pode criar um teste!';
				break;
			case 'exist_user':
				$result_type = 'warning';
				$result_title = 'Ops...';
				$result_message = 'Já existe um usuário com este nome, tente novamente.';
				break;
			case 'captcha_error':
				$result_type = 'warning';
				$result_title = 'Ops...';
				$result_message = 'Falha na verificação da Captcha, tente novamente.';
				break;
		}

	?>
		<script src=""></script>
		<script type="text/javascript">
			Swal.fire(
				'<?php echo $result_title ?>',
				'<?php echo $result_message ?>',
				'<?php echo $result_type ?>'
			)

			function checkForm(form) {
				form.formsubmit.disabled = true;
				//form.formsubmit.value = "Please wait...";
				return true;
			}
		</script>

	<?php } ?>
</body>

</html>