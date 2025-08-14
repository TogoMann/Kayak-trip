<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../page/composer.php'); exit; }

$etapes = isset($_POST['etapes']) ? json_decode($_POST['etapes'], true) : [];
$participants = isset($_POST['participants']) ? (int)$_POST['participants'] : 1;
$duree = isset($_POST['duree']) ? (int)$_POST['duree'] : 1;
$date_depart = $_POST['date_depart'] ?? null;
$date_fin = $_POST['date_fin'] ?? null;
$options = isset($_POST['options']) ? (array)$_POST['options'] : [];

if (!$etapes || !is_array($etapes)) { header('Location: ../page/composer.php'); exit; }

$_SESSION['itineraire'] = [
  'etapes' => $etapes,
  'participants' => max(1,$participants),
  'duree' => max(1,$duree),
  'date_depart' => $date_depart,
  'date_fin' => $date_fin,
  'options' => $options
];

header('Location: ../page/choix_hebergements.php');
exit;
