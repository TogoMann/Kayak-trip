<?php
require_once __DIR__ . '/_guard.php';
$cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$q = trim($_GET['q'] ?? '');
$cats = $pdo->query("SELECT id,nom FROM service_categorie WHERE actif=1 ORDER BY COALESCE(ordre,9999), nom")->fetchAll(PDO::FETCH_ASSOC);
$params = [];
$sql = "SELECT s.id,s.nom,s.description,s.prix,s.actif,s.categorie_id,sc.nom AS categorie FROM service s LEFT JOIN service_categorie sc ON sc.id=s.categorie_id";
$w = [];
if ($cat > 0) { $w[] = "s.categorie_id=?"; $params[] = $cat; }
if ($q !== '') { $w[] = "(s.nom LIKE ? OR s.description LIKE ?)"; $like="%$q%"; $params[]=$like; $params[]=$like; }
if ($w) { $sql .= " WHERE ".implode(" AND ",$w); }
$sql .= " ORDER BY s.actif DESC, COALESCE(sc.ordre,9999) ASC, sc.nom ASC, s.nom ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$page_title = 'Services';
$active = 'options';
require_once __DIR__ . '/_layout_start.php';
?>
<?php if (isset($_GET['ok'])): ?><div class="alert alert-success">Opération effectuée.</div><?php endif; ?>

<div class="card p-3 mb-3">
  <form class="row g-2 align-items-center" method="get">
    <div class="col-12 col-md-4">
      <select class="form-select" name="cat" onchange="this.form.submit()">
        <option value="0">Toutes les catégories</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?php echo (int)$c['id']; ?>" <?php echo $cat===(int)$c['id']?'selected':''; ?>><?php echo htmlspecialchars($c['nom']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12 col-md-5">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Rechercher par nom ou description">
      </div>
    </div>
    <div class="col-6 col-md-auto">
      <button class="btn btn-primary">Rechercher</button>
    </div>
    <div class="col-6 col-md-auto ms-auto text-end">
      <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal"><i class="bi bi-plus-lg me-1"></i>Ajouter</button>
    </div>
  </form>
</div>

<div class="card p-0">
  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle mb-0">
      <thead>
        <tr>
          <th style="width:70px">ID</th>
          <th>Nom</th>
          <th style="width:200px">Catégorie</th>
          <th style="width:140px">Prix (€)</th>
          <th style="width:110px">Actif</th>
          <th>Description</th>
          <th style="width:220px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td><?php echo htmlspecialchars($r['nom']); ?></td>
          <td><?php echo htmlspecialchars($r['categorie'] ?? '—'); ?></td>
          <td><?php echo number_format((float)$r['prix'],2,',',' '); ?></td>
          <td><?php echo (int)$r['actif']===1?'Oui':'Non'; ?></td>
          <td class="text-truncate" style="max-width:420px"><?php echo htmlspecialchars($r['description'] ?? ''); ?></td>
          <td class="d-flex gap-2">
            <button type="button" class="btn btn-outline-light btn-sm btn-edit"
              data-id="<?php echo (int)$r['id']; ?>"
              data-nom="<?php echo htmlspecialchars($r['nom']); ?>"
              data-description="<?php echo htmlspecialchars($r['description'] ?? ''); ?>"
              data-prix="<?php echo htmlspecialchars($r['prix']); ?>"
              data-actif="<?php echo (int)$r['actif']; ?>"
              data-categorie="<?php echo (int)($r['categorie_id'] ?? 0); ?>"
              data-bs-toggle="modal" data-bs-target="#editModal"><i class="bi bi-pencil-square me-1"></i>Modifier</button>
            <form action="/admin/options_actions.php" method="post" onsubmit="return confirm('Supprimer ce service ?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
              <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (count($rows)===0): ?>
        <tr><td colspan="7" class="text-center text-secondary py-4">Aucun service</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/admin/options_actions.php" method="post">
        <input type="hidden" name="action" value="create">
        <div class="modal-header">
          <h5 class="modal-title">Ajouter un service</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Catégorie</label>
            <select name="categorie_id" class="form-select">
              <option value="">Sans catégorie</option>
              <?php foreach ($cats as $c): ?>
                <option value="<?php echo (int)$c['id']; ?>"><?php echo htmlspecialchars($c['nom']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3"><label class="form-label">Prix (€)</label><input type="number" step="0.01" name="prix" class="form-control" value="0"></div>
          <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4"></textarea></div>
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
      <form action="/admin/options_actions.php" method="post">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="eid">
        <div class="modal-header">
          <h5 class="modal-title">Modifier le service</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nom</label><input type="text" name="nom" id="enom" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Catégorie</label>
            <select name="categorie_id" id="ecat" class="form-select">
              <option value="">Sans catégorie</option>
              <?php foreach ($cats as $c): ?>
                <option value="<?php echo (int)$c['id']; ?>"><?php echo htmlspecialchars($c['nom']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3"><label class="form-label">Prix (€)</label><input type="number" step="0.01" name="prix" id="eprix" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Description</label><textarea name="description" id="edesc" class="form-control" rows="4"></textarea></div>
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
    document.getElementById('enom').value=this.dataset.nom;
    document.getElementById('eprix').value=this.dataset.prix;
    document.getElementById('edesc').value=this.dataset.description;
    document.getElementById('eactif').checked=this.dataset.actif==='1';
    document.getElementById('ecat').value=this.dataset.categorie||'';
  });
});
</script>
<?php require_once __DIR__ . '/_layout_end.php'; ?>
