<?php
require_once __DIR__ . '/_guard.php';
$q = trim($_GET['q'] ?? '');
$params = [];
$sql = "SELECT id,nom,slug,description,ordre,actif FROM service_categorie";
if ($q !== '') { $sql .= " WHERE nom LIKE ? OR slug LIKE ? OR description LIKE ?"; $like = "%$q%"; $params = [$like, $like, $like]; }
$sql .= " ORDER BY ordre ASC, nom ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$page_title = 'Catégories de services';
$active = 'categories';
require_once __DIR__ . '/_layout_start.php';
?>
<?php if (isset($_GET['ok'])): ?><div class="alert alert-success">Opération effectuée.</div><?php endif; ?>
<div class="card p-3 mb-3">
  <form class="row g-2" method="get">
    <div class="col-12 col-md-6">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Rechercher">
      </div>
    </div>
    <div class="col-12 col-md-auto ms-auto text-end">
      <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal">Ajouter</button>
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
          <th style="width:180px">Slug</th>
          <th style="width:100px">Ordre</th>
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
          <td><?php echo htmlspecialchars($r['slug']); ?></td>
          <td><?php echo (int)$r['ordre']; ?></td>
          <td><?php echo (int)$r['actif']===1?'Oui':'Non'; ?></td>
          <td class="text-truncate" style="max-width:420px"><?php echo htmlspecialchars($r['description']??''); ?></td>
          <td class="d-flex gap-2">
            <button type="button" class="btn btn-outline-light btn-sm btn-edit"
              data-id="<?php echo (int)$r['id']; ?>"
              data-nom="<?php echo htmlspecialchars($r['nom']); ?>"
              data-slug="<?php echo htmlspecialchars($r['slug']); ?>"
              data-description="<?php echo htmlspecialchars($r['description']??''); ?>"
              data-ordre="<?php echo (int)$r['ordre']; ?>"
              data-actif="<?php echo (int)$r['actif']; ?>"
              data-bs-toggle="modal" data-bs-target="#editModal">Modifier</button>
            <form action="/admin/categories_actions.php" method="post" onsubmit="return confirm('Supprimer cette catégorie ?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
              <button class="btn btn-outline-danger btn-sm">Supprimer</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (count($rows)===0): ?>
        <tr><td colspan="7" class="text-center text-secondary py-4">Aucune catégorie</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/admin/categories_actions.php" method="post">
        <input type="hidden" name="action" value="create">
        <div class="modal-header">
          <h5 class="modal-title">Ajouter une catégorie</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Ordre</label><input type="number" name="ordre" class="form-control" value="0"></div>
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
      <form action="/admin/categories_actions.php" method="post">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="eid">
        <div class="modal-header">
          <h5 class="modal-title">Modifier la catégorie</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nom</label><input type="text" name="nom" id="enom" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Slug</label><input type="text" name="slug" id="eslug" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Ordre</label><input type="number" name="ordre" id="eordre" class="form-control" value="0"></div>
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
    document.getElementById('eslug').value=this.dataset.slug;
    document.getElementById('eordre').value=this.dataset.ordre;
    document.getElementById('edesc').value=this.dataset.description;
    document.getElementById('eactif').checked=this.dataset.actif==='1';
  });
});
</script>
<?php require_once __DIR__ . '/_layout_end.php'; ?>
