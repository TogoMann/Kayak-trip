<?php
require_once __DIR__ . '/_guard.php';
$action = $_POST['action'] ?? '';
if ($action === 'create') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $lat = $_POST['latitude'] ?? null;
    $lng = $_POST['longitude'] ?? null;
    if ($nom === '' || $lat === null || $lng === null) { header('Location: /admin/points.php'); exit; }
    $stmt = $pdo->prepare("INSERT INTO point_arret (nom,description,latitude,longitude) VALUES (?,?,?,?)");
    $stmt->execute([$nom,$description,$lat,$lng]);
    header('Location: /admin/points.php?ok=1'); exit;
}
if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $lat = $_POST['latitude'] ?? null;
    $lng = $_POST['longitude'] ?? null;
    if ($id<=0 || $nom === '' || $lat === null || $lng === null) { header('Location: /admin/points.php'); exit; }
    $stmt = $pdo->prepare("UPDATE point_arret SET nom=?,description=?,latitude=?,longitude=? WHERE id=?");
    $stmt->execute([$nom,$description,$lat,$lng,$id]);
    header('Location: /admin/points.php?ok=1'); exit;
}
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id>0) {
        try { $pdo->prepare("DELETE FROM point_arret WHERE id=?")->execute([$id]); header('Location: /admin/points.php?ok=1'); exit; }
        catch (Throwable $e) { header('Location: /admin/points.php?err=constraint'); exit; }
    }
    header('Location: /admin/points.php'); exit;
}
header('Location: /admin/points.php');
