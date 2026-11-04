<?php
session_start();
require_once '../config/security.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
    <title>Créer un compte | Rendez-vous</title>
</head>

<body class="auth-page">
    <main class="auth-shell">
        <div class="panel">
            <a class="back-link" href="../index.php">← Retour aux disponibilités</a>
            <p class="eyebrow">Bienvenue</p>
            <h1>Inscription</h1>
            <form action="" method="post" id="form" class="auth-form">
                <p id="result" class="form-message"></p>

                <div class="form-field">
                    <label for="name">Votre nom :</label>
                    <input type="text" name="username" id="name" placeholder="Votre nom..." required>
                </div>

                <div class="form-field">
                    <label for="email">Votre email :</label>
                    <input type="email" name="email" id="email" placeholder="Votre email..." required>
                </div>

                <div class="form-field">
                    <label for="password">Votre mot de passe :</label>
                    <input type="password" name="password" id="password" placeholder="Votre mot de passe..." minlength="8" required>
                </div>
                <div class="form-field">
                    <label for="role">Vous êtes :</label>
                    <select name="role" id="role">
                        <option value="client">Client</option>
                        <option value="professional">Professionnel</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">S'inscrire</button>
                <p class="text-muted">Déjà un compte ? <a href="login_html.php" class="link">Se connecter</a></p>
            </form>
        </div>
    </main>

    <script>
        const csrfToken = <?= json_encode(csrf_token()) ?>;
        let result = document.getElementById("result");
        document.getElementById("form").addEventListener("submit", e => {
            e.preventDefault();

            let datas = {
                name: document.getElementById("name").value,
                email: document.getElementById("email").value,
                password: document.getElementById("password").value,
                role: document.getElementById("role").value
            };

            fetch("register_php.php", {
                    method: "POST",
                    headers: {
                        "content-type": "application/json",
                        "X-CSRF-Token": csrfToken
                    },
                    body: JSON.stringify(datas)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = "../index.php";
                        return;
                    }

                    result.textContent = data.message;
                    result.className = "form-message error";
                    setTimeout(() => {
                        document.getElementById("form").reset();
                    }, 400);
                })
                .catch(err => {
                    console.log(err);
                });
        });
    </script>
</body>

</html>