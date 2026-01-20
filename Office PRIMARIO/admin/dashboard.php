<?php
include_once "functions.php";
isLoggedAdmin();

$auth = getUser();
$DB_Data = GetDBData();


if (isset($_POST['submit'])) {

  if (empty($_POST['xtream_db_pass'])) {
    $_POST['xtream_db_pass'] = $DB_Data['remote_db']['password'];
  }
  $DB_INFO['remote_db'] = [
    "panel_type" => $_POST['panel_type'],
    "hostname" => $_POST['xtream_db_host'],
    "username" => $_POST['xtream_db_user'],
    "database" => $_POST['xtream_db_name'],
    "password" => $_POST['xtream_db_pass'],
    "port" => $_POST['xtream_db_port']
  ];

  if (file_put_contents(__DIR__ . "/../../dbinfo.json", json_encode($DB_INFO))) {
    $result1 = true;
  }

  if ($result1) {
    file_put_contents(__DIR__ . "/../.update", "hostmk.com.br");
  }

  $data['username'] = $_POST['username'];
  $data['password'] = $_POST['password'];

  $result2 = updateUser($data);

  if ($result1 && $result2) {
    header("Location: dashboard.php");
  }
}

?>
<!DOCTYPE html>

<html class="loading" data-textdirection="ltr">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="Content-Language" content="pt-br">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
  <title>Office - Admin</title>
  <link rel="apple-touch-icon" href="assets/images/ico/apple-icon-120.html">
  <link rel="shortcut icon" type="image/x-icon" href="https://www.pixinvent.com/demo/frest-clean-bootstrap-admin-dashboard-template/app-assets/images/ico/favicon.ico">
  <link href="https://fonts.googleapis.com/css?family=Rubik:300,400,500,600%7CIBM+Plex+Sans:300,400,500,600,700" rel="stylesheet">

  <link rel="stylesheet" type="text/css" href="assets/vendors/css/vendors.min.css">
  <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="assets/css/bootstrap-extended.min.css">
  <link rel="stylesheet" type="text/css" href="assets/css/colors.min.css">
  <link rel="stylesheet" type="text/css" href="assets/css/components.min.css">
  <link rel="stylesheet" type="text/css" href="assets/css/themes/dark-layout.min.css">
  <link rel="stylesheet" type="text/css" href="assets/css/themes/semi-dark-layout.min.css">
  <link rel="stylesheet" type="text/css" href="assets/css/core/menu/menu-types/vertical-menu.min.css">
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>

