<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();

if (!hasPermissionResource($logged_user['id'], "iptv") || !getServerProperty('iptv_migration_status', 1)) {
	header('location: /dashboard');
	exit();
}

$logged_user_id = intval($_SESSION['__l0gg3d_us3r__']);
$server_name = getServerProperty('server_name');

if (isset($_POST['m3u_link']) && isset($_POST['verify_list'])) {

	$result = Verify_m3u($_POST['m3u_link']);
	if (!isset($result['error'])) {
		$exibe = true;
	} else {
		header('location: ?result=' . $result['error']);
		exit();
	}
}
if (isset($_POST['m3u_link']) && isset($_POST['import_client'])) {
	$result = Verify_m3u($_POST['m3u_link']);
	if (!isset($result['error'])) {
		$package = getPackageByID(getServerProperty("fast_test_package"));
		$bouquets = $package['bouquets'];

		if (getServerProperty('iptv_migration_fee', 1)) {
			$addOrRemoveCredits = addOrRemoveCredits($logged_user['id'], - ($result['credits']));
		} else {
			$addOrRemoveCredits = true;
		}
		if ($addOrRemoveCredits) {
			$insert_result = insertClient($logged_user_id, $result['user_info']['username'], $result['user_info']['password'], "", "", $result['user_info']['exp_date'], "", "", $bouquets, $result['user_info']['max_connections'], 0);
			if ($insert_result) {
				$old_credits = $logged_user['credits'];
				$logged_user = getLoggedUser();
				$now_credits = $logged_user['credits'];
				insertRegUserLog($logged_user['id'], $result['user_info']['username'], $result['user_info']['password'], '<b>Cliente Migrado</b> | Pacote: ' . $package['package_name'] . ' | Créditos: <font color="green">' . $old_credits . '</font> > <font color="red">' . $now_credits . '</font> | Custo: 1 Crédito');
				header('location: /iptv/clients/show/' . $insert_result);
				exit();
			}
		}
	} else {
		header('location: ?result=' . $result['error']);
		exit();
	}
}

$automatic_test_packages = (isset($settings['automatic_test_packages']) ? json_decode($settings['automatic_test_packages'], true) : array());

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
						case 'template_saved':
							$result_message = 'Templates salvos com sucesso.';
							$result_type = 'success';
							break;
						case 'many_days':
							$result_message = 'Linha com validade superior a 29 dias.';
							$result_type = 'warning';
							break;
						case 'invalid_url':
							$result_message = 'O Link inserido é inválido!';
							$result_type = 'warning';
							break;
						case 'is_trial':
							$result_message = 'A linha não pode ser um teste!';
							$result_type = 'warning';
							break;
						case 'client_exist':
							$result_message = 'Já existe uma linha com esse nome de usuário!';
							$result_type = 'warning';
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
							<h1>Migrar clientes <small>Em breve migração em massa</small></h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item">Clientes</li>
								<li class="breadcrumb-item active">Migrar</li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
				<div class="container-fluid">
					<div class="card card-default">
						<div class="card-header">
							<h3 class="card-title">Migrar clientes</h3>
						</div>
						<form autocomplete="off" action="#" method="post">
							<div class="row">
								<?php if (isset($exibe) and ($exibe)) { ?>
									<div class="col-md-6">
										<div class="col-md-12">
											<div class="card-body col-md-12">
												<div class="callout callout-info table-responsive">
													<form autocomplete="off" action="#" method="post">
														<input type="hidden" name="m3u_link" value="<?php echo $_POST['m3u_link'] ?>">
														<h5><strong>Informações da Lista</strong></h5><br>
														<table class="table table-hover table-sm">
															<tbody>
																<tr>
																	<td><strong>Servidor</strong></td>
																	<td><?php echo $result['server_info']['url'] ?></td>
																</tr>
																<tr>
																	<td><strong>Usuário</strong></td>
																	<td><?php echo $result['user_info']['username'] ?></td>
																</tr>
																<tr>
																	<td><strong>Senha</strong></td>
																	<td><?php echo $result['user_info']['password'] ?></td>
																</tr>
																<tr>
																	<td><strong>Conexões Simultâneas</strong></td>
																	<td><?php echo $result['user_info']['max_connections'] ?></td>
																</tr>
																<tr>
																	<td><strong>Dias Restantes</strong></td>
																	<td><?php echo $result['DaysToExpire'] ?></td>
																</tr>
																<tr>
																	<td><strong>Custo de Importação</strong></td>
																	<td><?php echo $result['credits'] ?></td>
																</tr>
															</tbody>
														</table>
														<div class="card-body text-center">
															<button type="submit" name="import_client" class="btn btn-info">Importar Cliente</button>
														</div>
													</form>
												</div>
											</div>
										</div>
									</div>
								<?php } else { ?>
									<div class="col-md-12">
										<div class="card-body col-md-12">
											<div class="callout callout-info">
												<form autocomplete="off" action="migrate_client.php" method="post">
													<p><strong> Use essa ferramenta para importar listas de outros servidores.</strong></br>
														O custo da importação é relativo a quantidade de dias de validade.</p><br>
													<ul>
														<li>A lista não pode ser encurtada.</li>
														<!-- <li>Não ter mais de 29 dias de validade.</li> -->
														<li>Não ser teste.</li>
														<li>Não ser bloqueada.</li>
													</ul>
													<div class="card-body">
														<label>Lista M3U do Cliente</label>
														<div class="input-group mb-3">
															<div class="input-group-prepend">
																<span class="input-group-text"><i class="fas fa-link"></i></span>
															</div>
															<input type="text" required="" autocomplete="off" name="m3u_link" data-minlength="6" minlength="6" class="form-control" placeholder="Lista M3U">
														</div>
													</div>
													<div class="form-group text-center">
														<button type="submit" name="verify_list" class="btn btn-info">Verificar Lista</button>
													</div>
												</form>
											</div>
										</div>
									</div>
								<?php } ?>
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
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.min.js?<?php echo OFFICE_VERSION ?>"></script>
</body>

</html>