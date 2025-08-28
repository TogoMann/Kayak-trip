<?php
require_once __DIR__.'/_guard.php';
$me=(int)$_SESSION['user_id'];
$tid=(int)($_POST['thread_id']??0);
$body=trim($_POST['body']??'');
if($tid<=0||$body===''){ http_response_code(400); exit; }
$chk=$pdo->prepare("SELECT id,status FROM support_thread WHERE id=? LIMIT 1"); $chk->execute([$tid]); $t=$chk->fetch(PDO::FETCH_ASSOC);
if(!$t||$t['status']!=='open'){ http_response_code(409); exit; }
$pdo->prepare("INSERT INTO support_message(thread_id,sender_role,sender_id,body) VALUES(?, 'admin', ?, ?)")->execute([$tid,$me,$body]);
http_response_code(204);
