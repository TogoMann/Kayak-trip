<?php
require_once __DIR__ . '/_guard.php';
$id = (int)($_GET['id'] ?? 0);
$h = $pdo->prepare("SELECT h.id,h.nom,p.nom AS point_nom FROM hebergement h INNER JOIN point_arret p ON p.id=h.point_arret_id WHERE h.id=?");
$h->execute([$id]);
$heb = $h->fetch(PDO::FETCH_ASSOC);
if (!$heb) { header('Location: /admin/hebergements.php'); exit; }
$photos = $pdo->prepare("SELECT id,chemin,is_cover,sort_order FROM hebergement_photo WHERE hebergement_id=? ORDER BY is_cover DESC, sort_order ASC, id ASC");
$photos->execute([$id]);
$rows = $photos->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Photos • <?php echo htmlspecialchars($heb['nom']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#0b1220;color:#e5e7eb}
.card{background:#111827;border:0}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px}
.thumb{width:100%;height:120px;object-fit:cover;border-radius:.5rem;border:1px solid rgba(255,255,255,.08)}
.badge{position:absolute;top:8px;left:8px}
</style>
</head>
<body>
<div class="container py-4">
<div class="d-flex justify-content-between align-items-center mb-3">
<h5 class="mb-0"><?php echo htmlspecialchars($heb['nom']); ?> • <?php echo htmlspecialchars($heb['point_nom']); ?></h5>
<a href="/admin/hebergements.php" class="btn btn-outline-light">Retour</a>
</div>
<div class="card p-3 mb-3">
<form action="/admin/hebergement_photos_actions.php" method="post" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
<input type="hidden" name="action" value="upload">
<input type="hidden" name="hebergement_id" value="<?php echo (int)$heb['id']; ?>">
<input type="file" name="photos[]" accept=".jpg,.jpeg,.png,.webp" class="form-control" multiple required>
<button class="btn btn-primary">Téléverser</button>
</form>
</div>
<div class="grid">
<?php foreach($rows as $p): ?>
<div class="position-relative">
<img class="thumb" src="<?php echo '/uploads/hebergements/'.htmlspecialchars($p['chemin']); ?>">
<?php if ((int)$p['is_cover']===1): ?><span class="badge text-bg-success">Couverture</span><?php endif; ?>
<div class="mt-2 d-flex gap-2">
<form action="/admin/hebergement_photos_actions.php" method="post">
<input type="hidden" name="action" value="set_cover">
<input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
<input type="hidden" name="hebergement_id" value="<?php echo (int)$heb['id']; ?>">
<button class="btn btn-outline-light btn-sm"<?php echo (int)$p['is_cover']===1?' disabled':'';?>>Définir couverture</button>
</form>
<form action="/admin/hebergement_photos_actions.php" method="post" onsubmit="return confirm('Supprimer cette photo ?');">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
<input type="hidden" name="hebergement_id" value="<?php echo (int)$heb['id']; ?>">
<button class="btn btn-outline-danger btn-sm">Supprimer</button>
</form>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</body>
</html>
