<?php
require_once __DIR__ . '/_guard.php';
$hid = (int)($_GET['id'] ?? 0);
$h = $pdo->prepare("SELECT h.id,h.nom,p.nom AS point_nom FROM hebergement h INNER JOIN point_arret p ON p.id=h.point_arret_id WHERE h.id=?");
$h->execute([$hid]);
$heb = $h->fetch(PDO::FETCH_ASSOC);
if (!$heb) { header('Location: /admin/hebergements.php'); exit; }
$sql = "SELECT r.id AS res_id,r.type,r.nb_personnes,r.date_debut,r.date_fin,r.total,u.email,u.nom,u.prenom,re.date
FROM reservation_etape re
INNER JOIN reservation r ON r.id=re.reservation_id
INNER JOIN utilisateur u ON u.id=r.utilisateur_id
WHERE re.hebergement_id=?
ORDER BY re.date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$hid]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Réservations • <?php echo htmlspecialchars($heb['nom']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#0b1220;color:#e5e7eb}
.card{background:#111827;border:0}
.table thead th{color:#9ca3af;border-color:rgba(255,255,255,.08)}
.table td{vertical-align:middle;border-color:rgba(255,255,255,.08)}
</style>
</head>
<body>
<div class="container py-4">
<div class="d-flex justify-content-between align-items-center mb-3">
<h5 class="mb-0"><?php echo htmlspecialchars($heb['nom']); ?> • <?php echo htmlspecialchars($heb['point_nom']); ?></h5>
<a href="/admin/hebergements.php" class="btn btn-outline-light">Retour</a>
</div>
<div class="card p-0">
<div class="table-responsive">
<table class="table table-dark table-hover align-middle mb-0">
<thead>
<tr>
<th style="width:90px">Réservation</th>
<th>Client</th>
<th style="width:120px">Type</th>
<th style="width:120px">Étape</th>
<th style="width:120px">Nb pers.</th>
<th style="width:130px">Période</th>
<th style="width:120px">Total (€)</th>
</tr>
</thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
<td>#<?php echo (int)$r['res_id']; ?></td>
<td><?php echo htmlspecialchars($r['prenom'].' '.$r['nom'].' • '.$r['email']); ?></td>
<td><?php echo htmlspecialchars($r['type']); ?></td>
<td><?php echo htmlspecialchars(date('d/m/Y',strtotime($r['date']))); ?></td>
<td><?php echo (int)$r['nb_personnes']; ?></td>
<td><?php echo ($r['date_debut']?date('d/m',strtotime($r['date_debut'])):'—').' → '.($r['date_fin']?date('d/m',strtotime($r['date_fin'])):'—'); ?></td>
<td><?php echo $r['total']!==null?number_format((float)$r['total'],2,',',' ') :'—'; ?></td>
</tr>
<?php endforeach; ?>
<?php if (count($rows)===0): ?>
<tr><td colspan="7" class="text-center text-secondary py-4">Aucune réservation</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>
</body>
</html>
