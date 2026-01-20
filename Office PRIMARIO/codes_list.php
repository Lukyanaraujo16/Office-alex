<?php
include_once(__DIR__ . '/sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
if (!hasPermissionResource($logged_user['id'], "codes") || !getServerProperty('code_status', 1)) {
	header('location: /dashboard');
	exit();
}
$server_name = getServerProperty('server_name');
$server_dns = getServerDNS();
$fast_packages = json_decode(getServerProperty('fast_packages'), true);

$resellers = isAdmin($logged_user) ? getAllUsers() : getAllResellersByOwnerID($logged_user['id']);

$page_name = 'Códigos';
?>
<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?php echo $server_name . " - " . $page_name ?></title>
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
							<h1><?php echo $page_name ?></h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active"><?php echo $page_name ?></li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
				<div class="container-fluid">
					<div class="card">
						<div class="card-header">
							<h3 class="card-title"><?php echo $page_name ?></h3>
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
											<select class="form-control select2" name="reseller" id="select-reseller" style="width: 100%;">
												<option value="" selected="selected">Todos</option>
												<option value="<?php echo $logged_user['id'] ?>"><s>Meus Clientes</s></option>
												<?php
												foreach ($resellers as $reseller) {
													if ($reseller['owner_id'] == $logged_user['id']) {
														$tree = '';
													} else {
														$tree = '*';
													}
												?>
													<option value="<?php echo $reseller['id'] ?>"><?php echo $reseller['username'] . " " . $tree ?></option>';
												<?php }
												?>
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
												<option value="expired">Expirado</option>
											</select>
										</div>
									</div>

									<div class="col-md-3">
										<div class="form-group">
											<label>Tipo</label>
											<select class="form-control select2" name="type" id="select-type">
												<option value="">Todos</option>
												<option value="official">Cliente</option>
												<option value="trial">Teste</option>
											</select>
										</div>
									</div>
								</div>
							</div>
							<table id="table" class="table table-bordered table-striped table-sm" style="width: 100%!important">
								<thead>
									<tr>
										<th>Id</th>
										<th>Código</th>
										<th>E-Mail</th>
										<th>Adicionado</th>
										<th>Vencimento</th>
										<th>Revendedor</th>
										<th>Conexões</th>
										<th>Notas</th>
										<th>Status</th>
										<th>Ações</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
								<tfoot>
									<tr>
										<th>Id</th>
										<th>Código</th>
										<th>E-Mail</th>
										<th>Adicionado</th>
										<th>Vencimento</th>
										<th>Revendedor</th>
										<th>Conexões</th>
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
	<script src="/bower_components/bootbox.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- Select2 -->
	<script src="/plugins/select2/js/select2.full.min.js"></script>
	<!-- DataTables -->
	<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
	<!-- <script src="/plugins/datatables/jquery.dataTables.js"></script>
	<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script> -->
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- Clipboard -->
	<script src="/bower_components/clipboard.min.js"></script>
	<!-- Toastr -->
	<script src="/plugins/toastr/toastr.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.js?<?php echo OFFICE_VERSION ?>"></script>

	<script src="https://cdn.datatables.net/plug-ins/1.10.12/sorting/date-eu.js"></script>

	<script type="text/javascript">
		$(function() {

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

			new ClipboardJS('.btncopycode');
			$('.btncopycode').click(function() {
				toastr.success('Recarregando tabela');
			});
		});
	</script>
	<script type="text/javascript">
		$(function() {
			var table = $('#table').DataTable({
				"ajax": "/sys/api.php?action=get_code_table",
				"processing": true,
				"serverSide": true,
				"columns": [{
						"data": "id"
					},
					{
						"data": "display_username"
					},
					{
						"data": "email"
					},
					{
						"data": "created_at"
					},
					{
						"data": "exp_date"
					},
					{
						"data": "reseller_name"
					},
					{
						"data": "max_connections"
					},
					{
						"data": "reseller_notes"
					},
					{
						"data": "status"
					},
					{
						"data": "action"
					}
				],
				columnDefs: [{
					"targets": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
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
				lengthMenu: [
					[10, 25, 50, 100, 500, 1000],
					[10, 25, 50, 100, 500, 1000]
				],
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
				initComplete: function() {
					// Configura o evento de mudança no elemento de seleção
					// $('#select-status').change(function() {
					$('#select-status, #select-type, #select-reseller').on('change', function() {
						var status = $('#select-status').val();
						var type = $('#select-type').val();
						var reseller = $('#select-reseller').val();
						atualizaTabela(status, type, reseller);
					});
				},
				"drawCallback": function() {
					$('[data-toggle="tooltip"]').tooltip();
				}
			});

			var searchInput = $('div.dataTables_filter input').detach();
			searchInput.appendTo('#search-div');
			var searchInput = $('div.dataTables_filter label').hide();

			$('.select2').select2()

			function atualizaTabela(status, type, reseller) {
				var url = "/sys/api.php?action=get_code_table&status=" + status + "&type=" + type + "&reseller=" + reseller;

				table.ajax.url(url).load();
			}

			$(document).on("click", ".btrefresh", function(e) {
				var status = $('#select-status').val();
				var type = $('#select-type').val();
				var reseller = $('#select-reseller').val();
				atualizaTabela(status, type, reseller);
				toastr.info("Recarregando tabela");
			});

			/* ADICIONAR TELA */
			$(document).on('click', '.bttela', function(e) {
				e.preventDefault();
				const id = $(this).data("id");

				bootbox.dialog({
					title: "Tem certeza que deseja adicionar mais uma tela ?",
					message: "<p>" + $(this).data("text") + "</p>",
					buttons: {
						cancel: {
							label: "Cancelar",
							className: 'btn-secondary',
							callback: function() {}
						},
						noclose: {
							label: "Confirmar",
							className: 'btn-info btnaddscreen',
							callback: function() {
								$('.btnaddscreen').hide();

								$.get('/sys/api.php?action=add_screen&client_id=' + id + '&type=code', function(data) {
									if (data.result === 'success') {
										table.ajax.reload();
										toastr.success('Máximo de conexões aumentada com sucesso!');
									} else {
										toastr.warning(data.message);
									}
								}, "json");
							}
						},
					}
				});
			});

			// Fast message
			$(document).on('click', '.btfastmessage', function(e) {
				e.preventDefault();
				const id = $(this).data("id");

				showFastMessage(id);
			});

			function showFastMessage(id) {
				bootbox.dialog({
					message: '<p class="text-center mb-0"><i class="fa fa-spin fa-cog"></i> Carregando...</p>',
					closeButton: false
				});

				$.post('/sys/api.php?action=fast_message&type=code&client_id=' + id, function(data) {
					bootbox.hideAll();
					$('.bootbox.modal').remove();
					$('.modal-backdrop').remove();

					if (data.result == 'success') {
						const fast_message = '<div class="fast-message">' + data.message.replace(/(?:\r\n|\r|\n)/g, '<br>') + '</div><button type="button" class="btn copy-fast-message d-none" data-clipboard-target=".fast-message">Hide Button ;)</button>';

						bootbox.dialog({
							message: fast_message,
							//size: "large",
							buttons: {
								noclose2: {
									label: 'Whatsapp',
									className: 'btn-success waves-effect waves-light',
									callback: function() {
										const message = encodeURIComponent(data.message.replace(/<br\s*[\/]?>/gi, ""));

										const destination = 'https://api.whatsapp.com/send?phone=&text=' + message;

										const win = window.open(destination, '_blank');
										if (win) {
											win.focus();
										} else {
											window.location.href = destination;
										}
										return false;
									}
								},
								noclose: {
									label: 'Copiar',
									className: 'btn-primary bg-gradient waves-effect waves-light',
									callback: function() {
										$('.copy-fast-message').click();
										return false;
									}
								},
								cancel: {
									label: 'Fechar',
									className: 'btn-secondary waves-effect waves-light',
									callback: function() {}
								}
							}
						});
						new ClipboardJS('.copy-fast-message');
					}
				}, "JSON");
			}

			/* RENOVAR VARIOS MESES CLIENTE */
			$(document).on('click', '.btrenewplus', function(e) {
				e.preventDefault();
				const id = $(this).data("id");

				bootbox.dialog({
					title: "Tem certeza que deseja renovar este cliente ?",
					message: '<p>' + $(this).data("text") + '</p><form class="form-horizontal">' + '<div class="form-group col-md-6"><label class="form-control-label">Quantidade de meses</label><div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar-plus-o"></i></span><input type="number" class="form-control" required="" value="1" autocomplete="off" id="months" name="months"></div></div>' + '<div class="form-group row">' + '<div class="col-md-12"><span class="text-white">Escolha a quantidade de meses.<br><br><b>Fique atento, caso seja um usuario de 2 telas irá cobrar o dobro de créditos equivalente a quantidade de meses.</b></span></div>' + '</div></form>',
					buttons: {
						cancel: {
							label: "Cancelar",
							className: 'btn-secondary',
							callback: function() {}
						},
						noclose: {
							label: "Confirmar",
							className: 'btn-info btnrenewplus',
							callback: function() {
								$('.btnrenewplus').hide();

								const months = $('#months').val();
								if (months > 0) {
									$.get('/sys/api.php?action=renew_client_plus&client_id=' + id + '&months=' + months, function(data) {
										if (data.result === 'success') {
											table.ajax.reload();
											toastr.success('Cliente renovado com sucesso!');
										} else {
											toastr.warning('Não foi possível renovar o cliente.');
										}
									}, "json");
								} else {
									toastr.warning('Quantidade de meses inválida.');
								}
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
					title: "Tem certeza que deseja bloquear/desbloquear este usuário ?",
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
								$.get('/sys/api.php?action=toggle_block_client&user_id=' + id, function(data) {
									if (data.result === 'success') {
										table.ajax.reload();
										toastr.success('Usuário bloqueado/desbloqueado com sucesso!');
									} else {
										toastr.warning('Não foi possível bloquear/desbloquear este usuário.');
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
					title: "Tem certeza que deseja deletar este usuário ?",
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
								$.get('/sys/api.php?action=delete_client&user_id=' + id, function(data) {
									if (data.result === 'success') {
										table.ajax.reload();
										toastr.success('Usuário deletado com sucesso!');
									} else {
										toastr.warning('Não foi possível deletar este usuário.');
									}
								}, "json");
							}
						},
					}
				});
			});
			const urlParams = new URLSearchParams(window.location.search);
			const clientId = urlParams.get('client_id');
			if (clientId) {
				showFastMessage(clientId);
			} else {
				if (window.location.pathname.split('/')[3] == 'show') {
					showFastMessage(window.location.pathname.split('/')[4]);
				}
			}
		});
	</script>
</body>

</html>