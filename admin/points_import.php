<?php
require_once __DIR__ . '/_guard.php';
if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) { header('Location: /admin/points.php'); exit; }
$path = $_FILES['csv']['tmp_name'];
$handle = fopen($path, 'r');
if (!$handle) { header('Location: /admin/points.php'); exit; }
$first = fgets($handle);
rewind($handle);
$delim = (substr_count($first, ';') > substr_count($first, ',')) ? ';' : ',';
$imported = 0;
$skipped = 0;
$line = 0;
while (($row = fgetcsv($handle, 0, $delim)) !== false) {
    $line++;
    if ($line === 1 && count($row) >= 4) {
        $hdr = strtolower(implode('|', $row));
        if (strpos($hdr,'nom')!==false && strpos($hdr,'latitude')!==false && strpos($hdr,'longitude')!==false) continue;
    }
    if (count($row) < 4) { $skipped++; continue; }
    $nom = trim($row[0] ?? '');
    $desc = trim($row[1] ?? '');
    $lat = trim($row[2] ?? '');
    $lng = trim($row[3] ?? '');
    if ($nom === '' || !is_numeric($lat) || !is_numeric($lng)) { $skipped++; continue; }
    $stmt = $pdo->prepare("INSERT INTO point_arret (nom,description,latitude,longitude) VALUES (?,?,?,?)");
    $stmt->execute([$nom,$desc,(float)$lat,(float)$lng]);
    $imported++;
}
fclose($handle);
header('Location: /admin/points.php?imported='.$imported.'&skipped='.$skipped);
exit;
