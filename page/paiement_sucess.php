<?php
session_start();
require_once('../includes/header.php');

if (empty($_SESSION['paiement']) || empty($_SESSION['itineraire'])) {
  header('Location: composer.php'); exit;
}

$itin = $_SESSION['itineraire'];
$pay = $_SESSION['paiement'];
?>
<div class="container py-5 text-center">
  <div class="mb-4">
    <div class="display-6">Paiement confirmé</div>
    <p class="text-muted">Merci pour votre réservation. Un e‑mail de confirmation vous sera envoyé.</p>
  </div>
  <div class="card mx-auto" style="max-width:600px">
    <div class="card-body">
      <div class="mb-2"><strong>Montant</strong></div>
      <div class="fs-4 mb-3"><?= number_format((float)$pay['total'], 2, ',', ' ') ?> €</div>
      <div class="mb-2"><strong>Dates</strong></div>
      <div class="mb-3"><?= htmlspecialchars($itin['date_depart'] ?? '-') ?> → <?= htmlspecialchars($itin['date_fin'] ?? '-') ?></div>
      <a href="reservations.php" class="btn btn-primary">Voir mes réservations</a>
    </div>
  </div>
</div>
<?php include('../includes/footer.php'); ?>
