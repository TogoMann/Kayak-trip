<?php
require_once __DIR__ . '/_guard.php';
$action = $_POST['action'] ?? '';
if ($action === 'create') {
    $nom = trim($_POST['nom'] ?? '');
    $point = (int)($_POST['point_arret_id'] ?? 0);
    $capacite = (int)($_POST['capacite'] ?? 0);
    $prix = $_POST['prix_base'] !== '' ? (float)$_POST['prix_base'] : null;
    $description = trim($_POST['description'] ?? '');
    $actif = isset($_POST['actif']) ? 1 : 0;
    if ($nom!=='' && $point>0 && $capacite>0) {
        $stmt = $pdo->prepare("INSERT INTO hebergement (point_arret_id,nom,description,capacite,prix_base,actif) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$point,$nom,$description,$capacite,$prix,$actif]);
    }
    header('Location: /admin/hebergements.php?ok=1'); exit;
}
if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $point = (int)($_POST['point_arret_id'] ?? 0);
    $capacite = (int)($_POST['capacite'] ?? 0);
    $prix = $_POST['prix_base'] !== '' ? (float)$_POST['prix_base'] : null;
    $description = trim($_POST['description'] ?? '');
    $actif = isset($_POST['actif']) ? 1 : 0;
    if ($id>0 && $nom!=='' && $point>0 && $capacite>0) {
        $stmt = $pdo->prepare("UPDATE hebergement SET point_arret_id=?, nom=?, description=?, capacite=?, prix_base=?, actif=? WHERE id=?");
        $stmt->execute([$point,$nom,$description,$capacite,$prix,$actif,$id]);
    }
    header('Location: /admin/hebergements.php?ok=1'); exit;
}
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id>0) {
        $del = $pdo->prepare("DELETE FROM hebergement WHERE id=?");
        $del->execute([$id]);
    }
    header('Location: /admin/hebergements.php?ok=1'); exit;
}
header('Location: /admin/hebergements.php');
