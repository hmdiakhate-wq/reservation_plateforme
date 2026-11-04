<?php
session_start();
require_once '../config/database_connexion.php';
require_once '../config/security.php';

header("content-type: application/json");
require_valid_csrf_token();

$data = json_decode(file_get_contents("php://input"), true);

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$plain_password = $data['password'] ?? '';
$role = trim($data['role'] ?? '');
if ($name === '' || $email === '' || $plain_password === '' || $role === '') {
    echo json_encode([
        "success" => false,
        "message" => "Tous les champs sont obligatoires."
    ]);
    exit;
}
if (mb_strlen($plain_password, 'UTF-8') < 8) {
    echo json_encode([
        "success" => false,
        "message" => "Le mot de passe doit contenir au moins 8 caractères."
    ]);
    exit;
}
$password = password_hash($plain_password, PASSWORD_DEFAULT);

//verifier si l'email existe déjà
try {
    $sql = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $sql->execute([
        $email
    ]);
    $result = $sql->fetch();
    if ($result) {
        echo json_encode([
            "success" => false,
            "message" => "Cet email existe déjà !"
        ]);
    } else {
        //Insérer les données au base de données mysql
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stm = $pdo->prepare($sql);
        $stm->execute([
            $name,
            $email,
            $password,
            $role
        ]);
        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        echo json_encode([
            "success" => true,
            "message" => "Données enregistrés avec success !"
        ]);
    }
} catch (PDOException $er) {
    error_log($er->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "Une erreur est survenue lors de l'inscription."
    ]);
}
