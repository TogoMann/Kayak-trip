<?php
session_start();
require_once('../includes/header.php');
require_once('../includes/db.php');

if (empty($_SESSION['itineraire']) || empty($_SESSION['itineraire']['etapes'])) {
    header('Location: composer.php'); exit;
}

$promoMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['apply_promo'])) {
        $code = strtoupper(trim($_POST['code_promo'] ?? ''));
        if ($code !== '') {
            $stmt = $pdo->prepare("SELECT * FROM promo_code WHERE code=? AND actif=1 AND (start_date IS NULL OR start_date<=CURDATE()) AND (end_date IS NULL OR end_date>=CURDATE()) LIMIT 1");
            $stmt->execute([$code]);
            $promo = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($promo) {
                $_SESSION['promo'] = [
                    'id' => (int)$promo['id'],
                    'code' => $promo['code'],
                    'type' => $promo['type'],
                    'value' => (float)$promo['value'],
                    'min_total' => $promo['min_total'] !== null ? (float)$promo['min_total'] : null,
                    'usage_limit' => $promo['usage_limit'] !== null ? (int)$promo['usage_limit'] : null,
                    'per_user_limit' => $promo['per_user_limit'] !== null ? (int)$promo['per_user_limit'] : null
                ];
            } else {
                $promoMsg = 'Code invalide ou expiré.';
            }
        }
    } elseif (isset($_POST['remove_promo'])) {
        unset($_SESSION['promo']);
        header('Location: paiement.php'); exit;
    }
}

$itin = $_SESSION['itineraire'];
$etapes = array_map('intval', $itin['etapes']);
$participants = (int)($itin['participants'] ?? 1);
$duree = (int)($itin['duree'] ?? 1);
$date_depart = $itin['date_depart'] ?? null;
$date_fin = $itin['date_fin'] ?? null;
$options = $itin['options'] ?? [];
$hebsSel = $itin['hebergements'] ?? [];
$totalHeb = isset($itin['total_heb']) ? (float)$itin['total_heb'] : 0;

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

$hebergements = [];
if (!empty($hebsSel)) {
  $ids = array_map('intval', array_column($hebsSel, 'hebergement_id'));
  if ($ids) {
    $in = implode(',', array_fill(0, count($ids), '?'));
    $q = $pdo->prepare("SELECT id, nom, point_arret_id, COALESCE(prix_base, prix_base) AS prix_nuit, capacite FROM hebergement WHERE id IN ($in)");
    $q->execute($ids);
    while ($h = $q->fetch(PDO::FETCH_ASSOC)) $hebergements[$h['id']] = $h;
  }
}

$servicesTotal = 0;
if (in_array('transport_bagages', $options)) $servicesTotal += 5 * max(0, count($etapes) - 1);
if (in_array('panier_garni', $options)) $servicesTotal += 20 * $nights * $participants;
if (in_array('location_materiel', $options)) $servicesTotal += 10 * $nights * $participants;

$totalGeneral = $totalHeb + $servicesTotal;

