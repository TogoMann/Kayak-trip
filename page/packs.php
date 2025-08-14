<?php
session_start();
require_once('../includes/header.php');
require_once('../includes/db.php');

$query = $pdo->query("SELECT * FROM pack");
$packs = $query->fetchAll(PDO::FETCH_ASSOC);

$images = [
    1 => 'decouverte.jpg',
    2 => 'aventure.jpg',
    3 => 'weekend.jpg',
    4 => 'chateaux.jpg'
];
?>

<div class="container py-5">
    <h2 class="mb-4 text-center">Nos Packs</h2>
    <div class="row">
        <?php foreach ($packs as $pack): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php
                        $file = isset($images[$pack['id']]) ? $images[$pack['id']] : 'placeholder.jpg';
                        $img = '../img/packs/' . $file;
                    ?>
                    <img src="<?= $img ?>" class="card-img-top" alt="<?= htmlspecialchars($pack['nom']) ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($pack['nom']) ?></h5>
                        <p class="card-text text-muted">Durée : <?= htmlspecialchars($pack['duree_jours']) ?> jours</p>
                        <p class="card-text fw-bold"><?= number_format($pack['prix_total'], 2, ',', ' ') ?> €</p>
                        <p class="card-text"><?= htmlspecialchars(mb_strimwidth($pack['description'], 0, 100, '...')) ?></p>
                        <a href="pack_detail.php?id=<?= $pack['id'] ?>" class="btn btn-primary">Voir plus</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
