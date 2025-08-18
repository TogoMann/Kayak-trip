<?php
require_once __DIR__ . '/_guard.php';
$q = $_GET['q'] ?? '';
$params = [];
$sql = "SELECT p.id,p.nom,p.description,p.latitude,p.longitude,COUNT(h.id) AS nb_hebergements FROM point_arret p LEFT JOIN hebergement h ON h.point_arret_id=p.id";
if ($q !== '') { $sql .= " WHERE p.nom LIKE ? OR p.description LIKE ?"; $like="%$q%"; $params=[$like,$like]; }
$sql .= " GROUP BY p.id ORDER BY p.nom ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin • Points d’arrêt</title>
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
.table thead th{color:#9ca3af;border-color:rgba(255,255,255,.08)}
.table td{vertical-align:middle;border-color:rgba(255,255,255,.08)}
.badge-soft{background:#1f2937}
</style>
</head>
<body>
<div class="sidebar d-flex flex-column">
<div class="brand d-flex align-items-center"><span><i class="bi bi-water"></i> Admin Kayak</span></div>
<div class="px-3">
<ul class="nav flex-column gap-1">
<li class="nav-item"><a class="nav-link" href="/admin/"><i class="bi bi-speedometer2 me-2"></i>Tableau de bord</a></li>
<li class="nav-item"><a class="nav-link" href="/admin/users.php"><i class="bi bi-people me-2"></i>Utilisateurs</a></li>
<li class="nav-item"><a class="nav-link" href="/admin/reservations.php"><i class="bi bi-calendar-check me-2"></i>Réservations</a></li>
<li class="nav-item"><a class="nav-link active" href="/admin/points.php"><i class="bi bi-geo-alt me-2"></i>Points d’arrêt</a></li>
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
<div class="text-white fw-semibold">Points d’arrêt</div>
<div class="d-flex align-items-center gap-2">
<a href="/" class="btn btn-light"><i class="bi bi-eye me-1"></i>Mode public</a>
</div>
</div>

<div class="container-fluid py-4">
<?php if (isset($_GET['ok'])): ?>
<div class="alert alert-success">Opération effectuée.</div>
<?php endif; ?>
<?php if (isset($_GET['err']) && $_GET['err']==='constraint'): ?>
<div class="alert alert-warning">Impossible de supprimer ce point d’arrêt car il est référencé.</div>
<?php endif; ?>

<div class="card p-3 mb-3">
<form class="row g-2 align-items-center" method="get">
<div class="col-12 col-md-6">
<input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Rechercher par nom ou description">
</div>
<div class="col-6 col-md-auto">
<button class="btn btn-primary"><i class="bi bi-search me-1"></i>Rechercher</button>
</div>
<div class="col-6 col-md-auto ms-auto text-end">
<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal"><i class="bi bi-plus-lg me-1"></i>Ajouter un point d’arrêt</button>
</div>
</form>
</div>

<div class="card p-0">
<div class="table-responsive">
<table class="table table-dark table-hover align-middle mb-0">
<thead>
<tr>
<th style="width:70px">ID</th>
<th>Nom</th>
<th>Description</th>
<th style="width:140px">Latitude</th>
<th style="width:140px">Longitude</th>
<th style="width:150px">Hébergements</th>
<th style="width:220px">Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr>
<td><?php echo (int)$r['id']; ?></td>
<td><?php echo htmlspecialchars($r['nom']); ?></td>
<td class="text-truncate" style="max-width:380px"><?php echo htmlspecialchars($r['description'] ?? ''); ?></td>
<td><?php echo htmlspecialchars((string)$r['latitude']); ?></td>
<td><?php echo htmlspecialchars((string)$r['longitude']); ?></td>
<td><span class="badge text-bg-secondary badge-soft"><?php echo (int)$r['nb_hebergements']; ?></span></td>
<td class="d-flex gap-2">
<button type="button" class="btn btn-outline-light btn-sm btn-edit"
 data-id="<?php echo (int)$r['id']; ?>"
 data-nom="<?php echo htmlspecialchars($r['nom']); ?>"
 data-description="<?php echo htmlspecialchars($r['description'] ?? ''); ?>"
 data-lat="<?php echo htmlspecialchars((string)$r['latitude']); ?>"
 data-lng="<?php echo htmlspecialchars((string)$r['longitude']); ?>"
 data-bs-toggle="modal" data-bs-target="#editModal"><i class="bi bi-pencil-square me-1"></i>Modifier</button>
<form action="/admin/points_actions.php" method="post" onsubmit="return confirm('Supprimer ce point d’arrêt ?');">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php if (count($rows)===0): ?>
<tr><td colspan="7" class="text-center text-secondary py-4">Aucun point d’arrêt</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>
</div>

<div class="modal fade" id="createModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form action="/admin/points_actions.php" method="post">
<input type="hidden" name="action" value="create">
<div class="modal-header"><h5 class="modal-title">Ajouter un point d’arrêt</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<div class="mb-3"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4"></textarea></div>
<div class="row g-2">
<div class="col-6"><label class="form-label">Latitude</label><input type="number" step="0.00001" name="latitude" class="form-control" required></div>
<div class="col-6"><label class="form-label">Longitude</label><input type="number" step="0.00001" name="longitude" class="form-control" required></div>
</div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
</form>
</div>
</div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form action="/admin/points_actions.php" method="post">
<input type="hidden" name="action" value="update">
<input type="hidden" name="id" id="eid">
<div class="modal-header"><h5 class="modal-title">Modifier le point d’arrêt</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<div class="mb-3"><label class="form-label">Nom</label><input type="text" name="nom" id="enom" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Description</label><textarea name="description" id="edesc" class="form-control" rows="4"></textarea></div>
<div class="row g-2">
<div class="col-6"><label class="form-label">Latitude</label><input type="number" step="0.00001" name="latitude" id="elat" class="form-control" required></div>
<div class="col-6"><label class="form-label">Longitude</label><input type="number" step="0.00001" name="longitude" id="elng" class="form-control" required></div>
</div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
</form>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-edit').forEach(function(b){b.addEventListener('click',function(){document.getElementById('eid').value=this.dataset.id;document.getElementById('enom').value=this.dataset.nom;document.getElementById('edesc').value=this.dataset.description;document.getElementById('elat').value=this.dataset.lat;document.getElementById('elng').value=this.dataset.lng;});});
</script>
</body>
</html>
