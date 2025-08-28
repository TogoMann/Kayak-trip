<?php
require_once __DIR__ . '/_guard.php';
$action = $_POST['action'] ?? '';
if ($action === 'create') {
    $nom = trim($_POST['nom'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $ordre = (int)($_POST['ordre'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $actif = isset($_POST['actif']) ? 1 : 0;
    if ($nom!=='' && $slug!=='') { $pdo->prepare("INSERT INTO service_categorie (nom,slug,description,ordre,actif) VALUES (?,?,?,?,?)")->execute([$nom,$slug,$description,$ordre,$actif]); }
    header('Location: /admin/categories.php?ok=1'); exit;
}
if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $ordre = (int)($_POST['ordre'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $actif = isset($_POST['actif']) ? 1 : 0;
    if ($id>0 && $nom!=='' && $slug!=='') { $pdo->prepare("UPDATE service_categorie SET nom=?,slug=?,description=?,ordre=?,actif=? WHERE id=?")->execute([$nom,$slug,$description,$ordre,$actif,$id]); }
    header('Location: /admin/categories.php?ok=1'); exit;
}
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id>0) { $pdo->prepare("DELETE FROM service_categorie WHERE id=?")->execute([$id]); }
    header('Location: /admin/categories.php?ok=1'); exit;
}
header('Location: /admin/categories.php');
