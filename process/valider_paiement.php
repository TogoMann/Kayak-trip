<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (empty($_SESSION['itineraire']) || empty($_SESSION['itineraire']['etapes'])) { header('Location: ../page/composer.php'); exit; }
if (empty($_SESSION['user_id'])) { header('Location: ../page/login.php?error=login'); exit; }

$itin = $_SESSION['itineraire'];
$uid = (int)$_SESSION['user_id'];
$etapes = array_map('intval', $itin['etapes']);
$participants = (int)($itin['participants'] ?? 1);
$duree = (int)($itin['duree'] ?? 1);
$date_depart = $itin['date_depart'] ?? null;
$date_fin = $itin['date_fin'] ?? null;
$options = $itin['options'] ?? [];
$hebsSel = $itin['hebergements'] ?? [];

$nights = $duree;
if ($date_depart && $date_fin) { $d1 = new DateTime($date_depart); $d2 = new DateTime($date_fin); $diff = $d1->diff($d2)->days; if ($diff > 0) $nights = $diff; }

$hebergements = [];
if (!empty($hebsSel)) {
  $ids = array_map('intval', array_column($hebsSel, 'hebergement_id'));
  if ($ids) {
    $in = implode(',', array_fill(0, count($ids), '?'));
    $q = $pdo->prepare("SELECT id, prix_base FROM hebergement WHERE id IN ($in)");
    $q->execute($ids);
    while ($h = $q->fetch(PDO::FETCH_ASSOC)) $hebergements[(int)$h['id']] = (float)$h['prix_base'];
  }
}

$totalHeb = 0.0;
foreach ($hebsSel as $pid => $sel) { $hid = (int)$sel['hebergement_id']; $pn = $hebergements[$hid] ?? 0.0; $totalHeb += $pn * $nights; }

$servicesTotal = 0.0;
if (in_array('transport_bagages', $options, true)) $servicesTotal += 5 * max(0, count($etapes) - 1);
if (in_array('panier_garni', $options, true)) $servicesTotal += 20 * $nights * $participants;
if (in_array('location_materiel', $options, true)) $servicesTotal += 10 * $nights * $participants;

$totalGeneral = $totalHeb + $servicesTotal;

try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("INSERT INTO reservation (utilisateur_id, type, date_debut, date_fin, nb_personnes, total) VALUES (?,?,?,?,?,?)");
  $stmt->execute([$uid, 'personnalise', $date_depart, $date_fin, $participants, $totalGeneral]);
  $rid = (int)$pdo->lastInsertId();

  $d = $date_depart ? new DateTime($date_depart) : new DateTime();
  foreach ($etapes as $pid) {
    $dateEtape = $d->format('Y-m-d');
    $hid = isset($hebsSel[$pid]['hebergement_id']) ? (int)$hebsSel[$pid]['hebergement_id'] : null;
    $ins = $pdo->prepare("INSERT INTO reservation_etape (reservation_id, point_arret_id, hebergement_id, date) VALUES (?,?,?,?)");
    $ins->execute([$rid, $pid, $hid, $dateEtape]);
    $d->modify('+1 day');
  }

  $map = [
    'transport_bagages' => "SELECT id FROM service WHERE nom LIKE 'Transport bagages%' LIMIT 1",
    'panier_garni' => "SELECT id FROM service WHERE nom LIKE 'Pack complet (3 repas)%' LIMIT 1",
    'location_materiel' => "SELECT id FROM service WHERE nom LIKE 'Location%matériel%' LIMIT 1"
  ];
  foreach ($options as $opt) {
    if (!isset($map[$opt])) continue;
    $sid = (int)$pdo->query($map[$opt])->fetchColumn();
    if ($sid > 0) $pdo->prepare("INSERT INTO reservation_service (reservation_id, service_id) VALUES (?,?)")->execute([$rid, $sid]);
  }

  $pdo->commit();
  $_SESSION['paiement'] = ['total' => $totalGeneral, 'reservation_id' => $rid];
  header('Location: ../page/paiement_sucess.php'); exit;
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  header('Location: ../page/paiement.php?error=save'); exit;
}
