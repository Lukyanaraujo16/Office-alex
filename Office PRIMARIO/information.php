<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$server_name = getServerProperty('server_name');
$fast_packages = json_decode(getServerProperty('fast_packages'), true);
$fixed_informations = htmlspecialchars_decode(getServerProperty('fixed_informations'));
?>
<!DOCTYPE html>
<html lang="pt_BR">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title><?php echo $server_name; ?></title>
	<!-- Font Awesome Icons -->
	<link rel="stylesheet" href="/plugins/fontawesome-pro/css/all.min.css">
	<!-- overlayScrollbars -->
	<link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="/dist/css/adminlte.min.css?<?php echo OFFICE_VERSION ?>">
	<!-- <link rel="stylesheet" href="dist/css/animate.css"> -->
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
							<h1 class="m-0 text-dark">Informações</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active">Informações</li>
							</ol>
						</div>
					</div>
				</div>
			</div>
			<section class="content">
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-6">
							<div class="card card-default">
								<div class="card-header">
									<h3 class="card-title text-center" style="float: none"><strong>ATENÇÃO AS REGRAS DO SERVIDOR</strong></h3>
								</div>
								<div class="card-body" style="display: block;">
									<div class="col-12 col-sm-12">
										<div class="html-content">
											<?php echo $fixed_informations; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="card card-default">
								<div class="card-header">
									<h3 class="card-title text-center" style="float: none"><strong>INFORMAÇÃOES ADCIONAIS</strong></h3>
									
								</div>
								<div class="card-body" style="display: block;">
									<?php if ($permission['iptv']) { ?>
										<div class="col-md-12">
											<label>ATENÇÃO : OS SEUS CLIENTES PODEM BAIXAR TODOS OS NOSSOS APLICATIVOS NA PLAY STORE VIA NTDOWN, APÓS ACESSAR O (NTDOWN), É SÓ DIGITAR O PIN DO APLICATIVO ESCOLHIDO. </label
											
										</div>
									<?php }
									if ($permission['codes']) { ?>
										<div class="col-md-12">
										
										</div>
									<?php }
									if ($permission['binstream']) { ?>
										<div class="col-md-12">
											<label>Link Gerador P2P OFF</label>
											<div class="form-group input-group">
												<input type="text" class="form-control" readonly value="<?php echo getTestUrl($logged_user['id'], "binstream"); ?>" id="generator_p2p" name="generator_p2p">
												<div class="input-group-append"> <button type="button" class="btn btn-sm btn-primary bg-gradient waves-effect waves-light copy_generator_p2p" data-clipboard-target="#generator_p2p">COPIAR</button> </div>
											</div>
										</div>
									<?php } ?>

								</div>
							</div>
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
	<!-- overlayScrollbars -->
	<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- AdminLTE App -->
	<script src="/dist/js/adminlte.js?<?php echo OFFICE_VERSION ?>"></script>
	<!-- Clipboard -->
	<script src="/bower_components/clipboard.min.js"></script>
	<!--Custom JS -->
	<!-- <script src="dist/js/custom.js"></script> -->
	<!-- jQuery Mapael -->
	<script src="/plugins/jquery-mousewheel/jquery.mousewheel.js"></script>
	<script src="/plugins/raphael/raphael.min.js"></script>
	<script src="/plugins/jquery-mapael/jquery.mapael.min.js"></script>
	<script src="/plugins/jquery-mapael/maps/usa_states.min.js"></script>
	<!-- Clipboard -->
	<script src="/bower_components/clipboard.min.js"></script>
	<script>
		new ClipboardJS(".btn");
	</script>
</body>

</html>