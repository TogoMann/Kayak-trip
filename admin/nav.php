<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/include/db.php';
$isAdmin = false;
if (isset($_SESSION['user_id'])) {
    $s = $pdo->prepare('SELECT role FROM utilisateur WHERE id = ? LIMIT 1');
    $s->execute([$_SESSION['user_id']]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
    $isAdmin = $r && $r['role'] === 'admin';
}
?>
<nav class="navbar navbar-expand-lg bg-light">
<div class="container">
<a class="navbar-brand" href="/">Kayak Loire</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="navMain">
<ul class="navbar-nav me-auto mb-2 mb-lg-0">
<li class="nav-item"><a class="nav-link" href="/composer.php">Créer mon itinéraire</a></li>
<?php if ($isAdmin): ?>
<li class="nav-item"><a class="nav-link" href="/admin/">Admin</a></li>
<?php endif; ?>
</ul>
<ul class="navbar-nav">
<li class="nav-item"><a class="nav-link" href="/profile.php">Profil</a></li>
</ul>
</div>
</div>
</nav>
