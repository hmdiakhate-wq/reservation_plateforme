# Rendez-vous — Plateforme de réservation

Application web de prise de rendez-vous qui met en relation des clients et des professionnels. Les professionnels publient leur profil et leurs créneaux hebdomadaires ; les clients consultent les disponibilités et réservent une date en ligne.

## Fonctionnalités

### Côté client

- Créer un compte client et se connecter.
- Consulter les professionnels et leurs disponibilités.
- Choisir une date correspondant au jour proposé et demander un rendez-vous.
- Consulter ses réservations et leur statut depuis son espace personnel.

### Côté professionnel

- Créer un compte professionnel et renseigner son profil (activité, description et localisation).
- Ajouter et supprimer des disponibilités hebdomadaires.
- Consulter les réservations reçues, puis les confirmer ou les annuler.

Les statuts de réservation disponibles sont : **en attente**, **confirmée**, **terminée** et **annulée**. Une réservation nouvellement créée est en attente. L’application empêche notamment la réservation d’une date passée, d’un jour non proposé ou d’un créneau déjà réservé.

## Technologies

- PHP
- MySQL ou MariaDB
- HTML, CSS et JavaScript (API Fetch et réponses JSON)
- PDO pour l’accès à la base de données

## Prérequis

- Un environnement PHP avec un serveur web (par exemple [XAMPP](https://www.apachefriends.org/)).
- PHP avec les extensions **PDO MySQL** et **mbstring** activées.
- MySQL ou MariaDB.

## Installation locale avec XAMPP

1. Placez le projet dans le répertoire web de XAMPP, par exemple `C:\xampp\htdocs\reservation_plateforme`.
2. Démarrez Apache et MySQL depuis le panneau de contrôle XAMPP.
3. Créez la base et ses tables en important [`config/reservation_plateforme.sql`](config/reservation_plateforme.sql) dans phpMyAdmin (`http://localhost/phpmyadmin`), ou exécutez ce fichier avec votre client MySQL.
4. Vérifiez les paramètres de connexion dans [`config/database_connexion.php`](config/database_connexion.php). Par défaut, l’application utilise `localhost`, la base `reservation_plateforme`, l’utilisateur `root` et un mot de passe vide ; adaptez-les à votre installation.
5. Ouvrez l’application à l’adresse :

   [http://localhost/reservation_plateforme/](http://localhost/reservation_plateforme/)

## Démarrage rapide

1. Depuis la page d’accueil, créez un compte en choisissant le rôle **Client** ou **Professionnel**.
2. En tant que professionnel, ouvrez l’espace professionnel, créez votre profil, puis ajoutez vos jours et horaires disponibles.
3. En tant que client, sélectionnez un créneau sur la page d’accueil, choisissez une date correspondante et confirmez la réservation.
4. Retrouvez les réservations dans les espaces respectifs du client et du professionnel.

## Structure du projet

```text
.
├── index.php                       # Page d’accueil et affichage des disponibilités
├── index_php.php                   # API JSON des disponibilités publiques
├── auth/                           # Inscription et connexion
├── booking/                        # Création, confirmation et annulation de réservations
├── client/                         # Espace client
├── config/
│   ├── database_connexion.php      # Connexion PDO à la base de données
│   ├── reservation_plateforme.sql  # Création de la base et des tables
│   └── security.php                # Gestion des jetons CSRF
├── profession/                     # Profil, espace et disponibilités professionnels
└── style/
    └── style.css                   # Styles de l’application
```

## Sécurité et configuration

Les mots de passe sont stockés sous forme de hachages PHP. Les accès à la base utilisent PDO et des requêtes préparées ; les requêtes de modification depuis l’interface transmettent un jeton CSRF.

Les identifiants de base de données sont définis directement dans `config/database_connexion.php`. Avant tout déploiement, configurez des identifiants dédiés et robustes, ne publiez pas de secrets et servez le site en HTTPS. Le réglage fourni par défaut est destiné au développement local.
