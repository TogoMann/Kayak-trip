<?php
session_start();
require_once('../includes/header.php');
require_once('../includes/db.php');

$query = $pdo->query("SELECT * FROM point_arret");
$points = $query->fetchAll(PDO::FETCH_ASSOC);

function point_image_path($nom) {
    $baseDir = '../img/points/';
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $nom);
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9]+/', '', $slug);
    foreach (['jpg','png','webp'] as $ext) {
        $path = $baseDir . $slug . '.' . $ext;
        if (file_exists($path)) return $path;
    }
    return $baseDir . 'placeholder.jpg';
}
?>

<div class="container py-5">
    <h2 class="mb-4 text-center">Points d'arrêt</h2>

    <div class="row mb-4">
        <div class="col-md-6 offset-md-3">
            <input type="text" id="search" class="form-control" placeholder="Rechercher un point d'arrêt...">
        </div>
    </div>

    <div class="row" id="points-container">
        <?php foreach ($points as $point): ?>
            <?php $img = point_image_path($point['nom']); ?>
            <div class="col-md-4 mb-4 point-card">
                <div class="card h-100 shadow-sm">
                    <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="<?= htmlspecialchars($point['nom']) ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($point['nom']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars(mb_strimwidth($point['description'] ?? '', 0, 100, '...')) ?></p>
                        <a href="point_detail.php?id=<?= $point['id'] ?>" class="btn btn-primary">Voir plus</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>

<script>
document.getElementById('search').addEventListener('input', function() {
    let v = this.value.toLowerCase();
    document.querySelectorAll('.point-card').forEach(card => {
        let t = card.querySelector('.card-title').textContent.toLowerCase();
        card.style.display = t.includes(v) ? '' : 'none';
    });
});
</script>