$discount = 0.0;
if (!empty($_SESSION['promo'])) {
    $p = $_SESSION['promo'];
    $ok = true;
    if ($p['min_total'] !== null && $totalGeneral < (float)$p['min_total']) $ok = false;
    if ($ok) {
        if ($p['type'] === 'percent') $discount = round($totalGeneral * ((float)$p['value'] / 100), 2);
        else $discount = (float)$p['value'];
        if ($discount > $totalGeneral) $discount = $totalGeneral;
    } else {
        $promoMsg = 'Total insuffisant pour appliquer ce code.';
    }
}
$totalDue = max(0, $totalGeneral - $discount);
?>
<div class="container py-5">
  <h3 class="mb-4">Paiement</h3>
  <div class="row">
    <div class="col-lg-8">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title mb-3">Récapitulatif de l’itinéraire</h5>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-2"><strong>Dates</strong></div>
              <div><?= htmlspecialchars($date_depart ?? '-') ?> → <?= htmlspecialchars($date_fin ?? '-') ?></div>
            </div>
            <div class="col-md-6">
              <div class="mb-2"><strong>Participants</strong></div>
              <div><?= htmlspecialchars($participants) ?></div>
            </div>
          </div>
          <hr>
          <div class="mb-2"><strong>Étapes</strong></div>
          <ol class="mb-0">
            <?php foreach ($etapes as $pid): ?>
              <li><?= htmlspecialchars($points[$pid] ?? ('Étape '.$pid)) ?></li>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title mb-3">Hébergements choisis</h5>
          <?php if (!empty($hebsSel)): ?>
            <ul class="list-group">
              <?php foreach ($hebsSel as $pid => $sel): ?>
                <?php
                  $hid = (int)$sel['hebergement_id'];
                  $h = $hebergements[$hid] ?? null;
                ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <div class="fw-semibold"><?= htmlspecialchars($points[$pid] ?? ('Étape '.$pid)) ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($h['nom'] ?? 'Hébergement') ?></div>
                  </div>
                  <div class="text-end">
                    <div class="small text-muted"><?= number_format((float)($h['prix_nuit'] ?? 0), 2, ',', ' ') ?> € / nuit</div>
                    <div class="fw-semibold"><?= number_format(((float)($h['prix_nuit'] ?? 0)) * $nights, 2, ',', ' ') ?> €</div>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <div class="text-muted">Aucun hébergement sélectionné.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title mb-3">Services</h5>
          <?php if (!empty($options)): ?>
            <ul class="list-group">
              <?php if (in_array('transport_bagages', $options)): ?>
                <li class="list-group-item d-flex justify-content-between">
                  <span>Transport de bagages</span>
                  <span><?= number_format(5 * max(0, count($etapes) - 1), 2, ',', ' ') ?> €</span>
                </li>
              <?php endif; ?>
              <?php if (in_array('panier_garni', $options)): ?>
                <li class="list-group-item d-flex justify-content-between">
                  <span>Pack 3 repas/jour</span>
                  <span><?= number_format(20 * $nights * $participants, 2, ',', ' ') ?> €</span>
                </li>
              <?php endif; ?>
              <?php if (in_array('location_materiel', $options)): ?>
                <li class="list-group-item d-flex justify-content-between">
                  <span>Location matériel</span>
                  <span><?= number_format(10 * $nights * $participants, 2, ',', ' ') ?> €</span>
                </li>
              <?php endif; ?>
            </ul>
          <?php else: ?>
            <div class="text-muted">Aucun service sélectionné.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Total</h5>

          <div class="mb-3">
            <?php if ($promoMsg): ?>
              <div class="alert alert-warning py-2 mb-2"><?= htmlspecialchars($promoMsg) ?></div>
            <?php endif; ?>
            <form method="post" class="input-group">
              <input type="text" name="code_promo" class="form-control" placeholder="Code promo" value="<?= htmlspecialchars($_SESSION['promo']['code'] ?? '') ?>">
              <?php if (!empty($_SESSION['promo'])): ?>
                <button name="remove_promo" class="btn btn-outline-danger" value="1">Retirer</button>
              <?php else: ?>
                <button name="apply_promo" class="btn btn-outline-primary" value="1">Appliquer</button>
              <?php endif; ?>
            </form>
          </div>

          <div class="d-flex justify-content-between">
            <span>Hébergements</span><strong><?= number_format($totalHeb, 2, ',', ' ') ?> €</strong>
          </div>
          <div class="d-flex justify-content-between">
            <span>Services</span><strong><?= number_format($servicesTotal, 2, ',', ' ') ?> €</strong>
          </div>
          <?php if ($discount > 0): ?>
            <div class="d-flex justify-content-between text-success mt-2">
              <span>Code promo <?= htmlspecialchars($_SESSION['promo']['code']) ?></span><strong>-<?= number_format($discount, 2, ',', ' ') ?> €</strong>
            </div>
          <?php endif; ?>
          <hr>
          <div class="d-flex justify-content-between fs-5">
            <span>Total à payer</span><strong><?= number_format($totalDue, 2, ',', ' ') ?> €</strong>
          </div>
          <hr>
          <form action="../process/valider_paiement.php" method="post" class="mt-3">
            <input type="hidden" name="total" value="<?= htmlspecialchars($totalDue) ?>">
            <div class="mb-3">
              <label class="form-label">Nom sur la carte</label>
              <input type="text" name="card_name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Numéro de carte</label>
              <input type="text" name="card_number" class="form-control" minlength="12" maxlength="19" required>
            </div>
            <div class="row">
              <div class="col-6 mb-3">
                <label class="form-label">Expiration</label>
                <input type="text" name="card_exp" class="form-control" placeholder="MM/AA" required>
              </div>
              <div class="col-6 mb-3">
                <label class="form-label">CVC</label>
                <input type="text" name="card_cvc" class="form-control" minlength="3" maxlength="4" required>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Payer maintenant</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include('../includes/footer.php'); ?>
