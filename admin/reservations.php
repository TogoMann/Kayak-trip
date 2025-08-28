<?php
require_once __DIR__ . '/_guard.php';
$status = $_GET['status'] ?? 'encours';
$q = trim($_GET['q'] ?? '');
$today = date('Y-m-d');
$params = [];
$w = [];
$base = "SELECT r.id,r.type,r.nb_personnes,r.date_debut,r.date_fin,r.total,r.utilisateur_id,u.email,COALESCE(u.nom_affichage,TRIM(CONCAT(u.prenom,' ',u.nom))) AS client";
$sql1 = $base . ",r.created_at FROM reservation r INNER JOIN utilisateur u ON u.id=r.utilisateur_id";
$sql2 = $base . ",NULL AS created_at FROM reservation r INNER JOIN utilisateur u ON u.id=r.utilisateur_id";
if ($status === 'encours') { $w[] = "(r.date_fin IS NULL OR r.date_fin >= ?)"; $params[] = $today; }
if ($status === 'termines') { $w[] = "(r.date_fin IS NOT NULL AND r.date_fin < ?)"; $params[] = $today; }
if ($q !== '') { $w[] = "(u.email LIKE ? OR u.nom_affichage LIKE ? OR r.id = ?)"; $params[] = "%$q%"; $params[] = "%$q%"; $params[] = ctype_digit($q) ? (int)$q : -1; }
try { $stmt = $pdo->prepare($sql1 . ($w ? " WHERE ".implode(" AND ",$w) : "") . " ORDER BY COALESCE(r.date_debut,r.id) DESC"); $stmt->execute($params); $hasCreatedAt = true; }
catch (Throwable $e) { $stmt = $pdo->prepare($sql2 . ($w ? " WHERE ".implode(" AND ",$w) : "") . " ORDER BY COALESCE(r.date_debut,r.id) DESC"); $stmt->execute($params); $hasCreatedAt = false; }
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$ids = array_map(fn($r)=>(int)$r['id'],$reservations);
$parcours = [];
$hebs = [];
if ($ids) {
  $in = implode(',', array_fill(0,count($ids),'?'));
  try { $s = $pdo->prepare("SELECT re.reservation_id,GROUP_CONCAT(p.nom ORDER BY re.date SEPARATOR ' → ') AS parcours FROM reservation_etape re LEFT JOIN point_arret p ON p.id=re.point_arret_id WHERE re.reservation_id IN ($in) GROUP BY re.reservation_id"); $s->execute($ids); foreach($s as $row){ $parcours[(int)$row['reservation_id']] = $row['parcours']; } } catch (Throwable $e) {}
  try { $s2 = $pdo->prepare("SELECT re.reservation_id,GROUP_CONCAT(DISTINCT h.nom ORDER BY h.nom SEPARATOR ', ') AS hebergements FROM reservation_etape re LEFT JOIN hebergement h ON h.id=re.hebergement_id WHERE re.reservation_id IN ($in) GROUP BY re.reservation_id"); $s2->execute($ids); foreach($s2 as $row){ $hebs[(int)$row['reservation_id']] = $row['hebergements']; } } catch (Throwable $e) {}
}
function duree_jours($d1,$d2){ if(!$d1||!$d2) return null; $a=strtotime($d1); $b=strtotime($d2); if($a===false||$b===false) return null; $j=(int)round(($b-$a)/86400); return max(0,$j); }
$page_title = 'Réservations';
$active = 'reservations';
require_once __DIR__ . '/_layout_start.php';
?>
<div class="card p-3 mb-3">
  <form class="row g-2 align-items-center" method="get">
    <div class="col-12 col-md-6">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Rechercher par e-mail, nom, ID">
      </div>
    </div>
    <div class="col-12 col-md-6">
      <div class="btn-group" role="group">
        <a href="/admin/reservations.php?status=encours&q=<?php echo urlencode($q); ?>" class="btn <?php echo $status==='encours'?'btn-primary':'btn-outline-light'; ?>">En cours</a>
        <a href="/admin/reservations.php?status=termines&q=<?php echo urlencode($q); ?>" class="btn <?php echo $status==='termines'?'btn-primary':'btn-outline-light'; ?>">Terminées</a>
      </div>
    </div>
  </form>
</div>

<div class="card p-0">
  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle mb-0">
      <thead>
        <tr>
          <th style="width:80px">ID</th>
          <th>Client</th>
          <th style="width:190px">Période</th>
          <th style="width:100px">Durée</th>
          <th style="width:120px">Nb pers.</th>
          <th>Parcours</th>
          <th style="width:180px">Hébergements</th>
          <th style="width:130px">Total (€)</th>
          <th style="width:180px">Réservé le</th>
          <th style="width:140px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($reservations as $r):
          $id = (int)$r['id'];
          $parc = $parcours[$id] ?? '—';
          $heb = $hebs[$id] ?? '—';
          $d = duree_jours($r['date_debut'],$r['date_fin']); ?>
        <tr>
          <td>#<?php echo $id; ?></td>
          <td><?php echo htmlspecialchars($r['client'] ?: $r['email']); ?><div class="text-secondary small"><?php echo htmlspecialchars($r['email']); ?></div></td>
          <td><?php echo $r['date_debut']?date('d/m/Y',strtotime($r['date_debut'])):'—'; ?> → <?php echo $r['date_fin']?date('d/m/Y',strtotime($r['date_fin'])):'—'; ?></td>
          <td><?php echo is_null($d)?'—':$d.' j'; ?></td>
          <td><?php echo (int)$r['nb_personnes']; ?></td>
          <td class="text-truncate" style="max-width:260px"><?php echo htmlspecialchars($parc); ?></td>
          <td class="text-truncate" style="max-width:180px"><?php echo htmlspecialchars($heb); ?></td>
          <td><?php echo $r['total']!==null?number_format((float)$r['total'],2,',',' '):'—'; ?></td>
          <td><?php echo $r['created_at']?date('d/m/Y H:i',strtotime($r['created_at'])):'—'; ?></td>
          <td><a class="btn btn-outline-light btn-sm" href="/admin/reservation_view.php?id=<?php echo $id; ?>"><i class="bi bi-eye me-1"></i>Voir</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if(count($reservations)===0): ?>
        <tr><td colspan="10" class="text-center text-secondary py-4">Aucune réservation</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/_layout_end.php'; ?>
