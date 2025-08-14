<?php
session_start();
require_once('../includes/db.php');
require_once('../includes/header.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM reservation WHERE utilisateur_id = ? ORDER BY date_reservation DESC");
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row" style="min-height: 100vh;">
        <div class="col-md-3 bg-dark text-white p-4">
            <h5 class="fw-bold mb-4"><?= htmlspecialchars($_SESSION['user_nom']) ?></h5>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="profil.php" class="nav-link text-white">Profil</a></li>
                <li class="nav-item mb-2"><a href="reservations.php" class="nav-link text-white bg-primary rounded px-3">Mes réservations</a></li>
                <li class="nav-item mb-2"><a href="messages.php" class="nav-link text-white">Messagerie</a></li>
                <li class="nav-item mt-3"><a href="../process/logout.php" class="nav-link text-white"><i class="bi bi-power"></i> Se déconnecter</a></li>
            </ul>
        </div>

        <div class="col-md-9 bg-black text-white p-5">
            <h3 class="mb-4">Mes réservations</h3>

            <?php if (count($reservations) > 0): ?>
                <table class="table table-dark table-bordered text-white">
                    <thead>
                        <tr>
                            <th># Réservation</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Début</th>
                            <th>Fin</th>
                            <th>Personnes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $r): ?>
                            <tr>
                                <td>#<?= $r['id'] ?></td>
                                <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                                <td><?= ucfirst($r['type']) ?></td>
                                <td><?= $r['date_debut'] ?></td>
                                <td><?= $r['date_fin'] ?></td>
                                <td><?= $r['nb_personnes'] ?></td>
                                <td>
                                    <a href="itineraire.php?id=<?= $r['id'] ?>" class="btn btn-outline-light btn-sm">Voir l’itinéraire</a>
                                    <a href="facture.php?id=<?= $r['id'] ?>" class="btn btn-outline-info btn-sm">Voir la facture</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aucune réservation trouvée.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
