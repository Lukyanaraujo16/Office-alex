<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();

if (!hasPermissionResource($logged_user['id'], "codes") || !getServerProperty('code_status', 1)) {
	header('location: /dashboard');
	exit();
}

$server_name = getServerProperty('server_name');
$allowed_bouquets = json_decode(getServerProperty('allowed_bouquets'), true);
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
				<?php
				if (isset($_GET['result'])) {
					$result = $_GET['result'];
					$result_message = 'Aconteceu um problema, tente novamente mais tarde!';
					$result_type = 'warning';

					switch ($result) {
						case 'success':
							$result_message = 'O usuário foi criado com sucesso.';
							$result_type = 'success';
							break;
						case 'invalid_username':
							$result_message = 'O nome de usuário escolhido é invalido!, deve ter no mínimo 6 caracteres.';
							break;
						case 'invalid_password':
							$result_message = 'A senha escolhida é invalida!, deve ter no mínimo 6 caracteres.';
							break;
						case 'invalid_notes':
							$result_message = 'A observação escolhida é invalida!, deve ter no máximo 500 caracteres.';
							break;
						case 'invalid_bouquet':
							$result_message = 'Os pacotes escolhidos são inválidos.';
							break;
						case 'insufficient_credits':
							$result_message = 'Você não tem créditos suficiente, recarregue seu painel.';
							break;
						case 'exist_client':
							$result_message = 'Já existe um usuário com este nome de usuário, escolha outro.';
							break;
						case 'cant_send_email':
							$result_message = 'O usuário foi criado com sucesso, mas não foi possível enviar o e-mail com os dados de acesso.';
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
							<h1>Criar Códigos</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Criar Códigos</li>
							</ol>
						</div>
					</div>
				</div>
				<!-- /.container-fluid -->
			</section>
			<!-- Main content -->
			<section class="content">
				<div class="container-fluid">
					<!-- SELECT2 EXAMPLE -->
					<div class="card card-default">
						<div class="card-header">
							<h3 class="card-title">Informações dos Códigos</h3>
						</div>
						<!-- /.card-header -->
						<form autocomplete="off" action="#" method="post" name="frm1">
							<div class="card-body">
								<div class="row col-lg-6 col-md-12">

									<div class="form-group col-md-4">
										<label>Quantidade</label>
										<div class="input-group mb-6">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fal fa-list-ol"></i></span>
											</div>
											<input type="number" required="" autocomplete="off" name="amount" id="amount" data-minlength="1" minlength="1" class="form-control" placeholder="Quantidade" value="1">
										</div>
									</div>

									<div class="form-group col-md-4">
										<label>Vencimento</label>
										<select class="custom-select" name="duration" id="duration" required="">
											<option value="1">1 Mês</option>
											<option value="2">2 Meses</option>
											<option value="3">3 Meses</option>
											<option value="6">6 Meses</option>
											<option value="12">12 Meses</option>
										</select>
									</div>

									<div class="form-group col-md-4">
										<label>Conexões</label>
										<div class="input-group mb-6">
											<div class="input-group-prepend">
												<span class="input-group-text"><i class="fal fa-tv"></i></span>
											</div>
											<input type="number" required="" autocomplete="off" name="connections" id="connections" data-minlength="1" minlength="1" class="form-control" placeholder="Conexões" value="1">
										</div>
									</div>

									<div class="col-12">
										<div class="form-group">
											<label>Selecionar Pacote</label>
											<select name="package" id="package" class="custom-select" required="" data-placeholder="Selecione os Pacotes" style="width: 100%;">

												<?php
													foreach (getPackages() as $package) {
														if ($package['is_trial']) {
															if ($package['id'] == getServerProperty("code_fast_test_package", "", true)) { ?>
																<option value="<?php echo $package['id']; ?>" selected><?php echo $package['package_name']; ?></option>
															<?php } else { ?>
																<option value="<?php echo $package['id']; ?>"><?php echo $package['package_name']; ?></option>
													<?php }
														}
													} ?>
											</select>
										</div>
									</div>
									<div class="form-group col-md-12">
										<label>Notas</label>
										<textarea class="form-control" rows="3" name="reseller_notes" id="reseller_notes" placeholder="Informações..."></textarea>
									</div>
								</div>
							</div>
							<div class="card-footer">
								<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#codes_modal">Criar Códigos</button>
							</div>
						</form>
					</div>
				</div>
			</section>
		</div>
		<?php include_once('footer.php'); ?>
	</div>
	<!-- Modal HTML -->
	<div id="codes_modal" class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Códigos Gerados</h5>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body">
					<textarea class="form-control modal-textarea-p2p" id="modal_codes" rows="15" placeholder="Aguarde gerando..."></textarea><br>
					<div>
						<p><strong>Esse Template pode ser alterado <a href="/template.php">AQUI</a></strong></p>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
					<a type="button" id="modal-wpp-p2p" class="btn btn-success" href="#" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
					<button type="button" class="btn btn-primary btn-modal-test" data-clipboard-target="#modal_codes"><i class="far fa-copy"></i> Copiar Dados!</button>
				</div>
			</div>
		</div>
		<!-- ./wrapper -->
		<!-- jQuery -->
		<script src="/plugins/jquery/jquery.min.js"></script>
		<!-- Bootstrap 4 -->
		<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="/plugins/select2/js/select2.full.min.js"></script>
		<!-- overlayScrollbars -->
		<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
		<!-- AdminLTE App -->
		<script src="/dist/js/adminlte.min.js?<?php echo OFFICE_VERSION ?>"></script>
		<!-- Clipboard -->
		<script src="/bower_components/clipboard.min.js"></script>
		<!-- Toastr -->
		<script src="/plugins/toastr/toastr.min.js"></script>
		<!-- Page script -->
		<script>
			$(function() {
				$('.select2').select2();

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
			})
		</script>
		<script type="text/javascript">
			$(function() {
				new ClipboardJS('.btn');

				$('.btn-modal-test').click(function() {
					toastr.success('Dados copiados!');
				});
			});
		</script>
		<script>
			$(document).ready(function() {
				$("#codes_modal").on("show.bs.modal", function(event) {
					var amount = $("input#amount").val()
					var duration = $("select#duration").val()
					var connections = $("input#connections").val()
					var package = $("select#package").val()
					var reseller_notes = $("textarea#reseller_notes").val()

					//$(this).find(".modal-textarea-p2p").load('./sys/api.php?action=fast_test&type=p2p');

					$.getJSON(`/sys/api.php?action=create_multi_codes&amount=${amount}&duration=${duration}&connections=${connections}&package=${package}&reseller_notes=${reseller_notes}&result_cb=json`, function(e) {
						var conteudo = encodeURIComponent(e);
						//console.log('vconteudo', conteudo)
						$("#modal-wpp-p2p").attr("href", `https://api.whatsapp.com/send?phone=&text=${conteudo}`);
						//console.log('e', e)
						$(".modal-textarea-p2p").text(e);
					})
				});
			});
		</script>
</body>

</html>