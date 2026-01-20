<?php
include_once('functions.php');
startSession();

if (isset($_SESSION['__logged_user__'])) {
	header('Location: ./dashboard.php');
	exit();
}

if ((isset($_POST['username']) && isset($_POST['password']))) {

	if (!empty($_POST['username']) || !empty($_POST['password'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];
	} elseif (!empty($_GET['username']) || !empty($_GET['password'])) {
		$username = $_GET['username'];
		$password = $_GET['password'];
	}

	$result = loginAdmin($username, $password);


	switch ($result) {
		case 1:
			header('location: ./dashboard.php');
			exit();
		case 2:
			header('location: ?result=cant_connect');
			exit();
		case 3:
			header('location: ?result=invalid_user_or_pass');
			exit();
		case 4:
			header('location: ?result=blocked');
			exit();
		case 5:
			header('location: ?result=insufficient_permission');
			exit();
		case 6:
			header('location: ?result=captcha');
			exit();
	}
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Administração Office</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../plugins/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="../dist/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="../plugins/sweetalert2/sweetalert2.min.css">
	<link rel="stylesheet" type="text/css" href="../dist/css/util.css">
	<link rel="stylesheet" type="text/css" href="../dist/css/main.css">

</head>

<body>
	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100 p-t-30 p-b-30">
				<form action="index.php" method="post" id="form-1" class="login100-form validate-form">
					<div class="login100-form-avatar p-b-30" style="width: 400px; height: 180px; border-radius:0%">
						<img src="../dist/img/logo_big.png" alt="logo">
					</div>
					<!--span class="login100-form-title p-t-20 p-b-45">
						<strong>hostmk</strong>
					</span-->
					<div class="wrap-input100 validate-input m-b-10" data-validate="Digite seu usuário">
						<input class="input100" type="text" name="username" placeholder="Usuário">
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
					<div class="container-login100-form-btn p-t-10">
						<button class="login100-form-btn " data-callback='onSubmit'>
							Login
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!--===============================================================================================-->
	<script src="../plugins/jquery/jquery.js"></script>
	<!--===============================================================================================-->
	<script src="../plugins/bootstrap/js/popper.js"></script>
	<script src="../plugins/bootstrap/js/bootstrap.min.js"></script>
	<!-- SweetAlert2 -->
	<script src="../plugins/sweetalert2/sweetalert2.js"></script>
	<!--===============================================================================================-->
	<script src="../dist/js/main.js"></script>

	<?php
	if (isset($_GET['result'])) {
		$result = $_GET['result'];
		$result_title = 'Erro!';
		$result_message = 'Aconteceu um problema, tente novamente mais tarde!';
		$result_type = 'warning';

		switch ($result) {
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