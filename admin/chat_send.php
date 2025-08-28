<?php
require_once __DIR__.'/_guard.php';
$me=(int)$_SESSION['user_id'];
$body=trim($_POST['body']??'');
$to=isset($_POST['to'])?(int)$_POST['to']:0;
$channel=($_POST['channel']??'');
if($body===''){ http_response_code(400); exit; }
if($channel==='general'){
  $stmt=$pdo->prepare("INSERT INTO admin_chat_message(sender_id,recipient_id,channel,body) VALUES(?,NULL,'general',?)");
  $stmt->execute([$me,$body]);
  http_response_code(204); exit;
}
if($to>0){
  $ok=$pdo->prepare("SELECT 1 FROM utilisateur WHERE id=? AND role='admin'")->execute([$to]);
  $stmt=$pdo->prepare("INSERT INTO admin_chat_message(sender_id,recipient_id,channel,body) VALUES(?,?,NULL,?)");
  $stmt->execute([$me,$to,$body]);
  http_response_code(204); exit;
}
http_response_code(400);
