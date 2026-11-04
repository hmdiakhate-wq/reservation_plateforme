<?php
session_start();
require_once '../config/database_connexion.php';
require_once '../config/security.php';

header("content-type: application/json");
require_valid_csrf_token();

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
if ($email === '' || $password === '') {
    echo json_encode([
        "success" => false,
        "message" => "Tous les champs sont obligatoires."
    ]);
    exit;
}

try {
    $sql = "SELECT * FROM users WHERE email = ?";
    $stm = $pdo->prepare($sql);
    $stm->execute([
        $email
    ]);
    $user = $stm->fetch();
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        $_SESSION['user_id'] = $user['id'];

        echo json_encode([
            "success" => true,
            "message" => "Vous existéz !"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Email ou mot de passe incorrect !"
        ]);

    }
} catch (PDOException $er) {
    error_log($er->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "Une erreur est survenue lors de la connexion."
    ]);
}
