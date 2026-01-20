<?php
include_once(__DIR__ . '/sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$server_name = getServerProperty('server_name');
$fast_packages = json_decode(getServerProperty('fast_packages'), true);
$fixed_informations = getServerProperty('fixed_informations');
$allowed_pages = getAllowedPages($logged_user['id']);

$permission['iptv'] = hasPermissionResource($logged_user['id'], "iptv");
$permission['binstream'] = (hasPermissionResource($logged_user['id'], "binstream") && binStreamEnabled()['success']);
$permission['codes'] = (hasPermissionResource($logged_user['id'], "codes") && getServerProperty('code_status', 1));

$unreadTickets = getUnreadTicketsCount($logged_user);
?>
<style>
	.nav-item.dropdown:hover .dropdown-menu {
		display: block !important;
	}
</style>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand <?php if (DarkMode()) {
																								echo "navbar-dark";
																							} else {
																								echo "navbar-white navbar-light";
																							} ?>">
	<!-- Left navbar links -->
	<ul class="navbar-nav">
		<li class="nav-item">
			<a class="nav-link" data-widget="pushmenu" href="#"><i class="fad fa-bars"></i></a>
		</li>
		<li class="nav-item">
			<?php if (DarkMode()) { ?>
				<a data-toggle="tooltip" data-original-title="Desativar Modo Noturno" class="nav-link darkmode" data-widget="darkmode" href="#"><i class='fad fa-sun' style='color:#fbff05'></i></a>
			<?php } else { ?>
				<a data-toggle="tooltip" data-original-title="Ativar Modo Noturno" class="nav-link darkmode" data-widget="darkmode" href="#"><i class='fad fa-moon-stars' style='color:#000000'></i></a>
			<?php } ?>
		</li>
		<a class="nav-link" href="/tickets">
			<i class="far fa-comments"></i>
			<?php if ($unreadTickets > 0) { ?>
				<span class="badge badge-danger navbar-badge"><?php echo $unreadTickets; ?></span>
			<?php } ?>
		</a>


		<button type="button" class="btn btn-block btn-primary btn-xs" data-toggle="dropdown" aria-expanded="false">Teste Rápido
		</button>
		<div class="dropdown-menu ml-5" role="menu" style="">
			<?php if ($permission['iptv']) { ?>
				<a class="dropdown-item" href="#" data-toggle="modal" data-target="#fast_test_iptv_modal">Teste Rápido IPTV</a>
			<?php }
			if ($permission['binstream']) { ?>
				<div class="dropdown-divider"></div>
				<a class="dropdown-item" href="#" data-toggle="modal" data-target="#fast_test_p2p_modal">Teste Rápido P2P</a>
			<?php }
			if ($permission['codes']) { ?>
				<div class="dropdown-divider"></div>
				<a class="dropdown-item" href="#" data-toggle="modal" data-target="#fast_test_code_modal">Teste Rápido Código</a>
			<?php } ?>
		</div>
	</ul>
	<!-- Right navbar links -->
	<ul class="navbar-nav ml-auto">
		<!-- Notifications Dropdown Menu -->
		<li class="nav-item">
			<spam class="badge badge-info">Créditos: <?php echo getCreditsByUser($logged_user); ?></spam>
		</li>
		<?php if (isAdmin($logged_user) || isPartner($logged_user)) { ?>


			<li class="nav-item dropdown">
				<a class="nav-link" href="/settings/geral">
					<i class='fad fa-cogs'></i>
				</a>
			</li>
		<?php } ?>
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
		<div class="user-panel mt-1 pb-1 mb-1 d-flex">
			<div class="info">
				<a href="/profile" class="d-block"><?php echo "Bem vindo, " . $logged_user['username'] ?></a>
			</div>
		</div>
		<!-- Sidebar Menu -->
		<nav class="mt-2">
			<ul class="nav nav-pills nav-sidebar flex-column nav-flat" data-widget="treeview" role="menu" data-accordion="false">
				<!-- Add icons to the links using the .nav-icon class
					with font-awesome or any other icon font library -->
				<!-- SECTION INFO -->
				<li class="nav-header">INFORMAÇÕES</li>
				<li class="nav-item <?php if (basename($_SERVER['SCRIPT_NAME']) == "dashboard.php") {
															echo 'menu-open';
														};  ?>">
					<a href="/dashboard" class="nav-link">
						<i class="nav-icon fas fa-tachometer-alt"></i>
						<p>Dashboard</p>
					</a>
				</li>
				<li class="nav-item <?php if (basename($_SERVER['SCRIPT_NAME']) == "information.php") {
															echo 'menu-open';
														};  ?>">
					<a href="/information" class="nav-link">
						<i class="nav-icon fad fa-newspaper"></i>
						<p>informações</p>
					</a>
				</li>
				<li class="nav-item <?php if (basename($_SERVER['SCRIPT_NAME']) == "new_content.php") {
															echo 'menu-open';
														};  ?>">
					<a href="/new_content" class="nav-link">
						<i class="nav-icon fad fa-play"></i>
						<p>Conteúdo Novo</p>
					</a>
				</li>
				<li class="nav-item has-treeview <?php if (in_array(basename($_SERVER['SCRIPT_NAME']), ["chatbot_config.php", "chatbot_create.php", "chatbot_list.php", "chatbot_edit.php"], true)) {
																						echo 'menu-open';
																					};  ?>">
					<a href="#" class="nav-link">
						<i class="nav-icon fad fa-user-robot" style="--fa-secondary-opacity: 0.6;"></i>
						<p>Chatbot<i class="fas fa-angle-left right"></i>
							<!--span class="badge badge-info right">6</span-->
						</p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item">
							<a href="/chatbot/config" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "chatbot_config.php") {
																													echo 'active';
																												};  ?>">
								<i class="far fa-circle nav-icon"></i>
								<p>Como configurar</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="/chatbot/list" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "chatbot_list.php") {
																												echo 'active';
																											};  ?>">
								<i class="far fa-circle nav-icon"></i>
								<p>Listar Regras</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="/chatbot/new" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "chatbot_create.php") {
																												echo 'active';
																											};  ?>">
								<i class="far fa-circle nav-icon"></i>
								<p>Criar Regra</p>
							</a>
						</li>
					</ul>
				</li>
				<!-- !SECTION -->
				<!-- SECTION IPTV -->
				<?php if ($permission['iptv']) { ?>

					<li class="nav-header">GESTÃO DE CLIENTES</li>
					<li class="nav-item has-treeview <?php if (in_array(basename($_SERVER['SCRIPT_NAME']), ["iptv_create_test.php"], true)) {
																							echo 'menu-open';
																						};  ?>">
						<a href="#" class="nav-link">
							<i class="nav-icon fad fa-user-clock" style="--fa-secondary-color: #d9831f; --fa-secondary-opacity: 0.8"></i>
							<p>Criar Teste<i class="fas fa-angle-left right"></i>
								<!--span class="badge badge-info right">6</span-->
							</p>
						</a>
						<ul class="nav nav-treeview">
							<li class="nav-item">
								<a href="/iptv/newtest" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "iptv_create_test.php") {
																													echo 'active';
																												};  ?>">
									<i class="far fa-circle nav-icon"></i>
									<p>PERSONALIZADO</p>
								</a>
							</li>
							<?php $packages = getPackages();
							foreach ($fast_packages as $package_id) {
								$package_key = array_search($package_id, array_column($packages, 'id'));

								if ($package_key !== false) {
									$current_package = $packages[$package_key];

									if ($current_package['is_trial'] == 1) {
										echo '<li class="nav-item"><a href="/sys/api.php?action=create_test&package_id=';
										echo $current_package['id'];
										echo '" class="nav-link" ><i class="far fa-circle nav-icon"></i> ';
										echo $current_package['package_name'];
										echo '</a></li>
							                  ';
									}
								}
							}
							?>
						</ul>
					</li>
					<li class="nav-item has-treeview <?php if (in_array(basename($_SERVER['SCRIPT_NAME']), ["iptv_online.php", "iptv_list.php", "iptv_client_create.php", "iptv_client_edit.php", "iptv_migrate_client.php"], true)) {
																							echo 'menu-open';
																						};  ?>">
						<a href="#" class="nav-link">
							<i class="nav-icon fad fa-user-check" style="--fa-secondary-color: #6AE00C; --fa-secondary-opacity: 0.8"></i>
							<p>Cliente Definitivo<i class="right fas fa-angle-left"></i>
							</p>
						</a>
						<ul class="nav nav-treeview">
							<?php if (getServerProperty('iptv_show_online_clients', 1)) { ?>
								<li class="nav-item">
									<a href="/iptv/online" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "iptv_online.php") {
																														echo 'active';
																													};  ?>">
										<i class="far fa-circle nav-icon"></i>
										<p>Clientes Online</p>
									</a>
								</li>
							<?php } ?>
							<li class="nav-item">
								<a href="/iptv/clients" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "iptv_list.php") {
																													echo 'active';
																												};  ?>">
									<i class="far fa-circle nav-icon"></i>
									<p>Gerir Clientes</p>
								</a>
							</li>
							<li class="nav-item">
								<a href="/iptv/new" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "iptv_client_create.php") {
																											echo 'active';
																										};  ?>">
									<i class="far fa-circle nav-icon"></i>
									<p>Criar Cliente</p>
								</a>
							</li>
							<?php if (getServerProperty('iptv_migration_status', 1)) { ?>
								<li class="nav-item">
									<a href="/iptv/migrate" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "iptv_migrate_client.php") {
																														echo 'active';
																													};  ?>">
										<i class="far fa-circle nav-icon"></i>
										<p>Migrar Cliente</p><span class="right badge badge-info">Novo</span>
									</a>
								</li>
							<?php } ?>
						</ul>
					</li>
					<!-- !SECTION -->
					<!-- SECTION CODES -->
				<?php }
				if ($permission['codes'] && getServerProperty('code_status', 1)) { ?>
					<li class="nav-item has-treeview <?php if (in_array(basename($_SERVER['SCRIPT_NAME']), ["codes_create.php", "codes_create_multi.php", "codes_online.php", "codes_list.php", "codes_edit.php"], true)) {
																							echo 'menu-open';
																						};  ?>">
						<a href="#" class="nav-link">
							<i class="nav-icon fad fa-key" style="--fa-secondary-opacity: 0.8"></i>
							<p>Códigos<i class="right fas fa-angle-left"></i>
							</p>
						</a>
						<ul class="nav nav-treeview">
							<?php if (getServerProperty('iptv_show_online_clients', 1)) { ?>
								<li class="nav-item">
									<a href="/codes/online" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "codes_online.php") {
																														echo 'active';
																													};  ?>">
										<i class="far fa-circle nav-icon"></i>
										<p>Online</p>
									</a>
								</li>
							<?php } ?>
							<li class="nav-item">
								<a href="/codes/clients" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "codes_list.php") {
																														echo 'active';
																													};  ?>">
									<i class="far fa-circle nav-icon"></i>
									<p>Gerir Códigos</p>
								</a>
							</li>
							<li class="nav-item">
								<a href="/codes/new" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "codes_create.php") {
																												echo 'active';
																											};  ?>">
									<i class="far fa-circle nav-icon"></i>
									<p>Criar Código</p>
								</a>
							</li>
							<li class="nav-item">
								<a href="/codes/new/multi" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "codes_create_multi.php") {
																															echo 'active';
																														};  ?>">
									<i class="far fa-circle nav-icon"></i>
									<p>Criar Códigos em Massa</p>
								</a>
							</li>
						</ul>
					</li>
				<?php
				}
				if ($permission['binstream']) { ?>
					<!-- !SECTION -->
					<!-- SECTION P2P BINSTREAM -->
					<li class="nav-header">Clientes P2P</li>
					<li class="nav-item has-treeview <?php if (in_array(basename($_SERVER['SCRIPT_NAME']), ["bin_create_test.php"], true)) {
																							echo 'menu-open';
																						};  ?>">
						<a href="#" class="nav-link">
							<i class="nav-icon fad fa-user-clock" style="--fa-secondary-color: #d9831f; --fa-secondary-opacity: 0.8"></i>
							<p>Criar Teste P2P<i class="fas fa-angle-left right"></i>
								<!--span class="badge badge-info right">6</span-->
							</p>
						</a>
						<ul class="nav nav-treeview">
							<li class="nav-item">
								<a href="/p2p/newtest" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "bin_create_test.php") {
																													echo 'active';
																												};  ?>">
									<i class="far fa-circle nav-icon"></i>
									<p>Personalizado</p>
								</a>
							</li>
							<?php
							$key = OFFICE_CONFIG['panel_id'] . "_binstream_packages";
							$cached_result = $redis->get($key);
							if ($cached_result) {
								$packages = json_decode($cached_result, true);
							} else {
								include_once(__DIR__ . "/sys/class/binstream.php");
								$binstream = new BinStream();
								$packages = $binstream->getPackages();
								$redis->setex($key, 3600, json_encode($packages));
							}
							$binstream_allowed_packages = json_decode(getServerProperty('binstream_allowed_packages'), true);

							foreach ($binstream_allowed_packages as $package_id) {
								$package_key = array_search($package_id, array_column($packages, 'id'));
								$current_package = $packages[$package_key]; ?>
								<li class="nav-item">
									<a href="/sys/api.php?action=create_test&type=binstream&package_id=<?php echo $current_package['id']; ?>" class="nav-link"><i class="far fa-circle nav-icon"></i><?php echo $current_package['name']; ?></a>
								</li>
							<?php } ?>
						</ul>
					</li>
					<li class="nav-item has-treeview <?php if (in_array(basename($_SERVER['SCRIPT_NAME']), ["bin_list_clients.php", "bin_create_client.php", "bin_edit_client.php"], true)) {
																							echo 'menu-open';
																						};  ?>">
						<a href="#" class="nav-link">
							<i class="nav-icon fad fa-user-check" style="--fa-secondary-color: #6AE00C; --fa-secondary-opacity: 0.8"></i>
							<p>Cliente P2P<i class="right fas fa-angle-left"></i>
							</p>
						</a>
						<ul class="nav nav-treeview">
							<li class="nav-item">
								<a href="/p2p/clients" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "bin_list_clients.php") {
																													echo 'active';
																												};  ?>">
									<i class="far fa-circle nav-icon"></i>
									<p>Gerir Clientes</p>
								</a>
							</li>
							<li class="nav-item">
								<a href="/p2p/new" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "bin_create_client.php") {
																											echo 'active';
																										};  ?>">
									<i class="far fa-circle nav-icon"></i>
									<p>Criar Cliente</p>
								</a>
							</li>
						</ul>
					</li>
				<?php } ?>
				<!-- !SECTION -->
				<!-- SECTION RESELLER -->
				<?php if (isAdmin($logged_user) || isPartner($logged_user) || isUltra($logged_user) || isMaster($logged_user)) { ?>
					<li class="nav-header">REVENDEDORES</li>
					<li class="nav-item has-treeview <?php if (in_array(basename($_SERVER['SCRIPT_NAME']), ["reseller_list.php", "reseller_create.php", "reseller_edit.php"], true)) {
																							echo 'menu-open';
																						};  ?>">
						<a href="#" class="nav-link">
							<i class="nav-icon fad fa-users" style="--fa-secondary-opacity: 0.8"></i>
							<p>Revendedores<i class="fas fa-angle-left right"></i>
							</p>
						</a>
						<ul class="nav nav-treeview">
							<li class="nav-item">
								<a href="/resellers" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "reseller_list.php") {
																												echo 'active';
																											};  ?>">
									<i class="far fa-circle nav-icon"></i>
									<p>Gerir Revendas</p>
								</a>
							</li>
							<li class="nav-item">
								<a href="/reseller/new" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "reseller_create.php") {
																													echo 'active';
																												};  ?>">
									<i class="far fa-circle nav-icon"></i>
									<p>Criar Revenda</p>
								</a>
							</li>
						</ul>
					</li>
				<?php } ?>
				<!-- !SECTION -->
				<!-- SECTION TOOLS -->
				<li class="nav-header">FERRAMENTAS</li>
				<li class="nav-item <?php if (basename($_SERVER['SCRIPT_NAME']) == "shortener.php") {
															echo 'menu-open';
														};  ?>">
					<a href="/shortener" class="nav-link">
						<i class="nav-icon fad fa-link" style="--fa-secondary-opacity: 0.8"></i>
						<p>Encurtador de Links</p>
					</a>
				</li>
				<li class="nav-item <?php if (basename($_SERVER['SCRIPT_NAME']) == "tools.php") {
															echo 'menu-open';
														};  ?>">
					<a href="/tools" class="nav-link">
						<i class="nav-icon fad fa-tools" style="--fa-secondary-opacity: 0.8"></i>
						<p>Ferramentas</p>
					</a>
				</li>
				<!-- !SECTION -->
				<!-- SECTION TICKETS -->
				<li class="nav-item has-treeview <?php if (in_array(basename($_SERVER['SCRIPT_NAME']), ["ticket.php", "create_ticket.php", "manage_tickets.php"], true)) {
																						echo 'menu-open';
																					};  ?>">
					<a href="#" class="nav-link">
						<i class="nav-icon fad fa-ticket" style="--fa-secondary-opacity: 0.8"></i>
						<p>Tickets de Suporte<i class="fas fa-angle-left right"></i>
						</p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item">
							<a href="/ticket/new" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "create_ticket.php") {
																											echo 'active';
																										};  ?>">
								<i class="far fa-circle nav-icon"></i>
								<p>Criar Ticket</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="/tickets" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "manage_tickets.php") {
																										echo 'active';
																									};  ?>">
								<i class="far fa-circle nav-icon"></i>
								<p>Gerenciar Tickets</p>
							</a>
						</li>
					</ul>
				</li>
				<!-- !SECTION -->
				<!-- SECTION CONTACTS -->
				<?php
				$telegram = getServerProperty('telegram');
				$whatsapp = getServerProperty('whatsapp');
				if ((!empty($telegram)) or (!empty($whatsapp))) { ?>
					<li class="nav-header">SUPORTE/APLICATIVOS</li>
				<?php }
				if (!empty($telegram)) { ?>
					<li class="nav-item">
						<a href="<?php echo $telegram ?>" target="_blank" class="nav-link">
							<i class="nav-icon fab fa-telegram-plane"></i>
							<p>Telegram</p>
						</a>
					</li>
				<?php }
				if (!empty($whatsapp)) { ?>
					<li class="nav-item">
						<a href="<?php echo $whatsapp ?>" target="_blank" class="nav-link">
						    <i class="nav-icon fab fas fa-mobile-alt"></i>
						    <p>Loja de Aplicativos</p>
							
				    	</a>
					</li>
				<?php } ?>
				<!-- !SECTION -->
				<!-- SECTION CONFIGS -->
				<li class="nav-header">CONFIGURAÇÕES</li>

				<li class="nav-item has-treeview <?php if (in_array(basename($_SERVER['SCRIPT_NAME']), ["gateways.php", "plans.php"], true)) {
																						echo 'menu-open';
																					};  ?>">
					<a href="#" class="nav-link">
						<i class="nav-icon fad fa-users-cog" style="--fa-secondary-opacity: 0.8; --fa-secondary-color: #9CAAB4;"></i>
						<p>Não altere as configuraçes<i class="fas fa-angle-left right"></i>
						</p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item">
							<a href="/plans" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "plans.php") {
																									echo 'active';
																								};  ?>">
								<i class="far fa-circle nav-icon"></i>
								<p>Configurações Gerais</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="/gateways" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "gateways.php") {
																										echo 'active';
																									};  ?>">
								<i class="far fa-circle nav-icon"></i>
								<p>Configurar Gateways</p>
							</a>
						</li>
					</ul>
				</li>
				<!-- !SECTION -->
				<!-- SECTION LOGS -->
				<li class="nav-item has-treeview <?php if (in_array(basename($_SERVER['SCRIPT_NAME']), ["transactions.php", "active_clients_report.php", "bin_active_clients_report.php", "log_reseller.php", "log_credits.php"], true)) {
																						echo 'menu-open';
																					};  ?>">
					<a href="#" class="nav-link">
						<i class="nav-icon fas fa-file-invoice-dollar"></i>
						<p>Relatrios<i class="fas fa-angle-left right"></i>
						</p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item">
							<a href="/reports/transactions" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "transactions.php") {
																																echo 'active';
																															};  ?>">
								<i class="fas fa-receipt nav-icon"></i>
								<p>Pagamentos</p>
							</a>
						</li>
					</ul>
					<ul class="nav nav-treeview">
						<li class="nav-item">
							<a href="/reports/active_clients" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "active_clients_report.php") {
																																	echo 'active';
																																};  ?>">
								<i class="far fa-address-card nav-icon"></i>
								<p>Clientes Ativos</p>
							</a>
						</li>
					</ul>
					<?php
					if ($permission['binstream']) { ?>
						<ul class="nav nav-treeview">
							<li class="nav-item">
								<a href="/reports/p2p/active_clients" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "bin_active_clients_report.php") {
																																				echo 'active';
																																			};  ?>">
									<i class="far fa-address-card nav-icon"></i>
									<p>Clientes Ativos P2P<span class="right badge badge-info">Novo</span></p>
								</a>
							</li>
						</ul>
					<?php } ?>
					<ul class="nav nav-treeview">
						<li class="nav-item">
							<a href="/reports/reseller" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "log_reseller.php") {
																														echo 'active';
																													};  ?>">
								<i class="fas fa-list-ul nav-icon"></i>
								<p>Log de Revenda</p>
							</a>
						</li>
					</ul>
					<ul class="nav nav-treeview">
						<li class="nav-item">
							<a href="/reports/credits" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "log_credits.php") {
																														echo 'active';
																													};  ?>">
								<i class="fas fa-list-ol nav-icon"></i>
								<p>Log de Créditos</p>
							</a>
						</li>
					</ul>
				</li>
				<!-- !SECTION -->
				<!-- SECTION MAIL -->
				<li class="nav-item has-treeview <?php if (in_array(basename($_SERVER['SCRIPT_NAME']), ["mail_settings.php", "mail_templates.php"], true)) {
																						echo 'menu-open';
																					};  ?>">
					<a href="#" class="nav-link">
						<i class="nav-icon far fa-envelope"></i>
						<p>E-mail<i class="fas fa-angle-left right"></i>
						</p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item">
							<a href="/email/settings" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "mail_settings.php") {
																													echo 'active';
																												};  ?>">
								<i class="fas fa-at nav-icon"></i>
								<p>Configurações SMTP</p>
							</a>
						</li>
					</ul>
					<ul class="nav nav-treeview">
						<li class="nav-item">
							<a href="/email/templates" class="nav-link <?php if (basename($_SERVER['SCRIPT_NAME']) == "mail_templates.php") {
																														echo 'active';
																													};  ?>">
								<i class="far fa-file-alt nav-icon"></i>
								<p>Templates</p>
							</a>
						</li>
					</ul>
				</li>
				<!-- !SECTION -->
				<!-- SECTION TEMPLATE -->
				<li class="nav-item <?php if (basename($_SERVER['SCRIPT_NAME']) == "template.php") {
															echo 'menu-open';
														};  ?>">
					<a href="/template" class="nav-link">
						<i class="nav-icon far fa-file-alt"></i>
						<p>Template Mensagem Rápida</p>
					</a>
				</li>
				<!-- !SECTION -->
				<!-- SECTION CHANGELOGS -->
				<li class="nav-item <?php if (basename($_SERVER['SCRIPT_NAME']) == "update.php") {
															echo 'menu-open';
														};  ?>">
					
					</a>
				</li>
				<!-- !SECTION -->
				<!-- SECTION PROFILE -->
				<li class="nav-item <?php if (basename($_SERVER['SCRIPT_NAME']) == "profile.php") {
															echo 'menu-open';
														};  ?>">
					<a href="/profile" class="nav-link">
						<i class="nav-icon far fa-user-circle"></i>
						<p>Perfil</p>
					</a>
				</li>
				<!-- !SECTION -->
				<!-- SECTION LOGOUT -->
				<li class="nav-item">
					<a href="/logout" class="nav-link">
						<i class="nav-icon fas fa-sign-out-alt"></i>
						<p>Sair</p>
					</a>
				</li>
				<!-- !SECTION -->
			</ul>
		</nav>
		<!-- /.sidebar-menu -->
	</div>
	<!-- /.sidebar -->
