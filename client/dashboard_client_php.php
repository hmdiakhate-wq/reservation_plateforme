<?php

session_start();

require_once '../config/database_connexion.php';

header("Content-Type: application/json");


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {

    echo json_encode([
        "success" => false,
        "message" => "Vous étes pas connecté !"
    ]);

    exit();
}


$user_id = $_SESSION['user_id'];


// Récupérer le nom et le rôle
$sql = $pdo->prepare("
    SELECT username, role
    FROM users
    WHERE id = ?
");

$sql->execute([$user_id]);

$user = $sql->fetch(PDO::FETCH_ASSOC);


// Vérifier que l'utilisateur existe
if (!$user) {

    echo json_encode([
        "success" => false,
        "message" => "Utilisateur introuvable."
    ]);

    exit();
}


// Vérifier le rôle
if ($user['role'] !== 'client') {

    echo json_encode([
        "success" => false,
        "message" => "Vous étes un professionnel."
    ]);

    exit();
}


// Récupérer les réservations du client
$sql = $pdo->prepare("
    SELECT
        bookings.id AS booking_id,
        bookings.start_time,
        bookings.end_time,
        bookings.status,
        bookings.created_at,
        professionals.profession,
        professionals.location
    FROM bookings
    INNER JOIN professionals
        ON bookings.professional_id = professionals.id
    WHERE bookings.client_id = ?
    ORDER BY bookings.start_time DESC
");

$sql->execute([$user_id]);

$reservation = $sql->fetchAll(PDO::FETCH_ASSOC);


// Envoyer les données 
echo json_encode([
    "success" => true,
    "message" => "Données récupérées avec succès !",
    "username" => $user['username'],
    "reservation" => $reservation
]);