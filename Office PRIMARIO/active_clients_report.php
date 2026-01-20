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
$ActiveClients = getActiveClientsTree($logged_user['id']);
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
	<link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.css">
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
							<h1>Clientes Ativos </h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Relatórios </li>
								<li class="breadcrumb-item active">Clientes Ativos </li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-4 col-6">
							<!-- small card -->
							<div class="small-box bg-info">
								<div class="inner">
									<h3><?php echo number_format($ActiveClients['total_clients'], 0, ',', '.'); ?></h3>
									<p>Total de Clientes</p>
								</div>
								<div class="icon">
									<i class="fas fa-user"></i>
								</div>
							</div>
						</div>
						<!-- ./col -->
						<div class="col-lg-4 col-6">
							<!-- small card -->
							<div class="small-box bg-success">
								<div class="inner">
									<h3><?php echo number_format($ActiveClients['total_conns'], 0, ',', '.'); ?></h3>
									<p>Total de Conexões</p>
								</div>
								<div class="icon">
									<i class="fas fa-user-plus"></i>
								</div>
							</div>
						</div>
						<!-- ./col -->
						<div class="col-lg-4 col-6">
							<!-- small card -->
							<div class="small-box bg-secondary">
								<div class="inner">
									<h3><?php echo "R$" . number_format($ActiveClients['estimated_value'], 0, ',', '.'); ?></h3>
									<p>Valor Estimado</p>
								</div>
								<div class="icon">
									<i class="fas fa-dollar-sign"></i>
								</div>
							</div>
						</div>
						<!-- ./col -->
					</div>
					<div class="card">
						<div class="card-header">
							<h3 class="card-title">Clientes Ativos </h3>
							<div class="card-tools">
								<button type="button" class="btn btn-tool btrefresh"><i class="fas fa-sync-alt"></i></button>
							</div>
						</div>
						<div class="card-body table-responsive">
							<table id="table" class="table table-bordered table-striped table-sm" style="width: 100%!important">
								<thead>
									<tr>
										<th>Id</th>
										<th>Revendedor</th>
										<th>E-mail</th>
										<th>Status</th>
										<th>Clientes Ativos</th>
										<th>Conexões totais</th>
										<th>Valor Estimado</th>
										<th>Notas</th>
										<th>Ações</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
								<tfoot>
									<tr>
										<th>Id</th>
										<th>Revendedor</th>
										<th>E-mail</th>
										<th>Status</th>
										<th>Clientes Ativos</th>
										<th>Conexões totais</th>
										<th>Valor Estimado</th>
										<th>Notas</th>
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
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<script scr="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.15.0/popper.min.js"></script>
	<!-- DataTables -->
	<script src="/plugins/datatables/jquery.dataTables.js"></script>
	<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
	<!-- Toastr -->
	<script src="/plugins/toastr/toastr.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.js?<?php echo OFFICE_VERSION ?>"></script>
	<script type="text/javascript">
		$(function() {
			var table = $('#table').DataTable({
				"ajax": "/sys/api.php?action=get_active_clients_count",
				"processing": true,
				"serverSide": true,
				"columns": [{
						"data": "id"
					},
					{
						"data": "username"
					},
					{
						"data": "email"
					},
					{
						"data": "status"
					},
					{
						"data": "active_clients"
					},
					{
						"data": "active_conns"
					},
					{
						"data": "estimated_cost"
					},
					{
						"data": "notes"
					},
					{
						"data": "action"
					}
				],
				columnDefs: [{
					"targets": [0, 1, 2, 3, 4, 5, 6, 7, 8],
					"className": "text-center",
				}],
				order: [
					[0, "desc"]
				],
				paging: true,
				lengthChange: true,
				searching: true,
				ordering: true,
				orderMulti: false,
				info: true,
				autoWidth: false,
				language: {
					processing: "Processando...",
					lengthMenu: "Mostrar _MENU_ registros",
					zeroRecords: "Não foram encontrados resultados",
					info: "Mostrando de _START_ até _END_ de _TOTAL_ registros",
					infoEmpty: "Mostrando de 0 até 0 de 0 registros",
					sInfoFiltered: "",
					sInfoPostFix: "",
					search: "Buscar:",
					url: "",
					loadingRecords: "Carregando...",
					paginate: {
						first: "Primeiro",
						previous: "<i class='fas fa-chevron-left'></i>",
						next: "<i class='fas fa-chevron-right'></i>",
						last: "Último"
					}
				},
				"drawCallback": function() {
					$('[data-toggle="tooltip"]').tooltip();
				}
			});

			toastr.options = {
				"closeButton": false,
				"debug": false,
				"newestOnTop": true,
				"progressBar": true,
				"positionClass": "toast-top-right",
				"preventDuplicates": false,
				"onclick": null,
				"showDuration": "300",
				"hideDuration": "1000",
				"timeOut": "5000",
				"extendedTimeOut": "1000",
				"showEasing": "swing",
				"hideEasing": "linear",
				"showMethod": "fadeIn",
				"hideMethod": "fadeOut"
			}

			$(document).on('click', '.btrefresh', function(e) {
				table.ajax.reload();
				toastr.info('Recarregando tabela');
			});
			/* ADICIONAR/REMOVER CREDITOS */
			$(document).on('click', '.btcredits', function(e) {
				e.preventDefault();
				const id = $(this).data("id");
				bootbox.dialog({
					title: "Adic/Remover créditos",
					message: '<p>' + $(this).data("text") + '</p><form class="form-horizontal">' + '<div class="form-group col-md-6"><label class="form-control-label">Quantidade de créditos</label><div class="input-group"><span class="input-group-addon"><i class="fa fa-dollar"></i></span><input type="number" class="form-control" required="" value="0" autocomplete="off" id="credits" name="credits"></div></div>' + '<div class="form-group row">' + '<div class="col-md-12"><span class="text-blue">Escolha a quantidade de créditos.<br><b>*Para retirar créditos coloque o sinal de menos na frente.</b></span></div>' + '</div></form>',
					buttons: {
						cancel: {
							label: "Cancelar",
							className: 'btn-secondary>',
							callback: function() {}
						},
						noclose: {
							label: "Confirmar",
							className: 'btn-info btncredits',
							callback: function() {
								$('.btncredits').hide();
								const credits = $('#credits').val();
								$.get('/sys/api.php?action=change_credits&reseller_id=' + id + '&credits=' + credits, function(data) {
									if (data.result === 'success') {
										table.ajax.reload();
										toastr.success('Os créditos foram adicionados/removidos com sucesso!');
									} else {
										toastr.error('Não foi possível adicionar/remover os créditos, verifique se a quantia é válida.');
									}
								}, "json");
							}
						},
					}
				});
			});
			/* BLOQUEAR/DESBLOQUEAR */
			$(document).on('click', '.btblock', function(e) {
				e.preventDefault();
				const id = $(this).data("id");
				bootbox.dialog({
					title: "Tem certeza que deseja bloquear/desbloquear este revendedor ?",
					message: "<p>" + $(this).data("text") + "</p>",
					buttons: {
						cancel: {
							label: "Cancelar",
							className: 'btn-secondary',
							callback: function() {}
						},
						noclose: {
							label: "Confirmar",
							className: 'btn-warning btnblock',
							callback: function() {
								$('.btnblock').hide();
								$.get('/sys/api.php?action=toggle_block_reseller&reseller_id=' + id, function(data) {
									if (data.result === 'success') {
										table.ajax.reload();
										toastr.success('O revendedor foi bloqueado/desbloqueado com sucesso!');
									} else {
										toastr.error('Não foi possível bloquear/desbloquear o revendedor.');
									}
								}, "json");
							}
						},
					}
				});
			});
			/* DELETAR */
			$(document).on('click', '.btdelete', function(e) {
				e.preventDefault();
				const id = $(this).data("id");
				bootbox.dialog({
					title: "Tem certeza que deseja deletar esta revenda ?",
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
								$.get('/sys/api.php?action=delete_reseller&reseller_id=' + id, function(data) {
									if (data.result === 'success') {
										table.ajax.reload();
										toastr.success('Revendedor foi deletado com sucesso!');
									} else {
										toastr.error('Não foi possível deletar o revendedor.');
									}
								}, "json");
							}
						},
					}
				});
			});
		});
	</script>
</body>

</html>