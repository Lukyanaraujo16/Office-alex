<?php
include_once('../sys/functions.php');
isClientLogged();
$logged_user = getLoggedClient();
$server_name = getServerProperty('server_name');

if ((!getUserProperty($logged_user['member_id'], "mercado_pago")) && (!getUserProperty($logged_user['member_id'], "pag_seguro"))) {
  header("Location: ./dashboard.php");
  exit;
}

$default_password = getServerProperty("code_default_pass");
$plans = getUserPropertyDecode($logged_user['member_id'], "client_area_plans");

$new_exp = $logged_user['exp_date'];

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
  <!-- Select2 -->
  <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
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
              <h1 class="m-0 text-dark">Renovar Plano</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Renovar Plano</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
      <section class="content">
        <div class="container-fluid">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Detalhes</h3>
            </div>
            <div class="card-body" style="display: block;">
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Renovar por:</label>
                    <select class="form-control" id="plan" name="plan">
                      <option value="" selected="selected">Selecione</option>
                      <?php
                      foreach ($plans as $plan) {
                        if ($plan['price'] != 0) {
                          echo '<option data-plan-id="' . $plan['id'] . '" value="' . $plan['price'] . '">' . $plan['name'] . '</option>';
                        }
                      }
                      ?>
                    </select>
                  </div>
                </div>

                <div class="col-12 col-md-12 col-lg-12">
                  <div class="text-muted">
                    <p class="text-sm">Usuário:
                      <b class="d-block"><?php echo $logged_user['username']; ?></b>
                    </p>
                    <p class="text-sm">Conexões:
                      <b class="d-block"><?php echo $logged_user['max_connections']; ?></b>
                    </p>
                    <br>
                    <span class="d-block message-price-alert">
                      <h4>Selecione o tempo a renovar</h4>
                    </span>
                    <span class="d-none price">
                      <h4>Total: <span class="value"></span></h4>
                    </span>
                  </div>
                </div>
                <div class="col-12 col-md-12 col-lg-12">
                  <div class="checkout price d-none">
                    <span class="d-flex">
                      <button class="btn btn-primary send">
                        <i class="fas fa-1x fa-sync-alt fa-spin loading d-none"></i> Prosseguir
                      </button>
                      <button class="btn btn-primary payments payment-mercadopago d-none mr-3">
                        MercadoPago
                      </button>
                      <button class="btn btn-primary payments payment-pagseguro d-none">
                        Pagseguro
                      </button>
                      <button class="btn btn-primary payments payment-woovi d-none">
                        Pix
                      </button>
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <!-- /.card-body -->
          </div>
      </section>
    </div>
    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark"></aside>
    <!-- /.control-sidebar -->
    <!-- Main Footer -->
    <?php include_once("footer.php"); ?>
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
  <!-- Select2 -->
  <script src="/plugins/select2/js/select2.full.min.js"></script>

  <!-- SweetAlert2 -->
  <script src="/plugins/sweetalert2/sweetalert2.min.js"></script>
  <!-- PAGE PLUGINS -->
  <!-- jQuery Mapael -->
  <script src="/plugins/jquery-mousewheel/jquery.mousewheel.js"></script>
  <script src="/plugins/raphael/raphael.min.js"></script>
  <script src="/plugins/jquery-mapael/jquery.mapael.min.js"></script>
  <script src="/plugins/jquery-mapael/maps/usa_states.min.js"></script>
  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  <script src="https://sdk.mercadopago.com/js/v2"></script>
  <script type="text/javascript" src="https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.lightbox.js"></script>
  <script>
    const formCurrency = new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
      minimumFractionDigits: 2
    });

    function show(element) {
      $(element).removeClass('d-none').addClass('d-block');
    }

    function hide(element) {
      if (element == ".payment-mercadopago") {
        $(element).html('');
      }

      $(element).removeClass('d-block').addClass('d-none');
    }

    $('#plan').on('select2:select', function(e) {
      var connections = parseInt(<?php echo $logged_user["max_connections"]; ?>);
      var expiration = $('#plan').val();

      if (expiration > 0) {
        hide(".message-price-alert");
        show(".price");
        show(".checkout");
        show(".checkout .send");

        $('.price .value').text(formCurrency.format(connections * Number(expiration)));
        $(".checkout button.send").attr("data-plan-id", e.params.data.element.dataset.planId);
      } else {
        show(".message-price-alert")
        hide(".price");
        hide(".checkout");
      }
    }).select2({
      theme: 'bootstrap4'
    });

    $(".checkout button.send").on("click", (e) => {
      const $el = $(e.currentTarget);
      const planId = $el.attr("data-plan-id");

      $(".checkout .send").prop("disabled", true);
      show(".checkout .send .loading");

      $.post("/sys/clientApi.php?action=load_gateway", {
        planId
      }, function(gateway) {
        hide(".checkout .send");
        hide(".checkout .send .loading");
        $(".checkout .send").prop("disabled", false);

        if (gateway.mercadopago) {
          const mp = new MercadoPago('<?php echo getUserProperty($logged_user['member_id'], 'mercado_pago_public_key'); ?>', {
            locale: 'pt-BR'
          });

          show(".payment-mercadopago");
          $(".payment-mercadopago").on("click", (e) => {
            e.preventDefault();

            $.post(`/sys/clientApi.php?action=start_gateway&payment=${gateway.payment}`, {
              gateway: 'mercadopago'
            }, function(loadGateway) {
              if (loadGateway.success) {
                mp.checkout({
                  preference: {
                    id: gateway.mercadopago
                  },
                  autoOpen: true
                });
              }
            });
          });
        }

        if (gateway.pagseguro) {
          show('.payment-pagseguro');

          $(".payment-pagseguro").on("click", (e) => {
            e.preventDefault();

            $.post(`/sys/clientApi.php?action=start_gateway&payment=${gateway.payment}`, {
              gateway: 'pagseguro'
            }, function(loadGateway) {
              let isOpenLightbox = PagSeguroLightbox(gateway.pagseguro, {
                success: function(transactionCode) {
                  alert("Compra feita com sucesso, código de transação: " + transactionCode);
                },
                abort: function() {
                  hide('.payments');
                  show(".message-price-alert")
                  hide(".price");
                  hide(".checkout");
                }
              });
              // Redireciona o comprador, caso o navegador não tenha suporte ao Lightbox
              if (!isOpenLightbox) {
                location.href = "https://pagseguro.uol.com.br/v2/checkout/payment.html?code=" + code;
                console.log("Redirecionamento")
              }
            });
          })
        }

        if (gateway.woovi) {
          show('.payment-woovi');

          $(".payment-woovi").on("click", (e) => {
            e.preventDefault();

            $.post(`/sys/clientApi.php?action=start_gateway&payment=${gateway.payment}`, {
              gateway: 'woovi'
            }, function(loadGateway) {
              window.open(
                gateway.woovi.paymentLinkUrl,
                '_blank' // <- This is what makes it open in a new window.
              );
              console.log("Redirecionamento")
            });
          })
        }
      });
    })
  </script>
</body>

</html>