<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$server_name = getServerProperty('server_name');
$fast_packages = json_decode(getServerProperty('fast_packages'), true);
$fixed_informations = getServerProperty('fixed_informations');
$categories = getAllCategories();
$page_name = "Dashboard";
$panel_expiration = closeExpiration();

?>
<!DOCTYPE html>
<html lang="pt_BR">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title><?php echo $server_name . " - " . $page_name ?></title>
	<!-- Font Awesome Icons -->
	<link rel="stylesheet" href="/plugins/fontawesome-pro/css/all.min.css">
	<!-- DataTables -->
	<link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
	<!-- overlayScrollbars -->
	<link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="/dist/css/adminlte.min.css?<?php echo OFFICE_VERSION ?>">
	<!-- <link rel="stylesheet" href="dist/css/animate.css"> -->
	<!-- Toastr -->
	<link rel="stylesheet" href="/plugins/toastr/toastr.min.css">
	<!-- Google Font: Source Sans Pro -->
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed text-sm <?php if (DarkMode()) {
																																																					echo "dark-mode";
																																																				} ?>">
	<div class="wrapper">
		<?php include_once('sidebar.php'); ?>
		<div class="content-wrapper">
			<div class="content-header">
				<div class="container-fluid">
					<div class="row mb-2">
						<div class="col-sm-6">
							<h1 class="m-0 text-dark"><?php echo $page_name ?></h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active"><?php echo $page_name ?></li>
							</ol>
						</div>
					</div>
				</div>
			</div>
			<?php
			if (isAdmin($logged_user) || isPartner($logged_user)) {
				if (maintenanceEnabled()["status"]) {
			?>
					<div class="container-fluid">
						<div class="row">
							<div class="col-12">
								<div class="alert alert-warning alert-dismissible">
									<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
									<h5><i class="icon fas fa-ban"></i>Manutenção ativada!</h5>
									<p class="mb-0">Revendedores não poderam acessar o painel, <a href="/settings/maintenance"></i>clique aqui</a>
										para desativar</p>
								</div>
							</div>
						</div>
					</div>
				<?php
				}
				if ($panel_expiration) { ?>
					<div class="container-fluid">
						<div class="row">
							<div class="col-12">
								<div class="<?php echo $panel_expiration['class'] ?>">
									<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
									<?php echo $panel_expiration['text'] ?></h5>
								</div>
							</div>
						</div>
					</div>
				<?php
				}
				if (checkUpdate()) { ?>
					<div class="container-fluid">
						<div class="row">
							<div class="col-12">
								<div class="alert alert-info alert-dismissible">
									<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
									<h5><i class="icon far fa-info"></i>Nova versão do office disponível!
										<button type="button" class="btn btn-success btn-sm requestUpdate"><i class="far fa-cloud-upload"></i> Atualizar agora!</button>
									</h5>
								</div>
							</div>
						</div>
					</div>
				<?php } elseif (hasUpdated()) { ?>
					<div class="container-fluid">
						<div class="row">
							<div class="col-12">
								<div class="alert alert-success alert-dismissible">
									<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
									<h5><i class="icon far fa-check"></i>Office atualizado!
										<a href="/update" type="button" class="btn btn-secondary btn-sm"></i> Ver novidades</a>
									</h5>
								</div>
							</div>
						</div>
					</div>
			<?php
				}
			}
			?>
			<section class="content">
				<div class="container-fluid">
					<?php if (hasPermissionResource($logged_user['id'], "iptv")) { ?>
						<h5>INFORMAÇÕES DE CLIENTES</h5>
						<div class="row">
							<div class="col-12 col-sm-6 col-md-3">
								<div class="info-box shadow">
									<span class="info-box-icon bg-info elevation-1"><i class="fad fa-users" style="--fa-secondary-opacity: 0.8"></i></span>
									<div class="info-box-content">
										<span class="info-box-text">Total de clientes</span>
										<span class="info-box-number iptv-clients-total">
											<i class="fad fa-spinner fa-spin"></i>
										</span>
									</div>
								</div>
							</div>
							<div class="col-12 col-sm-6 col-md-3">
								<div class="info-box shadow mb-3">
									<span class="info-box-icon bg-success elevation-1"><i class="fad fa-user-check" style="--fa-secondary-opacity: 0.8"></i></span>
									<div class="info-box-content">
										<span class="info-box-text">Total de clientes ativos</span>
										<span class="info-box-number iptv-clients-active">
											<i class="fad fa-spinner fa-spin"></i>
										</span>
									</div>
								</div>
							</div>
							<div class="clearfix hidden-md-up"></div>
							<div class="col-12 col-sm-6 col-md-3">
								<div class="info-box shadow mb-3">
									<span class="info-box-icon bg-warning elevation-1"><i class="fad fa-user-clock" style="--fa-secondary-opacity: 0.8"></i></span>
									<div class="info-box-content">
										<span class="info-box-text">Total de teste</span>
										<span class="info-box-number iptv-clients-trial">
											<i class="fad fa-spinner fa-spin"></i>
										</span>
									</div>
								</div>
							</div>
							<div class="col-12 col-sm-6 col-md-3">
								<div class="info-box shadow mb-3">
									<span class="info-box-icon bg-secondary elevation-1"><i class="fad fa-user-plus" style="--fa-secondary-opacity: 0.8"></i></span>
									<div class="info-box-content">
										<span class="info-box-text">Total de novos clientes</span>
										<span class="info-box-number iptv-clients-new">
											<i class="fad fa-spinner fa-spin"></i>
										</span>
									</div>
								</div>
							</div>
						</div>
					<?php }
					if ($permission['binstream']) {
						include_once(__DIR__ . "/sys/class/binstream.php");

						$binstream = new BinStream();
					?>
						<h5>P2P</h5>
						<div class="row">
							<div class="col-12 col-sm-6 col-md-3">
								<div class="info-box shadow">
									<span class="info-box-icon bg-info elevation-1"><i class="fad fa-users" style="--fa-secondary-opacity: 0.8"></i></span>
									<div class="info-box-content">
										<span class="info-box-text">Total de clientes</span>
										<span class="info-box-number p2p-clients-total">
											<i class="fad fa-spinner fa-spin"></i>
										</span>
									</div>
								</div>
							</div>
							<div class="col-12 col-sm-6 col-md-3">
								<div class="info-box shadow mb-3">
									<span class="info-box-icon bg-success elevation-1"><i class="fad fa-user-check" style="--fa-secondary-opacity: 0.8"></i></span>
									<div class="info-box-content">
										<span class="info-box-text">Total de clientes ativos</span>
										<span class="info-box-number p2p-clients-active">
											<i class="fad fa-spinner fa-spin"></i>
										</span>
									</div>
								</div>
							</div>
							<div class="clearfix hidden-md-up"></div>
							<div class="col-12 col-sm-6 col-md-3">
								<div class="info-box shadow mb-3">
									<span class="info-box-icon bg-warning elevation-1"><i class="fad fa-user-clock" style="--fa-secondary-opacity: 0.8"></i></span>
									<div class="info-box-content">
										<span class="info-box-text">Total de teste</span>
										<span class="info-box-number p2p-clients-trial">
											<i class="fad fa-spinner fa-spin"></i>
										</span>
									</div>
								</div>
							</div>
							<div class="col-12 col-sm-6 col-md-3">
								<div class="info-box shadow mb-3">
									<span class="info-box-icon bg-secondary elevation-1"><i class="fad fa-user-plus" style="--fa-secondary-opacity: 0.8"></i></span>
									<div class="info-box-content">
										<span class="info-box-text">Total de novos clientes</span>
										<span class="info-box-number p2p-clients-new">
											<i class="fad fa-spinner fa-spin"></i>
										</span>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					<div class="row">
						<div class="col-md-6">
							<div class="card card-default ">
								<div class="card-header">
									<h3 class="card-title text-center" style="float: none"><strong>Clientes com Vencimento próximo</strong></h3>
								</div>
								<div class="card-body table-responsive" style="display: block; padding: 5px; padding-bottom: 20px">
									<table id="table-expiring" class="table table-bordered table-striped table-sm" style="width: 100%!important">
										<thead>
											<tr>
												<th>Id</th>
												<th>Usuário</th>
												<th>Vencimento</th>
												<th>Revendedor</th>
												<th>Açes</th>
											</tr>
										</thead>
										<tbody>
										</tbody>
										<tfoot>
											<tr>
												<th>Id</th>
												<th>Usurio</th>
												<th>Vencimento</th>
												<th>Revendedor</th>
												<th>Ações</th>
											</tr>
										</tfoot>
									</table>
								</div>
							</div>
						</div>
						<?php
						if ($permission['binstream']) { ?>
							<div class="col-md-6">
								<div class="card card-default ">
									<div class="card-header">
										<h3 class="card-title text-center" style="float: none"><strong>Clientes com Vencimento próximo P2P</strong></h3>
									</div>
									<div class="card-body table-responsive" style="display: block; padding: 5px; padding-bottom: 20px">
										<table id="table-expiring-p2p" class="table table-bordered table-striped table-sm" style="width: 100%!important">
											<thead>
												<tr>
													<th>Id</th>
													<th>Usuário</th>
													<th>Vencimento</th>
													<th>Revendedor</th>
													<th>Ações</th>
												</tr>
											</thead>
											<tbody>
											</tbody>
											<tfoot>
												<tr>
													<th>Id</th>
													<th>Usuário</th>
													<th>Vencimento</th>
													<th>Revendedor</th>
													<th>Ações</th>
												</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
		</div>
		</section>
	</div>
	<!-- Control Sidebar -->
	<aside class="control-sidebar control-sidebar-dark"></aside>
	<!-- /.control-sidebar -->
	<!-- Main Footer -->
	<?php include_once('footer.php'); ?>
	</div>

	<!-- REQUIRED SCRIPTS -->
	<!-- jQuery -->
	<script src="/plugins/jquery/jquery.min.js"></script>
	<!-- Bootstrap -->
	<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- DataTables -->
	<script src="/plugins/datatables/jquery.dataTables.js"></script>
	<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
	<!-- Bootbox -->
	<script src="/bower_components/bootbox.min.js"></script>
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.js?<?php echo OFFICE_VERSION ?>"></script>
	<!-- Clipboard -->
	<script src="/bower_components/clipboard.min.js"></script>
	<!-- SweetAlert2 -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<!-- Toastr -->
	<script src="/plugins/toastr/toastr.min.js"></script>
	<!-- PAGE PLUGINS -->
	<!-- jQuery Mapael -->
	<script src="/plugins/jquery-mousewheel/jquery.mousewheel.js"></script>
	<script src="/plugins/raphael/raphael.min.js"></script>
	<script src="/plugins/jquery-mapael/jquery.mapael.min.js"></script>
	<script src="/plugins/jquery-mapael/maps/usa_states.min.js"></script>
	<!-- ChartJS -->
	<!--script src="plugins/chart.js/Chart.min.js"></script-->
	<!-- PAGE SCRIPTS -->
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

			var table = $('#table-expiring').DataTable({
				"ajax": "/sys/api.php?action=get_clients_expiring",
				"ordering": false,
				"processing": true,
				"serverSide": true,
				"pageLength": 5,
				"columns": [{
						"data": "id",
					}, {
						"data": "display_username",
					},
					{
						"data": "days_to_exp"
					},
					{
						"data": "reseller_name"
					},
					{
						"data": "action"
					}
				],
				columnDefs: [{
					"targets": [0, 1, 2, 3, 4],
					"className": "text-center",
				}],
				order: [
					[3, "desc"]
				],
				paging: true,
				lengthChange: false,
				searching: false,
				orderMulti: false,
				info: true,
				autoWidth: false,
				language: {
					processing: "Processando...",
					//lengthMenu: "Mostrar _MENU_ registros",
					zeroRecords: "Nenhum cliente próximo da exipiração",
					info: "Mostrando de _START_ até _END_ de _TOTAL_ registros",
					infoEmpty: "Mostrando de 0 até 0 de 0 registros",
					sInfoFiltered: "",
					sInfoPostFix: "",
					//search: "Buscar:",
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
			<?php
			if ($permission['binstream']) { ?>
				var table_p2p = $('#table-expiring-p2p').DataTable({
					"ajax": "/sys/api.php?action=get_clients_expiring&type=binstream",
					"ordering": false,
					"processing": true,
					"serverSide": true,
					"pageLength": 5,
					"columns": [{
							"data": "id",
						}, {
							"data": "display_username",
						},
						{
							"data": "days_to_exp"
						},
						{
							"data": "exField1"
						},
						{
							"data": "action"
						}
					],
					columnDefs: [{
						"targets": [0, 1, 2, 3, 4],
						"className": "text-center",
					}],
					order: [
						[3, "desc"]
					],
					paging: true,
					lengthChange: false,
					searching: false,
					orderMulti: false,
					info: true,
					autoWidth: false,
					language: {
						processing: "Processando...",
						//lengthMenu: "Mostrar _MENU_ registros",
						zeroRecords: "Nenhum cliente próximo da exipiração",
						info: "Mostrando de _START_ até _END_ de _TOTAL_ registros",
						infoEmpty: "Mostrando de 0 até 0 de 0 registros",
						sInfoFiltered: "",
						sInfoPostFix: "",
						//search: "Buscar:",
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
			<?php } ?>
			getDashStats()

			function getDashStats() {
				$.get('/sys/api.php?action=getDashStats', function(data) {
					if (data.iptv) {
						$('.iptv-clients-total').html(data.iptv.count.all);
						$('.iptv-clients-active').html(data.iptv.count.active);
						$('.iptv-clients-trial').html(data.iptv.count.trial);
						$('.iptv-clients-new').html(data.iptv.count.new);
					}
					<?php
					if ($permission['binstream']) { ?>
						if (data.p2p) {
							$('.p2p-clients-total').html(data.p2p.count.all);
							$('.p2p-clients-active').html(data.p2p.count.active);
							$('.p2p-clients-trial').html(data.p2p.count.trial);
							$('.p2p-clients-new').html(data.p2p.count.new);
						}
					<?php } ?>
				});
			}

			// Expiration Message
			$(document).on('click', '.btexpmessage', function(e) {
				e.preventDefault();
				const id = $(this).data("id");

				showExpMessage(id);
			});

			function showExpMessage(id) {
				bootbox.dialog({
					message: '<p class="text-center mb-0"><i class="fa fa-spin fa-cog"></i> Carregando...</p>',
					closeButton: false
				});

				$.post('/sys/api.php?action=exp_message&client_id=' + id, function(data) {
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

										const destination = 'https://api.whatsapp.com/send?phone=' + data.phone + '&text=' + message;

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
				const type = $(this).data("type");
				if (type == 'binstream') {
					var trust_renew = <?php echo getServerProperty("binstream_trust_renew_status", 0, true) ?>;
				} else {
					var trust_renew = <?php echo getServerProperty("iptv_trust_renew_status", 0, true) ?>;
				}

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
									$.get('/sys/api.php?action=renew_client_plus&clean_cache=true&client_id=' + id + '&months=' + months + "&type=" + type, function(data) {
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
						noclose2: {
							label: "Renovação de Confiança",
							className: "btn-outline-info btntrustrenew" +
								(trust_renew == 0 ? " d-none" : ""),
							callback: function() {
								$(".btnrenewplus").hide();
								$(".btntrustrenew").hide();

								$.get(
									"/sys/api.php?action=trust_renew_client&client_id=" + id + "&type=" + type,
									function(data) {
										if (data.result === "success") {
											table.ajax.reload();
											toastr.success("Renovação de confiança realizada com sucesso!");
										} else if (data.result === false) {
											toastr.warning(data.message);
										} else {
											toastr.warning("Não foi possível realizar a renovaço de confiança.");
										}
										bootbox.hideAll();
									},
									"json"
								);

							},
						},
					}
				});
			});

			$(document).on('click', '.requestUpdate', function(e) {
				e.preventDefault();

				// Exibe o spinner
				Swal.fire({
					title: 'Atualização em andamento',
					html: 'Por favor aguarde, isso deve levar alguns segundos! <br><br><div class="spinner-container"><i class="fad fa-spinner fa-spin fa-lg"></i></div><br>',
					icon: 'info',
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false,
					allowEscapeKey: false,
				});

				// Faz a requisiço da atualização
				fetch("/sys/api.php?action=requestUpdate")
					.then((response) => response.json())
					.then((data) => {
						if (data.result === "success") {
							Swal.fire({
								title: 'Atualização Concluída',
								text: 'O sistema será recarregado em 5 segundos.',
								icon: 'success',
								allowOutsideClick: false,
								allowEscapeKey: false,
								showConfirmButton: false,
							});

							setTimeout(() => {
								location.reload();
							}, 5000);
						} else {
							// Exibe uma mensagem de erro se a atualização falhou
							Swal.fire({
								title: 'Erro ao atualizar',
								text: 'Entre em contato com o suporte!',
								icon: 'error',
								showCloseButton: true,
								allowOutsideClick: false,
								showConfirmButton: false,
							});
						}
					});
			});
		});
	</script>
</body>

</html>