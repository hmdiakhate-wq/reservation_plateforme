<?php

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function require_valid_csrf_token()
{
    $submitted_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    if (
        !isset($_SESSION['csrf_token']) ||
        !is_string($submitted_token) ||
        !hash_equals($_SESSION['csrf_token'], $submitted_token)
    ) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "Votre session a expiré. Rechargez la page et réessayez."
        ]);
        exit();
    }
}