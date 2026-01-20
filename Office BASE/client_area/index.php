<?php
include_once('../sys/functions.php');
$server_name = getServerProperty('server_name');
$background = getServerProperty('login_background');
startSession();

if (isset($_SESSION['__l0gg3d_Client__'])) {
	header('Location: /client_area/dashboard.php');
	exit;
}

#if (isset($_POST['username']) && isset($_POST['password']) && $_POST["g-recaptcha-response"]) {
if (isset($_POST['username']) && isset($_POST['password'])) {

	$username = $_POST['username'];
	$password = $_POST['password'];
	$result = loginClient($username, $password);



	$showResult = null;

	switch ($result) {
		case 1:
			header('location: /client_area/dashboard.php');
			exit;
		case 2:
			$showResult = 'cant_connect';
			break;
		case 3:
			$showResult = 'invalid_user_or_pass';
			break;
		case 4:
			$showResult = 'blocked';
			break;
		case 5:
			$showResult = 'insufficient_permission';
			break;
		case 6:
			$showResult = 'captcha';
			break;
			// case 11:
			// 	header('location: /client_area/dashboard.php');
			// 	exit;
	}
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title><?php echo $server_name; ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="<?php echo get_logo('big') ?>" />
	<link rel="stylesheet" type="text/css" href="/plugins/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="/dist/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="/plugins/sweetalert2/sweetalert2.min.css">
	<link rel="stylesheet" type="text/css" href="/dist/css/util.css">
	<link rel="stylesheet" type="text/css" href="/dist/css/main.css">
	<link rel="stylesheet" type="text/css" href="/dist/css/checkbox.css">
	<?php
	if (getServerProperty('recaptcha_enable')) { ?>
		<script src='https://www.google.com/recaptcha/api.js?hl=pt-BR'></script>
		<script>
			function onSubmit(token) {
				document.getElementById("form-1").submit();
			}
		</script>
	<?php } ?>
</head>

<body>
	<div class="limiter">
		<div class="container-login100" style="background-image: url('<?php echo $background ?>');">
			<div class="wrap-login100 p-t-100 p-b-0">
				<form action="index.php" method="post" id="form-1" class="login100-form validate-form">
					<div class="login100-form-avatar p-b-30" style="width: 100%; height: 100%; border-radius:0%">
						<img src="<?php echo get_logo('big'); ?>" alt="<?php echo $server_name; ?>">
					</div>
					<!--span class="login100-form-title p-t-20 p-b-45">
						<strong>hostmk</strong>
					</span-->
					<div class="g-recaptcha" data-sitekey="<?php echo getServerProperty('recaptcha_site_key'); ?>" data-callback="onSubmit" data-size="invisible"></div>
					<div class="wrap-input100 validate-input m-b-10" data-validate="Digite seu usuário">
						<input class="input100" type="text" name="username" placeholder="Usuário" value="<?php if (!empty($_POST['username'])) {
																																																echo $_POST['username'];
																																															} ?>">
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-user"></i>
						</span>
					</div>
					<div class="wrap-input100 validate-input m-b-10" data-validate="Digite sua senha">
						<input class="input100" type="password" name="password" placeholder="Senha">
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-lock"></i>
						</span>
					</div>
					<div class="contact100-form-checkbox m-l-4 p-t-10">
						<input id="c1" type="checkbox" value="1" name="isP2P">
						<label for="c1">Cliente P2P</label>
					</div>
					<div class="container-login100-form-btn p-t-15">
						<button class="login100-form-btn " data-sitekey="<?php echo getServerProperty('recaptcha_site_key'); ?>" data-callback='onSubmit'>
							Login
						</button>
					</div>
					<!-- <div class="text-center w-full p-t-25 p-b-30">
						<a href="forget_password.php" class="txt1">
							Esqueci minha senha!
						</a>
					</div> -->
				</form>
			</div>
			<p class="float-r pt-2">
				<?php echo OFFICE_VERSION; ?>
			</p>
		</div>
	</div>

	<!--===============================================================================================-->
	<script src="/plugins/jquery/jquery.js"></script>
	<!--===============================================================================================-->
	<script src="/plugins/bootstrap/js/popper.js"></script>
	<script src="/plugins/bootstrap/js/bootstrap.min.js"></script>
	<!-- SweetAlert2 -->
	<script src="/plugins/sweetalert2/sweetalert2.js"></script>
	<!--===============================================================================================-->
	<script src="/dist/js/main.js"></script>

	<?php
	if (!empty($showResult)) {
		$result_title = 'Erro!';
		$result_message = 'Aconteceu um problema, tente novamente mais tarde!';
		$result_type = 'warning';

		switch ($showResult) {
			case 'cant_connect':
				$result_title = 'Erro!';
				$result_type = 'error';
				$result_message = 'Não é possível se conectar agora, tente novamente em alguns minutos!';
				break;
			case 'invalid_user_or_pass':
				$result_title = 'Erro!';
				$result_type = 'error';
				$result_message = 'Usuário ou/e senha incorreto(s).';
				break;
			case 'blocked':
				$result_title = 'Erro!';
				$result_type = 'error';
				$result_message = 'Usuário bloqueado, contacte seu revendedor.';
				break;
			case 'insufficient_permission':
				$result_title = 'Erro!';
				$result_type = 'error';
				$result_message = 'Você não tem permissão para acessar o painel office!';
				break;
			case 'password_changed':
				$result_title = 'Feito!';
				$result_type = 'success';
				$result_message = 'Senha alterada com sucesso, conecte-se.';
				break;
			case 'logged_out':
				$result_title = 'Feito!';
				$result_type = 'success';
				$result_message = 'Deslogado com sucesso!';
				break;
			case 'captcha':
				$result_title = 'Erro!';
				$result_type = 'warning';
				$result_message = 'reCAPTCHA falhou! Tente novamente';
				break;
		}
	?>

		<script type="text/javascript">
			window.onload = function ErrorAlert() {
				Swal.fire(
					'<?php echo $result_title; ?>',
					'<?php echo $result_message; ?>',
					'<?php echo $result_type; ?>'
				)
			};
		</script>
	<?php } ?>
</body>

</html>