<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$server_name = getServerProperty('server_name');
$fast_packages = json_decode(getServerProperty('fast_packages'), true);

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
	<!-- iCheck for checkboxes and radio inputs -->
	<link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
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
							<h1>Encurtador</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Encurtador</li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
				<div class="container-fluid">
					<div class="card card-default">
						<div class="card-header">
							<h3 class="card-title">Encurtador de links</h3>
							<div class="card-tools">
								<button type="button" class="btn btn-tool btrefresh"><i class="fas fa-sync-alt"></i></button>
							</div>
						</div>
						<div class="row">
							<div class="card-body col-sm-12">
								<label class="form-control-label">Link que deseja encurtar</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">
											<i class="fas fa-link"></i>
										</span>
									</div>
									<input type="text" class="form-control" id="copymplus" name="link-new" placeholder="Digite o link para encurtar">
									<div class="input-group-append">
										<button type="button" class="btn btn-info btnenc" id="encurtar">Encurtar</button>
									</div>
								</div>
							</div>
							<div class="card-body table-responsive col-sm-12">
								<table id="table" class="table table-bordered table-striped">
									<thead>
										<tr>
											<th>Url Encurtada</th>
											<th>Url Completa</th>
											<th>Ações</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
							<!-- /.box-body -->
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
	<!-- DataTables -->
	<script src="/plugins/datatables/jquery.dataTables.js"></script>
	<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- Clipboard -->
	<script src="/bower_components/clipboard.min.js"></script>
	<!-- SweetAlert2 -->
	<script src="/plugins/sweetalert2/sweetalert2.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.js?<?php echo OFFICE_VERSION ?>"></script>
	<!-- Page script -->
	<script type="text/javascript">
		$(function() {
			const shortener_url = "<?php echo OFFICE_CONFIG['shorten_url']; ?>"
			const reseller_id = " <?php echo $logged_user["id"] ?>"

			$(document).on('click', '#encurtar', function() {
				const url = $('#copymplus').val();
				$.get(shortener_url, {
					url: url,
					creator_id: reseller_id,
					format: 'text'
				}, function() {
					$('#copymplus').val('');
					$('#table').DataTable().ajax.reload();
				});
			});

			$('#table').DataTable({
				"ajax": shortener_url + "/api.php?creator_id=" + reseller_id,
				"processing": true,
				"columns": [{
						"data": "shorten_url"
					},
					{
						"data": "url"
					},
					{
						"data": "options"
					},
				],
				columnDefs: [{
					"targets": [2],
					"className": "text-center",
				}],
				"order": [],
				"paging": true,
				"lengthChange": true,
				"searching": true,
				"ordering": true,
				"info": true,
				"autoWidth": false,
				"language": {
					"processing": "Processando...",
					"lengthMenu": "Mostrar _MENU_ registros",
					"zeroRecords": "Não foram encontrados resultados",
					"info": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
					"infoEmpty": "Mostrando de 0 até 0 de 0 registros",
					"sInfoFiltered": "",
					"sInfoPostFix": "",
					"search": "Buscar:",
					"url": "",
					"loadingRecords": "Carregando...",
					"paginate": {
						"first": "Primeiro",
						"previous": "<i class='fas fa-chevron-left'></i>",
						"next": "<i class='fas fa-chevron-right'></i>",
						"last": "Último"
					}
				},
				"drawCallback": function() {
					$('[data-toggle="tooltip"]').tooltip();
				},
			});
			const Toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 5000
			});
			$(document).on('click', '.btrefresh', function(e) {
				$('#table').DataTable().ajax.reload();
				Toast.fire({
					type: 'info',
					title: 'Recarregando tabela'
				})
			});

			new ClipboardJS('.copy-button');
			new ClipboardJS('.copy-button2');
		});
	</script>
</body>

</html>