</aside>
<div id="fast_test_p2p_modal" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Dados do teste P2P</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<textarea class="form-control modal-textarea-p2p" id="test_modal_p2p" rows="15" placeholder="Aguarde..."></textarea><br>
				<div>
					<p><strong>Esse Template pode ser alterado <a href="/template.php">AQUI</a></strong></p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
				<a type="button" id="modal-wpp-p2p" class="btn btn-success" href="#" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
				<button type="button" class="btn btn-primary btn-modal-test" data-clipboard-target="#test_modal_p2p"><i class="far fa-copy"></i> Copiar Dados!</button>
			</div>
		</div>
	</div>
</div>
<div id="fast_test_code_modal" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Dados do teste Código</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<textarea class="form-control modal-textarea-code" id="test_modal_code" rows="15" placeholder="Aguarde..."></textarea><br>
				<div>
					<p><strong>Esse Template pode ser alterado <a href="/template.php">AQUI</a></strong></p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
				<a type="button" id="modal-wpp-code" class="btn btn-success" href="#" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
				<button type="button" class="btn btn-primary btn-modal-test" data-clipboard-target="#test_modal_code"><i class="far fa-copy"></i> Copiar Dados!</button>
			</div>
		</div>
	</div>
</div>
<div id="fast_test_iptv_modal" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Dados do teste</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<textarea class="form-control modal-textarea-iptv" id="test_modal_iptv" rows="15" placeholder="Aguarde..."></textarea><br>
				<div>
			        <p><strong>USUÁRIO CRIADO COM SUCESSO! </a></strong></p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
				<a type="button" id="modal-wpp-iptv" class="btn btn-success" href="#" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
				<button type="button" class="btn btn-primary btn-modal-test" data-clipboard-target="#test_modal_iptv"><i class="far fa-copy"></i> Copiar Dados!</button>
			</div>
		</div>
	</div>
