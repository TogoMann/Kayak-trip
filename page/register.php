<?php include('../includes/header.php'); ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow-sm p-4" style="width: 100%; max-width: 500px;">
        <h2 class="text-center mb-4">Inscription</h2>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger text-center">
        <?php
        if ($_GET['error'] === 'mdp') {
            echo "Les mots de passe ne correspondent pas.";
        } elseif ($_GET['error'] === 'exists') {
            echo "Cette adresse e-mail est déjà utilisée.";
        } elseif ($_GET['error'] === 'missing') {
            echo "Veuillez remplir tous les champs.";
        } else {
            echo "Une erreur est survenue.";
        }
        ?>
    </div>
<?php endif; ?>


        <form action="../process/register_process.php" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Adresse e-mail</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Créer un compte</button>
        </form>

        <div class="mt-3 text-center">
            <span>Vous avez déjà un compte ?</span>
            <a href="login.php">Connectez-vous</a>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
