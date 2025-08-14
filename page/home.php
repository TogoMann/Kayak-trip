<?php include('../includes/header.php'); ?>



<header class="text-center position-relative">
    <img src="../img/hero.jpg" alt="Kayak sur la Loire" class="w-100" style="height:80vh;object-fit:cover;pointer-events:none;">
    
    <div class="position-absolute top-50 start-50 translate-middle p-4 rounded" style="background-color: rgba(0, 0, 0, 0.4);">
        <h1 class="display-4 fw-bold text-white" style="text-shadow: 2px 2px 4px black;">
            Réservez votre aventure<br>en kayak sur la Loire
        </h1>
        <div class="mt-4">
            <a href="composer.php" class="btn btn-primary btn-lg me-2">Composer mon parcours</a>
            <a href="packs.php" class="btn btn-light btn-lg">Voir les packs</a>
        </div>
    </div>
</header>

<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="mb-3">
                    <i class="bi bi-geo-alt fs-1"></i>
                </div>
                <h4>Étapes</h4>
                <p>Choisissez vos points d’arrêt le long du fleuve et composez votre itinéraire personnalisé.</p>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <i class="bi bi-map fs-1"></i>
                </div>
                <h4>Packs</h4>
                <p>Découvrez nos itinéraires préconstruits incluant étapes et hébergements.</p>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <i class="bi bi-basket fs-1"></i>
                </div>
                <h4>Services</h4>
                <p>Ajoutez des services complémentaires pour une expérience sur mesure.</p>
            </div>
        </div>
    </div>
</section>

<?php include('../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
