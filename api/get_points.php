<?php
require_once('../includes/db.php');

header('Content-Type: application/json');

$sql = "SELECT id, nom, latitude, longitude FROM point_arret";
$stmt = $pdo->query($sql);
$points = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($points);
