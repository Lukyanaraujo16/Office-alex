<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$server_name = getServerProperty('server_name');
$fast_packages = json_decode(getServerProperty('fast_packages'), true);

if (isset($_POST['title']) && isset($_POST['message'])) {
	$title = purifyHTML($_POST['title']);
	$message = purifyHTML($_POST['message']);
	$reseller_id = (isset($_POST['reseller']) ? purifyHTML($_POST['reseller']) : '');
	if ((strlen($title) < 6) || (255 < strlen($title))) {
		header('location: ?result=invalid_title');
		exit();
	}

	if ((strlen($message) < 6) || (1000 < strlen($message))) {
		header('location: ?result=invalid_message');
		exit();
	}

	$ticket_id = createTicket($logged_user, $reseller_id, $title, $message);

	if ($ticket_id !== false) {
		header('location: /ticket/view/' . $ticket_id . '?result=success');
		exit();
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
	<!-- iCheck for checkboxes and radio inputs -->
	<link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
	<!-- overlayScrollbars -->
	<link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
	<link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
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
							$result_message = 'O ticket foi criado com sucesso.';
							$result_type = 'success';
							break;
						case 'invalid_title':
							$result_message = 'O titulo escolhido é invalido!, deve ter no mínimo 6 caracteres e no máximo 255.';
							break;
						case 'invalid_message':
							$result_message = 'A mensagem escolhida é invalida!, deve ter no mínimo 6 caracteres e no máximo 1000.';
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
							<h1>Criar Ticket</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Criar Ticket</li>
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
							<h3 class="card-title">Informações do Ticket</h3>
						</div>
						<!-- /.card-header -->
						<form autocomplete="off" action="#" method="post" name="frm1">
							<div class="card-body">
								<div class="row col-lg-6 col-md-12">
									<?php if (isAdmin($logged_user)) { ?>
										<div class="col-sm-12">
											<div class="form-group">
												<label>Destinatário</label>
												<select name="reseller" required="" autocomplete="off" class="form-control select2bs4" style="width: 100%;">
													<option value="">SELECIONE O DESTINATÁRIO</option>
													<?php
													$all_users = getAllUsers();
													foreach ($all_users as $user) { ?>
														<option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
													<?php } ?>
												</select>
											</div>
										</div>
									<?php } ?>
									<div class="form-group col-md-12">
										<label>Titulo do Ticket</label>
										<div class="input-group mb-3">
											<input type="text" required="" autocomplete="off" name="title" data-minlength="6" minlength="6" class="form-control" placeholder="Titulo do Ticket">
										</div>
									</div>
									<div class="form-group col-md-12">
										<label>Mensagem</label>
										<textarea class="form-control" rows="3" value="" name="message" data-maxlength="1000" maxlength="1000" placeholder="Informações Detalhadas..."></textarea>
									</div>

								</div>
							</div>
							<div class="card-footer">
								<button type="submit" class="btn btn-primary">Criar Ticket</button>
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
	<!-- InputMask -->
	<script src="/plugins/moment/moment.min.js"></script>
	<script src="/plugins/inputmask/min/jquery.inputmask.bundle.min.js"></script>
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.min.js?<?php echo OFFICE_VERSION ?>"></script>
	<!-- Page script -->
	<script>
		$(function() {
			//Initialize Select2 Elements
			$('.select2bs4').select2({
				theme: 'bootstrap4'
			})
			//Initialize InputMask Elements
			$('[data-mask]').inputmask()
		})
	</script>
</body>

</html>