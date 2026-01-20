<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$server_name = getServerProperty('server_name');
$fast_packages = json_decode(getServerProperty('fast_packages'), true);
$plans = getUserProperty($logged_user['id'], "client_area_plans", "", true);

if (substr_count($plans, 'plan') < 6) {
	deleteUserProperty($logged_user["id"], 'client_area_plans');
	addUserProperty($logged_user['id'], 'client_area_plans', '[{"id":"plan1","name":"1 Mês","price":"0", "duration":"1"},{"id":"plan2","name":"2 Meses","price":"0", "duration":"2"},{"id":"plan3","name":"3 Meses","price":"0", "duration":"3"},{"id":"plan6","name":"6 Meses","price":"0", "duration":"6"},{"id":"plan12","name":"1 Ano","price":"0", "duration":"12"},{"id":"plan24","name":"2 Anos","price":"0", "duration":"24"}]');
	header('location: ?result=plans_error');
	exit();
} else {
	$plans = json_decode($plans, true);
}

if (isset($_POST['client_informations'])) {

	$client_area = (isset($_POST['client_area']) ? 1 : 0);
	deleteUserProperty($logged_user["id"], 'client_area');
	$result1 = addUserProperty($logged_user["id"], 'client_area', $client_area);

	$client_informations = $_POST['client_informations'];
	deleteUserProperty($logged_user["id"], 'client_informations');
	$result2 = addUserProperty($logged_user["id"], 'client_informations', $client_informations);

	if ($result1 && $result2) {
		header('location: ?result=client_informations_saved');
		exit();
	}

	header('location: ?info&result=failed');
	exit();
}
if (isset($_POST['addplan']) && isset($_POST['price']) && isset($_POST['plan'])) {

	$plans = getUserPropertyDecode($logged_user['id'], "client_area_plans");
	$new_plans = [];
	foreach ($plans as $plan) {
		if ($plan['id'] == $_POST['plan']) {
			$plan['price'] = $_POST['price'];
		}
		$new_plans[] = ['id' => $plan['id'], 'name' => $plan['name'], 'price' => $plan['price'], 'duration' => $plan['duration']];
	}
	$result = updateUserProperty($logged_user['id'], 'client_area_plans', json_encode($new_plans));

	if ($result) {
		header('location: ?result=plan_added');
		exit();
	}

	header('location: ?info&result=failed');
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
	<link rel="stylesheet" href="plugins/fontawesome-pro/css/all.min.css">
	<!-- Ionicons -->
	<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<!-- iCheck for checkboxes and radio inputs -->
	<link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="plugins/select2/css/select2.min.css">
	<link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
	<!-- overlayScrollbars -->
	<link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
	<!-- summernote -->
	<link rel="stylesheet" href="plugins/summernote/summernote-bs4.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="dist/css/adminlte.min.css">
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
						case 'client_informations_saved':
							$result_message = 'Configurações salvas!';
							$result_type = 'success';
							break;
						case 'plan_deleted':
							$result_message = 'Plano Deletado!';
							$result_type = 'success';
							break;
						case 'plan_added':
							$result_message = 'Plano Adicionado!';
							$result_type = 'success';
							break;
						case 'plan_cant_del':
							$result_message = 'Não foi possível apagar esse plano!';
							$result_type = 'error';
							break;
						case 'plans_error':
							$result_message = 'Foi encontrado um erro nos planos e eles foram redefinidos!';
							$result_type = 'info';
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
							<h1>Área do Cliente</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Área do Cliente</li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-6">
							<div class="card card-default">
								<div class="card-header">
									<h3 class="card-title">Área do Cliente</h3>
								</div>
								<form autocomplete="off" action="#" method="post">
									<div class="card-body">
										<div class="row">
											<div class="input-group">
												<div class="col-md-12">
													<div class="form-group">

														<div class="custom-control custom-checkbox">
															<input class="custom-control-input" name="client_area" type="checkbox" id="client_area" <?php if (getUserProperty($logged_user['id'], 'client_area')) {
																																																												echo 'checked';
																																																											} ?>>
															<label for="client_area" class="custom-control-label">Ativar Área do Cliente</label>
														</div>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<p class="text-sm mb-0">
															<b>Escreva informações importantes e úteis para serem exibidas na área do cliente</b>
														</p>
														<br>
														<div class="mb-3">
															<textarea class="textarea" id="client_informations" name="client_informations" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;">
																<?php echo getUserProperty($logged_user['id'], "client_informations"); ?>
														</textarea>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="card-footer text-center">
										<button type="submit" class="btn btn-info font-weight-bold">Salvar Informações</button>
									</div>
								</form>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card card-default">
								<div class="card-header">
									<h3 class="card-title">Planos</h3>
								</div>
								<div class="card-body">
									<div class="row">
										<form autocomplete="off" action="#" method="post">
											<div class="input-group">
												<div class="col-md-12">
													<div class="row">
														<div class="col-4">
															<div class="form-group">
																<label>Plano</label>
																<select class="form-control select2bs4" required="" autocomplete="off" id="plan" name="plan">
																	<option value="">Selecione</option>
																	<?php
																	foreach ($plans as $plan) {
																		if ($plan['price'] == 0) {
																			echo '<option value="' . $plan['id'] . '">' . $plan['name'] . '</option>';
																		} else {
																			echo '<option disabled value="' . $plan['id'] . '">' . $plan['name'] . ' (Ativo)</option>';
																		}
																	}
																	?>
																</select>
															</div>
														</div>
														<div class="col-4">
															<div class="form-group">
																<label>Valor</label>
																<input type="number" class="form-control" required="" autocomplete="off" id="price" name="price" placeholder="Valor do plano">
															</div>
														</div>
														<div class="col-4">
															<label style="opacity: 0">Adicionar</label>
															<button type="submit" name="addplan" class="btn btn-info btn-block font-weight-bold">Criar Plano</button>
														</div>
													</div>
												</div>
												<div class="col-md-12 pt-4">
													<p class="text-center">
														<strong>Planos Ativos</strong>
													</p>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<div class="table-responsive-md">
															<table id="table" class="table table-hover table-bordered text-nowrap table-sm" role="grid">
																<thead>
																	<tr>
																		<th style="text-align: center">Nome do Plano</th>
																		<th style="text-align: center">Valor</th>
																		<th style="text-align: center">Ações</th>
																	</tr>
																</thead>
																<tbody>
																	<?php

																	foreach ($plans as $plan) {
																		if ($plan['price'] != 0) {
																	?>
																			<tr>
																				<td class="align-middle text-center"><?php echo $plan["name"]; ?></td>
																				<td class="align-middle text-center"><?php echo "R$ " . $plan['price']; ?></td>
																				<td class="align-middle text-center"><?php echo '<a href="#" class="btn btn-sm btn-icon text-red btdelete" data-toggle="tooltip" data-original-title="Deletar Plano" data-id="' . $plan['id'] . '" data-text="Deletar o plano: ' . $plan['name'] . '">
				        																			<i class="far fa-trash-alt" aria-hidden="true" style="font-size: 16px"></i>
				    																				</a>' ?>
																				</td>

																			</tr>
																	<?php }
																	} ?>
																</tbody>
																<tfoot>
																</tfoot>
															</table>
														</div>
													</div>
												</div>
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
		<?php include_once('footer.php'); ?>
	</div>
	<!-- jQuery -->
	<script src="plugins/jquery/jquery.min.js"></script>
	<!-- Bootbox -->
	<script src="bower_components/bootbox.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- Select2 -->
	<script src="plugins/select2/js/select2.full.min.js"></script>
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- Summernote -->
	<script src="plugins/summernote/summernote-bs4.min.js"></script>
	<script src="plugins/summernote/lang/summernote-pt-BR.js"></script>
	<!-- Select2 -->
	<script src="plugins/select2/js/select2.full.min.js"></script>
	<!-- AdminLTE App -->
	<script src="dist/js/adminlte.min.js"></script>
	<!-- Page script -->
	<script>
		//Initialize Select2 Elements
		$('.select2bs4').select2({
			theme: 'bootstrap4'
		})
		//Initialize Summernote
		$('.textarea').summernote({
			lang: 'pt-BR'
		})
		/* DELETAR */
		$(document).on('click', '.btdelete', function(e) {
			e.preventDefault();
			const id = $(this).data("id");
			bootbox.dialog({
				title: "Tem certeza que deseja deletar este plano?",
				message: "<p>" + $(this).data("text") + "</p>",
				buttons: {
					cancel: {
						label: "Cancelar",
						className: 'btn-secondary',
						callback: function() {}
					},
					noclose: {
						label: "Confirmar",
						className: 'btn-danger btndelete',
						callback: function() {
							$('.btndelete').hide();
							$.get('./sys/api.php?action=delete_client_plan&plan_id=' + id, function(data) {
								if (data.result === 'success') {
									window.location.href = "?result=plan_deleted";
								} else {
									window.location.href = "?result=plan_cant_del";
								}
							}, "json");
						}
					},
				}
			});
		});
	</script>
</body>

</html>