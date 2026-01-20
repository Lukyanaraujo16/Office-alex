<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$logged_user_id = intval($_SESSION['__l0gg3d_us3r__']);
$server_name = getServerProperty('server_name');

if (isset($_POST['template_iptv']) && isset($_POST['template_p2p'])) {
	$template_iptv = purifyHTML($_POST['template_iptv']);
	$template_p2p = purifyHTML($_POST['template_p2p']);
	$template_code = purifyHTML($_POST['template_code']);
	$template_exp_mesage = purifyHTML($_POST['template_exp_mesage']);

	deleteUserProperty($logged_user_id, 'fast_test_template_iptv');
	deleteUserProperty($logged_user_id, 'fast_test_template_p2p');
	deleteUserProperty($logged_user_id, 'fast_test_template_code');
	deleteUserProperty($logged_user_id, 'expiring_template');
	$result1 = addUserProperty($logged_user_id, 'fast_test_template_iptv', $template_iptv);
	$result2 = addUserProperty($logged_user_id, 'fast_test_template_code', $template_p2p);
	$result3 = addUserProperty($logged_user_id, 'fast_test_template_p2p', $template_code);
	$result4 = addUserProperty($logged_user_id, 'expiring_template', $template_exp_mesage);

	if ($result1 && $result2 && $result3 && $result4) {
		header('location: ?result=template_saved');
		exit();
	}

	header('location: ?result=failed');
	exit();
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
	<!-- Ionicons -->
	<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<!-- overlayScrollbars -->
	<link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
	<!-- summernote -->
	<link rel="stylesheet" href="/plugins/summernote/summernote-bs4.css">
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
						case 'template_saved':
							$result_message = 'Templates salvos com sucesso.';
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
							<h1>Configurações do Template</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item">Configurações</li>
								<li class="breadcrumb-item active">Template</li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
				<div class="container-fluid">
					<div class="card card-default">
						<div class="card-header">
							<h3 class="card-title">Configurações do Template</h3>
						</div>
						<form autocomplete="off" action="#" method="post">
							<div class="card-body pad">
								<div class="accordion" id="accordionExample">
									<button class="btn btn-block btn-secondary text-left mb-3 templates" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
										<h6> Template mensagem rápida </h6>
										<i class="fal fa-arrow-alt-from-top"></i>
									</button>
									<div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
										<div class="row col-lg-12">
											<div class="col-lg-6" id="div-iptv">
												<div class="input-group mb-3">
													<textarea class="form-control" id="template_iptv" name="template_iptv" style="width: 100%; min-height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" placeholder="Deixe em branco para manter o padrão do sistema"><?php echo getUserProperty($logged_user_id, 'fast_test_template_iptv', "", true); ?></textarea>
												</div>
											</div>
											<div class="col-lg-6 callout callout-info">
												<h5>Variáveis para Substituição <i class="fad fa-question-circle text-teal" data-toggle="tooltip" data-original-title="Clique na variável para inserir no template rapidamente"></i></h5>
												<p>Use as variáveis abaixo para inserir as informações como usuário e senha ao seu template</p>
												<table class="table table-bordered table-sm">
													<thead>
														<tr>
															<!--th style="width: 10px">#</th-->
															<th class="text-center">Variável</th>
															<th class="text-center">Informação inserida</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#username#</strong></td>
															<td class="text-center">Usuário do cliente criado</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#password#</strong></td>
															<td class="text-center">Senha do cliente criado</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#m3u_link#</strong></td>
															<td class="text-center">Link m3u encurtado</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#m3u_link_hls#</strong></td>
															<td class="text-center">Link m3u HLS completo</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#m3u_link_mpegts#</strong></td>
															<td class="text-center">Link m3u MPEGTS completo</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#ssiptv_link#</strong></td>
															<td class="text-center">Link SSIPTV do cliente</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#exp_info#</strong></td>
															<td class="text-center">Informação de vencimento. Ex: expira amanhã</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#exp_date#</strong></td>
															<td class="text-center">Informação de vencimento. Ex: <?php echo date("d/m/Y", strtotime("+5 day")) ?></td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#server_name#</strong></td>
															<td class="text-center">Nome do servidor. (<b><?php echo $server_name ?></b>)</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#whatsapp#</strong></td>
															<td class="text-center">Seu WhatsApp. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_iptv',this)">#telegram#</strong></td>
															<td class="text-center">Seu Telegram. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
														</tr>
													</tbody>
												</table>
												<p>Você pode copiar o template do sistema para editar clicando <strong class="hover-var" onclick="pasteDefaultTemplate('template_iptv','iptv')">aqui</strong></p>

											</div>
										</div>
									</div>
									<button class="btn btn-block btn-secondary text-left mb-3 templates" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
										<h6>Template mensagem rapida P2P BinStream</h6>
										<i class="fal fa-arrow-alt-from-top"></i>
									</button>
									<div id="collapseTwo" class="collapse" aria-labelledby="collapseTwo" data-parent="#accordionExample">
										<div class="row col-lg-12">
											<div class="col-lg-6" id="div-binstream">
												<div class="input-group mb-3">
													<textarea class="form-control" id="template_p2p" name="template_p2p" style="width: 100%; min-height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" placeholder="Deixe em branco para manter o padrão do sistema"><?php echo getUserProperty($logged_user_id, 'fast_test_template_p2p', "", true); ?></textarea>
												</div>
											</div>
											<div class="col-lg-6 callout callout-info">
												<h5>Variáveis para Substituição <i class="fad fa-question-circle text-teal" data-toggle="tooltip" data-original-title="Clique na variável para inserir no template rapidamente"></i></h5>
												<p>Use as variveis abaixo para inserir as informações como usuário e senha ao seu template</p>
												<table class="table table-bordered table-sm">
													<thead>
														<tr>
															<th class="text-center">Variável</th>
															<th class="text-center">Informação inserida</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_p2p',this)">#username#</strong></td>
															<td class="text-center">Usuário do cliente criado</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_p2p',this)">#password#</strong></td>
															<td class="text-center">Senha do cliente criado</td>
														</tr>

														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_p2p',this)">#exp_info#</strong></td>
															<td class="text-center">Informação de vencimento. Ex: expira amanhã</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_p2p',this)">#exp_date#</strong></td>
															<td class="text-center">Informação de vencimento.Ex: <?php echo date("d/m/Y", strtotime("+5 day")) ?></td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_p2p',this)">#server_name#</strong></td>
															<td class="text-center">Nome do servidor. (<b><?php echo $server_name ?></b>)</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_p2p',this)">#whatsapp#</strong></td>
															<td class="text-center">Seu WhatsApp. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_p2p',this)">#telegram#</strong></td>
															<td class="text-center">Seu Telegram. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
														</tr>
													</tbody>
												</table>
												<p>Você pode copiar o template do sistema para editar clicando <strong class="hover-var" onclick="pasteDefaultTemplate('template_p2p','p2p')">aqui</strong></p>

											</div>
										</div>
									</div>
									<button class="btn btn-block btn-secondary text-left mb-3 templates" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="true" aria-controls="collapseThree">
										<h6>Template mensagem rápida Código</h6>
										<i class="fal fa-arrow-alt-from-top"></i>
									</button>
									<div id="collapseThree" class="collapse" aria-labelledby="collapseThree" data-parent="#accordionExample">
										<div class="row col-lg-12">
											<div class="col-lg-6" id="div-code">
												<div class="input-group mb-3">
													<textarea class="form-control" id="template_code" name="template_code" style="width: 100%; min-height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" placeholder="Deixe em branco para manter o padrão do sistema"><?php echo getUserProperty($logged_user_id, 'fast_test_template_code', "", true); ?></textarea>
												</div>
											</div>
											<div class="col-lg-6 callout callout-info">
												<h5>Variáveis para Substituição <i class="fad fa-question-circle text-teal" data-toggle="tooltip" data-original-title="Clique na variável para inserir no template rapidamente"></i></h5>
												<p>Use as variáveis abaixo para inserir as informações como usuário e senha ao seu template</p>
												<table class="table table-bordered table-sm">
													<thead>
														<tr>
															<th class="text-center">Variável</th>
															<th class="text-center">Informaço inserida</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_code',this)">#username#</strong></td>
															<td class="text-center">Usuário do cliente criado</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_code',this)">#exp_info#</strong></td>
															<td class="text-center">Informação de vencimento. Ex: expira amanhã</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_code',this)">#exp_date#</strong></td>
															<td class="text-center">Informaço de vencimento.Ex: <?php echo date("d/m/Y", strtotime("+5 day")) ?></td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_code',this)">#server_name#</strong></td>
															<td class="text-center">Nome do servidor. (<b><?php echo $server_name ?></b>)</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_code',this)">#whatsapp#</strong></td>
															<td class="text-center">Seu WhatsApp. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_code',this)">#telegram#</strong></td>
															<td class="text-center">Seu Telegram. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
														</tr>
													</tbody>
												</table>
												<p>Você pode copiar o template do sistema para editar clicando <strong class="hover-var" onclick="pasteDefaultTemplate('template_code','code')">aqui</strong></p>

											</div>
										</div>
									</div>
									<button class="btn btn-block btn-secondary text-left mb-3 templates" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="true" aria-controls="collapseFour">
										<h6>Template mensagem Expiração</h6>
										<i class="fal fa-arrow-alt-from-top"></i>
									</button>
									<div id="collapseFour" class="collapse" aria-labelledby="collapseFour" data-parent="#accordionExample">
										<div class="row col-lg-12" id="div-expiration">
											<div class="col-lg-6">
												<div class="input-group mb-3">
													<textarea class="form-control" id="template_exp_mesage" name="template_exp_mesage" style="width: 100%; min-height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" placeholder="Deixe em branco para manter o padrão do sistema"><?php echo getUserProperty($logged_user_id, 'expiring_template', "", true); ?></textarea>
												</div>
											</div>
											<div class="col-lg-6 callout callout-info">
												<h5>Variveis para Substituição <i class="fad fa-question-circle text-teal" data-toggle="tooltip" data-original-title="Clique na varivel para inserir no template rapidamente"></i></h5>
												<p>Use as variáveis abaixo para inserir as informações como usuário e senha ao seu template</p>
												<table class="table table-bordered table-sm">
													<thead>
														<tr>
															<th class="text-center">Varivel</th>
															<th class="text-center">Informação inserida</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_exp_mesage',this)">#username#</strong></td>
															<td class="text-center">Usuário do cliente criado</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_exp_mesage',this)">#exp_info#</strong></td>
															<td class="text-center">Informaço de vencimento. Ex: expira amanhã</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_exp_mesage',this)">#exp_date#</strong></td>
															<td class="text-center">Informação de vencimento.Ex: <?php echo date("d/m/Y", strtotime("+5 day")) ?></td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_exp_mesage',this)">#server_name#</strong></td>
															<td class="text-center">Nome do servidor. (<b><?php echo $server_name ?></b>)</td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_exp_mesage',this)">#whatsapp#</strong></td>
															<td class="text-center">Seu WhatsApp. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
														</tr>
														<tr>
															<td class="text-center"><strong class="hover-var" onclick="pasteVar('template_exp_mesage',this)">#telegram#</strong></td>
															<td class="text-center">Seu Telegram. <a style="color: #007bff;" href="/profile"><b>Configure aqui</b></a></td>
														</tr>
													</tbody>
												</table>
												<p>Você pode copiar o template do sistema para editar clicando <strong class="hover-var" onclick="pasteDefaultTemplate('template_exp_mesage','exp_mesage')">aqui</strong></p>

											</div>
										</div>
									</div>
								</div>
							</div>
							<textarea class="d-none" id="server_template_iptv"><?php echo getServerProperty('default_test_template_iptv', ""); ?></textarea>
							<textarea class="d-none" id="server_template_p2p"><?php echo getServerProperty('default_test_template_p2p', ""); ?></textarea>
							<textarea class="d-none" id="server_template_code"><?php echo getServerProperty('default_test_template_code', ""); ?></textarea>
							<textarea class="d-none" id="server_template_exp_mesage"><?php echo getServerProperty('default_expiring_template', ""); ?></textarea>
							<script>
								window.onload = function() {
									$('[data-toggle="tooltip"]').tooltip();
								}

								function pasteDefaultTemplate(textarea, type) {
									var textarea = document.getElementById(textarea);
									var template = document.getElementById('server_template_' + type).value;
									textarea.value = template;
									textarea.focus();
								}

								function pasteVar(textarea, element) {
									var texto = element.textContent;
									var textarea = document.getElementById(textarea);
									var posicaoCursor = textarea.selectionStart;
									var textoAntes = textarea.value.substring(0, posicaoCursor);
									var textoDepois = textarea.value.substring(posicaoCursor, textarea.value.length);
									var novoTexto = textoAntes + texto + textoDepois;
									textarea.value = novoTexto;
									var novaPosicaoCursor = posicaoCursor + texto.length;
									textarea.setSelectionRange(novaPosicaoCursor, novaPosicaoCursor);
									textarea.focus();
								}
							</script>
							<div class="card-footer">
								<button type="submit" name="save_info" class="btn btn-primary">Salvar informações</button>
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
	<!-- Select2 -->
	<script src="/plugins/select2/js/select2.full.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.min.js"></script>
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- Summernote -->
	<script src="/plugins/summernote/summernote-bs4.min.js"></script>
	<script src="/plugins/summernote/lang/summernote-pt-BR.js"></script>
	<script>
		$(function() {

			//Initialize Bootstrap Switch
			$("input[data-bootstrap-switch]").each(function() {
				$(this).bootstrapSwitch('state', $(this).prop('checked'));
			});

			//Initialize Select2 Elements
			$('.select2').select2()

			//Initialize Summernote
			$('.textarea').summernote({
				lang: 'pt-BR'
			})
		})
	</script>
	<script type="text/javascript">
		$(".alert").delay(3000).slideUp(200, function() {
			$(this).alert('close');
		});

		$('.use_smtp').on('ifChecked', function(event) {
			if ($('.use_smtp:checked').val() === '0') {
				$(".smtp_form :input").attr("readonly", true);
			} else {
				$(".smtp_form :input").removeAttr("readonly");
			}
		});
	</script>
</body>

</html>