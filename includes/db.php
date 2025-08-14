<?php
$host = 'localhost';
$dbname = 'kayaktrip_m';
$username = 'root';
$password = '6Us9=0#;?m=SH5/S1m';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
