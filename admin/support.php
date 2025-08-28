<?php
require_once __DIR__.'/_guard.php';
$active='support';
$page_title='Support';
$status=$_GET['status']??'open';
$cat=$_GET['cat']??'';
$q=trim($_GET['q']??'');
$params=[];
$w=[];
$sql="SELECT t.*,u.email,COALESCE(u.nom_affichage,TRIM(CONCAT(u.prenom,' ',u.nom))) AS uname,(SELECT MAX(m.created_at) FROM support_message m WHERE m.thread_id=t.id) AS last_msg FROM support_thread t LEFT JOIN utilisateur u ON u.id=t.user_id";
if($status==='open') $w[]="t.status='open'";
if($status==='closed') $w[]="t.status='closed'";
if($cat!==''){ $w[]="t.category=?"; $params[]=$cat; }
if($q!==''){ $w[]="(t.id=? OR t.email LIKE ? OR t.name LIKE ? OR u.email LIKE ?)"; $params[]=ctype_digit($q)?(int)$q:-1; $params[]="%$q%"; $params[]="%$q%"; $params[]="%$q%"; }
if($w) $sql.=" WHERE ".implode(" AND ",$w);
$sql.=" ORDER BY COALESCE(last_msg,t.created_at) DESC";
$stmt=$pdo->prepare($sql); $stmt->execute($params); $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
require_once __DIR__.'/_layout_start.php';
?>
<div class="card p-3 mb-3">
  <form class="row g-2 align-items-center" method="get">
    <div class="col-12 col-md-3">
      <select name="status" class="form-select" onchange="this.form.submit()">
        <option value="">Tous</option>
        <option value="open" <?php echo $status==='open'?'selected':''; ?>>Ouverts</option>
        <option value="closed" <?php echo $status==='closed'?'selected':''; ?>>Fermés</option>
      </select>
    </div>
    <div class="col-12 col-md-3">
      <select name="cat" class="form-select" onchange="this.form.submit()">
        <option value="">Toutes catégories</option>
        <option value="technique" <?php echo $cat==='technique'?'selected':''; ?>>Technique</option>
        <option value="commercial" <?php echo $cat==='commercial'?'selected':''; ?>>Commercial</option>
        <option value="autre" <?php echo $cat==='autre'?'selected':''; ?>>Autre</option>
      </select>
    </div>
    <div class="col-12 col-md-4">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Rechercher par ID, e-mail, nom">
      </div>
    </div>
    <div class="col-12 col-md-auto"><button class="btn btn-primary">Rechercher</button></div>
  </form>
</div>

<div class="card p-0">
  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle mb-0">
      <thead>
        <tr>
          <th style="width:80px">ID</th>
          <th>Client</th>
          <th style="width:140px">Catégorie</th>
          <th style="width:160px">Créé le</th>
          <th style="width:170px">Dernier msg</th>
          <th style="width:120px">Statut</th>
          <th style="width:220px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): ?>
        <tr>
          <td>#<?php echo (int)$r['id']; ?></td>
          <td>
            <div class="fw-semibold"><?php echo htmlspecialchars($r['uname']?:($r['name']?:'Visiteur')); ?></div>
            <div class="text-secondary small"><?php echo htmlspecialchars($r['email']?:''); ?></div>
          </td>
          <td><?php echo htmlspecialchars(ucfirst($r['category'])); ?></td>
          <td><?php echo date('d/m/Y H:i',strtotime($r['created_at'])); ?></td>
          <td><?php echo $r['last_msg']?date('d/m/Y H:i',strtotime($r['last_msg'])):'—'; ?></td>
          <td><?php echo $r['status']==='open'?'Ouvert':'Fermé'; ?></td>
          <td class="d-flex gap-2">
            <a class="btn btn-outline-light btn-sm" href="/admin/support_view.php?id=<?php echo (int)$r['id']; ?>"><i class="bi bi-eye me-1"></i>Ouvrir</a>
            <form action="/admin/support_actions.php" method="post">
              <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
              <input type="hidden" name="action" value="<?php echo $r['status']==='open'?'close':'open'; ?>">
              <button class="btn btn-outline-<?php echo $r['status']==='open'?'warning':'success'; ?> btn-sm"><?php echo $r['status']==='open'?'Fermer':'Réouvrir'; ?></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(!count($rows)): ?>
        <tr><td colspan="7" class="text-center text-secondary py-4">Aucun fil</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__.'/_layout_end.php'; ?>
