<?php
$host = "localhost";
$dbname = "reservation_plateforme";
$user = "root";
$pass = "";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connexion reuissit !";
} catch (PDOException $err) {
    error_log($err->getMessage());
    die("Une erreur est survenue lors de la connexion à la base de données.");
}
