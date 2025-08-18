<?php
require_once __DIR__ . '/_guard.php';
$action = $_POST['action'] ?? '';
if ($action === 'create') {
    $nom = trim($_POST['nom_affichage'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'client';
    $verifie = isset($_POST['verifie']) ? 1 : 0;
    if ($nom === '' || $email === '' || $password === '') { header('Location: /admin/users.php?q=' . urlencode($email)); exit; }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, nom_affichage, email, mot_de_passe, role, verifie) VALUES ('','',?,?,?,?,?)");
    $stmt->execute([$nom,$email,$hash,$role,$verifie]);
    header('Location: /admin/users.php'); exit;
}
if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $nom = trim($_POST['nom_affichage'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'client';
    if ($id <= 0 || $nom === '' || $email === '') { header('Location: /admin/users.php'); exit; }
    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE utilisateur SET nom_affichage=?, email=?, role=?, mot_de_passe=? WHERE id=?");
        $stmt->execute([$nom,$email,$role,$hash,$id]);
    } else {
        $stmt = $pdo->prepare("UPDATE utilisateur SET nom_affichage=?, email=?, role=? WHERE id=?");
        $stmt->execute([$nom,$email,$role,$id]);
    }
    header('Location: /admin/users.php'); exit;
}
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) { $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id=?"); $stmt->execute([$id]); }
    header('Location: /admin/users.php'); exit;
}
header('Location: /admin/users.php');
