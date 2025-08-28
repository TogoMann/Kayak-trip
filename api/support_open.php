<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=utf-8');
$uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$sessionKey = session_id();
$category = strtolower(trim($_POST['category'] ?? 'autre'));
if (!in_array($category, ['technique','commercial','autre'], true)) $category = 'autre';
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$first = trim($_POST['message'] ?? '');
$stmt = $pdo->prepare("INSERT INTO support_thread(user_id,session_key,name,email,category) VALUES(?,?,?,?,?)");
$stmt->execute([$uid, $sessionKey, $name !== '' ? $name : null, $email !== '' ? $email : null, $category]);
$tid = (int)$pdo->lastInsertId();
if ($first !== '') {
  $s = $pdo->prepare("INSERT INTO support_message(thread_id,sender_role,sender_id,body) VALUES(?,?,?,?)");
  $s->execute([$tid, 'user', $uid, $first]);
}
echo json_encode(['id'=>$tid], JSON_UNESCAPED_UNICODE);
