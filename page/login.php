<?php include('../includes/header.php'); ?>


<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow-sm p-4" style="width: 100%; max-width: 400px;">
        <h2 class="text-center mb-4">Connexion</h2>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger text-center">
        <?php
        if ($_GET['error'] === 'login') {
            echo "Adresse e-mail ou mot de passe incorrect.";
        } elseif ($_GET['error'] === 'missing') {
            echo "Veuillez remplir tous les champs.";
        } else {
            echo "Une erreur est survenue.";
        }
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
    <div class="alert alert-success text-center">
        Compte créé avec succès. Vous pouvez maintenant vous connecter.
    </div>
<?php endif; ?>
        <form action="../process/login_process.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Adresse e-mail</label>
                <input type="email" class="form-control" name="email" id="email" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" name="password" id="password" required>
                <div class="text-end mt-1">
                    <a href="#" class="text-decoration-none small">Mot de passe oublié ?</a>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>

        <div class="mt-3 text-center">
            <span>Vous n’avez pas de compte ?</span>
            <a href="register.php">Inscrivez-vous</a>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
