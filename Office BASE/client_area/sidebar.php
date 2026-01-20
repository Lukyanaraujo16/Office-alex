<?php
include_once('../sys/functions.php');
isClientLogged();
$logged_user = getLoggedClient();
$server_name = getServerProperty('server_name');
$default_password = getServerProperty("code_default_pass");
?>
<style>
	.nav-item.dropdown:hover .dropdown-menu {
		display: block !important;
	}
</style>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand <?php if (DarkMode(true)) {
																								echo "navbar-dark";
																							} else {
																								echo "navbar-white navbar-light";
																							} ?>">
	<!-- Left navbar links -->
	<ul class="navbar-nav">
		<li class="nav-item">
			<a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
		</li>
	</ul>
	<!-- Right navbar links -->
	<ul class="navbar-nav ml-auto">
		<!-- Notifications Dropdown Menu -->
		<li class="nav-item">
			<?php if ($logged_user["password"] == $default_password) { ?>
				<spam class="badge badge-info">Código: <?php echo $logged_user["username"] ?></spam>
			<?php } else { ?>
				<spam class="badge badge-info">Usuário: <?php echo $logged_user["username"] ?></spam>
			<?php } ?>
		</li>
	</ul>
</nav>
<!-- /.navbar -->
<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-info elevation-4">
	<!-- Brand Logo -->
	<a href="#" class="brand-link">
		<img src="<?php echo get_logo("small") ?>" alt="<?php echo $server_name; ?>" class="brand-image" style="float: none;">
		<!--span class="brand-text font-weight-light"><?php echo $server_name; ?></span-->
	</a>
	<!-- Sidebar -->
	<div class="sidebar">
		<!-- Sidebar user panel (optional) -->
		<!-- Sidebar Menu -->
		<nav class="mt-2">
			<ul class="nav nav-pills nav-sidebar flex-column nav-child-indent text-sm" data-widget="treeview" role="menu" data-accordion="false">
				<!-- Add icons to the links using the .nav-icon class
					with font-awesome or any other icon font library -->
				<li class="nav-header">Menu Principal</li>
				<li class="nav-item <?php if ($_SERVER["REQUEST_URI"] == "/client_area/dashboard.php") {
															echo 'menu-open';
														};  ?>">
					<a href="/client_area/dashboard.php" class="nav-link">
						<i class="nav-icon fas fa-tachometer-alt"></i>
						<p>Dashboard</p>
					</a>
				</li>
				<?php if ((getUserProperty($logged_user['member_id'], "mercado_pago")) || (getUserProperty($logged_user['member_id'], "pag_seguro"))) { ?>
					<li class="nav-item <?php if ($_SERVER["REQUEST_URI"] == "/client_area/renew.php") {
																echo 'menu-open';
															};  ?>">
						<a href="/client_area/renew.php" class="nav-link">
							<i class="nav-icon far fa-calendar-plus"></i>
							<p>Renovar plano</p>
						</a>
					</li>
				<?php } ?>
				<li class="nav-item <?php if ($_SERVER["REQUEST_URI"] == "/client_area/transactions.php") {
															echo 'menu-open';
														};  ?>">
					<a href="/client_area/transactions.php" class="nav-link">
						<i class="nav-icon fas fa-receipt"></i>
						<p>Historico de Pagamentos</p>
					</a>
				</li>
				<li class="nav-item <?php if ($_SERVER["REQUEST_URI"] == "/client_area/new_content.php") {
															echo 'menu-open';
														};  ?>">
					<a href="/client_area/new_content.php" class="nav-link">
						<i class="nav-icon fas fa-ticket-alt"></i>
						<p>Coneúdo Novo</p>
					</a>
				</li>

				<?php
				$telegram = getUserProperty($logged_user['member_id'], 'telegram');
				$whatsapp = getUserProperty($logged_user['member_id'], 'whatsapp');
				if ((!empty($telegram)) or (!empty($whatsapp))) { ?>
					<li class="nav-header">Suporte</li>
				<?php }
				if (!empty($telegram)) { ?>
					<li class="nav-item">
						<a href="<?php echo "https://t.me/" . $telegram ?>" target="_blank" class="nav-link">
							<i class="nav-icon fab fa-telegram-plane"></i>
							<p>Telegram</p>
						</a>
					</li>
				<?php }
				if (!empty($whatsapp)) { ?>
					<li class="nav-item">
						<a href="<?php echo $whatsapp ?>" target="_blank" class="nav-link">
							<i class="nav-icon fab fa-whatsapp"></i>
							<p>WhatsApp</p>
						</a>
					</li>
				<?php } ?>
				<li class="nav-header"></li>
				<li class="nav-item">
					<a href="logout.php" class="nav-link">
						<i class="nav-icon fas fa-sign-out-alt"></i>
						<p>Sair</p>
					</a>
				</li>
			</ul>
		</nav>
		<!-- /.sidebar-menu -->
	</div>
	<!-- /.sidebar -->
</aside>