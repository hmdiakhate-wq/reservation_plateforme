<?php

session_start();

require_once '../config/database_connexion.php';
require_once '../config/security.php';

header("Content-Type: application/json");
require_valid_csrf_token();


// Vérifier la connexion
if (!isset($_SESSION['user_id'])) {

    echo json_encode([
        "success" => false,
        "message" => "Vous étes pas connecté !"
    ]);

    exit();
}

$user_id = $_SESSION['user_id'];


// Vérifier le rôle
$sql = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$sql->execute([$user_id]);

$user = $sql->fetch(PDO::FETCH_ASSOC);

if (!$user) {

    echo json_encode([
        "success" => false,
        "message" => "Utilisateur introuvable."
    ]);

    exit();
}

if ($user['role'] !== 'professional') {

    echo json_encode([
        "success" => false,
        "message" => "Vous étes un client."
    ]);

    exit();
}


// Récupérer le professionnel connecté
$sql = $pdo->prepare("SELECT id FROM professionals WHERE user_id = ?");
$sql->execute([$user_id]);

$professional = $sql->fetch(PDO::FETCH_ASSOC);

if (!$professional) {

    echo json_encode([
        "success" => false,
        "message" => "Profil professionnel introuvable."
    ]);

    exit();
}


// Récupérer les données envoyées
$data = json_decode(
    file_get_contents("php://input"),
    true
);

$id = $data['id'] ?? null;


// Vérifier l'identifiant
if (!$id) {

    echo json_encode([
        "success" => false,
        "message" => "Identifiant de réservation manquant."
    ]);

    exit();
}


try {

    // Annuler uniquement une réservation
    // appartenant au professionnel connecté
    // et qui n'est pas déjà terminée ou annulée
    $sql = $pdo->prepare("
        UPDATE bookings
        SET status = 'cancelled'
        WHERE id = ?
        AND professional_id = ?
        AND status IN ('pending', 'confirmed')
    ");

    $sql->execute([
        $id,
        $professional['id']
    ]);


    if ($sql->rowCount() === 0) {

        echo json_encode([
            "success" => false,
            "message" => "Réservation introuvable ou impossible à annuler."
        ]);

        exit();
    }


    echo json_encode([
        "success" => true,
        "message" => "Réservation annulée avec succès !"
    ]);

} catch (PDOException $er) {

    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de l'annulation de la réservation."
    ]);
}