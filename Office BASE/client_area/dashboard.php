<?php
include_once('../sys/functions.php');
isClientLogged();
$logged_user = getLoggedClient();
$server_name = getServerProperty('server_name');
$server_dns = getServerDNS();
$default_password = getServerProperty("code_default_pass");
$fixed_informations = getuserproperty($logged_user["member_id"], "client_informations");

$active_connections = intval(getClientConnections($logged_user['id']));

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
  <!-- DataTables -->
  <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="/dist/css/adminlte.min.css?<?php echo OFFICE_VERSION ?>">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed text-sm layout-footer-fixed <?php if (DarkMode(true)) {
                                                                                                          echo "dark-mode";
                                                                                                        } ?>">
  <div class="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0 text-dark">Área do Cliente</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Área do Cliente</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
              <div class="info-box">
                <?php
                $status = "";
                if ($logged_user["admin_enabled"] && $logged_user["enabled"]) {
                  if (!$logged_user["exp_date"] || time() < $logged_user["exp_date"]) {
                    $status = "Ativo";
                    echo '<span class="info-box-icon bg-success elevation-1"><i class="fas fa-user-check"></i></span>';
                  } else {
                    $status = "Expirado";
                    echo '<span class="info-box-icon bg-warning elevation-1"><i class="fas fa-user-clock"></i></span>';
                  }
                } else {
                  $status = "Desativado";
                  echo '<span class="info-box-icon bg-danger elevation-1"><i class="fas fa-user-times"></i></span>';
                }
                ?>
                <div class="info-box-content">
                  <span class="info-box-text">Status do Plano</span>
                  <span class="info-box-number">
                    <?php echo $status; ?>
                  </span>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
              <div class="info-box mb-3">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-calendar-alt"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Validade do Plano</span>
                  <span class="info-box-number"><?php echo !empty($logged_user["exp_date"]) ? date("d/m/Y H:i", $logged_user["exp_date"]) : "Lifetime"; ?></span>
                </div>
              </div>
            </div>
            <div class="clearfix hidden-md-up"></div>
            <div class="col-12 col-sm-6 col-md-3">
              <div class="info-box mb-3">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-desktop"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Conexões Aitvas</span>
                  <span class="info-box-number"><?php echo $active_connections . "/" . $logged_user["max_connections"]; ?></span>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
              <div class="info-box mb-3">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-user-plus"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Cliente Desde</span>
                  <span class="info-box-number"><?php echo date("d/m/Y H:i", $logged_user["created_at"]); ?></span>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="card card-default">
                <div class="card-header">
                  <h3 class="card-title">Dados de Acesso</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                  <div class="row">

                    <?php if ($logged_user["password"] == $default_password) {
                      $is_code = true;
                    ?>
                      <div class="form-group col-md-12">
                        <label>Código</label>
                      <?php } else {
                      $is_code = false; ?>
                        <div class="form-group col-md-6">
                          <label>Usuário</label>
                        <?php } ?>
                        <div class="input-group mb-6">
                          <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                          </div>
                          <input type="text" name="username" id="username" readonly class="form-control" value="<?php echo $logged_user['username']; ?>">
                          <div class="input-group-append">
                            <button type="button" class="btn btn-info copyusername" data-clipboard-target="#username">Copiar</button>
                          </div>
                        </div>
                        </div>
                        <?php if (!$is_code) { ?>
                          <div class="form-group col-md-6">
                            <label>Senha</label>
                            <div class="input-group mb-6">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                              </div>
                              <input type="text" name="password" id="password" readonly class="form-control" value="<?php echo $logged_user['password']; ?>">
                              <div class="input-group-append">
                                <button type="button" class="btn btn-info copypassword" data-clipboard-target="#password">Copiar</button>
                              </div>
                            </div>
                          </div>
                          <div class="form-group col-md-12">
                            <label>Lista M3U (MPEGTS)</label>
                            <div class="input-group mb-6 mpegtsdiv">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                              </div>
                              <input type="text" name="m3umpegts" id="m3umpegts" readonly class="form-control" value="<?php echo $server_dns . "/get.php?username=" . $logged_user['username'] . "&password=" . $logged_user['password'] . "&type=m3u_plus&output=mpegts"; ?>">
                              <div class="input-group-append">
                                <button type="button" class="btn btn-info copym3umpegts" data-clipboard-target="#m3umpegts">Copiar</button>
                              </div>
                            </div>
                          </div>
                          <div class="form-group col-md-12">
                            <label>Lista M3U (HLS)</label>
                            <div class="input-group mb-6">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                              </div>
                              <input type="text" name="m3uhls" id="m3uhls" readonly class="form-control" value="<?php echo $server_dns . "/get.php?username=" . $logged_user['username'] . "&password=" . $logged_user['password'] . "&type=m3u_plus&output=m3u8"; ?>">
                              <div class="input-group-append">
                                <button type="button" class="btn btn-info copym3uhls" data-clipboard-target="#m3uhls">Copiar</button>
                              </div>
                            </div>
                          </div>
                        <?php } ?>
                      </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card card-default">
                  <div class="card-header">
                    <h3 class="card-title text-center" style="float: none"><strong>Informações</strong></h3>
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
  <!-- Modal HTML -->
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
  <div id="fast_test_iptv_modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Dados do teste IPTV</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <textarea class="form-control modal-textarea-iptv" id="test_modal_iptv" rows="15" placeholder="Aguarde..."></textarea><br>
          <div>
            <p><strong>Esse Template pode ser alterado <a href="/template.php">AQUI</a></strong></p>
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
  <!-- REQUIRED SCRIPTS -->
  <!-- jQuery -->
  <script src="/plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap -->
  <script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- DataTables -->
  <script src="/plugins/datatables/jquery.dataTables.js"></script>
  <script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
  <!-- overlayScrollbars -->
  <script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
  <!-- AdminLTE App -->
  <script src="/dist/js/adminlte.js?<?php echo OFFICE_VERSION ?>"></script>
  <!-- Clipboard -->
  <script src="/bower_components/clipboard.min.js"></script>

  <!-- SweetAlert2 -->
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- PAGE PLUGINS -->
  <!-- jQuery Mapael -->
  <script src="/plugins/jquery-mousewheel/jquery.mousewheel.js"></script>
  <script src="/plugins/raphael/raphael.min.js"></script>
  <script src="/plugins/jquery-mapael/jquery.mapael.min.js"></script>
  <script src="/plugins/jquery-mapael/maps/usa_states.min.js"></script>
  <!-- ChartJS -->
  <!--script src="../plugins/chart.js/Chart.min.js"></script-->
  <!-- PAGE SCRIPTS -->
  <script type="text/javascript">
    $(function() {
      new ClipboardJS('.btn');
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });

      function showToast(title) {
        if (title) {
          Toast.fire({
            icon: 'success',
            title,
            background: '#ffffff ',
          });
        }
      };

      $('.copyusername').click(function() {
        showToast("Usuário copiado!");
      });
      $('.copypassword').click(function() {
        showToast("Senha copiada!");
      });
      $('.copym3uhls, .copym3umpegts').click(function() {
        showToast("Link M3U copiado!");
      });
    });
  </script>
  <?php

  if (isset($_GET['gateway']) && isset($_GET['payment']) && !empty($_GET['payment'])) {
    setActionPayment(intval($_GET['status']), intval($_GET['payment']));
  }

  if (isset($_GET['status'])) {
    switch ($_GET['status']) {
      case 'success':
        $title = 'Pagamento Efetuado!';
        $icon = 'success';
        $text = 'Seu plano foi renovado!';
        break;
      case 'pending':
        $title = 'Aguardando pagamento';
        $icon = 'warning';
        $text = 'Conclua seu pagamento ou aguarde o processamento';
        break;
      case 'failure':
        $title = 'Ocorreu um erro com o pagamento!';
        $icon = 'error';
        $text = 'Revise os dados do pagamento e tente novamente!';
        break;
    }
  ?>

    <script type="text/javascript">
      window.onload = function ErrorAlert() {
        Swal.fire({
          title: '<?php echo $title; ?>',
          icon: '<?php echo $icon; ?>',
          text: '<?php echo $text; ?>',
          showConfirmButton: true
        })
      };
    </script>

  <?php } ?>
</body>

</html>