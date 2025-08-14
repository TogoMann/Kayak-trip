<?php
session_start();
require_once('../includes/header.php');
require_once('../includes/db.php');

if (!isset($_GET['id'])) {
    header('Location: points.php');
    exit;
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM point_arret WHERE id = ?");
$stmt->execute([$id]);
$point = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$point) {
    header('Location: points.php');
    exit;
}

function slugify($s) {
    $s = iconv('UTF-8','ASCII//TRANSLIT',$s);
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/','_', $s);
    $s = preg_replace('/_+/', '_', $s);
    return trim($s,'_');
}

$basePoint = '../img/points/' . slugify($point['nom']);
$imgPoint = '../img/points/placeholder.jpg';
foreach (['jpg','jpeg','png','webp'] as $ext) {
    $p = $basePoint . '.' . $ext;
    if (file_exists($p)) { $imgPoint = $p; break; }
}

$hebStmt = $pdo->prepare("SELECT * FROM hebergement WHERE point_arret_id = ? AND actif = 1");
$hebStmt->execute([$id]);
$hebergements = $hebStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <img src="<?= $imgPoint ?>" alt="<?= htmlspecialchars($point['nom']) ?>" class="img-fluid rounded shadow">
        </div>
        <div class="col-md-6">
            <h2><?= htmlspecialchars($point['nom']) ?></h2>
            <p><?= nl2br(htmlspecialchars($point['description'] ?? '')) ?></p>
            <a href="composer.php?add_point=<?= $point['id'] ?>" class="btn btn-primary mt-2">Ajouter cette étape à mon itinéraire</a>
        </div>
    </div>

    <h3 class="mb-3">Hébergements disponibles</h3>

    <?php if ($hebergements): ?>
        <div class="row">
            <?php foreach ($hebergements as $h): ?>
                <?php
                    $baseHeb = '../img/hebergements/' . slugify($h['nom']);
                    $imgHeb = '../img/hebergements/placeholder.jpg';
                    foreach (['jpg','jpeg','png','webp'] as $ext) {
                        $p = $baseHeb . '.' . $ext;
                        if (file_exists($p)) { $imgHeb = $p; break; }
                    }
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= $imgHeb ?>" class="card-img-top" alt="<?= htmlspecialchars($h['nom']) ?>" style="height: 180px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($h['nom']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars(mb_strimwidth($h['description'] ?? '', 0, 120, '...')) ?></p>
                            <p class="text-muted mb-1">Capacité: <?= (int)$h['capacite'] ?> pers.</p>
                            <p class="fw-bold mb-0"><?= number_format((float)$h['prix_base'], 2, ',', ' ') ?> € / nuit</p>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="composer.php?add_point=<?= $point['id'] ?>" class="btn btn-outline-primary w-100">Choisir pour mon itinéraire</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">Aucun hébergement enregistré pour ce point d'arrêt.</p>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
