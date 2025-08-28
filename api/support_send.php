<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=utf-8');
$uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$sessionKey = session_id();
$tid = (int)($_POST['thread_id'] ?? 0);
$body = trim($_POST['body'] ?? '');
if ($tid <= 0 || $body === '') { http_response_code(400); echo json_encode(['err'=>'bad_request']); exit; }
$chk = $pdo->prepare("SELECT id FROM support_thread WHERE id=? AND (user_id ".($uid!==null?'= ?':'IS NULL')." OR session_key=?) LIMIT 1");
$params = [$tid];
if ($uid!==null) $params[] = $uid;
$params[] = $sessionKey;
$chk->execute($params);
if (!$chk->fetch()) { http_response_code(403); echo json_encode(['err'=>'forbidden']); exit; }
$ins = $pdo->prepare("INSERT INTO support_message(thread_id,sender_role,sender_id,body) VALUES(?,?,?,?)");
$ins->execute([$tid,'user',$uid,$body]);
echo json_encode(['ok'=>1,'id'=>$pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);
