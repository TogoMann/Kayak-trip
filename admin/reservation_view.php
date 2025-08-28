<?php
require_once __DIR__ . '/_guard.php';
$id = (int)($_GET['id'] ?? 0);
if ($id<=0) { header('Location: /admin/reservations.php'); exit; }
$base = "SELECT r.id,r.type,r.nb_personnes,r.date_debut,r.date_fin,r.total,r.utilisateur_id,u.email,COALESCE(u.nom_affichage,TRIM(CONCAT(u.prenom,' ',u.nom))) AS client";
$sql1 = $base.",r.created_at FROM reservation r INNER JOIN utilisateur u ON u.id=r.utilisateur_id WHERE r.id=?";
$sql2 = $base.",NULL AS created_at FROM reservation r INNER JOIN utilisateur u ON u.id=r.utilisateur_id WHERE r.id=?";
try { $st = $pdo->prepare($sql1); $st->execute([$id]); $res = $st->fetch(PDO::FETCH_ASSOC); }
catch (Throwable $e) { $st = $pdo->prepare($sql2); $st->execute([$id]); $res = $st->fetch(PDO::FETCH_ASSOC); }
if (!$res) { header('Location: /admin/reservations.php'); exit; }
$s = $pdo->prepare("SELECT re.date,p.nom AS point_nom,h.nom AS heb_nom FROM reservation_etape re LEFT JOIN point_arret p ON p.id=re.point_arret_id LEFT JOIN hebergement h ON h.id=re.hebergement_id WHERE re.reservation_id=? ORDER BY re.date ASC");
$s->execute([$id]);
$steps = $s->fetchAll(PDO::FETCH_ASSOC);
$parcours = implode(' → ', array_filter(array_map(fn($x)=>$x['point_nom']??'', $steps), fn($v)=>$v!==''));
$hebs = implode(', ', array_values(array_unique(array_filter(array_map(fn($x)=>$x['heb_nom']??'', $steps), fn($v)=>$v!==''))));
$sv = $pdo->prepare("SELECT s.nom, rs.quantite, rs.prix_unitaire, rs.total FROM reservation_service rs INNER JOIN service s ON s.id=rs.service_id WHERE rs.reservation_id=? ORDER BY s.nom");
$sv->execute([$id]);
$services = $sv->fetchAll(PDO::FETCH_ASSOC);
$options = implode(', ', array_map(fn($x)=>$x['nom'], $services));
function duree_jours($d1,$d2){ if(!$d1||!$d2) return null; $a=strtotime($d1); $b=strtotime($d2); if($a===false||$b===false) return null; $j=(int)round(($b-$a)/86400); return max(0,$j); }
$now = date('Y-m-d');
$statut = ($res['date_fin'] && $res['date_fin'] < $now) ? 'Terminée' : 'En cours';
$d = duree_jours($res['date_debut'],$res['date_fin']);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Réservation #<?php echo (int)$res['id']; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#0b1220;color:#e5e7eb}
.card{background:#111827;border:0}
.card .title{color:#9ca3af;font-size:.9rem}
.badge-soft{background:#1f2937}
.table thead th{color:#9ca3af;border-color:rgba(255,255,255,.08)}
.table td{vertical-align:middle;border-color:rgba(255,255,255,.08)}
</style>
</head>
<body>
<div class="container py-4">
<div class="d-flex justify-content-between align-items-center mb-3">
<h5 class="mb-0">Réservation #<?php echo (int)$res['id']; ?></h5>
<a href="/admin/reservations.php" class="btn btn-outline-light">Retour</a>
</div>

<div class="row g-3">
<div class="col-12 col-lg-3">
<div class="card p-3 h-100">
<div class="title">Client</div>
<div class="h6 mb-1"><?php echo htmlspecialchars($res['client']?:$res['email']); ?></div>
<div class="text-secondary small"><?php echo htmlspecialchars($res['email']); ?></div>
</div>
</div>
<div class="col-6 col-lg-3">
<div class="card p-3 h-100">
<div class="title">Période</div>
<div class="h6 mb-1"><?php echo $res['date_debut']?date('d/m/Y',strtotime($res['date_debut'])):'—'; ?> → <?php echo $res['date_fin']?date('d/m/Y',strtotime($res['date_fin'])):'—'; ?></div>
<div class="text-secondary small"><?php echo is_null($d)?'—':($d.' jours'); ?></div>
</div>
</div>
<div class="col-6 col-lg-3">
<div class="card p-3 h-100">
<div class="title">Participants</div>
<div class="h6 mb-1"><?php echo (int)$res['nb_personnes']; ?></div>
<div class="text-secondary small"><?php echo htmlspecialchars($res['type']); ?></div>
</div>
</div>
<div class="col-12 col-lg-3">
<div class="card p-3 h-100">
<div class="title">Paiement</div>
<div class="h6 mb-1"><?php echo $res['total']!==null?number_format((float)$res['total'],2,',',' ').' €':'—'; ?></div>
<div class="text-secondary small"><?php echo $res['created_at']?'Réservé le '.date('d/m/Y H:i',strtotime($res['created_at'])):'Date de réservation indisponible'; ?></div>
</div>
</div>
</div>

<div class="row g-3 mt-1">
<div class="col-12">
<div class="card p-3">
<div class="d-flex justify-content-between align-items-center mb-2">
<div class="title">Résumé</div>
<span class="badge <?php echo $statut==='En cours'?'text-bg-primary':'text-bg-secondary'; ?>"><?php echo $statut; ?></span>
</div>
<div class="row g-3">
<div class="col-12 col-lg-6">
<div class="mb-1">Parcours</div>
<div><?php echo $parcours?htmlspecialchars($parcours):'—'; ?></div>
</div>
<div class="col-12 col-lg-6">
<div class="mb-1">Hébergements</div>
<div><?php echo $hebs?htmlspecialchars($hebs):'—'; ?></div>
</div>
<div class="col-12">
<div class="mb-1">Services</div>
<div><?php echo $options?htmlspecialchars($options):'—'; ?></div>
</div>
</div>
</div>
</div>

<div class="col-12">
<div class="card p-0">
<div class="table-responsive">
<table class="table table-dark table-hover align-middle mb-0">
<thead>
<tr>
<th style="width:140px">Date</th>
<th>Point d’arrêt</th>
<th>Hébergement</th>
</tr>
</thead>
<tbody>
<?php foreach($steps as $s): ?>
<tr>
<td><?php echo $s['date']?date('d/m/Y',strtotime($s['date'])):'—'; ?></td>
<td><?php echo htmlspecialchars($s['point_nom']??''); ?></td>
<td><?php echo htmlspecialchars($s['heb_nom']??''); ?></td>
</tr>
<?php endforeach; ?>
<?php if (count($steps)===0): ?>
<tr><td colspan="3" class="text-center text-secondary py-4">Aucune étape</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>

<div class="col-12">
<div class="card p-0 mt-3">
<div class="table-responsive">
<table class="table table-dark table-hover align-middle mb-0">
<thead>
<tr>
<th>Service</th>
<th style="width:120px">Quantité</th>
<th style="width:140px">Prix unitaire (€)</th>
<th style="width:140px">Total (€)</th>
</tr>
</thead>
<tbody>
<?php foreach($services as $sv): ?>
<tr>
<td><?php echo htmlspecialchars($sv['nom']); ?></td>
<td><?php echo (int)$sv['quantite']; ?></td>
<td><?php echo number_format((float)$sv['prix_unitaire'],2,',',' '); ?></td>
<td><?php echo number_format((float)$sv['total'],2,',',' '); ?></td>
</tr>
<?php endforeach; ?>
<?php if (count($services)===0): ?>
<tr><td colspan="4" class="text-center text-secondary py-4">Aucun service</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>

<div class="d-flex gap-2 mt-3">
<a class="btn btn-outline-light" href="/admin/reservations.php?status=encours">Voir les en cours</a>
<a class="btn btn-outline-light" href="/admin/reservations.php?status=termines">Voir les terminées</a>
</div>
</div>
</body>
</html>
