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
    <title>Ajouter des disponibilités | Rendez-vous</title>
</head>

<body>
    <main class="page-shell">
        <a class="back-link" href="dashboard_profession_html.php">← Retour à mon espace</a>
        <header class="page-heading">
            <p class="eyebrow">Organisation</p>
            <h1>Ajouter une disponibilité</h1>
            <p>Indiquez le jour et les heures où vous recevez des réservations.</p>
        </header>
        <section class="form-panel" aria-label="Nouvelle disponibilité">
            <form id="form" class="form-grid">
                <p id="message" class="form-message" role="status" aria-live="polite"></p>
                <div class="form-field">
                    <label for="day_of_week">Jour de la semaine</label>
                <select name="day_of_week" id="day_of_week">
                    <option value="Monday">Lundi</option>
                    <option value="Tuesday">Mardi</option>
                    <option value="Wednesday">Mercredi</option>
                    <option value="Thursday">Jeudi</option>
                    <option value="Friday">Vendredi</option>
                    <option value="Saturday">Samedi</option>
                    <option value="Sunday">Dimanche</option>
                </select>
                </div>
                <div class="form-field">
                    <label for="start_time">Heure de début</label>
                    <input type="time" id="start_time" required>
                </div>
                <div class="form-field">
                    <label for="end_time">Heure de fin</label>
                    <input type="time" id="end_time" required>
                </div>
                <button type="submit">Ajouter la disponibilité</button>
            </form>
        </section>
    </main>


    <script>
        const csrfToken = <?= json_encode(csrf_token()) ?>;
        document.getElementById("form").addEventListener("submit", function(e) {
            e.preventDefault();
            let datas = {
                day_of_week: document.getElementById("day_of_week").value,
                start_time: document.getElementById("start_time").value,
                end_time: document.getElementById("end_time").value
            };
            fetch("availability_php.php", {
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