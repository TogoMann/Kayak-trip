<?php
require_once __DIR__.'/_guard.php';
$action=$_POST['action']??'';
if($action==='create'){
  $nom=trim($_POST['nom']??'');
  $duree=$_POST['duree_jours']!==''?(int)$_POST['duree_jours']:null;
  $prix=$_POST['prix_total']!==''?(float)$_POST['prix_total']:null;
  $desc=trim($_POST['description']??'');
  if($nom!==''){ $pdo->prepare("INSERT INTO pack (nom,description,duree_jours,prix_total) VALUES (?,?,?,?)")->execute([$nom,$desc,$duree,$prix]); }
  header('Location: /admin/packs.php?ok=1'); exit;
}
if($action==='update'){
  $id=(int)($_POST['id']??0);
  $nom=trim($_POST['nom']??'');
  $duree=$_POST['duree_jours']!==''?(int)$_POST['duree_jours']:null;
  $prix=$_POST['prix_total']!==''?(float)$_POST['prix_total']:null;
  $desc=trim($_POST['description']??'');
  if($id>0 && $nom!==''){ $pdo->prepare("UPDATE pack SET nom=?, description=?, duree_jours=?, prix_total=? WHERE id=?")->execute([$nom,$desc,$duree,$prix,$id]); }
  header('Location: /admin/packs.php?ok=1'); exit;
}
if($action==='delete'){
  $id=(int)($_POST['id']??0);
  if($id>0){ $pdo->prepare("DELETE FROM pack WHERE id=?")->execute([$id]); }
  header('Location: /admin/packs.php?ok=1'); exit;
}
header('Location: /admin/packs.php');
