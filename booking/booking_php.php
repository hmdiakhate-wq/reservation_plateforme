<?php

session_start();

require_once '../config/database_connexion.php';
require_once '../config/security.php';

header("Content-Type: application/json");


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


// Seul un client peut réserver
if ($user['role'] !== 'client') {

    echo json_encode([
        "success" => false,
        "message" => "Seuls les clients peuvent effectuer une réservation."
    ]);

    exit();
}


//Récupérer les informations d'une disponibilité

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $availability_id = $_GET['availability_id'] ?? null;


    // Vérifier l'id
    if (!$availability_id) {

        echo json_encode([
            "success" => false,
            "message" => "Identifiant de disponibilité manquant."
        ]);

        exit();
    }


    // Récupérer la disponibilité et le professionnel
    $sql = $pdo->prepare("
        SELECT
            availability.id AS availability_id,
            professionals.id AS professional_id,
            professionals.profession,
            professionals.description,
            professionals.location,
            availability.day_of_week,
            availability.start_time,
            availability.end_time
        FROM availability
        INNER JOIN professionals
            ON availability.professional_id = professionals.id
        WHERE availability.id = ?
    ");

    $sql->execute([$availability_id]);

    $availability = $sql->fetch(PDO::FETCH_ASSOC);


    // Vérifier que la disponibilité existe
    if (!$availability) {

        echo json_encode([
            "success" => false,
            "message" => "Disponibilité introuvable."
        ]);

        exit();
    }


    // Envoyer les données
    echo json_encode([
        "success" => true,
        "message" => "Disponibilité récupérée avec succès !",
        "result" => $availability
    ]);

    exit();
}


//Créer une réservation

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_valid_csrf_token();

    $data = json_decode(file_get_contents("php://input"), true);


    $availability_id = $data['availability_id'] ?? null;
    $date = trim($data['date'] ?? '');


    // Vérifier les champs
    if (!$availability_id || $date === '') {

        echo json_encode([
            "success" => false,
            "message" => "Tous les champs sont obligatoires."
        ]);

        exit();
    }


    // Récupérer la disponibilité
    $sql = $pdo->prepare("
        SELECT
            availability.id AS availability_id,
            professionals.id AS professional_id,
            availability.day_of_week,
            availability.start_time,
            availability.end_time
        FROM availability
        INNER JOIN professionals
            ON availability.professional_id = professionals.id
        WHERE availability.id = ?
    ");

    $sql->execute([$availability_id]);

    $availability = $sql->fetch(PDO::FETCH_ASSOC);


    // Vérifier l'existence
    if (!$availability) {

        echo json_encode([
            "success" => false,
            "message" => "Disponibilité introuvable."
        ]);

        exit();
    }


    // Vérifier la date


    $date_object = DateTime::createFromFormat(
        '!Y-m-d',
        $date
    );


    if (
        !$date_object ||
        $date_object->format('Y-m-d') !== $date
    ) {

        echo json_encode([
            "success" => false,
            "message" => "Date invalide."
        ]);

        exit();
    }


    // Empêcher une réservation dans le passé
    if ($date < date('Y-m-d')) {

        echo json_encode([
            "success" => false,
            "message" => "Vous ne pouvez pas réserver une date passée."
        ]);

        exit();
    }


    //Vérifier que la date correspond au jour disponible
    

    $selected_day = $date_object->format('l');


    if ($selected_day !== $availability['day_of_week']) {

        echo json_encode([
            "success" => false,
            "message" => "La date choisie ne correspond pas au jour disponible."
        ]);

        exit();
    }


    // Créer les DATETIME
    

    $start_datetime = $date . ' ' . $availability['start_time'];
    $end_datetime = $date . ' ' . $availability['end_time'];


    try {

        $pdo->beginTransaction();

        $sql = $pdo->prepare("SELECT id FROM professionals WHERE id = ? FOR UPDATE");
        $sql->execute([$availability['professional_id']]);

        $sql = $pdo->prepare("
            SELECT id
            FROM bookings
            WHERE professional_id = ?
            AND status != 'cancelled'
            AND start_time < ?
            AND end_time > ?
            LIMIT 1
        ");

        $sql->execute([
            $availability['professional_id'],
            $end_datetime,
            $start_datetime
        ]);

        $existing_booking = $sql->fetch(PDO::FETCH_ASSOC);

        if ($existing_booking) {
            $pdo->rollBack();
            echo json_encode([
                "success" => false,
                "message" => "Ce créneau est déjà réservé."
            ]);
            exit();
        }

        $sql = $pdo->prepare("
            INSERT INTO bookings
            (
                client_id,
                professional_id,
                start_time,
                end_time,
                status
            )
            VALUES (?, ?, ?, ?, 'pending')
        ");


        $sql->execute([
            $user_id,
            $availability['professional_id'],
            $start_datetime,
            $end_datetime
        ]);

        $pdo->commit();


        echo json_encode([
            "success" => true,
            "message" => "Réservation créée avec succès !"
        ]);

        exit();
    } catch (PDOException $er) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log($er->getMessage());

        echo json_encode([
            "success" => false,
            "message" => "Erreur lors de la création de la réservation."
        ]);

        exit();
    }
}


// Méthode HTTP inconnue
echo json_encode([
    "success" => false,
    "message" => "Méthode HTTP non autorisée."
]);
