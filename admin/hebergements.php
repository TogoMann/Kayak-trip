<?php
require_once __DIR__ . '/_guard.php';
$point = isset($_GET['point']) ? (int)$_GET['point'] : 0;
$q = $_GET['q'] ?? '';
$points = $pdo->query("SELECT id,nom FROM point_arret ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$params = [];
$sql = "SELECT h.id,h.nom,h.description,h.capacite,h.prix_base,h.actif,h.point_arret_id,p.nom AS point_nom,(SELECT COUNT(*) FROM reservation_etape re WHERE re.hebergement_id=h.id) AS nb_resa FROM hebergement h INNER JOIN point_arret p ON p.id=h.point_arret_id";
$where = [];
if ($point > 0) { $where[] = "h.point_arret_id=?"; $params[] = $point; }
if ($q !== '') { $where[] = "(h.nom LIKE ? OR h.description LIKE ?)"; $like = "%$q%"; $params[] = $like; $params[] = $like; }
if ($where) { $sql .= " WHERE " . implode(" AND ", $where); }
$sql .= " ORDER BY p.nom ASC,h.nom ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$page_title = 'Hébergements';
$active = 'hebergements';
require_once __DIR__ . '/_layout_start.php';
?>
<?php if (isset($_GET['ok'])): ?><div class="alert alert-success">Opération effectuée.</div><?php endif; ?>
<div class="card p-3 mb-3">
  <form class="row g-2 align-items-center" method="get">
    <div class="col-12 col-md-4">
      <select class="form-select" name="point" onchange="this.form.submit()">
        <option value="0">Tous les points d’arrêt</option>
        <?php foreach ($points as $p): ?>
          <option value="<?php echo (int)$p['id']; ?>" <?php echo $point === (int)$p['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['nom']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12 col-md-5">
      <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Rechercher par nom ou description">
    </div>
    <div class="col-6 col-md-auto">
      <button class="btn btn-primary"><i class="bi bi-search me-1"></i>Rechercher</button>
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
          <th>Hébergement</th>
          <th style="width:160px">Point d’arrêt</th>
          <th style="width:110px">Capacité</th>
          <th style="width:120px">Prix</th>
          <th style="width:110px">Réservations</th>
          <th style="width:120px">État</th>
          <th style="width:240px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <img class="cover" src="<?php
              $ph = $pdo->prepare("SELECT chemin FROM hebergement_photo WHERE hebergement_id=? ORDER BY is_cover DESC, sort_order ASC, id ASC LIMIT 1");
              $ph->execute([$r['id']]);
              $img = $ph->fetchColumn();
              echo $img ? '/uploads/hebergements/' . $img : 'https://placehold.co/56x56?text=%20';
              ?>">
              <div>
                <div class="fw-semibold"><?php echo htmlspecialchars($r['nom']); ?></div>
                <div class="text-secondary small text-truncate" style="max-width:280px"><?php echo htmlspecialchars(mb_strimwidth($r['description'] ?? '', 0, 80, '…', 'UTF-8')); ?></div>
              </div>
            </div>
          </td>
          <td><?php echo htmlspecialchars($r['point_nom']); ?></td>
          <td><?php echo (int)$r['capacite']; ?></td>
          <td><?php echo $r['prix_base']!==null?number_format((float)$r['prix_base'],2,',',' ').' €':'—'; ?></td>
          <td><span class="badge text-bg-secondary"><?php echo (int)$r['nb_resa']; ?></span></td>
          <td><?php echo (int)$r['actif']===1?'Actif':'Inactif'; ?></td>
          <td class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-light btn-sm btn-edit"
              data-id="<?php echo (int)$r['id']; ?>"
              data-nom="<?php echo htmlspecialchars($r['nom']); ?>"
              data-description="<?php echo htmlspecialchars($r['description'] ?? ''); ?>"
              data-capacite="<?php echo (int)$r['capacite']; ?>"
              data-prix="<?php echo htmlspecialchars($r['prix_base']); ?>"
              data-point="<?php echo (int)$r['point_arret_id']; ?>"
              data-actif="<?php echo (int)$r['actif']; ?>"
              data-bs-toggle="modal" data-bs-target="#editModal"><i class="bi bi-pencil-square me-1"></i>Modifier</button>
            <a class="btn btn-outline-primary btn-sm" href="/admin/hebergement_photos.php?id=<?php echo (int)$r['id']; ?>"><i class="bi bi-images me-1"></i>Photos</a>
            <a class="btn btn-outline-info btn-sm" href="/admin/hebergement_reservations.php?id=<?php echo (int)$r['id']; ?>"><i class="bi bi-people me-1"></i>Réservations</a>
            <form action="/admin/hebergements_actions.php" method="post" onsubmit="return confirm('Supprimer cet hébergement ?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
              <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (count($rows)===0): ?>
        <tr><td colspan="8" class="text-center text-secondary py-4">Aucun hébergement</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/admin/hebergements_actions.php" method="post">
        <input type="hidden" name="action" value="create">
        <div class="modal-header">
          <h5 class="modal-title">Ajouter un hébergement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Point d’arrêt</label>
            <select name="point_arret_id" class="form-select" required>
              <?php foreach ($points as $p): ?>
              <option value="<?php echo (int)$p['id']; ?>"><?php echo htmlspecialchars($p['nom']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3"><label class="form-label">Capacité</label><input type="number" name="capacite" class="form-control" min="1" required></div>
          <div class="mb-3"><label class="form-label">Prix de base (€)</label><input type="number" step="0.01" name="prix_base" class="form-control"></div>
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
      <form action="/admin/hebergements_actions.php" method="post">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="eid">
        <div class="modal-header">
          <h5 class="modal-title">Modifier l’hébergement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nom</label><input type="text" name="nom" id="enom" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Point d’arrêt</label>
            <select name="point_arret_id" id="epoint" class="form-select" required>
              <?php foreach ($points as $p): ?>
              <option value="<?php echo (int)$p['id']; ?>"><?php echo htmlspecialchars($p['nom']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3"><label class="form-label">Capacité</label><input type="number" name="capacite" id="ecapacite" class="form-control" min="1" required></div>
          <div class="mb-3"><label class="form-label">Prix de base (€)</label><input type="number" step="0.01" name="prix_base" id="eprix" class="form-control"></div>
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
    document.getElementById('edesc').value=this.dataset.description;
    document.getElementById('ecapacite').value=this.dataset.capacite;
    document.getElementById('eprix').value=this.dataset.prix;
    document.getElementById('epoint').value=this.dataset.point;
    document.getElementById('eactif').checked=this.dataset.actif==='1';
  });
});
</script>
<?php require_once __DIR__ . '/_layout_end.php'; ?>
