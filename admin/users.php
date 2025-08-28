<?php
require_once __DIR__ . '/_guard.php';
$q = trim($_GET['q'] ?? '');
$params = [];
$w = [];
$sql1 = "SELECT id,email,role,COALESCE(nom_affichage,TRIM(CONCAT(prenom,' ',nom))) AS affichage,created_at FROM utilisateur";
$sql2 = "SELECT id,email,role,COALESCE(nom_affichage,TRIM(CONCAT(prenom,' ',nom))) AS affichage,NULL AS created_at FROM utilisateur";
if ($q !== '') { $w[] = "(email LIKE ? OR nom_affichage LIKE ? OR prenom LIKE ? OR nom LIKE ? OR id=?)"; $like = "%$q%"; $params = [$like,$like,$like,$like,ctype_digit($q)?(int)$q:-1]; }
$sql = $sql1 . ($w ? " WHERE ".implode(" AND ",$w) : "") . " ORDER BY COALESCE(created_at,id) DESC";
try { $stmt = $pdo->prepare($sql); $stmt->execute($params); }
catch (Throwable $e) { $sqlF = $sql2 . ($w ? " WHERE ".implode(" AND ",$w) : "") . " ORDER BY id DESC"; $stmt = $pdo->prepare($sqlF); $stmt->execute($params); }
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$page_title = 'Utilisateurs';
$active = 'users';
require_once __DIR__ . '/_layout_start.php';
?>
<?php if (isset($_GET['ok'])): ?><div class="alert alert-success">Opération effectuée.</div><?php endif; ?>
<?php if (isset($_GET['err']) && $_GET['err']==='self'): ?><div class="alert alert-warning">Impossible de supprimer votre propre compte.</div><?php endif; ?>
<div class="card p-3 mb-3">
  <form class="row g-2 align-items-center" method="get">
    <div class="col-12 col-md-7">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Rechercher par e-mail, nom, ID">
      </div>
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
          <th style="width:80px">ID</th>
          <th>Nom d’affichage</th>
          <th style="width:280px">E-mail</th>
          <th style="width:140px">Rôle</th>
          <th style="width:180px">Créé le</th>
          <th style="width:240px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): ?>
        <tr>
          <td>#<?php echo (int)$r['id']; ?></td>
          <td><?php echo htmlspecialchars($r['affichage'] ?: '—'); ?></td>
          <td><?php echo htmlspecialchars($r['email']); ?></td>
          <td><?php echo htmlspecialchars($r['role']); ?></td>
          <td><?php echo $r['created_at'] ? date('d/m/Y H:i', strtotime($r['created_at'])) : '—'; ?></td>
          <td class="d-flex gap-2">
            <button type="button" class="btn btn-outline-light btn-sm btn-edit"
              data-id="<?php echo (int)$r['id']; ?>"
              data-email="<?php echo htmlspecialchars($r['email']); ?>"
              data-affichage="<?php echo htmlspecialchars($r['affichage'] ?: ''); ?>"
              data-role="<?php echo htmlspecialchars($r['role']); ?>"
              data-bs-toggle="modal" data-bs-target="#editModal">Modifier</button>
            <form action="/admin/users_actions.php" method="post" onsubmit="return confirm('Supprimer cet utilisateur ?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
              <button class="btn btn-outline-danger btn-sm">Supprimer</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(count($rows)===0): ?>
        <tr><td colspan="6" class="text-center text-secondary py-4">Aucun utilisateur</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/admin/users_actions.php" method="post">
        <input type="hidden" name="action" value="create">
        <div class="modal-header"><h5 class="modal-title">Ajouter un utilisateur</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nom d’affichage</label><input type="text" name="nom_affichage" class="form-control"></div>
          <div class="mb-3"><label class="form-label">E-mail</label><input type="email" name="email" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Mot de passe</label><input type="password" name="password" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Rôle</label>
            <select name="role" class="form-select">
              <option value="user">user</option>
              <option value="admin">admin</option>
            </select>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/admin/users_actions.php" method="post">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="eid">
        <div class="modal-header"><h5 class="modal-title">Modifier l’utilisateur</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nom d’affichage</label><input type="text" name="nom_affichage" id="eaff" class="form-control"></div>
          <div class="mb-3"><label class="form-label">E-mail</label><input type="email" name="email" id="eemail" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Nouveau mot de passe</label><input type="password" name="password" class="form-control" placeholder="Laisser vide pour ne pas changer"></div>
          <div class="mb-3"><label class="form-label">Rôle</label>
            <select name="role" id="erole" class="form-select">
              <option value="user">user</option>
              <option value="admin">admin</option>
            </select>
          </div>
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
    document.getElementById('eemail').value=this.dataset.email;
    document.getElementById('eaff').value=this.dataset.affichage;
    document.getElementById('erole').value=this.dataset.role;
  });
});
</script>
<?php require_once __DIR__ . '/_layout_end.php'; ?>
