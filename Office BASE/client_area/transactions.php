<?php
include_once('../sys/functions.php');
isClientLogged();
$logged_user = getLoggedClient();
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
	<link rel="stylesheet" href="../plugins/fontawesome-pro/css/all.min.css">
	<!-- Ionicons -->
	<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<!-- DataTables -->
	<link rel="stylesheet" href="../plugins/datatables-bs4/css/dataTables.bootstrap4.css">
	<!-- daterange picker -->
	<link rel="stylesheet" href="../plugins/daterangepicker/daterangepicker.css">
	<!-- SweetAlert2 -->
	<link rel="stylesheet" href="../plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="../dist/css/adminlte.min.css">
	<!-- Google Font: Source Sans Pro -->
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini text-sm layout-footer-fixed <?php if (DarkMode(true)) {
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
							<h1>Histórico de Pagamentos </h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Relatórios </li>
								<li class="breadcrumb-item active">Histórico de Pagamentos </li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
				<div class="container-fluid">
					<div class="card">
						<div class="card-header">
							<h3 class="card-title">Histórico de Pagamentos </h3>
							<div class="card-tools">
								<button type="button" class="btn btn-tool btrefresh"><i class="fas fa-sync-alt"></i></button>
							</div>
						</div>
						<div class="card-body table-responsive">
							<table id="table" class="table table-bordered table-striped table-sm" style="width: 100%!important">
								<thead>
									<tr>
										<th>Id</th>
										<th>Plano</th>
										<th>Valor</th>
										<th>Gateway</th>
										<th>Status</th>
										<th>Data</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
								<tfoot>
									<tr>
										<th>Id</th>
										<th>Plano</th>
										<th>Valor</th>
										<th>Gateway</th>
										<th>Status</th>
										<th>Data</th>
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
	<script src="../plugins/jquery/jquery.min.js"></script>

	<!-- Bootstrap 4 -->
	<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

	<script scr="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.15.0/popper.min.js"></script>
	<!-- DataTables -->
	<script src="../plugins/datatables/jquery.dataTables.js"></script>
	<script src="../plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
	<!-- SweetAlert2 -->
	<script src="../plugins/sweetalert2/sweetalert2.min.js"></script>
	<!-- AdminLTE App -->
	<script src="../dist/js/adminlte.js"></script>
	<script type="text/javascript">
		$(function() {
			var table = $('#table').DataTable({
				"ajax": "../sys/clientApi.php?action=get_transactions",
				"processing": true,
				"serverSide": true,
				"columns": [{
						"data": "id",
						visible: false
					},
					{
						"data": "plan_id"
					},
					{
						"data": "amount"
					},
					{
						"data": "gateway_name"
					},
					{
						"data": "status"
					},
					{
						"data": "modified_at"
					}
				],
				columnDefs: [{
					"targets": [0, 1, 2, 3, 4, 5],
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
			const Toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 5000
			});
			$(document).on('click', '.btrefresh', function(e) {
				table.ajax.reload();
				Toast.fire({
					type: 'info',
					title: 'Recarregando tabela'
				})
			});
		});
	</script>
</body>

</html>