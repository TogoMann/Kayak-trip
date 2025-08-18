<?php
require_once __DIR__ . '/_guard.php';
$q = $_GET['q'] ?? '';
$params = [];
$sql = "SELECT id, email, nom_affichage, role, verifie, created_at FROM utilisateur";
if ($q !== '') { $sql .= " WHERE email LIKE ? OR nom_affichage LIKE ? OR prenom LIKE ? OR nom LIKE ?"; $like = "%$q%"; $params = [$like,$like,$like,$like]; }
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin • Utilisateurs</title>
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
.badge-role{background:#1f2937}
</style>
</head>
<body>
<div class="sidebar d-flex flex-column">
<div class="brand d-flex align-items-center"><span><i class="bi bi-water"></i> Admin Kayak</span></div>
<div class="px-3">
<ul class="nav flex-column gap-1">
<li class="nav-item"><a class="nav-link" href="/admin/"><i class="bi bi-speedometer2 me-2"></i>Tableau de bord</a></li>
<li class="nav-item"><a class="nav-link active" href="/admin/users.php"><i class="bi bi-people me-2"></i>Utilisateurs</a></li>
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
<div class="text-white fw-semibold">Utilisateurs</div>
<div class="d-flex align-items-center gap-2">
<a href="/" class="btn btn-light"><i class="bi bi-eye me-1"></i>Mode public</a>
</div>
</div>

<div class="container-fluid py-4">
<div class="card p-3 mb-3">
<form class="row g-2 align-items-center" method="get">
<div class="col-12 col-md-6">
<input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Rechercher par e-mail, nom d’affichage, nom, prénom">
</div>
<div class="col-6 col-md-auto">
<button class="btn btn-primary"><i class="bi bi-search me-1"></i>Rechercher</button>
</div>
<div class="col-6 col-md-auto ms-auto text-end">
<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal"><i class="bi bi-plus-lg me-1"></i>Créer un utilisateur</button>
</div>
</form>
</div>

<div class="card p-0">
<div class="table-responsive">
<table class="table table-dark table-hover align-middle mb-0">
<thead>
<tr>
<th style="width:70px">ID</th>
<th>Nom d’affichage</th>
<th>E-mail</th>
<th style="width:140px">Créé le</th>
<th style="width:120px">Rôle</th>
<th style="width:110px">Vérifié</th>
<th style="width:220px">Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($users as $u): ?>
<tr>
<td><?php echo (int)$u['id']; ?></td>
<td><?php echo htmlspecialchars($u['nom_affichage'] ?? ''); ?></td>
<td><?php echo htmlspecialchars($u['email']); ?></td>
<td><?php echo date('d/m/Y H:i', strtotime($u['created_at'])); ?></td>
<td><span class="badge text-bg-secondary badge-role"><?php echo htmlspecialchars($u['role']); ?></span></td>
<td><?php echo (int)$u['verifie']===1?'Oui':'Non'; ?></td>
<td class="d-flex gap-2">
<button type="button" class="btn btn-outline-light btn-sm btn-edit"
 data-id="<?php echo (int)$u['id']; ?>"
 data-email="<?php echo htmlspecialchars($u['email']); ?>"
 data-nom="<?php echo htmlspecialchars($u['nom_affichage'] ?? ''); ?>"
 data-role="<?php echo htmlspecialchars($u['role']); ?>"
 data-bs-toggle="modal" data-bs-target="#editModal"><i class="bi bi-pencil-square me-1"></i>Modifier</button>
<form action="/admin/users_actions.php" method="post" onsubmit="return confirm('Supprimer cet utilisateur ?');">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php if (count($users)===0): ?>
<tr><td colspan="7" class="text-center text-secondary py-4">Aucun utilisateur</td></tr>
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
<form action="/admin/users_actions.php" method="post">
<input type="hidden" name="action" value="create">
<div class="modal-header"><h5 class="modal-title">Créer un utilisateur</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<div class="mb-3">
<label class="form-label">Nom d’affichage</label>
<input type="text" name="nom_affichage" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">E-mail</label>
<input type="email" name="email" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Mot de passe</label>
<input type="password" name="password" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Rôle</label>
<select name="role" class="form-select">
<option value="client">client</option>
<option value="admin">admin</option>
</select>
</div>
<div class="form-check">
<input class="form-check-input" type="checkbox" name="verifie" id="cverifie" value="1">
<label class="form-check-label" for="cverifie">Compte vérifié</label>
</div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Créer</button></div>
</form>
</div>
</div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form action="/admin/users_actions.php" method="post">
<input type="hidden" name="action" value="update">
<input type="hidden" name="id" id="eid">
<div class="modal-header"><h5 class="modal-title">Modifier l’utilisateur</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<div class="mb-3">
<label class="form-label">Nom d’affichage</label>
<input type="text" name="nom_affichage" id="enom" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">E-mail</label>
<input type="email" name="email" id="eemail" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Nouveau mot de passe</label>
<input type="password" name="password" id="epass" class="form-control" placeholder="Laisser vide pour conserver">
</div>
<div class="mb-3">
<label class="form-label">Rôle</label>
<select name="role" id="erole" class="form-select">
<option value="client">client</option>
<option value="admin">admin</option>
</select>
</div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
</form>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-edit').forEach(function(b){b.addEventListener('click',function(){document.getElementById('eid').value=this.dataset.id;document.getElementById('eemail').value=this.dataset.email;document.getElementById('enom').value=this.dataset.nom;document.getElementById('erole').value=this.dataset.role;document.getElementById('epass').value='';});});
</script>
</body>
</html>
