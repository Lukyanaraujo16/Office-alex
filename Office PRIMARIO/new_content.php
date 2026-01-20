<?php
include_once('./sys/functions.php');
isLogged();
$logged_user = getLoggedUser();
$server_name = getServerProperty('server_name');
$fast_packages = json_decode(getServerProperty('fast_packages'), true);
$categories = getAllCategories();
?>
<!DOCTYPE html>
<html lang="pt_BR">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">


  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title><?php echo $server_name; ?></title>
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="plugins/fontawesome-pro/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css?<?php echo OFFICE_VERSION ?>">
  <!-- <link rel="stylesheet" href="dist/css/animate.css"> -->
  <!-- Toastr -->
  <link rel="stylesheet" href="plugins/toastr/toastr.min.css">
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
              <h1 class="m-0 text-dark">Conteúdo novo</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item">Informações</li>
                <li class="breadcrumb-item active">Conteúdo novo</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-4 col-md-12">
              <!-- small card -->
              <div class="small-box bg-info">
                <div class="inner">
                  <h3><?php echo number_format(getAllChannelsCount(), 0, ',', '.'); ?></h3>
                  <p>Total de Canais</p>
                </div>
                <div class="icon">
                  <i class="fad fad fa-play"></i>
                </div>
                <a href="#" class="small-box-footer newChannelsCopy">
                  Copiar Novidades <i class="fad fa-file-import"></i>
                </a>
              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-4 col-md-12">
              <!-- small card -->
              <div class="small-box bg-success">
                <div class="inner">
                  <h3><?php echo number_format(getAllVodsCount(), 0, ',', '.'); ?></h3>
                  <p>Total de Filmes</p>
                </div>
                <div class="icon">
                  <i class="fas fa-play"></i>
                </div>
                <a href="#" class="small-box-footer newMoviesCopy">
                  Copiar Novidades <i class="fad fa-file-import"></i>
                </a>
              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-4 col-md-12">
              <!-- small card -->
              <div class="small-box bg-secondary">
                <div class="inner">
                  <h3><?php echo number_format(getAllSeriesCount(), 0, ',', '.'); ?></h3>
                  <p>Total de Séries</p>
                </div>
                <div class="icon">
                  <i class="far fa-play"></i>
                </div>
                <a href="#" class="small-box-footer newTvshowsCopy" data-clipboard-target="#newTvshowsCopy">
                  Copiar Novidades <i class="fad fa-file-import"></i>
                </a>
              </div>
            </div>
            <!-- ./col -->
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="card card-default">
                <div class="card-header">
                  <h3 class="card-title text-center" style="float: none"><strong>Novos Canais</strong></h3>
                 </div>
                <div class="card-body p-0" style="display: block;">
                  <div class="overlay preloadNewChannels">
                    <i class="fas fa-3x fa-sync-alt fa-spin p-3"></i>
                  </div>
                  <table class="table newChannels d-none table-striped projects">
                    <thead style="height: 0px;">
                      <tr style="height: 0px;">
                        <th style="width: 20%; pad4ing-top: 0px; padding-bottom: 0px; border-bottom-width: 0px; height: 0px;"></th>
                        <th style="width: 60%; padding-top: 0px; padding-bottom: 0px; border-bottom-width: 0px; height: 0px;"></th>
                        <th style="width: 20%; padding-top: 0px; padding-bottom: 0px; border-bottom-width: 0px; height: 0px;"></th>
                      </tr>
                    </thead>
                    <tbody class="content"></tbody>
                  </table>
                </div>
              </div>
            </div>
            <textarea id="newChannelsCopy" class="d-none newChannels"></textarea>
            <div class="col-md-4">
              <div class="card card-default" id="newMovies">
                <div class="card-header">
                  <h3 class="card-title text-center" style="float: none"><strong>Novos Filmes</strong></h3>
                </div>
                <div class="card-body p-0" style="display: block;">
                  <div class="overlay preloadNewMovies">
                    <i class="fas fa-3x fa-sync-alt fa-spin p-3"></i>
                  </div>
                  <table class="table newMovies d-none table-striped projects">
                    <thead style="height: 0px;">
                      <tr style="height: 0px;">
                        <th style="width: 20%; padding-top: 0px; padding-bottom: 0px; border-bottom-width: 0px; height: 0px;"></th>
                        <th style="width: 60%; padding-top: 0px; padding-bottom: 0px; border-bottom-width: 0px; height: 0px;"></th>
                        <th style="width: 20%; padding-top: 0px; padding-bottom: 0px; border-bottom-width: 0px; height: 0px;"></th>
                      </tr>
                    </thead>
                    <tbody class="content"></tbody>
                  </table>
                </div>
              </div>
            </div>
            <textarea id="newMoviesCopy" class="d-none newMovies"></textarea>
            <div class="col-md-4">
              <div class="card card-default">
                <div class="card-header">
                  <h3 class="card-title text-center" style="float: none"><strong>Novas Séries</strong></h3>
                </div>
                <div class="card-body p-0" style="display: block;">
                  <div class="overlay preloadNewTvshows">
                    <i class="fas fa-3x fa-sync-alt fa-spin p-3"></i>
                  </div>
                  <table class="table newTvshows d-none table-striped projects">
                    <thead style="height: 0px;">
                      <tr style="height: 0px;">
                        <th style="width: 20%; padding-top: 0px; padding-bottom: 0px; border-bottom-width: 0px; height: 0px;"></th>
                        <th style="width: 60%; padding-top: 0px; padding-bottom: 0px; border-bottom-width: 0px; height: 0px;"></th>
                        <th style="width: 20%; padding-top: 0px; padding-bottom: 0px; border-bottom-width: 0px; height: 0px;"></th>
                      </tr>
                    </thead>
                    <tbody class="content"></tbody>
                  </table>
                </div>
              </div>
            </div>
            <textarea id="newTvshowsCopy" class="d-none newTvshows"></textarea>
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
  <!-- Toastr -->
  <script src="/plugins/toastr/toastr.min.js"></script>
  <!-- jQuery Mapael -->
  <script src="plugins/jquery-mousewheel/jquery.mousewheel.js"></script>
  <script src="plugins/raphael/raphael.min.js"></script>
  <script src="plugins/jquery-mapael/jquery.mapael.min.js"></script>
  <script src="plugins/jquery-mapael/maps/usa_states.min.js"></script>
  <!-- PAGE SCRIPTS -->
  <script type="text/javascript">
    $(function() {
      $.ajax({
        url: './sys/api.php?action=new_vods',
        method: 'GET',
        dataType: 'json'
      }).done(function(contents) {
        $(".preloadNewChannels").addClass("d-none");
        $("table.newChannels").removeClass("d-none").find('tbody.content').html(contents.channels);
        $("textarea.newChannels").html(contents.channels_copy);


        $(".preloadNewMovies").addClass("d-none");
        $("table.newMovies").removeClass("d-none").find('tbody.content').html(contents.movies);
        $("textarea.newMovies").html(contents.movies_copy);

        $(".preloadNewTvshows").addClass("d-none");
        $("table.newTvshows").removeClass("d-none").find('tbody.content').html(contents.tvshows);
        $("textarea.newTvshows").html(contents.tvshows_copy);
      })

      // new ClipboardJS('.btn');

      $('.newChannelsCopy').click(function() {
        $("textarea.newChannels").removeClass("d-none").select();
        document.execCommand("copy");
        $("textarea.newChannels").addClass("d-none");
        toastr.success('Lista de Novos Canais copiada!', "Feito!");
      });

      $('.newMoviesCopy').click(function() {
        $("textarea.newMovies").removeClass("d-none").select();
        document.execCommand("copy");
        $("textarea.newMovies").addClass("d-none");
        toastr.success('Lista de Novos Filmes copiada!', "Feito!");
      });

      $('.newTvshowsCopy').click(function() {
        $("textarea.newTvshows").removeClass("d-none").select();
        document.execCommand("copy");
        $("textarea.newTvshows").addClass("d-none");
        toastr.success('Lista de Novas Séries copiada!', "Feito!");
      });
    });
  </script>
</body>

</html>