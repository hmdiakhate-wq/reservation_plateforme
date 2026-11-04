<?php
session_start();
require_once '../config/database_connexion.php';

header("content-type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Vous étes pas connecté !"
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer le nom
$sql = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$sql->execute([$user_id]);

$username = $sql->fetch(PDO::FETCH_ASSOC);

if (!$username) {
    echo json_encode([
        "success" => false,
        "message" => "Utilisateur introuvable."
    ]);
    exit();
}

// Récupérer le profession et location
$sql = $pdo->prepare("SELECT * FROM professionals WHERE user_id = ?");
$sql->execute([$user_id]);

$userprofession = $sql->fetch(PDO::FETCH_ASSOC);

$dispo = [];
$reservation = [];

if ($userprofession) {

    // Récupérer les disponibilités
    $sql = $pdo->prepare("SELECT * FROM availability WHERE professional_id = ?");
    $sql->execute([$userprofession['id']]);

    $dispo = $sql->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les réservations
    $sql = $pdo->prepare("SELECT * FROM bookings WHERE professional_id = ?");
    $sql->execute([$userprofession['id']]);

    $reservation = $sql->fetchAll(PDO::FETCH_ASSOC);
}

// Envoyer toutes les données dans un seul JSON
echo json_encode([
    "success" => true,
    "message" => "Données récupérées avec succès !",
    "username" => $username['username'],
    "userprofession" => $userprofession,
    "dispo" => $dispo,
    "reservation" => $reservation
]);