<body class="vertical-layout vertical-menu-modern dark-layout 2-columns  navbar-sticky footer-static  " data-open="click" data-menu="vertical-menu-modern" data-col="2-columns" data-layout="dark-layout">

  <!-- BEGIN: Content-->
  <div class="app-content content" style="margin-left: 0px;">
    <div class="content-overlay"></div>
    <div class="content-wrapper" style="margin-top: 0px;">
      <div class="content-body">
        <!-- Basic tabs start -->

        <!-- Nav Filled Starts -->
        <section id="nav-filled">
          <div class="row">
            <div class="col-sm-3">
            </div>
            <div class="col-sm-6">
              <?php
              if (!empty($DB_Data['remote_db']['hostname']) || !empty($DB_Data['remote_db']['port']) || !empty($DB_Data['remote_db']['database']) || !empty($DB_Data['remote_db']['username']) || !empty($DB_Data['remote_db']['password'])) {
                try {
                  require_once(__DIR__ . '/../sys/database.php');
                  if ($database->ping()) {
                    $text = "Conexão com o banco de dados estabelecida com sucesso!";
                    $class = "success";
                    $icon = "bx bx-like";

                    $table = $DB_Data['remote_db']['panel_type'] == "XUI" ? "lines" : "users";
                    $columnsDB = $database->rawQuery("SHOW COLUMNS FROM `$table`;");
                    $columns = array();
                    foreach ($columnsDB as $column) {
                      array_push($columns, $column['Field']);
                    }

                    $requiredColumns = ['email', 'phone'];
                    $result = true;

                    foreach ($requiredColumns as $column) {
                      $exists = !empty(array_search($column, $columns)) ? true : false;
                      if (!$exists) {
                        $queryResult = $database->rawQuery("ALTER TABLE `$table` ADD `$column` VARCHAR(255) NULL DEFAULT NULL AFTER `password`;");
                        if (!$queryResult) {
                          $result = false;
                        }
                      }
                    }
                  } else {
                    $text = "Não foi possível conectar ao banco de dados! Verifique os dados inseridos e tente novamente.";
                    $class = "danger";
                    $icon = "bx bx-error-circle";
                  }
                } catch (Exception $e) {
                  $text = "Não foi possível conectar ao banco de dados! Verifique os dados inseridos e tente novamente.";
                  $class = "danger";
                  $icon = "bx bx-error-circle";
                }
              ?>
                <div class="alert alert-<?php echo $class ?> alert-dismissible mb-2" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                  </button>
                  <div class="d-flex align-items-center">
                    <i class="<?php echo $icon ?>"></i>
                    <span>
                      <?php echo $text; ?>
                    </span>
                  </div>
                </div>
              <?php } ?>
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title text-center">Configurações do Office</h4>
                  <a href="./logout.php">Sair</a>
                </div>
                <div class="card-body">
                  <form method="post">

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs nav-fill" id="myTab" role="tablist">
                      <!--li class="nav-item">
                        <a class="nav-link active" id="home-tab-fill" data-toggle="tab" href="#home-fill" role="tab" aria-controls="home-fill" aria-selected="true">
                          Personalização
                        </a>
                      </li-->
                      <li class="nav-item">
                        <a class="nav-link active" id="profile-tab-fill" data-toggle="tab" href="#profile-fill" role="tab" aria-controls="profile-fill" aria-selected="true">
                          Configurações do Xtream
                        </a>
                      </li>
                      <!--li class="nav-item">
                        <a class="nav-link" id="messages-tab-fill" data-toggle="tab" href="#messages-fill" role="tab" aria-controls="messages-fill" aria-selected="false">
                          Desativar Grupos
                        </a>
                      </li-->
                      <li class="nav-item">
                        <a class="nav-link" id="settings-tab-fill" data-toggle="tab" href="#settings-fill" role="tab" aria-controls="settings-fill" aria-selected="false">
                          Alterar Senha
                        </a>
                      </li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content pt-1 ">

                      <!-- Início Configurações Xtream -->
                      <div class="tab-pane active" id="profile-fill" role="tabpanel" aria-labelledby="profile-tab-fill">
                        <div class="row">
                          <div class="col-md-12">
                            <fieldset class="form-group">
                              <label for="basicInput">Tipo do Painel </label>
                              <select class="form-control" id="panel_type" name="panel_type">
                                <option value="Xtream" <?php if ($DB_Data["remote_db"]["panel_type"] == 'Xtream') {
                                                          echo "selected";
                                                        } ?>>XtreamUI</option>
                                <option value="StreamCreed" <?php if ($DB_Data["remote_db"]["panel_type"] == 'StreamCreed') {
                                                              echo "selected";
                                                            } ?>>StreamCreed</option>
                                <option value="XUI" <?php if ($DB_Data["remote_db"]["panel_type"] == 'XUI') {
                                                      echo "selected";
                                                    } ?>>XUI.one</option>
                              </select>
                            </fieldset>
                            <fieldset class="form-group">
                              <label for="basicInput">IP do Banco de dados</label>
                              <input type="text" class="form-control" id="xtream_db_host" name="xtream_db_host" placeholder="xxx.xxx.xxx.xxx" minlength="7" maxlength="15" size="15" required="" value="<?php echo $DB_Data['remote_db']['hostname'] ?>">
                              <p><small class="text-muted">Exemplo: <b>123.1.2.3</b></small></p>
                            </fieldset>
                            <fieldset class="form-group">
                              <label for="basicInput">Porta do Banco de dados</label>
                              <input type="text" class="form-control" id="xtream_db_port" name="xtream_db_port" placeholder="" type="number" minlength="2" maxlength="5" size="5" required="" value="<?php echo $DB_Data['remote_db']['port'] ?>">
                              <p><small class="text-muted">Padrão: <b>7999</b> para XtreamUI e StreamCreed, <b>3306</b> para XUI.one</small></p>
                            </fieldset>
                            <fieldset class="form-group">
                              <label for="basicInput">Nome do Banco de dados</label>
                              <input type="text" class="form-control" id="xtream_db_name" name="xtream_db_name" placeholder="Nome do Banco de dados" required value="<?php echo $DB_Data['remote_db']['database'] ?>">
                              <p><small class="text-muted">Exemplo: <b>xtream_iptvpro</b></small></p>
                            </fieldset>
                            <fieldset class="form-group">
                              <label for="basicInput">Usuário do Banco de dados</label>
                              <input type="text" class="form-control" id="xtream_db_user" name="xtream_db_user" placeholder="Usuário do Banco de dados" required value="<?php echo $DB_Data['remote_db']['username'] ?>">
                              <p><small class="text-muted">Padrão: <b>user_iptvpro</b></small></p>
                            </fieldset>
                            <fieldset class="form-group">
                              <label for="basicInput">Senha do Banco de dados</label>
                              <?php
                              if (empty($DB_Data['remote_db']['password'])) {
                                $placeholder = '&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;';
                                $required = 'required';
                              } else {
                                $placeholder = 'Já preenchido, deixe em branco para manter a senha atual';
                                $required = '';
                              }
                              ?>
                              <input type="text" class="form-control" id="xtream_db_pass" name="xtream_db_pass" <?php echo $required ?> placeholder="<?php echo $placeholder ?>" value="">
                            </fieldset>
                          </div>
                        </div>
                      </div>
                      <!-- Fim Configurações Xtream -->
                      <!-- Início Desativar Grupos -->
                      <div class="tab-pane" id="messages-fill" role="tabpanel" aria-labelledby="messages-tab-fill">
                        <p>
                          Em Breve!
                        </p>
                      </div>
                      <!-- Fim Desativar Grupos -->
                      <!-- Início Alterar Senha -->
                      <div class="tab-pane" id="settings-fill" role="tabpanel" aria-labelledby="settings-tab-fill">
                        <p>
                          Aqui você pode alterar o usuário e senha de acesso a esse sistema de administração.
                        </p>
                        <div class="row">
                          <div class="col-md-12">
                            <fieldset class="form-group">
                              <label for="basicInput">Usuário</label>
                              <input type="text" class="form-control" id="username" name="username" autocomplete="off" placeholder="Usurio" value="<?php echo $auth['username'] ?>">
                            </fieldset>
                            <fieldset class="form-group">
                              <label for="basicInput">Senha</label>
                              <input type="text" class="form-control" id="password" name="password" autocomplete="off" placeholder="Senha" value="<?php echo $auth['password'] ?>">
                            </fieldset>
                          </div>
                        </div>
                      </div>
                      <!-- Fim Alterar Senha -->
                      <!-- Início Botão Salvar Dados -->
                      <div class="col-12 mt-3 text-center">
                        <button type="submit" name="submit" class="btn btn-success center glow mr-1 mb-1">
                          <i class="bx bx-check"></i>
                          <span class="align-middle ml-25">Salvar Informações</span>
                        </button>
                      </div>
                      <!-- Fim Botão Salvar Dados -->
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
  <div class="sidenav-overlay"></div>
  <div class="drag-target"></div>
  <footer class="footer footer-static footer-light" style="margin-left: 0px;">
    <p class="clearfix mb-0"><span class="float-left d-inline-block">Developed by<a href="" target="_blank">HostMK </a>&copy; <?php echo date("Y") ?> </span>
      <button class="btn btn-primary btn-icon scroll-top" type="button"><i class="bx bx-up-arrow-alt"></i></button>
    </p>
  </footer>
  <script src="assets/vendors/js/vendors.min.js"></script>
  <script src="assets/js/scripts/configs/vertical-menu-dark.min.js"></script>
  <script src="assets/js/core/app-menu.min.js"></script>
  <script src="assets/js/core/app.min.js"></script>
  <script src="assets/js/scripts/components.min.js"></script>
  <script src="assets/js/scripts/footer.min.js"></script>
  <script src="assets/js/scripts/customizer.min.js"></script>
  <script src="assets/js/scripts/navs/navs.min.js"></script>
  <script src="assets/js/scripts/forms/validation/form-validation.js"></script>
  <script>
    //on select change add value to the input
    $(document).ready(function() {

      $("#panel_type").on("change", function(e) {
        const value = $(this).val();
        const input_db_name = $("#xtream_db_name");
        const input_port = $("#xtream_db_port");

        if (value == 'Xtream') {
          input_db_name.val('xtream_iptvpro')
          input_port.val('7999')
        } else if (value == 'XUI') {
          input_db_name.val('xui')
          input_port.val('3306')
        } else if (value == 'StreamCreed') {
          input_db_name.val('streamcreed_db')
          input_port.val('7999')
        }
      });
    });
  </script>
</body>

</html>