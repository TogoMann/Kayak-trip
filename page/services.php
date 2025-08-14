<?php
session_start();
require_once('../includes/header.php');
?>

<div class="text-white text-center" style="background: url('../img/horo.jpg') center/cover no-repeat; min-height: 50vh; display: flex; align-items: center; justify-content: center;">
    <div style="background-color: rgba(0, 0, 0, 0.4); padding: 20px; border-radius: 8px;">
        <h1 class="display-4 fw-bold">Services complémentaires</h1>
    </div>
</div>

<div class="container py-5">
    <div class="row gy-4">

        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h4 class="card-title text-primary">Transport de bagages</h4>
                    <p class="card-text">
                        Faites transporter vos bagages d’un point d’arrêt à l’autre.
                    </p>
                    <ul>
                        <li>Standard (moins de 10kg et 80cm) : 5 € / étape</li>
                        <li>Supérieur (plus de 10kg ou 80cm) : 8 € / étape</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h4 class="card-title text-primary">Repas</h4>
                    <p class="card-text">Recevez vos repas chaque jour à votre hébergement.</p>
                    <ul>
                        <li>Petit-déjeuner : 4 € / jour</li>
                        <li>Déjeuner : 8 € / jour</li>
                        <li>Dîner : 10 € / jour</li>
                        <li>Pack complet (3 repas) : 20 € / jour</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h4 class="card-title text-primary">Location de matériel</h4>
                    <p class="card-text">Louez le matériel nécessaire pour votre aventure.</p>
                    <ul>
                        <li>Kayak simple : 15 € / jour</li>
                        <li>Kayak double : 25 € / jour</li>
                        <li>Tente : 10 € / jour</li>
                        <li>Matériel de camping : 8 € / jour</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h4 class="card-title text-primary">Extras</h4>
                    <p class="card-text">Ajoutez des options pour plus de confort ou de sécurité.</p>
                    <ul>
                        <li>Assurance matériel : 3 € / jour</li>
                        <li>Accompagnement par un guide : 40 € / jour</li>
                        <li>Assistance GPS & Secours : 5 € / jour</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include('../includes/footer.php'); ?>
