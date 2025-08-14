<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../page/choix_hebergements.php'); exit; }

$selection = isset($_POST['selection']) ? json_decode($_POST['selection'], true) : [];
$total_heb = isset($_POST['total_heb']) ? (float)$_POST['total_heb'] : 0;

if (!isset($_SESSION['itineraire'])) { header('Location: ../page/composer.php'); exit; }

$_SESSION['itineraire']['hebergements'] = $selection;
$_SESSION['itineraire']['total_heb'] = $total_heb;

header('Location: ../page/paiement.php');
exit;
