<?php
include_once('./sys/functions.php');
$server_name = getServerProperty('server_name');
$background = getServerProperty('login_background');
startSession();

if (isset($_SESSION['__l0gg3d_us3r__'])) {
	header('Location: ./dashboard');
	exit;
}

if (isset($_SESSION['__l0gg3d_Client__'])) {
	header('Location: ./client_area/dashboard');
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title><?php echo $server_name; ?></title>
	<!-- <link rel="canonical" href="https://office.brtv.me">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-title" content="<?php echo $server_name; ?>">
	<link rel="apple-touch-icon" href="/dist/img/icons/apple-icon-180.png">
	<meta name="theme-color" content="#ff2028">
	<link rel="manifest" href="manifest.json"> -->
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="<?php echo get_logo('big') ?>" />
	<link rel="stylesheet" type="text/css" href="plugins/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="dist/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="plugins/sweetalert2/sweetalert2.min.css">
	<link rel="stylesheet" type="text/css" href="dist/css/util.min.css">
	<link rel="stylesheet" type="text/css" href="dist/css/main.min.css">
	<link rel="stylesheet" type="text/css" href="dist/css/checkbox.min.css">
	<?php
	if (getServerProperty('recaptcha_enable')) { ?>
		<script src='https://www.google.com/recaptcha/api.js?hl=pt-BR' async defer></script>
	<?php } ?>
</head>

<body>
	<script>
		// This is the service worker with the Cache-first network
		// Add this below content to your HTML page, or add the js file to your page at the very top to register service worker
		// Check compatibility for the browser we're running this in
		if ("serviceWorker" in navigator) {
			if (!navigator.serviceWorker.controller) {
				// Register the service worker
				navigator.serviceWorker
					.register("serviceWorker.js", {
						scope: "./"
					})
					.then(function(reg) {
						console.log("[PWA Builder] Service worker has been registered for scope: " + reg.scope);
					});
			}
		}
	</script>
	<div class="limiter">
		<div class="container-login100" style="background-image: url('<?php echo $background ?>');">
			<div class="wrap-login100 p-t-100 p-b-0">
				<form id="form-1" class="login100-form validate-form">
					<div class="login100-form-avatar p-b-30" style="width: 100%; height: 100%; border-radius:0%">
						<img src="<?php echo get_logo('big'); ?>" alt="<?php echo $server_name; ?>">
					</div>
					<div class="g-recaptcha" data-sitekey="<?php echo getServerProperty('recaptcha_site_key'); ?>"></div>
					<div class="wrap-input100 validate-input m-b-10" data-validate="Digite seu usuário">
						<input class="input100" type="text" id="username" name="username" placeholder="Usuário" value="<?php if (!empty($_POST['username'])) {
																																																							echo $_POST['username'];
																																																						} ?>">
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-user"></i>
						</span>
					</div>
					<div class="wrap-input100 validate-input m-b-10" data-validate="Digite sua senha">
						<input class="input100" type="password" id="password" name="password" placeholder="Senha">
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-lock"></i>
						</span>
					</div>
					<div class="container-login100-form-btn p-t-15">
						<button class="login100-form-btn" id="button-login">
							Login
						</button>
					</div>
					<div class="text-center w-full p-t-25 txt1">
						Esqueceu a senha?
						<a href="forget_password.php" class="txt1"> <b>Clique aqui</b>
						</a>
					</div>
					<div class="text-center w-full p-b-30 txt1">
						Painel do cliente >>>
						<a href="/client_area" class="txt1"> <b>Clique aqui</b>
						</a>
					</div>
				</form>
			</div>
			<p class="float-r">
				<?php echo OFFICE_VERSION ?>
			</p>
			<p class="powered">
				Powered by <b><a href="https://playonegestor.com/" target="_blank">PLAYONE TV</a></b>
			</p>
		</div>
	</div>

	<script src="plugins/jquery/jquery.js"></script>
	<script src="plugins/bootstrap/js/popper.js"></script>
	<script src="plugins/bootstrap/js/bootstrap.min.js"></script>
	<!-- SweetAlert2 -->
	<script src="plugins/sweetalert2/sweetalert2.js"></script>
	<script src="dist/js/main.js"></script>
	<script>
		$(document).ready(function() {
			var useRecaptcha = <?php echo getServerProperty('recaptcha_enable') ?>; // Definir se o reCAPTCHA deve ser usado ou não

			$("#form-1").submit(function(event) {
				event.preventDefault();
				var buttonLogin = $("#button-login");

				buttonLogin.text("Aguarde...");
				buttonLogin.css("background-color", "goldenrod");

				var username = $("#username").val();
				var password = $("#password").val();
				if (useRecaptcha) {
					var recaptcha = grecaptcha.getResponse();
					if (!recaptcha) {
						$("#message").text("Please complete the reCAPTCHA challenge.");
						return;
					}
				}
				$.ajax({
					url: "/sys/api.php",
					type: "POST",
					timeout: 20000,
					data: {
						action: "login",
						username: username,
						password: password,
						recaptcha: recaptcha
					},
					success: function(data) {
						data.toString()
						if (data.success === true) {
							window.location.href = "dashboard";
						} else {
							buttonLogin.text("Login");
							buttonLogin.css("background-color", "");
							switch (data.message) {
								case "cant_connect":
									Swal.fire("Erro!", "Não  possível se conectar agora, tente novamente em alguns minutos!", "error")
									break;
								case "invalid_user_or_pass":
									Swal.fire("Aviso!", "Usurio e/ou senha incorreto(s).", "warning")
									break;
								case "empty_user_or_pass":
									Swal.fire("Aviso!", "Usurio e/ou senha em branco", "warning")
									break;
								case "blocked":
									Swal.fire("Aviso!", "Usuário bloqueado, contacte seu revendedor.", "warning")
									break;
								case "insufficient_permission":
									Swal.fire("Aviso!", "Você não tem permissão para acessar o painel Office!", "warning")
									break;
								case "captcha":
									Swal.fire("Aviso!", "reCAPTCHA falhou! Tente novamente.", "warning")
									break;
								case "maintenance":
									Swal.fire({
										title: "Aviso da administração!",
										html: data.text,
										icon: "warning",
										confirmButtonText: data.button_text,
									}).then((result) => {
										if (result.isConfirmed) {
											window.location.href = data.button_link;
										}
									});
									break;
								default:
									Swal.fire("Erro!", "Aconteceu um problema, tente novamente mais tarde!", "error")
							}
						}
					},
					error: function(data) {
						Swal.fire("Erro interno!", "Isso está demorando demais, contate o suporte!", "error")
						buttonLogin.text("Login");
						buttonLogin.css("background-color", "");
					}
				});
			});
		});
	</script>
</body>

</html>