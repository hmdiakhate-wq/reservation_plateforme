<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Rendez-vous | Trouver un créneau</title>
</head>

<body>
    <header class="site-header">
        <nav class="site-nav" aria-label="Navigation principale">
            <a class="brand" href="index.php">
                <span class="brand-mark" aria-hidden="true">R</span>
                <span class="brand-name">Rendez-vous</span>
            </a>
            <div class="nav-links">
                <a href="#disponibilites">Disponibilités</a>
                <a href="client/dashboard_client_html.php">Mon espace</a>
                <a href="profession/dashboard_profession_html.php">Professionnel</a>
                <a href="auth/login_html.php">Connexion</a>
                <a class="nav-cta" href="auth/register_html.php">Créer un compte</a>
            </div>
        </nav>
    </header>
    <main class="home-shell">
        <section class="home-intro" aria-labelledby="home-title">
            <div>
                <p class="eyebrow">Des rendez-vous, simplement</p>
                <h1 id="home-title">Trouvez le créneau qui vous convient.</h1>
                <p>Parcourez les disponibilités des professionnels et choisissez le moment qui vous arrange.</p>
            </div>
            <aside class="intro-note">
                Consultez les jours et horaires proposés, puis réservez en quelques étapes.
            </aside>
        </section>

        <section class="directory-section" id="disponibilites" aria-labelledby="availability-title">
            <div class="section-heading">
                <h2 id="availability-title">Disponibilités à venir</h2>
            </div>
            <div class="availability-list" aria-live="polite"></div>
        </section>
    </main>
    <footer class="site-footer">
        <p>Rendez-vous · Plateforme de réservation</p>
    </footer>
    <script>
        const card = document.querySelector(".availability-list");
        fetch("index_php.php", {
                method: "GET",
                headers: {
                    "content-type": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success === false) {
                    console.log("Une erreur est survenue lors de récupération !");
                    return;
                }
                if (data.result.length === 0) {
                    card.textContent = "Aucun disponibilité pour le moment !";
                    return;
                }
                data.result.forEach(dispo => {
                    const availabilityCard = document.createElement("div");
                    availabilityCard.className = "availability-card";

                    const profession = document.createElement("p");
                    profession.textContent = `Profession : ${dispo.profession}`;
                    availabilityCard.appendChild(profession);

                    const location = document.createElement("p");
                    location.textContent = `Localisation : ${dispo.location}`;
                    availabilityCard.appendChild(location);

                    const day = document.createElement("p");
                    day.textContent = `Jour : ${formatDay(dispo.day_of_week)}`;
                    availabilityCard.appendChild(day);

                    const time = document.createElement("div");
                    time.className = "availability-time";

                    const start = document.createElement("small");
                    start.textContent = `Début : ${formatTime(dispo.start_time)}`;
                    time.appendChild(start);

                    const end = document.createElement("small");
                    end.textContent = `Fin : ${formatTime(dispo.end_time)}`;
                    time.appendChild(end);
                    availabilityCard.appendChild(time);

                    const button = document.createElement("button");
                    button.textContent = "Réserver";
                    button.addEventListener("click", () => reserver(dispo.availability_id));
                    availabilityCard.appendChild(button);

                    card.appendChild(availabilityCard);
                });

            });

        function reserver(id) {

            console.log("ID :", id);

            window.location.href = `booking/booking_html.php?availability_id=${id}`;

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
    </script>

</body>

</html>