<?php
require_once __DIR__.'/_guard.php';
$action=$_POST['action']??'';
if($action==='create'){
  $code=strtoupper(trim($_POST['code']??''));
  $type=in_array($_POST['type']??'fixed',['fixed','percent'],true)?$_POST['type']:'fixed';
  $value=(float)($_POST['value']??0);
  $start=$_POST['start_date']??null;
  $end=$_POST['end_date']??null;
  $usage=$_POST['usage_limit']!==''? (int)$_POST['usage_limit'] : null;
  $per=$_POST['per_user_limit']!==''? (int)$_POST['per_user_limit'] : null;
  $min=$_POST['min_total']!==''? (float)$_POST['min_total'] : null;
  $desc=trim($_POST['description']??'');
  $act=isset($_POST['actif'])?1:0;
  if($code!==''){
    $stmt=$pdo->prepare("INSERT INTO promo_code(code,description,type,value,start_date,end_date,usage_limit,per_user_limit,min_total,actif) VALUES(?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$code,$desc!==''?$desc:null,$type,$value,$start?:null,$end?:null,$usage,$per,$min,$act]);
  }
  header('Location: /admin/promos.php?ok=1'); exit;
}
if($action==='update'){
  $id=(int)($_POST['id']??0);
  $code=strtoupper(trim($_POST['code']??''));
  $type=in_array($_POST['type']??'fixed',['fixed','percent'],true)?$_POST['type']:'fixed';
  $value=(float)($_POST['value']??0);
  $start=$_POST['start_date']??null;
  $end=$_POST['end_date']??null;
  $usage=$_POST['usage_limit']!==''? (int)$_POST['usage_limit'] : null;
  $per=$_POST['per_user_limit']!==''? (int)$_POST['per_user_limit'] : null;
  $min=$_POST['min_total']!==''? (float)$_POST['min_total'] : null;
  $desc=trim($_POST['description']??'');
  $act=isset($_POST['actif'])?1:0;
  if($id>0 && $code!==''){
    $stmt=$pdo->prepare("UPDATE promo_code SET code=?, description=?, type=?, value=?, start_date=?, end_date=?, usage_limit=?, per_user_limit=?, min_total=?, actif=? WHERE id=?");
    $stmt->execute([$code,$desc!==''?$desc:null,$type,$value,$start?:null,$end?:null,$usage,$per,$min,$act,$id]);
  }
  header('Location: /admin/promos.php?ok=1'); exit;
}
if($action==='delete'){
  $id=(int)($_POST['id']??0);
  if($id>0){ $pdo->prepare("DELETE FROM promo_code WHERE id=?")->execute([$id]); }
  header('Location: /admin/promos.php?ok=1'); exit;
}
header('Location: /admin/promos.php');
