<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=utf-8');
$uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$sessionKey = session_id();
$tid = (int)($_GET['thread_id'] ?? 0);
$since = (int)($_GET['since_id'] ?? 0);
if ($tid <= 0) { echo json_encode([]); exit; }
$chk = $pdo->prepare("SELECT id FROM support_thread WHERE id=? AND (user_id ".($uid!==null?'= ?':'IS NULL')." OR session_key=?) LIMIT 1");
$params = [$tid];
if ($uid!==null) $params[] = $uid;
$params[] = $sessionKey;
$chk->execute($params);
if (!$chk->fetch()) { echo json_encode([]); exit; }
$sql = "SELECT m.id,m.sender_role,m.sender_id,m.body,m.created_at,COALESCE(u.nom_affichage,TRIM(CONCAT(u.prenom,' ',u.nom)),u.email) AS sender_name FROM support_message m LEFT JOIN utilisateur u ON u.id=m.sender_id WHERE m.thread_id=?".($since>0?" AND m.id>?":"")." ORDER BY m.id ASC LIMIT 100";
$st = $pdo->prepare($sql);
$st->execute($since>0?[$tid,$since]:[$tid]);
$out = [];
while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
  $out[] = [
    'id'=>(int)$r['id'],
    'sender_role'=>$r['sender_role'],
    'sender_id'=>$r['sender_id']!==null?(int)$r['sender_id']:null,
    'sender_name'=>$r['sender_role']==='admin' ? ($r['sender_name'] ?: 'Admin') : 'Vous',
    'body_html'=>nl2br(htmlspecialchars($r['body'])),
    'created_at'=>$r['created_at']
  ];
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);
