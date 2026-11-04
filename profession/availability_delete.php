<?php

session_start();
require_once '../config/database_connexion.php';
require_once '../config/security.php';

header("Content-Type: application/json");
require_valid_csrf_token();

$data = json_decode(file_get_contents("php://input"), true);

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

$role = $sql->fetch(PDO::FETCH_ASSOC);

if (!$role || $role['role'] === 'client') {
    echo json_encode([
        "success" => false,
        "message" => "Vous étes un client"
    ]);
    exit();
}

// Récupérer le profil professionnel
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

// Vérifier l'id envoyé
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode([
        "success" => false,
        "message" => "Identifiant de disponibilité manquant."
    ]);
    exit();
}

try {

    // Supprimer uniquement une disponibilité
    // appartenant au professionnel connecté
    $sql = $pdo->prepare("
        DELETE FROM availability
        WHERE id = ?
        AND professional_id = ?
    ");

    $sql->execute([
        $id,
        $professional['id']
    ]);

    if ($sql->rowCount() === 0) {
        echo json_encode([
            "success" => false,
            "message" => "Disponibilité introuvable ou non autorisée."
        ]);
        exit();
    }

    echo json_encode([
        "success" => true,
        "message" => "Disponibilité supprimée avec succès !"
    ]);

} catch (PDOException $er) {

    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de la suppression."
    ]);
}