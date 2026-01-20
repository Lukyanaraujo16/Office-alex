<?php
include_once('./sys/functions.php');
startSession();

if (isset($_SESSION['__l0gg3d_us3r__'])) {
	header('Location: ./dashboard');
	exit();
}

$server_name = getServerProperty('server_name');

if (isset($_POST['email'])) {
	$email = purifyHTML($_POST['email']);
	$result = resetPassword($email);

	switch ($result) {
		case 1:
			header('location: ?result=success');
			exit();
		case 2:
			header('location: ?result=invalid_email');
			exit();
		case 3:
			header('location: ?result=email_not_found');
			exit();
	}
}
?>
<!DOCTYPE html>
<html lang="pt_BR">

<head>
	<title><?php echo $server_name; ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="<?php echo get_logo('big'); ?>" />
	<link rel="stylesheet" type="text/css" href="plugins/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="dist/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="plugins/sweetalert2/sweetalert2.min.css">
	<link rel="stylesheet" type="text/css" href="dist/css/util.css">
	<link rel="stylesheet" type="text/css" href="dist/css/main.css">
	<!-- SweetAlert2 -->

	<script src='https://www.google.com/recaptcha/api.js?hl=pt-BR'></script>
	<script>
		function onSubmit(token) {
			document.getElementById("form-1").submit();
		}
	</script>
</head>

<body>
	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">
				<form class="login100-form validate-form" method="post">
					<!--span class="login100-form-title p-b-26">
						Welcome
					</span-->
					<span class="login100-form-title p-b-10">
						<a href="index.php"><img src="<?php echo get_logo(); ?>" style="max-width: 100%;"></a>
					</span>
					<span class="login100-form-title p-b-36">
						Redefinir Senha
					</span>
					<div class="wrap-input100 validate-input m-b-10" data-validate="E-mail inválido">
						<input class="input100" type="text" name="email" placeholder="Digite seu email cadastrado">
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-at"></i>
						</span>
					</div>
					</br>
					<div class="container-login100-form-btn">
						<div class="wrap-login100-form-btn">
							<div class="login100-form-bgbtn"></div>
							<button class="login100-form-btn">
								Redefinir
							</button>
						</div>
					</div>
					<div class="text-center p-t-115">
						<a class="txt1">
							Enviaremos um email com instruções de como redefinir sua senha.
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div id="dropDownSelect1"></div>
	<!--===============================================================================================-->
	<script src="plugins/jquery/jquery.js"></script>
	<!--===============================================================================================-->
	<script src="plugins/bootstrap/js/popper.js"></script>
	<script src="plugins/bootstrap/js/bootstrap.min.js"></script>
	<!-- SweetAlert2 -->
	<script src="plugins/sweetalert2/sweetalert2.js"></script>
	<!--===============================================================================================-->
	<script src="dist/js/main.js"></script>
	<?php
	if (isset($_GET['result'])) {
		$result = $_GET['result'];
		$result_title = 'Erro!';
		$result_message = 'Aconteceu um problema, tente novamente mais tarde!';
		$result_type = 'error';

		switch ($result) {
			case 'success':
				$result_title = 'Feito!';
				$result_type = 'success';
				$result_message = 'As instruções para você alterar sua senha foram enviadas ao seu e-mail!';
				break;
			case 'invalid_email':
				$result_title = 'Erro!';
				$result_type = 'error';
				$result_message = 'E-mail inválido, verifique se o email esta correto e tente novamente.';
				break;
			case 'email_not_found':
				$result_title = 'Erro!';
				$result_type = 'error';
				$result_message = 'Não existe nenhum usuário cadastrado com este e-mail!';
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