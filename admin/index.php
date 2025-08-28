<?php
require_once __DIR__ . '/_guard.php';
$countUsers = 0;
$countReservations = 0;
$countPacks = 0;
try { $row = $pdo->query('SELECT COUNT(*) c FROM utilisateur')->fetch(PDO::FETCH_ASSOC); $countUsers = (int)$row['c']; } catch (Throwable $e) {}
try { $row = $pdo->query('SELECT COUNT(*) c FROM reservation')->fetch(PDO::FETCH_ASSOC); $countReservations = (int)$row['c']; } catch (Throwable $e) {}
try { $row = $pdo->query('SELECT COUNT(*) c FROM pack')->fetch(PDO::FETCH_ASSOC); $countPacks = (int)$row['c']; } catch (Throwable $e) {}
$page_title = 'Tableau de bord';
$active = 'dashboard';
require_once __DIR__ . '/_layout_start.php';
?>
<style>
.kpi{display:flex;align-items:center;gap:12px}.kpi i{font-size:28px}
</style>
<div class="row g-3">
  <div class="col-12 col-md-4">
    <div class="card p-3">
      <div class="kpi">
        <i class="bi bi-people text-white-50"></i>
        <div>
          <div class="title">Utilisateurs</div>
          <div class="h3 mb-0"><?php echo $countUsers; ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card p-3">
      <div class="kpi">
        <i class="bi bi-calendar-check text-white-50"></i>
        <div>
          <div class="title">Réservations</div>
          <div class="h3 mb-0"><?php echo $countReservations > 0 ? $countReservations : '—'; ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card p-3">
      <div class="kpi">
        <i class="bi bi-map text-white-50"></i>
        <div>
          <div class="title">Parcours</div>
          <div class="h3 mb-0"><?php echo $countPacks > 0 ? $countPacks : '—'; ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mt-1">
  <div class="col-12 col-lg-8">
    <div class="card p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="title">Activité récente</div>
        <a href="/admin/reservations.php" class="btn btn-outline-light btn-sm">Voir tout</a>
      </div>
      <div class="text-secondary">Aucune donnée à afficher</div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card p-3">
      <div class="title mb-2">Actions rapides</div>
      <div class="d-grid gap-2">
        <a href="/admin/reservations.php" class="btn btn-primary">Gérer les réservations</a>
        <a href="/admin/points.php" class="btn btn-outline-light">Gérer les points d’arrêt</a>
        <a href="/admin/hebergements.php" class="btn btn-outline-light">Gérer les hébergements</a>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/_layout_end.php'; ?>
