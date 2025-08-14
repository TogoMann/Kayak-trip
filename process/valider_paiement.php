<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['itineraire'])) {
  header('Location: ../page/composer.php'); exit;
}

$total = isset($_POST['total']) ? (float)$_POST['total'] : 0;
$_SESSION['paiement'] = [
  'total' => $total,
  'date' => date('Y-m-d H:i:s')
];

header('Location: ../page/paiement_success.php');
exit;
