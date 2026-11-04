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
    <title>Espace professionnel | Rendez-vous</title>
</head>

<body>
    <main class="dashboard-shell">
        <header class="dashboard-heading">
            <div>
                <p class="eyebrow">Espace professionnel</p>
                <h1>Mon activité</h1>
                <div id="message" aria-live="polite"></div>
            </div>
            <a class="button-link" href="../index.php">Voir les disponibilités</a>
        </header>

        <div class="dashboard-grid">
            <section class="dashboard-section profile-details profil" aria-label="Mon profil"></section>
            <section class="dashboard-section dispo" aria-label="Mes disponibilités"></section>
        </div>
        <section class="dashboard-section reserv" aria-label="Mes réservations"></section>
    </main>


    <script>
        const csrfToken = <?= json_encode(csrf_token()) ?>;
        const message = document.querySelector("#message");
        const profil = document.querySelector(".profil");
        const dispo = document.querySelector(".dispo");
        const reserv = document.querySelector(".reserv");

        function addText(parent, tagName, text) {
            const element = document.createElement(tagName);
            element.textContent = text;
            parent.appendChild(element);
            return element;
        }

        function addField(parent, label, value) {
            addText(parent, "p", `${label} ${value ?? ""}`);
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

        function formatStatus(status) {
            const statuses = {
                pending: "En attente",
                confirmed: "Confirmée",
                completed: "Terminée",
                cancelled: "Annulée"
            };

            return statuses[status] ?? status;
        }

        fetch("dashboard_profession_php.php", {
            method: "GET",
            headers: {
                "content-type": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success === false && data.message === "Vous étes pas connecté !") {
                window.location.href = "../auth/login_html.php";
                return;
            }

            if (data.success === false) {
                message.textContent = data.message;
                return;
            }

            addText(message, "p", `Bonjour, ${data.username}`);

            if (!data.userprofession) {
                const createLink = document.createElement("a");
                createLink.href = "create_profession_html.php";
                createLink.textContent = "Créer une profession";
                message.appendChild(createLink);
                addText(profil, "p", "Aucune profession enregistrée.");
                return;
            }

            addText(profil, "h2", "Mon profil");
            addField(profil, "Profession :", data.userprofession.profession);
            addField(profil, "Description :", data.userprofession.description);
            addField(profil, "Localisation :", data.userprofession.location);

            addText(dispo, "h2", "Mes disponibilités");
            const addAvailabilityButton = addText(dispo, "button", "Ajouter une disponibilité");
            addAvailabilityButton.addEventListener("click", ajouterDisponibilite);

            if (data.dispo.length === 0) {
                addText(dispo, "p", "Aucune disponibilité pour le moment.");
            } else {
                data.dispo.forEach(disponibilite => {
                    const availabilityCard = document.createElement("div");
                    availabilityCard.className = "availability-card";
                    addField(availabilityCard, "Jour :", formatDay(disponibilite.day_of_week));
                    addField(availabilityCard, "Heure début :", disponibilite.start_time);
                    addField(availabilityCard, "Heure fin :", disponibilite.end_time);

                    const deleteButton = addText(availabilityCard, "button", "Supprimer");
                    deleteButton.addEventListener("click", () => supprimerDisponibilite(disponibilite.id));
                    dispo.appendChild(availabilityCard);
                });
            }

            addText(reserv, "h2", "Mes réservations");
            if (data.reservation.length === 0) {
                addText(reserv, "p", "Aucune réservation pour le moment.");
            } else {
                data.reservation.forEach(reservation => {
                    const reservationCard = document.createElement("div");
                    reservationCard.className = "reservation-card";
                    addField(reservationCard, "Client ID :", reservation.client_id);
                    addField(reservationCard, "Début :", reservation.start_time);
                    addField(reservationCard, "Fin :", reservation.end_time);
                    addField(reservationCard, "Statut :", formatStatus(reservation.status));

                    const confirmButton = addText(reservationCard, "button", "Confirmer");
                    confirmButton.addEventListener("click", () => confirmerReservation(reservation.id));

                    const cancelButton = addText(reservationCard, "button", "Annuler");
                    cancelButton.addEventListener("click", () => annulerReservation(reservation.id));
                    reserv.appendChild(reservationCard);
                });
            }
        })
        .catch(err => {
            console.log("Erreur :", err);
        });

        function ajouterDisponibilite() {
            window.location.href = "availability_html.php";
        }

        function supprimerDisponibilite(id) {
            fetch("availability_delete.php", {
                method: "DELETE",
                headers: {
                    "content-type": "application/json",
                    "X-CSRF-Token": csrfToken
                },
                body: JSON.stringify({ id: id })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(err => console.log(err));
        }

        function confirmerReservation(id) {
            updateReservation("../booking/booking_confirm.php", id);
        }

        function annulerReservation(id) {
            updateReservation("../booking/booking_cancel.php", id);
        }

        function updateReservation(url, id) {
            fetch(url, {
                method: "PATCH",
                headers: {
                    "content-type": "application/json",
                    "X-CSRF-Token": csrfToken
                },
                body: JSON.stringify({ id: id })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(err => console.log("Erreur :", err));
        }
    </script>
</body>

</html>