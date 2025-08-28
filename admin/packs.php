<?php
require_once __DIR__.'/_guard.php';
$q=trim($_GET['q']??'');
$params=[];
$sql="SELECT p.id,p.nom,p.description,p.duree_jours,p.prix_total,(SELECT COUNT(*) FROM pack_etape pe WHERE pe.pack_id=p.id) AS nb_etapes FROM pack p";
if($q!==''){ $sql.=" WHERE p.nom LIKE ? OR p.description LIKE ?"; $like="%$q%"; $params=[$like,$like]; }
$sql.=" ORDER BY p.nom ASC";
$stmt=$pdo->prepare($sql); $stmt->execute($params); $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
$page_title='Packs';
$active='packs';
require_once __DIR__.'/_layout_start.php';
?>
<?php if(isset($_GET['ok'])): ?><div class="alert alert-success">Opération effectuée.</div><?php endif; ?>
<div class="card p-3 mb-3">
<form class="row g-2 align-items-center" method="get">
<div class="col-12 col-md-7">
<div class="input-group">
<span class="input-group-text"><i class="bi bi-search"></i></span>
<input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Rechercher par nom ou description">
</div>
</div>
<div class="col-6 col-md-auto"><button class="btn btn-primary">Rechercher</button></div>
<div class="col-6 col-md-auto ms-auto text-end"><button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal"><i class="bi bi-plus-lg me-1"></i>Ajouter</button></div>
</form>
</div>
<div class="card p-0">
<div class="table-responsive">
<table class="table table-dark table-hover align-middle mb-0">
<thead><tr>
<th style="width:70px">ID</th>
<th>Pack</th>
<th style="width:120px">Durée</th>
<th style="width:120px">Étapes</th>
<th style="width:140px">Prix (€)</th>
<th style="width:260px">Actions</th>
</tr></thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
<td><?php echo (int)$r['id']; ?></td>
<td>
<div class="d-flex align-items-center gap-2">
<img class="cover" src="<?php
$ph=$pdo->prepare("SELECT chemin FROM pack_photo WHERE pack_id=? ORDER BY is_cover DESC, sort_order ASC, id ASC LIMIT 1");
$ph->execute([$r['id']]); $img=$ph->fetchColumn();
echo $img?('/uploads/packs/'.$img):'https://placehold.co/56x56?text=%20';
?>">
<div>
<div class="fw-semibold"><?php echo htmlspecialchars($r['nom']); ?></div>
<div class="text-secondary small text-truncate" style="max-width:320px"><?php echo htmlspecialchars(mb_strimwidth($r['description']??'',0,90,'…','UTF-8')); ?></div>
</div>
</div>
</td>
<td><?php echo (int)$r['duree_jours']; ?> j</td>
<td><?php echo (int)$r['nb_etapes']; ?></td>
<td><?php echo $r['prix_total']!==null?number_format((float)$r['prix_total'],2,',',' '):'—'; ?></td>
<td class="d-flex flex-wrap gap-2">
<button type="button" class="btn btn-outline-light btn-sm btn-edit"
 data-id="<?php echo (int)$r['id']; ?>"
 data-nom="<?php echo htmlspecialchars($r['nom']); ?>"
 data-description="<?php echo htmlspecialchars($r['description']??''); ?>"
 data-duree="<?php echo (int)$r['duree_jours']; ?>"
 data-prix="<?php echo htmlspecialchars($r['prix_total']); ?>"
 data-bs-toggle="modal" data-bs-target="#editModal"><i class="bi bi-pencil-square me-1"></i>Modifier</button>
<a class="btn btn-outline-primary btn-sm" href="/admin/pack_photos.php?id=<?php echo (int)$r['id']; ?>"><i class="bi bi-images me-1"></i>Photos</a>
<a class="btn btn-outline-info btn-sm" href="/admin/pack_steps.php?id=<?php echo (int)$r['id']; ?>"><i class="bi bi-geo me-1"></i>Étapes</a>
<form action="/admin/packs_actions.php" method="post" onsubmit="return confirm('Supprimer ce pack ?');">
<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php if(count($rows)===0): ?><tr><td colspan="6" class="text-center text-secondary py-4">Aucun pack</td></tr><?php endif; ?>
</tbody></table>
</div></div>

<div class="modal fade" id="createModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<form action="/admin/packs_actions.php" method="post">
<input type="hidden" name="action" value="create">
<div class="modal-header"><h5 class="modal-title">Ajouter un pack</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<div class="mb-3"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Durée (jours)</label><input type="number" name="duree_jours" class="form-control" min="1"></div>
<div class="mb-3"><label class="form-label">Prix total (€)</label><input type="number" step="0.01" name="prix_total" class="form-control"></div>
<div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4"></textarea></div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
</form>
</div></div></div>

<div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<form action="/admin/packs_actions.php" method="post">
<input type="hidden" name="action" value="update"><input type="hidden" name="id" id="eid">
<div class="modal-header"><h5 class="modal-title">Modifier le pack</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<div class="mb-3"><label class="form-label">Nom</label><input type="text" name="nom" id="enom" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Durée (jours)</label><input type="number" name="duree_jours" id="eduree" class="form-control" min="1"></div>
<div class="mb-3"><label class="form-label">Prix total (€)</label><input type="number" step="0.01" name="prix_total" id="eprix" class="form-control"></div>
<div class="mb-3"><label class="form-label">Description</label><textarea name="description" id="edesc" class="form-control" rows="4"></textarea></div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
</form>
</div></div></div>

<script>
document.querySelectorAll('.btn-edit').forEach(function(b){
  b.addEventListener('click',function(){
    document.getElementById('eid').value=this.dataset.id;
    document.getElementById('enom').value=this.dataset.nom;
    document.getElementById('edesc').value=this.dataset.description;
    document.getElementById('eduree').value=this.dataset.duree;
    document.getElementById('eprix').value=this.dataset.prix;
  });
});
</script>
<?php require_once __DIR__.'/_layout_end.php'; ?>
