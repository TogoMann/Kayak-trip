<?php
require_once __DIR__.'/_guard.php';
$active='promos';
$page_title='Codes promos';
$q=trim($_GET['q']??'');
$act=($_GET['act']??'');
$params=[];
$w=[];
$sql="SELECT p.*, (SELECT COUNT(*) FROM promo_redemption pr WHERE pr.promo_id=p.id) AS used_count FROM promo_code p";
if($q!==''){ $w[]="(p.code LIKE ? OR p.description LIKE ?)"; $like="%$q%"; $params[]=$like; $params[]=$like; }
if($act==='on'){ $w[]="p.actif=1"; }
if($act==='off'){ $w[]="p.actif=0"; }
if($w){ $sql.=" WHERE ".implode(" AND ",$w); }
$sql.=" ORDER BY p.actif DESC, p.code ASC";
$stmt=$pdo->prepare($sql); $stmt->execute($params); $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
require_once __DIR__.'/_layout_start.php';
?>
<?php if(isset($_GET['ok'])): ?><div class="alert alert-success">Opération effectuée.</div><?php endif; ?>
<div class="card p-3 mb-3">
  <form class="row g-2 align-items-center" method="get">
    <div class="col-12 col-md-5">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Rechercher par code ou description">
      </div>
    </div>
    <div class="col-12 col-md-3">
      <select name="act" class="form-select" onchange="this.form.submit()">
        <option value="">Tous les statuts</option>
        <option value="on" <?php echo $act==='on'?'selected':''; ?>>Actifs</option>
        <option value="off" <?php echo $act==='off'?'selected':''; ?>>Inactifs</option>
      </select>
    </div>
    <div class="col-6 col-md-auto"><button class="btn btn-primary">Rechercher</button></div>
    <div class="col-6 col-md-auto ms-auto text-end"><button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal"><i class="bi bi-plus-lg me-1"></i>Ajouter</button></div>
  </form>
</div>

