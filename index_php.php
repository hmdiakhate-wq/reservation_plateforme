<?php

require_once 'config/database_connexion.php';

header("content-type: application/json");

//Récuperer les disponibilités
try {
    $sql = "SELECT
    availability.id AS availability_id,
    professionals.profession,
    professionals.location,
    availability.day_of_week,
    availability.start_time,
    availability.end_time
FROM professionals
INNER JOIN availability
    ON availability.professional_id = professionals.id
ORDER BY professionals.id DESC";
    $stm = $pdo->prepare($sql);
    $stm->execute();
    $results = $stm->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        "success" => true,
        "message" => "Données réçus avec succées !",
        "result" => $results
    ]);
} catch (PDOException $err) {
    error_log($err->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "Une erreur est survenue lors du chargement des disponibilités."
    ]);
}