</div>
<script src="/plugins/jquery/jquery.min.js"></script>
<script src="/bower_components/clipboard.min.js"></script>
<!-- Toastr -->
<link rel="stylesheet" href="/plugins/toastr/toastr.min.css">
<script src="/plugins/toastr/toastr.min.js"></script>

<script type="text/javascript">
	$(function() {
		new ClipboardJS('.btn');

		$('.btn-test-p2p').click(function() {
			toastr.success('Link de teste automático P2P copiado!', "Feito!");
		});
		$('.btn-test-iptv').click(function() {
			toastr.success('Link de teste automático copiado!', "Feito!");
		});
		$('.btn-modal-test').click(function() {
			toastr.success('Dados do teste copiados!', "Feito!");
		});
	});

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

	$(function() {
		$('.darkmode').click(function() {

			$('body').toggleClass('dark-mode')
			$('.navbar').toggleClass('navbar-dark')
			$('.navbar').toggleClass('navbar-white')
			$('.navbar').toggleClass('navbar-light')

			$.get('/sys/api.php?action=toggle_dark_mode', function(data) {
				if (data.result === 'success') {}
			}, "json");
		});
	})

	$(document).ready(function() {
		$("#fast_test_p2p_modal").on("show.bs.modal", function(event) {

			$.getJSON('/sys/api.php?action=fast_test&type=binstream&result_cb=json', function(e) {
				var conteudo = encodeURIComponent(e);
				$("#modal-wpp-p2p").attr("href", `https://api.whatsapp.com/send?phone=&text=${conteudo}`);
				$(".modal-textarea-p2p").text(`${e} `);
			})
		});
	});
	$(document).ready(function() {
		$("#fast_test_code_modal").on("show.bs.modal", function(event) {

			$.getJSON('/sys/api.php?action=fast_test&type=code&result_cb=json', function(e) {
				var conteudo = encodeURIComponent(e);
				$("#modal-wpp-code").attr("href", `https://api.whatsapp.com/send?phone=&text=${conteudo}`);
				$(".modal-textarea-code").text(`${e} `);
			})
		});
	});
	$(document).ready(function() {
		$("#fast_test_iptv_modal").on("show.bs.modal", function(event) {
			$.getJSON('/sys/api.php?action=fast_test&type=iptv&result_cb=json', function(e) {
				var conteudo = encodeURIComponent(e);
				$("#modal-wpp-iptv").attr("href", `https://api.whatsapp.com/send?phone=&text=${conteudo}`);
				$(".modal-textarea-iptv").text(e);
			})
		});
	});
</script>