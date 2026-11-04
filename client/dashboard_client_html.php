<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
    <title>Mon espace | Rendez-vous</title>
</head>

<body>
    <main class="dashboard-shell client-dashboard">
        <header class="dashboard-heading">
            <div>
                <p class="eyebrow">Espace client</p>
                <div id="message" aria-live="polite"></div>
            </div>
            <a class="button-link" href="../index.php">Trouver un créneau</a>
        </header>
        <section class="dashboard-section reserv" aria-labelledby="reservations-title"></section>
    </main>


    <script>

        let message = document.querySelector("#message");
        let reserv = document.querySelector(".reserv");

        fetch("dashboard_client_php.php", {
            method: "GET",
            headers: {
                "content-type": "application/json"
            }
        })

        .then(res => res.json())

        .then(data => {

            // Vérifier si le client est connecté
            if (data.success === false &&
                data.message === "Vous étes pas connecté !") {

                window.location.href = "../auth/login_html.php";
                return;
            }


            // Vérifier les autres erreurs
            if (data.success === false) {

                message.textContent = data.message;
                return;
            }


            // Message de bienvenue
            const welcome = document.createElement("h1");
            welcome.textContent = `Bonjour : ${data.username}`;
            message.replaceChildren(welcome);

            const title = document.createElement("h2");
            title.id = "reservations-title";
            title.textContent = "Mes réservations";
            reserv.replaceChildren(title);


            // Afficher les réservations
            if (data.reservation.length === 0) {
                const emptyMessage = document.createElement("p");
                emptyMessage.textContent = "Aucune réservation pour le moment.";
                reserv.appendChild(emptyMessage);

            } else {
                data.reservation.forEach(reservation => {
                    const reservationCard = document.createElement("div");
                    reservationCard.className = "reservation-card";

                    const profession = document.createElement("h3");
                    profession.textContent = reservation.profession;
                    reservationCard.appendChild(profession);

                    const fields = [
                        ["Localisation :", reservation.location],
                        ["Début :", reservation.start_time],
                        ["Fin :", reservation.end_time],
                        ["Statut :", formatStatus(reservation.status)]
                    ];

                    fields.forEach(([label, value]) => {
                        const paragraph = document.createElement("p");
                        paragraph.textContent = `${label} ${value ?? ""}`;
                        reservationCard.appendChild(paragraph);
                    });

                    reserv.appendChild(reservationCard);
                });
            }

        })

        .catch(err => {
            console.log("Erreur :", err);
        });

        function formatStatus(status) {
            const statuses = {
                pending: "En attente",
                confirmed: "Confirmée",
                completed: "Terminée",
                cancelled: "Annulée"
            };

            return statuses[status] ?? status;
        }

    </script>

</body>

</html>