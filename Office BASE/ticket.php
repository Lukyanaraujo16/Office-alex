<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$server_name = getServerProperty('server_name');
$fast_packages = json_decode(getServerProperty('fast_packages'), true);

if (!isset($_GET['ticket_id'])) {
	exit();
}

$ticket_id = intval($_GET['ticket_id']);
$ticket = getTicketByID($ticket_id);

if (!$ticket) {
	exit();
}

if (($ticket['member_id'] !== $logged_user['id']) && !isAdmin($logged_user)) {
	exit();
}

if (isset($_POST['send_message']) && isset($_POST['message'])) {
	if ($ticket['status']) {
		$message = purifyHTML($_POST['message']);
		$admin_reply_ = (isAdmin($logged_user) ? 1 : 0);
		insertTicketReply($ticket_id, $admin_reply_, $message);
		$other_person = (isAdmin($logged_user) ? 'user' : 'admin');
		updateReadTicket($ticket_id, $other_person, 0);
		header('location: ?ticket_id=' . $ticket_id . '&result=success');
		exit();
	}
}

$reseller = getUserById($ticket['member_id']);
$reseller_name = ($reseller ? $reseller['username'] : 'Desconhecido(Não encontrado)');
$ticket_replies = getTicketReplies($ticket_id);
$person = (isAdmin($logged_user) ? 'admin' : 'user');
updateReadTicket($ticket_id, $person, 1);

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
	<!-- iCheck for checkboxes and radio inputs -->
	<link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
	<!-- overlayScrollbars -->
	<link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
	<link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
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
							$result_message = 'O ticket foi enviado com sucesso.';
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
							<h1>Ticket</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Ticket</li>
							</ol>
						</div>
					</div>
				</div>
				<!-- /.container-fluid -->
			</section>
			<!-- Main content -->
			<section class="content">
				<div class="container-fluid">
					<div class="card card-prirary cardutline direct-chat direct-chat-primary" style="height: 610px;">
						<div class="card-header">
							<h3 class="card-title">Ticket: <?php echo $ticket['title']; ?></h3>
						</div>
						<div class="card-body">
							<div class="direct-chat-messages" style="height: 500px;">
								<?php foreach ($ticket_replies as $current_reply) {
									$is_owner = isAdmin($logged_user) == $current_reply['admin_reply'];
								?>
									<div class="direct-chat-msg <?php if ($is_owner) {
																								echo 'right';
																							} ?>">
										<div class="direct-chat-infos clearfix">
											<span class="direct-chat-name <?php echo $is_owner ? 'float-right' : 'float-left' ?>"><?php echo $current_reply['admin_reply'] ? 'Admin' : $reseller_name; ?></span>
											<span class="direct-chat-timestamp <?php echo $is_owner ? 'float-left' : 'float-right' ?>"><?php echo date('d/m/Y H:i:s', $current_reply['date']); ?></span>
										</div>
										<!-- /.direct-chat-infos -->
										<img class="direct-chat-img" <?php echo $current_reply['admin_reply'] ? 'src="/dist/img/admin.png"' : 'src="/dist/img/avatar5.png"'; ?> alt="Message User Image">
										<!-- /.direct-chat-img -->
										<div class="direct-chat-text">
											<?php echo $current_reply['message']; ?>
										</div>
										<!-- /.direct-chat-text -->
									</div>
									<!-- /.direct-chat-msg -->
								<?php } ?>
							</div>


							<!-- /.direct-chat-pane -->
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<form action="#" method="post">
								<div class="input-group">
									<input type="text" name="message" placeholder="Digite sua mensagem aqui ..." class="form-control" <?php if (!$ticket['status']) {
																																																											echo 'disabled';
																																																										} ?>>
									<span class="input-group-append">
										<button type="submit" class="btn btn-primary" name="send_message" <?php if (!$ticket['status']) {
																																												echo 'disabled';
																																											} ?>>Enviar</button>
									</span>
								</div>
							</form>
						</div>
						<!-- /.card-footer-->
					</div>
					<!--/.direct-chat -->
				</div>
				<!-- /.col -->

				<div class="col-md-3">
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
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- InputMask -->
	<script src="/plugins/moment/moment.min.js"></script>
	<script src="/plugins/inputmask/min/jquery.inputmask.bundle.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.min.js?<?php echo OFFICE_VERSION ?>"></script>
	<!-- Page script -->
	<script>
		$(function() {
			$(".alert").delay(3000).slideUp(200, function() {
				$(this).alert('close');
			});
		});
	</script>
</body>

</html>