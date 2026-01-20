<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$server_name = getServerProperty('server_name');
$fast_packages = json_decode(getServerProperty('fast_packages'), true);

if (isset($_POST['save_email_messages']) && isset($_POST['auto_test_subject']) && isset($_POST['auto_test_message'])) {
	$custom_template = (isset($_POST['custom_template']) ? 1 : 0);
	deleteUserProperty($logged_user["id"], 'custom_template');
	$result1 = addUserProperty($logged_user["id"], 'custom_template', $custom_template);

	$email_messages = $_POST;
	unset($email_messages['save_email_messages']);
	$email_messages = json_encode($email_messages);

	deleteUserProperty($logged_user['id'], 'email_messages');
	$result2 = addUserProperty($logged_user['id'], 'email_messages', $email_messages);

	if ($result1 && $result2) {
		if (!$custom_template) {
			header('location: ?email&result=email_messages_off');
			exit();
		}
		header('location: ?email&result=email_messages_saved');
		exit();
	}

	header('location: ?email&result=failed');
	exit();
}
$email_messages = json_decode(getUserProperty($logged_user['id'], 'email_messages', "", true), true);

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
	<!-- iCheck for checkboxes and radio inputs -->
	<link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
	<!-- summernote -->
	<link rel="stylesheet" href="/plugins/summernote/summernote-bs4.css">
	<!-- overlayScrollbars -->
	<link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
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
						case 'email_messages_saved':
							$result_message = 'Templates de e-mail salvos com sucesso.';
							$result_type = 'success';
							break;
						case 'email_messages_off':
							$result_message = 'Templates personalizados desativados.';
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
							<h1>E-mail</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">E-mail</li>
								<li class="breadcrumb-item active">SMTP</li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
				<div class="container-fluid">
					<div class="card card-default">
						<form autocomplete="off" action="#" method="post">
							<div class="card-header">
								<h3 class="card-title">Templates de E-mail</h3>
							</div>
							<div class="card-body pad">
								<div class="row">
									<div class="col-md-12">
										<div class="form-group">

											<div class="custom-control custom_template custom-checkbox">
												<input class="custom-control-input" name="custom_template" type="checkbox" id="custom_template" <?php if (getUserProperty($logged_user['id'], 'custom_template')) {
																																																													echo 'checked';
																																																												} ?>>
												<label for="custom_template" class="custom-control-label">Usar templates personalizados</label>
											</div>
										</div>
									</div>
									<div class="all-fiels col-12" style="display:none">
										<div class="row">
											<div class="col-md-6">
												<div class="callout callout-info">
													<div class="col-md-12">
														<h5>Template de teste automático <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#modal-var-auto-testuser">Ver variáveis disponíveis</button></h5>
														<label>Assunto</label>
														<div class="form-group">
															<input type="text" class="form-control" value="<?php echo $email_messages['auto_test_subject']; ?>" data-minlength="1" minlength="1" autocomplete="off" id="auto_test_subject" name="auto_test_subject" placeholder="Assunto do e-mail">
														</div>
													</div>
													<div class="col-md-12 mb-3">
														<label>Mensagem de teste automático </label>
														<textarea class="textarea" id="auto_test_message" name="auto_test_message" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;">
														<?php echo $email_messages['auto_test_message']; ?>
													</textarea>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="callout callout-info">
													<div class="col-md-12">
														<h5>Template de teste automático (Código) <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#modal-var-auto-testcode">Ver variáveis disponíveis</button></h5>
														<label>Assunto</label>
														<div class="form-group">
															<input type="text" class="form-control" value="<?php echo $email_messages['auto_test_subject_code']; ?>" data-minlength="1" minlength="1" autocomplete="off" id="auto_test_subject_code" name="auto_test_subject_code" placeholder="Assunto do e-mail">
														</div>
													</div>
													<div class="col-md-12 mb-3">
														<label>Mensagem de teste automático</label>
														<textarea class="textarea" id="auto_test_message_code" name="auto_test_message_code" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;">
														<?php echo $email_messages['auto_test_message_code']; ?>
													</textarea>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="callout callout-info">
													<div class="col-md-12">
														<h5>Template de teste automático (Código) <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#modal-var-auto-testcode">Ver variáveis disponíveis</button></h5>
														<label>Assunto</label>
														<div class="form-group">
															<input type="text" class="form-control" value="<?php echo $email_messages['auto_test_subject_code']; ?>" data-minlength="1" minlength="1" autocomplete="off" id="auto_test_subject_code" name="auto_test_subject_code" placeholder="Assunto do e-mail">
														</div>
													</div>
													<div class="col-md-12 mb-3">
														<label>Mensagem de teste automático</label>
														<textarea class="textarea" id="auto_test_message_code" name="auto_test_message_code" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;">
														<?php echo $email_messages['auto_test_message_code']; ?>
													</textarea>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="callout callout-info">
													<div class="col-md-12">
														<h5>Template de clientes próximo do vencimento <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#modal-var-expiring-message">Ver variáveis disponíveis</button></h5>
														<label>Assunto</label>
														<div class="form-group">
															<input type="text" class="form-control" value="<?php echo $email_messages['expiring_subject']; ?>" data-minlength="1" minlength="1" autocomplete="off" id="expiring_subject" name="expiring_subject" placeholder="Assunto do e-mail">
														</div>
													</div>
													<div class="col-md-12 mb-3">
														<label>Mensagem de teste automático</label>
														<textarea class="textarea" id="expiring_message" name="expiring_message" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;">
														<?php echo $email_messages['expiring_message']; ?>
													</textarea>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="card-footer">
								<button type="submit" name="save_email_messages" class="btn btn-primary">Salvar</button>
							</div>
						</form>
					</div>
				</div>
			</section>
		</div>
		<div class="modal fade" id="modal-var-auto-testuser">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Variáveis para Substituição</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<p>Use as variáveis abaixo para inserir as informações como usuário e senha do teste gerado</p><br>
						<table class="table table-bordered">
							<thead>
								<tr>
									<!--th style="width: 10px">#</th-->
									<th>Variável</th>
									<th>Informação inserida</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><strong>#username#</strong></td>
									<td>Usuário do teste criado</td>
								</tr>
								<tr>
									<td><strong>#password#</strong></td>
									<td>Senha do teste criado</td>
								</tr>
								<tr>
									<td><strong>#m3u_link#</strong></td>
									<td>Link m3u do teste</td>
								</tr>
								<tr>
									<td><strong>#ssiptv_link#</strong></td>
									<td>Link SSIPTV do teste</td>
								</tr>
								<tr>
									<td><strong>#duration#</strong></td>
									<td>Tempo de duração do teste</td>
								</tr>
								<tr>
									<td><strong>#server_name#</strong></td>
									<td>Nome do servidor</td>
								</tr>
								<tr>
									<td><strong>#reseller_email#</strong></td>
									<td>E-mail do revendedor</td>
								</tr>
								<tr>
									<td><strong>#whatsapp#</strong></td>
									<td>WhatsApp do revendedor</td>
								</tr>
								<tr>
									<td><strong>#telegram#</strong></td>
									<td>Telegram do revendedor</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="modal-footer justify-content-between">
						<button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button>
					</div>
				</div>
				<!-- /.modal-content -->
			</div>
			<!-- /.modal-dialog -->
		</div>
		<div class="modal fade" id="modal-var-auto-testcode">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Variáveis para Substituição</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<p>Use as variáveis abaixo para inserir as informações como código do teste gerado</p><br>
						<table class="table table-bordered">
							<thead>
								<tr>
									<!--th style="width: 10px">#</th-->
									<th>Variável</th>
									<th>Informação inserida</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><strong>#usercode#</strong></td>
									<td>Código do teste criado</td>
								</tr>
								<tr>
									<td><strong>#duration#</strong></td>
									<td>Tempo de duração do teste</td>
								</tr>
								<tr>
									<td><strong>#server_name#</strong></td>
									<td>Nome do servidor</td>
								</tr>
								<tr>
									<td><strong>#reseller_email#</strong></td>
									<td>E-mail do revendedor</td>
								</tr>
								<tr>
									<td><strong>#whatsapp#</strong></td>
									<td>WhatsApp do revendedor</td>
								</tr>
								<tr>
									<td><strong>#telegram#</strong></td>
									<td>Telegram do revendedor</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="modal-footer justify-content-between">
						<button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button>
					</div>
				</div>
				<!-- /.modal-content -->
			</div>
			<!-- /.modal-dialog -->
		</div>
		<div class="modal fade" id="modal-var-expiring-message">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Variáveis para Substituição</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<p>Use as variáveis abaixo para inserir as informações como usuário que está próximo do vencimento</p><br>
						<table class="table table-bordered">
							<thead>
								<tr>
									<!--th style="width: 10px">#</th-->
									<th>Variável</th>
									<th>Informação inserida</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><strong>#username#</strong></td>
									<td>Usuário a vencer</td>
								</tr>
								<tr>
									<td><strong>#exp_date#</strong></td>
									<td>Data que o cliente expira (dd/mm/aaaa)</td>
								</tr>
								<tr>
									<td><strong>#exp_info#</strong></td>
									<td>Informação de quando a linha expira, exemplos:<br> expira em 3 dias<br> expira em 1 dia<br> expirou a 3 dias</td>
								</tr>
								<tr>
									<td><strong>#client_email#</strong></td>
									<td>E-mail do cliente</td>
								</tr>
								<tr>
									<td><strong>#reseller_email#</strong></td>
									<td>E-mail do revendedor</td>
								</tr>
								<tr>
									<td><strong>#whatsapp#</strong></td>
									<td>WhatsApp do revendedor</td>
								</tr>
								<tr>
									<td><strong>#telegram#</strong></td>
									<td>Telegram do revendedor</td>
								</tr>
								<tr>
									<td><strong>#server_name#</strong></td>
									<td>Nome do servidor</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="modal-footer justify-content-between">
						<button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button>
					</div>
				</div>
				<!-- /.modal-content -->
			</div>
			<!-- /.modal-dialog -->
		</div>
		<?php include_once('footer.php'); ?>
	</div>
	<!-- jQuery -->
	<script src="/plugins/jquery/jquery.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- Select2 -->
	<script src="/plugins/select2/js/select2.full.min.js"></script>
	<!-- InputMask -->
	<script src="/plugins/moment/moment.min.js"></script>
	<script src="/plugins/inputmask/min/jquery.inputmask.bundle.min.js"></script>
	<!-- Summernote -->
	<script src="/plugins/summernote/summernote-bs4.min.js"></script>
	<script src="/plugins/summernote/lang/summernote-pt-BR.js"></script>
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.min.js?<?php echo OFFICE_VERSION ?>"></script>
	<!-- Page script -->
	<script>
		$("#custom_template").on("change", function(e) {
			const $container = $(".all-fiels")

			if ($('#custom_template').prop('checked')) {
				$container.show();
			} else {
				$container.hide();
			}
		});
		//Initialize Summernote
		$('.textarea').summernote({
			lang: 'pt-BR'
		})
	</script>
	<?php if (getUserProperty($logged_user['id'], 'custom_template')) { ?>
		<script>
			const $container = $(".all-fiels")
			$container.show();
		</script>
	<?php } ?>
</body>

</html>