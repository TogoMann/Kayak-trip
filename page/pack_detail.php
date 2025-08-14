<?php
session_start();
require_once('../includes/header.php');
require_once('../includes/db.php');

if (!isset($_GET['id'])) {
    header('Location: packs.php');
    exit;
}

$id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM pack WHERE id = ?");
$stmt->execute([$id]);
$pack = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pack) {
    header('Location: packs.php');
    exit;
}

$images = [
    1 => 'decouverte.jpg',
    2 => 'aventure.jpg',
    3 => 'weekend.jpg',
    4 => 'chateaux.jpg'
];
$file = isset($images[$pack['id']]) ? $images[$pack['id']] : 'placeholder.jpg';
$img = '../img/packs/' . $file;

$etapesStmt = $pdo->prepare("
    SELECT pa.nom, pa.description, pa.id 
    FROM pack_etape pe
    JOIN point_arret pa ON pe.point_arret_id = pa.id
    WHERE pe.pack_id = ?
    ORDER BY pe.ordre ASC
");
$etapesStmt->execute([$id]);
$etapes = $etapesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <img src="<?= $img ?>" alt="<?= htmlspecialchars($pack['nom']) ?>" class="img-fluid rounded shadow">
        </div>
        <div class="col-md-6">
            <h2><?= htmlspecialchars($pack['nom']) ?></h2>
            <p class="text-muted">Durée : <?= htmlspecialchars($pack['duree_jours']) ?> jours</p>
            <p class="fw-bold fs-4"><?= number_format($pack['prix_total'], 2, ',', ' ') ?> €</p>
            <p><?= nl2br(htmlspecialchars($pack['description'])) ?></p>
            <a href="reserver_pack.php?id=<?= $pack['id'] ?>" class="btn btn-primary btn-lg mt-3">Réserver ce pack</a>
        </div>
    </div>

    <h3 class="mb-3">Itinéraire</h3>
    <div class="list-group mb-5">
        <?php foreach ($etapes as $etape): ?>
            <div class="list-group-item">
                <h5><?= htmlspecialchars($etape['nom']) ?></h5>
                <p><?= htmlspecialchars($etape['description']) ?></p>

                <?php
                $hebStmt = $pdo->prepare("SELECT * FROM hebergement WHERE point_arret_id = ?");
                $hebStmt->execute([$etape['id']]);
                $hebergements = $hebStmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php if ($hebergements): ?>
                    <div class="row">
                        <?php foreach ($hebergements as $h): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <img src="../img/hebergements/<?= htmlspecialchars($h['image']) ?>" class="card-img-top" style="height: 150px; object-fit: cover;">
                                    <div class="card-body">
                                        <h6 class="card-title"><?= htmlspecialchars($h['nom']) ?></h6>
                                        <p class="card-text"><?= htmlspecialchars($h['description']) ?></p>
                                        <p class="text-muted"><?= number_format($h['prix'], 2, ',', ' ') ?> € / nuit</p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Aucun hébergement enregistré pour cette étape.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
