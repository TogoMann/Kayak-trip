<?php
require_once __DIR__ . '/_guard.php';

function read_categorie_id(PDO $pdo, $key = 'categorie_id') {
    $raw = $_POST[$key] ?? null;
    if ($raw === null || $raw === '' || $raw === '0') return null;
    $id = (int)$raw;
    $chk = $pdo->prepare("SELECT id FROM service_categorie WHERE id=? LIMIT 1");
    $chk->execute([$id]);
    return $chk->fetchColumn() ? $id : null;
}

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $nom = trim($_POST['nom'] ?? '');
    $prix = $_POST['prix'] !== '' ? (float)$_POST['prix'] : 0;
    $description = trim($_POST['description'] ?? '');
    $actif = isset($_POST['actif']) ? 1 : 0;
    $categorie_id = read_categorie_id($pdo);
    if ($nom !== '') {
        $pdo->prepare("INSERT INTO service (nom,description,prix,actif,categorie_id) VALUES (?,?,?,?,?)")
            ->execute([$nom,$description,$prix,$actif,$categorie_id]);
    }
    header('Location: /admin/options.php?ok=1'); exit;
}

if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $prix = $_POST['prix'] !== '' ? (float)$_POST['prix'] : 0;
    $description = trim($_POST['description'] ?? '');
    $actif = isset($_POST['actif']) ? 1 : 0;
    $categorie_id = read_categorie_id($pdo);
    if ($id > 0 && $nom !== '') {
        $pdo->prepare("UPDATE service SET nom=?, description=?, prix=?, actif=?, categorie_id=? WHERE id=?")
            ->execute([$nom,$description,$prix,$actif,$categorie_id,$id]);
    }
    header('Location: /admin/options.php?ok=1'); exit;
}

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("DELETE FROM service WHERE id=?")->execute([$id]);
    }
    header('Location: /admin/options.php?ok=1'); exit;
}

header('Location: /admin/options.php');
