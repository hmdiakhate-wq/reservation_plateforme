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
    <title>Créer mon profil professionnel | Rendez-vous</title>
</head>

<body>
    <main class="page-shell">
        <a class="back-link" href="dashboard_profession_html.php">← Retour à mon espace</a>
        <header class="page-heading">
            <p class="eyebrow">Espace professionnel</p>
            <h1>Présentez votre activité</h1>
            <p>Ces informations aideront les clients à choisir votre service.</p>
        </header>
        <section class="form-panel" aria-label="Profil professionnel">
            <form id="form" class="form-grid">
                <p id="message" class="form-message" role="status" aria-live="polite"></p>
                <div class="form-field">
                    <label for="profession">Profession</label>
                    <input type="text" id="profession" placeholder="Ex. Coach sportif" required>
                </div>
                <div class="form-field">
                    <label for="description">Description</label>
                    <textarea id="description" rows="4" placeholder="Présentez votre activité" required></textarea>
                </div>
                <div class="form-field">
                    <label for="location">Adresse ou ville</label>
                    <input type="text" id="location" placeholder="Ex. Montréal" required>
                </div>
                <button type="submit">Créer mon profil</button>
            </form>
        </section>
    </main>
    <script>
        const csrfToken = <?= json_encode(csrf_token()) ?>;
        document.getElementById("form").addEventListener("submit", function(e) {
            e.preventDefault();
            let datas = {
                profession: document.getElementById("profession").value,
                desc: document.getElementById("description").value,
                location: document.getElementById("location").value
            };
            fetch("create_profession_php.php", {
                method: "POST",
                headers: {
                    "content-type": "application/json",
                    "X-CSRF-Token": csrfToken
                },
                body: JSON.stringify(datas)
            })
            .then(res => res.json())
            .then(data => {
                if (data.message === "Vous étes pas connecté !") {
                    window.location.href = "../auth/login_html.php";
                    return;
                }
                if (data.message === "Vous étes un client") {
                    document.getElementById("message").textContent = "Desolé !";
                    return;
                }
                if (data.success === true) {
                    document.getElementById("message").textContent = data.message;
                }

            })
            .catch(err => {
                console.log("Erreur : ", err);
            });
        });
    </script>

</body>

</html>