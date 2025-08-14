<?php
require_once('../includes/db.php');

if (
    isset($_POST['prenom'], $_POST['nom'], $_POST['email'], $_POST['password'], $_POST['confirm_password']) &&
    !empty($_POST['prenom']) && !empty($_POST['nom']) && !empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['confirm_password'])
) {
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $nom = htmlspecialchars(trim($_POST['nom']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        header('Location: ../page/register.php?error=mdp');
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        header('Location: ../page/register.php?error=exists');
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $insert = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
    $insert->execute([$nom, $prenom, $email, $hash]);

    header('Location: ../page/login.php?register=success');
    exit;
} else {
    header('Location: ../page/register.php?error=missing');
    exit;
}
