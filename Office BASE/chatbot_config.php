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
<style>
	.me-3 {
		margin-right: 1rem !important;
	}
</style>

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
						case 'exist_reseller':
							$result_message = 'Já existe um usuário com este nome de usuário, escolha outro.';
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
							<h1>Como Configurar</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
								<li class="breadcrumb-item"><a href="/chatbot/list">ChatBot</a></li>
								<li class="breadcrumb-item active">Como Configurar</li>
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
							<h3 class="card-title">Siga as instruções abaixo</h3>
						</div>
						<!-- /.card-header -->
						<form autocomplete="off" name="frm1">
							<div class="card-body">
								<div class="tab-pane">
									<div class="faq-box d-flex mb-2">
										<div class="flex-shrink-0 me-3 faq-icon" style="width: 20px; height: 20px;">
											<p style="font-size: 2.5em; font-weight: bold;">1</p>
										</div>
										<div class="flex-grow-1">
											<h5 class="font-size-15">Baixe o aplicativo Auto Reply</h5>
											<p class="text-muted">Aplicativo disponível apenas para android <a href="https://play.google.com/store/apps/details?id=com.pransuinc.autoreply&amp;hl=en_US&amp;gl=US" target="_blank">baixar aqui</a></p>
										</div>
									</div>

									<div class="faq-box d-flex mb-2">
										<div class="flex-shrink-0 me-3 faq-icon" style="width: 20px; height: 20px;">
											<p style="font-size: 2.5em; font-weight: bold;">2</p>
										</div>
										<div class="flex-grow-1">
											<h5 class="font-size-15">Abra o aplicativo</h5>
											<p class="text-muted">Inicie o App e forneça todas as permissões necessárias.</p>
										</div>
									</div>

									<div class="faq-box d-flex mb-2">
										<div class="flex-shrink-0 me-3 faq-icon" style="width: 20px; height: 20px;">
											<p style="font-size: 2.5em; font-weight: bold;">3</p>
										</div>
										<div class="flex-grow-1">
											<h5 class="font-size-15">Apague todas as mensagens</h5>
											<p class="text-muted">Apague as mensagens padrão do app e clique no ícone <i class="fas fa-plus"></i> para adicionar uma nova resposta.</p>
										</div>
									</div>

									<div class="faq-box d-flex mb-2">
										<div class="flex-shrink-0 me-3 faq-icon" style="width: 20px; height: 20px;">
											<p style="font-size: 2.5em; font-weight: bold;">4</p>
										</div>
										<div class="flex-grow-1">
											<h5 class="font-size-15">Selecione o Whatsapp</h5>
											<p class="text-muted">Selecione qual WhatsApp esta usando, WA Offical para o Whatsapp normal, WA Business para Whatsapp Business.</p>
										</div>
									</div>

									<div class="faq-box d-flex mb-2">
										<div class="flex-shrink-0 me-3 faq-icon" style="width: 20px; height: 20px;">
											<p style="font-size: 2.5em; font-weight: bold;">5</p>
										</div>
										<div class="flex-grow-1">
											<h5 class="font-size-15">Defina a mensagem recebida</h5>
											<p class="text-muted">Em Received message pattern marque a opção All.</p>
										</div>
									</div>

									<div class="faq-box d-flex mb-2">
										<div class="flex-shrink-0 me-3 faq-icon" style="width: 20px; height: 20px;">
											<p style="font-size: 2.5em; font-weight: bold;">6</p>
										</div>
										<div class="flex-grow-1">
											<h5 class="font-size-15">Ativar a opção de próprio servidor</h5>
											<p class="text-muted">Role a tela e em Reply message, ative a opção Connect to own Server.</p>
										</div>
									</div>

									<div class="faq-box d-flex mb-2">
										<div class="flex-shrink-0 me-3 faq-icon" style="width: 20px; height: 20px;">
											<p style="font-size: 2.5em; font-weight: bold;">7</p>
										</div>
										<div class="flex-grow-1">
											<h5 class="font-size-15">Defina a URL do servidor</h5>
											<p class="text-muted">No campo Server URL, digite o seguinte link: <?php echo getChatbotUrl($logged_user['id']) ?></p>
										</div>
									</div>

									<div class="faq-box d-flex mb-2">
										<div class="flex-shrink-0 me-3 faq-icon" style="width: 20px; height: 20px;">
											<p style="font-size: 2.5em; font-weight: bold;">8</p>
										</div>
										<div class="flex-grow-1">
											<h5 class="font-size-15">Salvar</h5>
											<p class="text-muted">Clique no ícone <i class="fas fa-check"></i> no canto inferior direito para salvar.</p>
										</div>
									</div>

									<div class="faq-box d-flex mb-2">
										<div class="flex-shrink-0 me-3 faq-icon" style="width: 20px; height: 20px;">
											<p style="font-size: 2.5em; font-weight: bold;">9</p>
										</div>
										<div class="flex-grow-1">
											<h5 class="font-size-15">Ative</h5>
											<p class="text-muted">Clique no ícone <i class="fas fa-toggle-off"></i> para Ativar o ChatBot.</p>
										</div>
									</div>
								</div>
							</div>
							<div class="card-footer">
								<a class="btn btn-primary addrule">Criar Regra</a>
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
	<!-- SweetAlert2 -->
	<script src="/plugins/sweetalert2/sweetalert2.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.min.js?<?php echo OFFICE_VERSION ?>"></script>
	<!-- Page script -->
</body>

</html>