<?php
require_once __DIR__.'/_guard.php';
$id=(int)($_POST['id']??0);
$action=$_POST['action']??'';
if($id>0){
  if($action==='close'){ $pdo->prepare("UPDATE support_thread SET status='closed' WHERE id=?")->execute([$id]); }
  if($action==='open'){ $pdo->prepare("UPDATE support_thread SET status='open' WHERE id=?")->execute([$id]); }
}
header('Location: /admin/support_view.php?id='.$id.'&ok=1');
