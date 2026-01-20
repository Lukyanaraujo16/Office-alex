<?php
// verifique se a execução é pelo cron
if (!isset($_SERVER['SHELL']) || (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST']))) {
  exit;
}

// pegue o horário atual
$now = time();

// verifique se é horário está entre 2:00 AM e 2:05 AM
if (!($now >= strtotime('2:00 AM') && $now <= strtotime('2:05 AM'))) {
  exit;
}

require_once __DIR__ . '/../functions.php';

$dbConn = getOfficeConnection();

function getExpiredClientsInAPeriod()
{
  $dbXtream = getConnection();

  $query = 'SELECT users.id, users.member_id, users.username, users.password, users.email, users.exp_date FROM users ';
  $query .= 'WHERE exp_date BETWEEN UNIX_TIMESTAMP(NOW()) ';
  $query .= ' AND ';
  $query .= 'UNIX_TIMESTAMP(NOW() + INTERVAL 3 day);';

  $sql = $dbXtream->prepare($query);

  if ($sql->execute()) {
    return $sql->fetchAll(PDO::FETCH_ASSOC);
  }

  return null;
}

$mails = [];

$clients = getExpiredClientsInAPeriod();
$email_settings_default = getServerPropertyDecode("email_settings");
foreach ($clients as $client) {
  $hasCustomSmtp = boolval(getUserPropertyDecode($client['member_id'], 'custom_smtp'));

  $email_settings = $hasCustomSmtp ?
    getUserPropertyDecode($client['member_id'], 'email_settings') :
    $email_settings_default;

  $templates = ($hasCustomSmtp) ?
    getUserPropertyDecode($client['member_id'], 'email_messages') :
    getServerPropertyDecode('email_messages');

  $mail = [
    'subject' => $templates['expiring_subject'],
    'body' => $templates['expiring_message']
  ];

  $safeText = function ($text) use ($client) {
    return str_replace([
      '#username#',
      '#exp_date#',
      '#email#'
    ], [
      $client['username'],
      date('d/m/Y', $client['exp_date']),
      $client['email'],
    ], $text);
  };

  // Subject
  $mail['subject'] = $safeText($mail['subject']);

  // Body
  $mail['body'] = $safeText($mail['body']);

  $mails[] = [
    'email' => $client['email'],
    'subject' => $mail['subject'],
    'body' => $mail['body'],
    'smtp' => [
      'host' => $email_settings['smtp_server'],
      'port' => $email_settings['smtp_port'],
      'username' => $email_settings['smtp_username'],
      'password' => $email_settings['smtp_password'],
      'profile' => [
        'name' => $email_settings["sender_name"],
        'email' => $email_settings["sender_email"]
      ]
    ]
  ];
}

try {
  file_put_contents(__DIR__ . '/temp/cron-mail.json.tmp', json_encode($mails));
} catch (Exception $e) {
  error_log($e->getMessage());
  exit;
}
