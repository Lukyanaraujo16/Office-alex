<?php
include_once('./sys/functions.php');
$logged_user = getLoggedUser();
$server_name = getServerProperty('server_name');
?>
<footer class="main-footer text-sm">
  <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="#"><?php echo $server_name; ?></a>.</strong>
  All rights reserved.
  <div class="float-right d-none d-sm-inline-block">
    <b>Version</b> <?php echo OFFICE_VERSION ?> - Powered by <b><a href="https://playonegestor.com/" target="_blank">PLAYONE TV</a></b>
  </div>
</footer>