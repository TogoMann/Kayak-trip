<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$root = dirname(__DIR__);
$paths = [$root . '/include/db.php', $root . '/includes/db.php'];
$found = false;
foreach ($paths as $p) {
    if (file_exists($p)) { require_once $p; $found = true; break; }
}
if (!$found) { die('Fichier db.php introuvable dans /include ou /includes'); }

$email = $_POST['email'] ?? '';
$pass = $_POST['password'] ?? '';
if ($email === '' || $pass === '') {
    header('Location: /page/login.php?error=missing');
    exit;
}

$stmt = $pdo->prepare('SELECT id, mot_de_passe, role FROM utilisateur WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

if ($u && password_verify($pass, $u['mot_de_passe'])) {
    $_SESSION['user_id'] = $u['id'];
    if ($u['role'] === 'admin') {
        header('Location: /admin/');
        exit;
    }
    header('Location: /');
    exit;
}

header('Location: /page/login.php?error=login');
exit;
