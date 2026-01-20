<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$server_name = getServerProperty('server_name');
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
	<!-- Select2 -->
	<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
	<link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
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
				<div class="container-fluid">
					<div class="row mb-2">
						<div class="col-sm-6">
							<h1>Criar Regra</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
								<li class="breadcrumb-item"><a href="/chatbot/list">ChatBot</a></li>
								<li class="breadcrumb-item active">Criar Regra</li>
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
							<h3 class="card-title">Informações da Regra</h3>
						</div>
						<!-- /.card-header -->
						<form autocomplete="off" name="frm1">
							<div class="card-body">
								<div class="row col-lg-6 col-md-12">
									<div class="col-sm-6">
										<div class="form-group">
											<label>Tipo da Regra</label>
											<select id="rule_type" name="rule_type" class="form-control select2bs4 required-input" style="width: 100%;">
												<option value="">Selecione o tipo</option>

												<option value="equals">Igual</option>
												<option value="contains">Contém</option>
											</select>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="form-group">
											<label>Ação da Regra</label>
											<select id="rule_action" name="rule_action" class="form-control select2bs4 required-input" style="width: 100%;">
												<option value="">Selecione uma ação</option>
												<option value="text">Enviar Texto</option>
												<?php if ($permission['iptv']) { ?>
													<option value="test_iptv">Enviar Teste IPTV</option>
												<?php }
												if ($permission['binstream']) { ?>
													<option value="test_binstream">Enviar Teste Binstream</option>
												<?php }
												if ($permission['codes']) { ?>
													<option value="test_code">Enviar Teste Código</option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="form-group col-md-12">
										<label>Mensagen</label>
										<div class="input-group mb-6">
											<input type="text" required="" autocomplete="off" name="message" id="message" class="form-control" placeholder="">
											<div class="input-group-append">
												<button type="button" class="btn btn-info addmessage">Adicionar</button>
											</div>
										</div>
										<p><small class="text-muted">Essa é a mensagem que irá disparar essa regra</small></p>
									</div>
									<div class="form-group col-md-12">
										<label>Mensagens:</label>
										<table id="message_list" class="table table-sm">
											<tbody>
											</tbody>
										</table>
									</div>
									<div class="form-group col-md-12">
										<label>Resposta</label>
										<textarea class="form-control required-input" rows="6" id="response" name="response" placeholder=""></textarea>
									</div>
								</div>
							</div>
							<textarea class="d-none" id="server_template_iptv"><?php echo getServerProperty('default_test_template_iptv', ""); ?></textarea>
							<textarea class="d-none" id="server_template_binstream"><?php echo getServerProperty('default_test_template_p2p', ""); ?></textarea>
							<textarea class="d-none" id="server_template_code"><?php echo getServerProperty('default_test_template_code', ""); ?></textarea>
							<div class="card-footer">
								<a class="btn btn-primary addrule">Criar Regra</a>
							</div>
						</form>
					</div>
				</div>
			</section>
		</div>
		<?php include_once('footer.php'); ?>
	</div>
	<!-- jQuery -->
	<script src="/plugins/jquery/jquery.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- Select2 -->
	<script src="/plugins/select2/js/select2.full.min.js"></script>
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- SweetAlert2 -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.min.js?<?php echo OFFICE_VERSION ?>"></script>
	<!-- Page script -->
	<script src="/assets/js/pages/chatbot_create.min.js?<?php echo OFFICE_VERSION ?>"></script>
</body>

</html>