<div class="card p-0">
  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle mb-0">
      <thead>
        <tr>
          <th style="width:70px">ID</th>
          <th style="width:160px">Code</th>
          <th style="width:120px">Type</th>
          <th style="width:120px">Valeur</th>
          <th style="width:160px">Période</th>
          <th style="width:140px">Conditions</th>
          <th style="width:110px">Actif</th>
          <th style="width:110px">Utilisé</th>
          <th>Description</th>
          <th style="width:230px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td class="fw-semibold"><?php echo htmlspecialchars($r['code']); ?></td>
          <td><?php echo htmlspecialchars($r['type']); ?></td>
          <td><?php echo number_format((float)$r['value'],2,',',' '); ?></td>
          <td><?php echo $r['start_date']?date('d/m/Y',strtotime($r['start_date'])):'—'; ?> → <?php echo $r['end_date']?date('d/m/Y',strtotime($r['end_date'])):'—'; ?></td>
          <td>
            <?php
              $lim=$r['usage_limit']!==null?('Max '.$r['usage_limit']):'—';
              $per=$r['per_user_limit']!==null?('Par client '.$r['per_user_limit']):'—';
              $min=$r['min_total']!==null?('Min '.number_format((float)$r['min_total'],2,',',' ').'€'):'—';
              echo htmlspecialchars($lim.' | '.$per.' | '.$min);
            ?>
          </td>
          <td><?php echo (int)$r['actif']===1?'Oui':'Non'; ?></td>
          <td><span class="badge text-bg-secondary"><?php echo (int)$r['used_count']; ?></span></td>
          <td class="text-truncate" style="max-width:320px"><?php echo htmlspecialchars($r['description']??''); ?></td>
          <td class="d-flex gap-2">
            <button type="button" class="btn btn-outline-light btn-sm btn-edit"
              data-id="<?php echo (int)$r['id']; ?>"
              data-code="<?php echo htmlspecialchars($r['code']); ?>"
              data-type="<?php echo htmlspecialchars($r['type']); ?>"
              data-value="<?php echo htmlspecialchars($r['value']); ?>"
              data-start="<?php echo htmlspecialchars($r['start_date']); ?>"
              data-end="<?php echo htmlspecialchars($r['end_date']); ?>"
              data-usage="<?php echo htmlspecialchars($r['usage_limit']); ?>"
              data-peruser="<?php echo htmlspecialchars($r['per_user_limit']); ?>"
              data-min="<?php echo htmlspecialchars($r['min_total']); ?>"
              data-actif="<?php echo (int)$r['actif']; ?>"
              data-description="<?php echo htmlspecialchars($r['description']??''); ?>"
              data-bs-toggle="modal" data-bs-target="#editModal">Modifier</button>
            <form action="/admin/promos_actions.php" method="post" onsubmit="return confirm('Supprimer ce code promo ?');">
              <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
              <button class="btn btn-outline-danger btn-sm">Supprimer</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(count($rows)===0): ?>
        <tr><td colspan="10" class="text-center text-secondary py-4">Aucun code</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/admin/promos_actions.php" method="post">
        <input type="hidden" name="action" value="create">
        <div class="modal-header"><h5 class="modal-title">Ajouter un code</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Type</label><select name="type" class="form-select"><option value="fixed">fixed</option><option value="percent">percent</option></select></div>
          <div class="mb-3"><label class="form-label">Valeur</label><input type="number" step="0.01" name="value" class="form-control" required></div>
          <div class="row g-2">
            <div class="col"><label class="form-label">Début</label><input type="date" name="start_date" class="form-control"></div>
            <div class="col"><label class="form-label">Fin</label><input type="date" name="end_date" class="form-control"></div>
          </div>
          <div class="row g-2 mt-2">
            <div class="col"><label class="form-label">Limite totale</label><input type="number" name="usage_limit" class="form-control"></div>
            <div class="col"><label class="form-label">Limite/client</label><input type="number" name="per_user_limit" class="form-control"></div>
          </div>
          <div class="mb-3 mt-2"><label class="form-label">Montant minimum (€)</label><input type="number" step="0.01" name="min_total" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
          <div class="form-check"><input class="form-check-input" type="checkbox" name="actif" id="cactif" value="1" checked><label class="form-check-label" for="cactif">Actif</label></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/admin/promos_actions.php" method="post">
        <input type="hidden" name="action" value="update"><input type="hidden" name="id" id="eid">
        <div class="modal-header"><h5 class="modal-title">Modifier le code</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Code</label><input type="text" name="code" id="ecode" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Type</label><select name="type" id="etype" class="form-select"><option value="fixed">fixed</option><option value="percent">percent</option></select></div>
          <div class="mb-3"><label class="form-label">Valeur</label><input type="number" step="0.01" name="value" id="evalue" class="form-control" required></div>
          <div class="row g-2">
            <div class="col"><label class="form-label">Début</label><input type="date" name="start_date" id="estart" class="form-control"></div>
            <div class="col"><label class="form-label">Fin</label><input type="date" name="end_date" id="eend" class="form-control"></div>
          </div>
          <div class="row g-2 mt-2">
            <div class="col"><label class="form-label">Limite totale</label><input type="number" name="usage_limit" id="eusage" class="form-control"></div>
            <div class="col"><label class="form-label">Limite/client</label><input type="number" name="per_user_limit" id="eperuser" class="form-control"></div>
          </div>
          <div class="mb-3 mt-2"><label class="form-label">Montant minimum (€)</label><input type="number" step="0.01" name="min_total" id="emin" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Description</label><textarea name="description" id="edesc" class="form-control" rows="3"></textarea></div>
          <div class="form-check"><input class="form-check-input" type="checkbox" name="actif" id="eactif" value="1"><label class="form-check-label" for="eactif">Actif</label></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
      </form>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.btn-edit').forEach(function(b){
  b.addEventListener('click',function(){
    document.getElementById('eid').value=this.dataset.id;
    document.getElementById('ecode').value=this.dataset.code;
    document.getElementById('etype').value=this.dataset.type;
    document.getElementById('evalue').value=this.dataset.value;
    document.getElementById('estart').value=this.dataset.start||'';
    document.getElementById('eend').value=this.dataset.end||'';
    document.getElementById('eusage').value=this.dataset.usage||'';
    document.getElementById('eperuser').value=this.dataset.peruser||'';
    document.getElementById('emin').value=this.dataset.min||'';
    document.getElementById('edesc').value=this.dataset.description||'';
    document.getElementById('eactif').checked=this.dataset.actif==='1';
  });
});
</script>
<?php require_once __DIR__.'/_layout_end.php'; ?>
