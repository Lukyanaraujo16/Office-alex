<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();

if (!isAdmin($logged_user) && !isPartner($logged_user) && !isUltra($logged_user) && !isMaster($logged_user)) {
	header('Location: ./index.php');
	exit();
}

$server_name = getServerProperty('server_name');
$fast_packages = json_decode(getServerProperty('fast_packages'), true);
$resellers = isAdmin($logged_user) ? getAllUsers() : getAllResellersByOwnerID($logged_user['id']);

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
	<!-- DataTables -->
	<!-- <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.css"> -->
	<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
	<!-- overlayScrollbars -->
	<link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
	<link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
	<!-- Toastr -->
	<link rel="stylesheet" href="/plugins/toastr/toastr.min.css">
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
							<h1>Revendedores </h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Revendedores </li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
				<div class="container-fluid">
					<div class="card">
						<div class="card-header">
							<h3 class="card-title">Revendedores </h3>
							<div class="card-tools">
								<button type="button" class="btn btn-tool btrefresh"><i class="fas fa-sync-alt"></i></button>
							</div>
						</div>
						<div class="card-body table-responsive">
							<div class="filters">
								<div class="row">
									<div class="col-md-3">
										<div class="form-group" id="search-div">
											<label>Pesquisa</label>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group">
											<label>Revendedor</label>
											<select class="form-control select2 dynamic-resellers" name="reseller" id="select-reseller" style="width: 100%;">
											</select>
										</div>
									</div>

									<div class="col-md-3">
										<div class="form-group">
											<label>Status</label>
											<select class="form-control select2" name="status" id="select-status">
												<option value="">Todos</option>
												<option value="enabled">Ativo</option>
												<option value="disabled">Bloqueado</option>
											</select>
										</div>
									</div>

									<div class="col-md-3">
										<div class="form-group">
											<label>Grupo</label>
											<select class="form-control select2" name="type" id="select-type">
												<option value="">Todos</option>
												<?php
												$group_settings = json_decode(getServerProperty('group_settings'), true);
												$admin_group = getGroupByID($group_settings['admin']);
												$partner_group = getGroupByID($group_settings['partner']);
												$ultra_group = getGroupByID($group_settings['ultra']);
												$master_group = getGroupByID($group_settings['master']);
												$reseller_group = getGroupByID($group_settings['reseller']);

												if (isAdmin($logged_user)) {
													echo '<option value=\'admin\'>' . $admin_group['group_name'] . '</option>';
													if (!$partner_group['group_name'] == "") {
														echo '<option value=\'partner\'>' . $partner_group['group_name'] . '</option>';
													}
													echo '<option value=\'ultra\'>' . $ultra_group['group_name'] . '</option>';
													echo '<option value=\'master\'>' . $master_group['group_name'] . '</option>';
													echo '<option value=\'reseller\'>' . $reseller_group['group_name'] . '</option>';
												} else if (isPartner($logged_user)) {
													echo '<option value=\'ultra\'>' . $ultra_group['group_name'] . '</option>';
													echo '<option value=\'master\'>' . $master_group['group_name'] . '</option>';
													echo '<option value=\'reseller\'>' . $reseller_group['group_name'] . '</option>';
												} else if (isUltra($logged_user)) {
													echo '<option value=\'master\'>' . $master_group['group_name'] . '</option>';
													echo '<option value=\'reseller\'>' . $reseller_group['group_name'] . '</option>';
												} else if (isMaster($logged_user)) {
													echo '<option value=\'reseller\'>' . $reseller_group['group_name'] . '</option>';
												}
												?>
											</select>
										</div>
									</div>
								</div>
							</div>
							<table id="table" class="table table-bordered table-striped table-sm" style="width: 100%!important">
								<thead>
									<tr>
										<th>ID</th>
										<th>Login</th>
										<th>Email</th>
										<th>Grupo</th>
										<th>IP</th>
										<th>Créditos</th>
										<th>Master</th>
										<th>Notas</th>
										<th>Status</th>
										<th>Ações</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
								<tfoot>
									<tr>
										<th>ID</th>
										<th>Login</th>
										<th>Email</th>
										<th>Grupo</th>
										<th>IP</th>
										<th>Créditos</th>
										<th>Master</th>
										<th>Notas</th>
										<th>Status</th>
										<th>Ações</th>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
			</section>
		</div>
		<?php include_once('footer.php'); ?>
	</div>
	<!-- jQuery -->
	<script src="/plugins/jquery/jquery.min.js"></script>
	<!-- Bootbox -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.3.3/bootbox.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- Select2 -->
	<script src="/plugins/select2/js/select2.full.min.js"></script>
	<script scr="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.15.0/popper.min.js"></script>
	<!-- DataTables -->
	<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
	<!-- <script src="/plugins/datatables/jquery.dataTables.js"></script>
	<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script> -->
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- Toastr -->
	<script src="/plugins/toastr/toastr.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.js?<?php echo OFFICE_VERSION ?>"></script>
	<!-- Page script -->
	<!-- <script src="/assets/js/pages/reseller_list.min.js?<?php echo OFFICE_VERSION ?>"></script> -->
	<script src="/assets/js/pages/reseller_list.js?<?php echo OFFICE_VERSION ?>"></script>
</body>

</html>