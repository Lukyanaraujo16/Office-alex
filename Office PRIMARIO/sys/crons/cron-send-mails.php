<?php
// verifique se a execução é pelo cron
if (!isset($_SERVER['SHELL']) || (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST']))) {
  exit;
}

// pegue o horário atual
$now = time();

// verifique se é horário está entre 1:00 PM e 1:05 PM
if (!($now >= strtotime('1:00 PM') && $now <= strtotime('1:05 PM'))) {
  exit;
}

require_once __DIR__ . '/../functions.php';

try {
  $mails = json_decode(file_get_contents(__DIR__ . '/temp/cron-mail.json.tmp'));

  foreach ($mails as $mail) {
    smtpmailer(
      $mail['email'],
      $mail['subject'],
      $mail['body'],
      $mail['smtp']
    );
  }
} catch (Exception $e) {
  echo "error!";
  error_log($e->getMessage());
  exit;
}
