<?php
session_start();
require_once '../config/database_connexion.php';
require_once '../config/security.php';

header("content-type: application/json");
require_valid_csrf_token();
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Vous étes pas connecté !"
    ]);
    exit();
}
$user_id = $_SESSION['user_id'];

//verifier le role
$sql = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$sql->execute([
    $user_id
]);
$role = $sql->fetch(PDO::FETCH_ASSOC);
if ($role['role'] === 'client') {
    echo json_encode([
        "success" => false,
        "message" => "Vous étes un client"
    ]);
    exit();
}

$sql = $pdo->prepare("SELECT id FROM professionals WHERE user_id = ?");
$sql->execute([$user_id]);

$professional = $sql->fetch(PDO::FETCH_ASSOC);

if (!$professional) {
    echo json_encode([
        "success" => false,
        "message" => "Vous devez d'abord créer votre profil professionnel."
    ]);
    exit();
}

$day_of_week = trim($data['day_of_week'] ?? '');
$start_time = trim($data['start_time'] ?? '');
$end_time = trim($data['end_time'] ?? '');
if ($day_of_week === '' || $start_time === '' || $end_time === '') {
    echo json_encode([
        "success" => false,
        "message" => "Tous les champs sont obligatoires."
    ]);
    exit();
}
try {
    $sql = "INSERT INTO availability(professional_id, day_of_week, start_time, end_time) VALUES (?,?,?,?)";
    $stm = $pdo->prepare($sql);
    $stm->execute([
        $professional['id'],
        $day_of_week,
        $start_time,
        $end_time
    ]);
    echo json_encode([
        "success" => true,
        "message" => "Disponibilité ajoutée avec success !"
    ]);
} catch (PDOException $er) {
    error_log($er->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "Une erreur est survenue lors de l'ajout de la disponibilité."
    ]);
}
