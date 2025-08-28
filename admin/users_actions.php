<?php
require_once __DIR__ . '/_guard.php';
$action = $_POST['action'] ?? '';
if ($action === 'create') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $role = in_array($_POST['role'] ?? 'user', ['user','admin'], true) ? $_POST['role'] : 'user';
  $aff = trim($_POST['nom_affichage'] ?? '');
  if ($email !== '' && $password !== '') {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $pdo->prepare("INSERT INTO utilisateur (email,mot_de_passe,role,nom_affichage) VALUES (?,?,?,?)")->execute([$email,$hash,$role,$aff!==''?$aff:null]);
  }
  header('Location: /admin/users.php?ok=1'); exit;
}
if ($action === 'update') {
  $id = (int)($_POST['id'] ?? 0);
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $role = in_array($_POST['role'] ?? 'user', ['user','admin'], true) ? $_POST['role'] : 'user';
  $aff = trim($_POST['nom_affichage'] ?? '');
  if ($id > 0 && $email !== '') {
    if ($password !== '') {
      $hash = password_hash($password, PASSWORD_BCRYPT);
      $pdo->prepare("UPDATE utilisateur SET email=?, mot_de_passe=?, role=?, nom_affichage=? WHERE id=?")->execute([$email,$hash,$role,$aff!==''?$aff:null,$id]);
    } else {
      $pdo->prepare("UPDATE utilisateur SET email=?, role=?, nom_affichage=? WHERE id=?")->execute([$email,$role,$aff!==''?$aff:null,$id]);
    }
  }
  header('Location: /admin/users.php?ok=1'); exit;
}
if ($action === 'delete') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id > 0 && (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] !== $id)) {
    $pdo->prepare("DELETE FROM utilisateur WHERE id=?")->execute([$id]);
    header('Location: /admin/users.php?ok=1'); exit;
  }
  header('Location: /admin/users.php?err=self'); exit;
}
header('Location: /admin/users.php');
