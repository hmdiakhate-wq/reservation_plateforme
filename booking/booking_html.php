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
    <title>Réserver un créneau | Rendez-vous</title>
</head>

<body class="booking-page">
    <main class="page-shell">
        <a class="back-link" href="../index.php">← Retour aux disponibilités</a>
        <header class="page-heading">
            <p class="eyebrow">Votre prochain rendez-vous</p>
            <h1>Réserver un créneau</h1>
            <p>Choisissez une date correspondant au jour proposé.</p>
        </header>

        <section class="form-panel booking-panel" aria-labelledby="booking-details-title">
            <h2 id="booking-details-title">Détails de la disponibilité</h2>
            <div id="message" class="form-message" role="status" aria-live="polite"></div>
            <div id="availability-info" class="detail-list"></div>

            <form id="form" class="form-grid">
                <div class="form-field">
                    <label for="date">Choisir une date</label>
                    <input type="date" id="date" required>
                </div>
                <button type="submit">Confirmer la réservation</button>
            </form>
        </section>
    </main>


    <script>

        const csrfToken = <?= json_encode(csrf_token()) ?>;
        let message = document.getElementById("message");
        let availabilityInfo = document.getElementById("availability-info");
        let form = document.getElementById("form");
        let date = document.getElementById("date");

        // Récupérer availability_id depuis l'URL
        const params = new URLSearchParams(window.location.search);
        const availability_id = params.get("availability_id");

        console.log("Availability ID :", availability_id);


        // Vérifier que l'id existe
        if (!availability_id) {

            message.textContent = "Aucune disponibilité sélectionnée.";
            form.style.display = "none";

        } else {

            // Récupérer les informations de la disponibilité
            fetch(`booking_php.php?availability_id=${availability_id}`, {
                method: "GET",
                headers: {
                    "content-type": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {

                // Vérifier la connexion
                if (data.success === false &&
                    data.message === "Vous étes pas connecté !") {

                    window.location.href = "../auth/login_html.php";
                    return;
                }

                // Vérifier les autres erreurs
                if (data.success === false) {

                    message.textContent = data.message;
                    form.style.display = "none";
                    return;
                }


                const details = [
                    ["Profession :", data.result.profession],
                    ["Description :", data.result.description],
                    ["Localisation :", data.result.location],
                    ["Jour :", formatDay(data.result.day_of_week)],
                    ["Début :", formatTime(data.result.start_time)],
                    ["Fin :", formatTime(data.result.end_time)]
                ];

                details.forEach(([label, value]) => {
                    const paragraph = document.createElement("p");
                    const title = document.createElement("strong");
                    title.textContent = label;
                    paragraph.append(title, document.createTextNode(` ${value ?? ""}`));
                    availabilityInfo.appendChild(paragraph);
                });

            })
            .catch(err => {

                console.log("Erreur :", err);

                message.textContent =
                    "Une erreur est survenue lors du chargement.";

            });
        }


        function formatDay(day) {
            const days = {
                Monday: "lundi",
                Tuesday: "mardi",
                Wednesday: "mercredi",
                Thursday: "jeudi",
                Friday: "vendredi",
                Saturday: "samedi",
                Sunday: "dimanche"
            };

            return days[day] ?? day;
        }

        function formatTime(time) {
            return String(time).slice(0, 5);
        }

        // Confirmer la réservation
        form.addEventListener("submit", function(e) {

            e.preventDefault();

            const selectedDate = date.value;

            if (selectedDate === "") {

                message.textContent =
                    "Veuillez choisir une date.";

                return;
            }


            fetch("booking_php.php", {

                method: "POST",

                headers: {
                    "content-type": "application/json",
                    "X-CSRF-Token": csrfToken
                },

                body: JSON.stringify({
                    availability_id: availability_id,
                    date: selectedDate
                })

            })
            .then(res => res.json())
            .then(data => {

                if (data.success === false &&
                    data.message === "Vous étes pas connecté !") {

                    window.location.href = "../auth/login_html.php";
                    return;
                }


                if (data.success === false) {

                    message.textContent = data.message;
                    return;
                }


                if (data.success === true) {

                    message.textContent = data.message;

                    // Rediriger vers le dashboard client
                    setTimeout(() => {
                        window.location.href =
                            "../client/dashboard_client_html.php";
                    }, 1000);

                }

            })
            .catch(err => {

                console.log("Erreur :", err);

                message.textContent =
                    "Une erreur est survenue.";

            });

        });

    </script>

</body>

</html>