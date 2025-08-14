<?php
session_start();
require_once('../includes/db.php');
require_once('../includes/header.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT nom, prenom, email FROM utilisateur WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$nom_complet = $user['prenom'] . ' ' . $user['nom'];
$email = $user['email'];
$username = strtolower($user['prenom']) . $user_id;
?>

<div class="container-fluid">
    <div class="row" style="min-height: 100vh;">
        <div class="col-md-3 bg-dark text-white p-4">
            <h5 class="fw-bold mb-4"><?= htmlspecialchars($nom_complet) ?></h5>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="profil.php" class="nav-link text-white bg-primary rounded px-3">Profil</a></li>
                <li class="nav-item mb-2"><a href="reservations.php" class="nav-link text-white">Mes réservations</a></li>
                <li class="nav-item mb-2"><a href="messages.php" class="nav-link text-white">Messagerie</a></li>
                <li class="nav-item mt-3"><a href="../process/logout.php" class="nav-link text-white"><i class="bi bi-power"></i> Se déconnecter</a></li>
            </ul>
        </div>

        <div class="col-md-9 bg-black text-white p-5">
            <h3 class="mb-4">Mes informations personnelles</h3>
            <div class="row mb-4">
                <div class="col-md-4">Nom complet</div>
                <div class="col-md-8"><?= htmlspecialchars($nom_complet) ?></div>
                <div class="col-md-4 mt-2">Adresse e-mail</div>
                <div class="col-md-8 mt-2"><?= htmlspecialchars($email) ?></div>
                <div class="col-md-4 mt-2">Nom d'utilisateur</div>
                <div class="col-md-8 mt-2"><?= htmlspecialchars($username) ?></div>
            </div>
            <a href="#" class="btn btn-outline-danger">Supprimer mon compte</a>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
