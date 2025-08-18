<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$root = dirname(__DIR__);
$paths = [$root . '/include/db.php', $root . '/includes/db.php'];
$found = false;
foreach ($paths as $p) {
    if (file_exists($p)) { require_once $p; $found = true; break; }
}
if (!$found) { die('db.php introuvable'); }
if (!isset($_SESSION['user_id'])) { header('Location: /page/login.php'); exit; }
$stmt = $pdo->prepare('SELECT role FROM utilisateur WHERE id = ? LIMIT 1');
$stmt->execute([$_SESSION['user_id']]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$u || $u['role'] !== 'admin') { header('Location: /'); exit; }
