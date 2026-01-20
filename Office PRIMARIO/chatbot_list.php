<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$server_name = getServerProperty('server_name');
$fast_packages = json_decode(getServerProperty('fast_packages'), true);

if (isset($_POST['title']) && isset($_POST['message'])) {
	$title = $_POST['title'];
	$message = $_POST['message'];
	$reseller_id = (isset($_POST['reseller']) ? $_POST['reseller'] : '');
	if ((strlen($title) < 6) || (255 < strlen($title))) {
		header('location: ?result=invalid_title');
		exit();
	}

	if ((strlen($message) < 6) || (1000 < strlen($message))) {
		header('location: ?result=invalid_message');
		exit();
	}

	if (createTicket($logged_user, $reseller_id, $title, $message)) {
		header('location: ?result=success');
		exit();
	}
}
?>
<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?php echo $server_name; ?></title>
	<!-- Font Awesome -->
	<link rel="stylesheet" href="/plugins/fontawesome-pro/css/all.min.css">
	<!-- Ionicons -->
	<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<!-- DataTables -->
	<link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
	<link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
	<!-- overlayScrollbars -->
	<link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">

	<!-- SweetAlert2 -->
	<link rel="stylesheet" href="/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
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
							<h1>Regras Chatbot </h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Chatbot</li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
				<div class="container-fluid">
					<div class="card">
						<div class="card-header">
							<h3 class="card-title">Regras Chatbot </h3>
							<div class="card-tools">
								<button type="button" class="btn btn-tool btrefresh"><i class="fas fa-sync-alt"></i></button>
							</div>
						</div>
						<div class="card-body table-responsive">
							<table id="table" class="table table-bordered table-striped table-sm" style="width: 100%!important">
								<thead>
									<tr>
										<th>ID</th>
										<th>Tipo da Regra</th>
										<th>Mensagens</th>
										<th>Ação da Regra</th>
										<th>Execuções</th>
										<th>Status</th>
										<th>Ações</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
								<tfoot>
									<tr>
										<th>ID</th>
										<th>Tipo da Regra</th>
										<th>Mensagens</th>
										<th>Ação da Regra</th>
										<th>Execuções</th>
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

	<script scr="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.15.0/popper.min.js"></script>
	<!-- DataTables -->
	<script src="/plugins/datatables/jquery.dataTables.js"></script>
	<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- SweetAlert2 -->
	<script src="/plugins/sweetalert2/sweetalert2.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.js?<?php echo OFFICE_VERSION ?>"></script>
	<script type="text/javascript">
		$(function() {
			var table = $('#table').DataTable({
				"ajax": "/sys/api.php?action=get_chatbot_rules",
				"processing": true,
				"serverSide": true,
				"columns": [{
						"data": "id"
					},
					{
						"data": "rule_type"
					},
					{
						"data": "messages"
					},
					{
						"data": "rule_action"
					},
					{
						"data": "runs"
					},
					{
						"data": "status"
					},
					{
						"data": "action"
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
			/* ABRIR/FECHAR */
			$(document).on('click', '.bttoggle', function(e) {
				e.preventDefault();
				const id = $(this).data("id");
				bootbox.dialog({
					title: "Tem certeza que deseja ativar/desativar esta regra?",
					message: "<p>" + $(this).data("text") + "</p>",
					buttons: {
						cancel: {
							label: "Cancelar",
							className: 'btn-secondary',
							callback: function() {}
						},
						noclose: {
							label: "Confirmar",
							className: 'btn-success btntoggle',
							callback: function() {
								$('.btntoggle').hide();
								$.get('/sys/api.php?action=togle_chatbot_rule&rule_id=' + id, function(data) {
									if (data.result === 'success') {
										table.ajax.reload();
										Toast.fire({
											type: 'success',
											title: 'Regra ativada/desativada com sucesso!'
										})
									} else {
										Toast.fire({
											type: 'error',
											title: 'Não foi possível ativar/desativar esta regra.'
										})
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
					title: "Tem certeza que deseja deletar esta regra?",
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
								$.get('/sys/api.php?action=delete_chatbot_rule&rule_id=' + id, function(data) {
									if (data.result === 'success') {
										table.ajax.reload();
										Toast.fire({
											type: 'success',
											title: 'Regra deletada com sucesso!'
										})
									} else {
										Toast.fire({
											type: 'error',
											title: 'Não foi possível deletar esta regra.'
										})
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