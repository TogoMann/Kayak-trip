<?php
if (session_status() === PHP_SESSION_NONE) {session_start();}
$root = __DIR__ . '/..';
$paths = [$root . '/include/db.php', $root . '/includes/db.php'];
foreach ($paths as $p) { if (file_exists($p)) { require_once $p; break; } }
$isAdmin = false;
if (isset($_SESSION['user_id']) && isset($pdo)) {
    $s = $pdo->prepare('SELECT role FROM utilisateur WHERE id = ? LIMIT 1');
    $s->execute([$_SESSION['user_id']]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
    $isAdmin = $r && $r['role'] === 'admin';
}
?>
<?php if ($isAdmin): ?>
<a href="/admin/" class="btn btn-dark position-fixed shadow" style="right:16px;bottom:16px;z-index:1050;">Mode admin</a>
<?php endif; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Kayak-Trip</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<?php include('nav.php'); ?>
