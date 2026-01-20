<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$server_name = getServerProperty('server_name');
$fast_packages = json_decode(getServerProperty('fast_packages'), true);

if (isset($_POST['line_type']) && isset($_POST['date']) && isset($_POST['action'])) {
	$line_type = $_POST['line_type'];
	$date = $_POST['date'];
	$action = $_POST['action'];

	if ($action === 'remove_lines') {
		if (is_array($line_type)) {
			$remove_expired = in_array('expired', $line_type);
			$remove_test = in_array('test', $line_type);

			if (strpos($date, ' - ') !== false) {
				list($start_date) = explode(' - ', $date);
				$start_date .= ' 00:00:00';
				list(, $end_date) = explode(' - ', $date);
				$end_date .= ' 23:59:59';
				$stime = DateTime::createFromFormat('m/d/Y H:i:s', $start_date);
				$etime = DateTime::createFromFormat('m/d/Y H:i:s', $end_date);
				if ($stime && $etime) {
					$start_date = $stime->getTimestamp();
					$end_date = $etime->getTimestamp();

					if (deleteExpiredTestUsersByOwner($logged_user['id'], $remove_expired, $remove_test, $start_date, $end_date)) {
						header('location: ?result=lines_removed');
						exit();
					}
				}
			}
		}
	}

	header('location: ?result=failed');
	exit();
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
	<!-- daterange picker -->
	<link rel="stylesheet" href="/plugins/daterangepicker/daterangepicker.css">
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
						case 'lines_removed':
							$result_message = 'Listas removidas com sucesso';
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
							<h1>Ferramentas</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Ferramentas</li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
				<div class="container-fluid">
					<div class="card card-default">
						<div class="card-header">
							<h3 class="card-title">Excluir listas expiradas/testes</h3>
						</div>
						<form role="form" method="POST" id="remove-lists">
							<input type="hidden" name="action" value="remove_lines">
							<div class="card-body col-md-6">
								<div class="row">
									<div class="form-group">
										<label>Intervalo de data:</label>
										<div class="input-group">
											<div class="input-group-prepend">
												<span class="input-group-text">
													<i class="far fa-calendar-alt"></i>
												</span>
											</div>
											<input type="text" class="form-control float-right" name="date" id="date" autocomplete="off">
										</div>
									</div>
								</div>
							</div>
							<div class="card-body col-sm-6">
								<label>Tipo de lista a excluir:</label>
								<div class="row">
									<div class="form-group clearfix">
										<div class="icheck-danger d-inline">
											<input type="checkbox" name="line_type[]" id="checkbox1" value="test">
											<label for="checkbox1">Testes
											</label>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="form-group clearfix">
										<div class="icheck-danger d-inline">
											<input type="checkbox" name="line_type[]" id="checkbox2" value="expired">
											<label for="checkbox2">Expiradas
											</label>
										</div>
									</div>
								</div>
							</div>

							<div class="card-footer">
								<button type="submit" class="btn btn-danger">Excluir Listas</button>
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
	<!-- InputMask -->
	<script src="/plugins/moment/moment.min.js"></script>
	<script src="/plugins/inputmask/min/jquery.inputmask.bundle.min.js"></script>
	<!-- date-range-picker -->
	<script src="/plugins/daterangepicker/daterangepicker.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.min.js?<?php echo OFFICE_VERSION ?>"></script>
	<!-- Page script -->
	<script>
		$(function() {
			//Date range picker
			$('#date').daterangepicker()
		})
	</script>
</body>

</html>