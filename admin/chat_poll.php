<?php
require_once __DIR__.'/_guard.php';
header('Content-Type: application/json; charset=utf-8');
$me=(int)$_SESSION['user_id'];
$since=(int)($_GET['since_id']??0);
$to=isset($_GET['to'])?(int)$_GET['to']:0;
$channel=($_GET['channel']??'');
$params=[];
$w=[];
if($channel==='general'){
  $w[]="m.channel='general'";
}else{
  if($to>0){
    $w[]="((m.sender_id=? AND m.recipient_id=?) OR (m.sender_id=? AND m.recipient_id=?))";
    $params[]=$me; $params[]=$to; $params[]=$to; $params[]=$me;
  } else {
    echo json_encode([]); exit;
  }
}
if($since>0){ $w[]="m.id>?"; $params[]=$since; }
$sql="SELECT m.id,m.sender_id,COALESCE(u.nom_affichage,TRIM(CONCAT(u.prenom,' ',u.nom)),u.email) AS sender_name,m.body,m.created_at FROM admin_chat_message m INNER JOIN utilisateur u ON u.id=m.sender_id";
if($w){ $sql.=" WHERE ".implode(" AND ",$w); }
$sql.=" ORDER BY m.id ASC LIMIT 100";
$stmt=$pdo->prepare($sql); $stmt->execute($params);
$out=[];
while($r=$stmt->fetch(PDO::FETCH_ASSOC)){
  $out[]=[
    'id'=>(int)$r['id'],
    'sender_id'=>(int)$r['sender_id'],
    'sender_name'=>$r['sender_name'],
    'body_html'=>nl2br(htmlspecialchars($r['body'])),
    'created_at'=>$r['created_at']
  ];
}
echo json_encode($out,JSON_UNESCAPED_UNICODE);
