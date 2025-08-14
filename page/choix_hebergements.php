<?php
session_start();
require_once('../includes/header.php');
require_once('../includes/db.php');

if (empty($_SESSION['itineraire']['etapes'])) { header('Location: composer.php'); exit; }

$itin = $_SESSION['itineraire'];
$etapes = array_map('intval', $itin['etapes']);
$participants = (int)$itin['participants'];
$duree = (int)$itin['duree'];
$date_depart = $itin['date_depart'] ?? null;
$date_fin = $itin['date_fin'] ?? null;

$nights = $duree;
if ($date_depart && $date_fin) {
  $d1 = new DateTime($date_depart);
  $d2 = new DateTime($date_fin);
  $diff = $d1->diff($d2)->days;
  if ($diff > 0) $nights = $diff;
}

$points = [];
if ($etapes) {
  $in = implode(',', array_fill(0, count($etapes), '?'));
  $stmt = $pdo->prepare("SELECT id, nom FROM point_arret WHERE id IN ($in)");
  $stmt->execute($etapes);
  while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) $points[$r['id']] = $r['nom'];
}

function slugify($s){ 
  $s = iconv('UTF-8','ASCII//TRANSLIT',$s); 
  $s = strtolower($s); 
  $s = preg_replace('/[^a-z0-9]+/','_', $s); 
  $s = preg_replace('/_+/', '_', $s); 
  return trim($s,'_'); 
}

$hebergementsParPoint = [];
foreach ($etapes as $pid) {
  $q = $pdo->prepare("SELECT id, nom, description, prix_base, capacite FROM hebergement WHERE point_arret_id = ?");
  $q->execute([$pid]);
  $hebergementsParPoint[$pid] = $q->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="container py-5">
  <div class="row">
    <div class="col-lg-8">
      <h3 class="mb-3">Choisir les hébergements</h3>
      <p class="text-muted">Nuits: <?= htmlspecialchars($nights) ?> • Participants: <?= htmlspecialchars($participants) ?></p>

      <?php foreach ($etapes as $pid): ?>
        <div class="mb-4 border rounded">
          <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?= htmlspecialchars($points[$pid] ?? ('Étape '.$pid)) ?></h5>
            <span class="text-muted small">Sélectionner un hébergement</span>
          </div>
          <div class="p-3">
            <?php if (!empty($hebergementsParPoint[$pid])): ?>
              <div class="row">
                <?php foreach ($hebergementsParPoint[$pid] as $h):
                  $img = '../img/hebergements/placeholder.jpg';
                  $base = '../img/hebergements/'.slugify($h['nom']);
                  foreach (['jpg','jpeg','png','webp'] as $ext) { 
                    $p = $base.'.'.$ext; 
                    if (file_exists($p)) { $img = $p; break; } 
                  }
                ?>
                <div class="col-md-6 mb-3">
                  <label class="w-100">
                    <input type="radio" class="form-check-input me-2 choix-heb" name="heb_<?= $pid ?>" value="<?= $h['id'] ?>" data-prix="<?= (float)$h['prix_base'] ?>" data-point="<?= $pid ?>">
                    <div class="card h-100">
                      <img src="<?= $img ?>" class="card-img-top" alt="<?= htmlspecialchars($h['nom']) ?>" style="height:160px;object-fit:cover;">
                      <div class="card-body">
                        <h6 class="card-title mb-1"><?= htmlspecialchars($h['nom']) ?></h6>
                        <div class="text-muted mb-2"><?= number_format((float)$h['prix_base'], 2, ',', ' ') ?> € / nuit • Capacité <?= (int)$h['capacite'] ?></div>
                        <div class="small"><?= htmlspecialchars(mb_strimwidth($h['description'] ?? '', 0, 140, '...')) ?></div>
                      </div>
                    </div>
                  </label>
                </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="text-muted">Aucun hébergement disponible pour cette étape.</div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="col-lg-4">
      <div class="sticky-top" style="top:90px">
        <div class="card shadow-sm mb-3">
          <div class="card-body">
            <h5 class="card-title">Récapitulatif</h5>
            <div class="d-flex justify-content-between">
              <span>Hébergements</span><strong><span id="total-heb">0</span> €</strong>
            </div>
            <div class="d-flex justify-content-between">
              <span>Services</span><strong><span id="total-services">0</span> €</strong>
            </div>
            <hr>
            <div class="d-flex justify-content-between fs-5">
              <span>Total</span><strong><span id="total-general">0</span> €</strong>
            </div>
            <div class="mt-3 small text-muted">
              Dates: <?= htmlspecialchars($date_depart ?? '-') ?> → <?= htmlspecialchars($date_fin ?? '-') ?><br>
              Nuits: <?= htmlspecialchars($nights) ?> • Participants: <?= htmlspecialchars($participants) ?>
            </div>
          </div>
        </div>

        <form action="../process/traitement_hebergements.php" method="post" id="form-hebergements">
          <input type="hidden" name="selection" id="selection-json">
          <input type="hidden" name="total_heb" id="total-heb-input">
          <button type="submit" class="btn btn-primary w-100">Continuer vers le paiement</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include('../includes/footer.php'); ?>

<script>
const nights = <?= (int)$nights ?>;
const participants = <?= (int)$participants ?>;
const selected = {};
function updateTotals(){
  let totalHeb = 0;
  Object.values(selected).forEach(o=>{
    totalHeb += o.prix * nights;
  });
  document.getElementById('total-heb').textContent = totalHeb.toFixed(2);
  const services = 0;
  document.getElementById('total-services').textContent = services.toFixed(2);
  document.getElementById('total-general').textContent = (totalHeb + services).toFixed(2);
  document.getElementById('selection-json').value = JSON.stringify(selected);
  document.getElementById('total-heb-input').value = totalHeb.toFixed(2);
}
document.querySelectorAll('.choix-heb').forEach(r=>{
  r.addEventListener('change', e=>{
    const pid = e.target.getAttribute('data-point');
    selected[pid] = { hebergement_id: Number(e.target.value), prix: Number(e.target.getAttribute('data-prix')) };
    updateTotals();
  });
});
updateTotals();
</script>
