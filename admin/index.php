<?php
require_once __DIR__ . '/_guard.php';
$countUsers = 0;
try {
    $res = $pdo->query('SELECT COUNT(*) AS c FROM utilisateur');
    $row = $res->fetch(PDO::FETCH_ASSOC);
    $countUsers = (int)$row['c'];
} catch (Throwable $e) {}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin • Kayak</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{background:#0b1220}
.sidebar{width:260px;background:#0f172a;position:fixed;top:0;bottom:0;left:0}
.sidebar .brand{color:#fff;padding:20px;font-weight:700;letter-spacing:.5px}
.sidebar a{color:#cbd5e1}
.sidebar a.active,.sidebar a:hover{color:#fff}
.content{margin-left:260px}
.card{background:#111827;border:0;color:#e5e7eb}
.card .title{color:#9ca3af;font-size:.9rem}
.topbar{background:#0b1220;border-bottom:1px solid rgba(255,255,255,.08)}
.kpi{display:flex;align-items:center;gap:12px}
.kpi i{font-size:28px}
</style>
</head>
<body>
<div class="sidebar d-flex flex-column">
<div class="brand d-flex align-items-center">
<span><i class="bi bi-water"></i> Admin Kayak</span>
</div>
<div class="px-3">
<ul class="nav flex-column gap-1">
<li class="nav-item"><a class="nav-link active" href="/admin/"><i class="bi bi-speedometer2 me-2"></i>Tableau de bord</a></li>
<li class="nav-item"><a class="nav-link" href="/admin/users.php"><i class="bi bi-people me-2"></i>Utilisateurs</a></li>
<li class="nav-item"><a class="nav-link" href="/admin/reservations.php"><i class="bi bi-calendar-check me-2"></i>Réservations</a></li>
<li class="nav-item"><a class="nav-link" href="/admin/points.php"><i class="bi bi-geo-alt me-2"></i>Points d’arrêt</a></li>
<li class="nav-item"><a class="nav-link" href="/admin/hebergements.php"><i class="bi bi-house-door me-2"></i>Hébergements</a></li>
<li class="nav-item"><a class="nav-link" href="/admin/options.php"><i class="bi bi-bag-check me-2"></i>Options</a></li>
<li class="nav-item"><a class="nav-link" href="/admin/parcours.php"><i class="bi bi-map me-2"></i>Parcours</a></li>
<li class="nav-item"><a class="nav-link" href="/admin/pages.php"><i class="bi bi-file-text me-2"></i>Pages</a></li>
</ul>
</div>
<div class="mt-auto p-3">
<a href="/process/logout.php" class="btn btn-outline-light w-100"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a>
</div>
</div>

<div class="content">
<div class="topbar py-3 px-4 d-flex justify-content-between align-items-center">
<div class="text-white fw-semibold">Tableau de bord</div>
<div class="d-flex align-items-center gap-2">
<a href="/" class="btn btn-light"><i class="bi bi-eye me-1"></i>Mode public</a>
</div>
</div>

<div class="container-fluid py-4">
<div class="row g-3">
<div class="col-12 col-md-4">
<div class="card p-3">
<div class="kpi">
<i class="bi bi-people text-white-50"></i>
<div>
<div class="title">Utilisateurs</div>
<div class="h3 mb-0"><?php echo $countUsers; ?></div>
</div>
</div>
</div>
</div>
<div class="col-12 col-md-4">
<div class="card p-3">
<div class="kpi">
<i class="bi bi-calendar-check text-white-50"></i>
<div>
<div class="title">Réservations</div>
<div class="h3 mb-0">—</div>
</div>
</div>
</div>
</div>
<div class="col-12 col-md-4">
<div class="card p-3">
<div class="kpi">
<i class="bi bi-map text-white-50"></i>
<div>
<div class="title">Parcours</div>
<div class="h3 mb-0">—</div>
</div>
</div>
</div>
</div>
</div>

<div class="row g-3 mt-1">
<div class="col-12 col-lg-8">
<div class="card p-3">
<div class="d-flex justify-content-between align-items-center mb-2">
<div class="title">Activité récente</div>
<a href="/admin/reservations.php" class="btn btn-outline-light btn-sm">Voir tout</a>
</div>
<div class="text-secondary">Aucune donnée à afficher</div>
</div>
</div>
<div class="col-12 col-lg-4">
<div class="card p-3">
<div class="title mb-2">Actions rapides</div>
<div class="d-grid gap-2">
<a href="/admin/reservations.php" class="btn btn-primary">Gérer les réservations</a>
<a href="/admin/points.php" class="btn btn-outline-light">Gérer les points d’arrêt</a>
<a href="/admin/hebergements.php" class="btn btn-outline-light">Gérer les hébergements</a>
</div>
</div>
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
