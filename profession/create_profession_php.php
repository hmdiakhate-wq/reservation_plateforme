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

$profession = trim($data['profession'] ?? '');
$description = trim($data['desc'] ?? '');
$location = trim($data['location'] ?? '');
if ($profession === '' || $description === '' || $location === '') {
    echo json_encode([
        "success" => false,
        "message" => "Tous les champs sont obligatoires."
    ]);
    exit();
}
try {
    $sql = "INSERT INTO professionals(user_id, profession, description, location) VALUES (?,?,?,?)";
    $stm = $pdo->prepare($sql);
    $stm->execute([
        $user_id,
        $profession,
        $description,
        $location
    ]);
    echo json_encode([
        "success" => true,
        "message" => "Profession ajouté avec success !"
    ]);
} catch (PDOException $er) {
    error_log($er->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "Une erreur est survenue lors de la création du profil."
    ]);
}
