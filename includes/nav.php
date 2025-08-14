<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<style>.navbar{z-index:2000;position:sticky;top:0}</style>
<nav class="navbar navbar-expand-lg bg-light shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="/page/home.php">KayakLoire</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="mainNav">
      <ul class="navbar-nav mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/page/points.php">Points d’arrêt</a></li>
        <li class="nav-item"><a class="nav-link" href="/page/packs.php">Packs</a></li>
        <li class="nav-item"><a class="nav-link" href="/page/services.php">Services</a></li>
        <li class="nav-item"><a class="btn btn-primary ms-2" href="/page/composer.php">Créer mon itinéraire</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item ms-2">
            <a class="nav-link" href="/page/profil.php">
              <i class="bi bi-person-circle fs-5"></i>
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="btn btn-outline-primary ms-2" href="/page/login.php">Connexion</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
