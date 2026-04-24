# Fiche de réalisation — SIO-2025

## Informations générales
- Projet : Gestion_patrimoine
- Auteur : NOM Prénom (à compléter)
- Classe / Groupe : (à compléter)
- Date de rendu : (à compléter)
- Temps passé estimé : 10 heures

## Contexte et objectifs
Le projet "Gestion_patrimoine" est une application web de gestion de patrimoine médical (patients, médecins, chirurgiens, infirmières, etc.). L'objectif principal de cette réalisation est de proposer une interface d'administration et de gestion des acteurs et des ressources, avec une API minimale, une interface web réactive et un déploiement facilité via Docker Compose.

Objectifs fonctionnels :
- Permettre la gestion des utilisateurs (administrateurs, médecins, chirurgiens, infirmières).
- Authentification / autorisation pour accéder à l'interface d'administration.
- Gestion CRUD (Créer, Lire, Mettre à jour, Supprimer) des entités principales : patients, médecins, chirurgiens, infirmières, rapports.
- Upload et affichage de documents (rapports, images) liés aux patients.
- Interface d'administration protégée (formulaire de connexion, redirections, sessions).
- Accès à une base de données MySQL et gestion des migrations.
- Interface de démonstration pour visualiser les listings et fiches détaillées.

## Fonctionnalités réalisées (liste détaillée — j'ai inclus tout ce que je pense utile)
- Authentification des utilisateurs (login, logout, session) avec redirection vers l'espace admin.
- Rôles et permissions : administrateur, médecin, chirurgien, infirmière.
- Gestion des utilisateurs : création, modification, suppression, réinitialisation de mot de passe (scripts de tests fournis dans `bin/`).
- Gestion des patients : création de fiches patient, édition, recherche, liste paginée.
- Gestion des rapports médicaux : création de rapports, attachement de fichiers (upload), visualisation et téléchargement.
- Interface d'administration (tableau de bord) listant les statistiques de base et accès rapide aux modules.
- Import / export basique : commandes/scripts pour générer des utilisateurs de test (`bin/create_test_users.php`, `TEST_CREDENTIALS.txt`).
- Intégration d'un serveur web contenu dans le conteneur via FrankenPHP + Caddy (Caddyfile fourni) pour servir l'application.
- Déploiement en local avec Docker Compose (fichier `compose.yaml` et `compose.override.yaml`) incluant :
  - Service `php` (FrankenPHP + app)
  - Service `database` (MySQL)
  - phpMyAdmin (sur le port 8080)
  - Mailpit pour tester l'envoi d'emails (ports 1025 & 8025)
- Système de logs accessibles via `docker compose logs -f php` et surveillance des fichiers de log dans `var/log`.
- Assets buildés via webpack (voir `assets/` et `webpack.config.js`).
- Pages et contrôleurs principaux :
  - Route `/` redirige vers `/login` si non authentifié.
  - Route `/login` pour la page de connexion.
  - Espace admin (`/admin-pannel`) et pages fonctionnelles (`/admin/medecins`, `/admin/chirurgiens`, etc.).
- Tests basiques et fixtures pour faciliter la validation manuelle.

## Architecture technique
- Langage : PHP 8.4
- Framework : Symfony 7.4
- Serveur d’application : FrankenPHP + Caddy (Caddyfile : `frankenphp/Caddyfile`)
- Conteneurisation : Docker Compose (`compose.yaml`, `compose.override.yaml`)
- Base de données : MySQL (service `database`)
- Outils : phpMyAdmin (8080), Mailpit (1025/8025), webpack
- Ports exposés localement :
  - 80 -> application HTTP
  - 443 -> HTTPS (auto_https désactivé dans la config par défaut)
  - 8080 -> phpMyAdmin
  - MySQL : port mappé dynamiquement (ex: 51512 sur l'hôte)

## Installation et exécution (pour l'environnement de développement)
Prérequis : Docker et Docker Compose installés.

1. Cloner le dépôt :

```bash
git clone <url_du_repo>
cd Gestion_patrimoine
```

2. Lancer les services en arrière-plan :

```bash
docker compose up -d --build
```

3. Vérifier les conteneurs :

```bash
docker ps
```

4. Accéder à l'application :
- Ouvrir http://localhost (port 80)
- phpMyAdmin : http://localhost:8080

5. Commandes utiles :

```bash
# Voir les logs de l'app
docker compose logs -f php

# Exécuter une commande à l'intérieur du conteneur php
docker compose exec php bash

# Composer / installation de dépendances si besoin
docker compose exec php composer install
```

Notes :
- Si Chrome tente d'utiliser HTTPS, il peut bloquer la connexion car `auto_https` est désactivé. Dans ce cas, utiliser explicitement `http://localhost` ou ajouter un certificat local (mkcert) et configurer Caddy.

## Tests et vérifications effectués
- Tests manuels :
  - Accès à la page d'accueil et redirection vers `/login` (vérifié avec `curl -I http://localhost`).
  - Connexion avec identifiants de test présents dans `TEST_CREDENTIALS.txt` (ex : `admin@hospital.fr` / `admin123`).
  - Navigation dans l'interface admin et accès aux pages de listing (medecins, chirurgiens, infirmieres).
  - Upload et affichage d'un rapport (test fonctionnel). 
  - Vérification du démarrage des services Docker (healthy) et des logs Caddy/FrankenPHP.

## Difficultés rencontrées et solutions apportées
- Problème : Chrome bloquait l'accès en forçant HTTPS (ERR_CONNECTION_RESET) alors que le serveur ne négociait pas TLS (auto_https off).
  - Solution : tester en HTTP (http://localhost) ou configurer un certificat local via mkcert. Explication et commandes fournies dans le README local.
- Gestion des uploads et autorisations de fichiers : nécessité de configurer les volumes et permissions (`var/uploads`, `var/cache`, `var/log`).
- Synchronisation des vendor mounts sous macOS : recommandation de laisser `vendor/` en volume pour de meilleures performances en dev (option décommentée dans `compose.override.yaml`).

## Répartition du temps (estimation)
- Mise en place de l’environnement Docker : 3h
- Développement des contrôleurs et vues : 3h
- Authentification & sécurité : 1.5h
- Tests manuels et corrections : 1h
- Documentation & packaging (Docker Compose, scripts) : 1.5h

Total estimé : 10h

## Améliorations futures
- Activer HTTPS localement avec mkcert et configurer Caddy pour servir des certificats locaux.
- Ajouter des tests automatisés (PHPUnit) pour les principaux contrôleurs et l’API.
- Mettre en place une CI pour builder l’image Docker et exécuter les tests.
- Améliorer l’UX / design des pages et l’accessibilité.
- Ajouter des modules d’export avancés (PDF, CSV) et un historique des modifications.

## Annexes
- Fichiers clés :
  - `frankenphp/Caddyfile` (configuration du serveur)
  - `compose.yaml`, `compose.override.yaml` (déploiement)
  - `public/index.php` (front controller)
  - `TEST_CREDENTIALS.txt` (comptes de test)
  - `bin/` (scripts utiles)
- Commandes principales :
  - `docker compose up -d --build`
  - `docker compose logs -f php`
  - `curl -I http://localhost`

---

*Fichier généré automatiquement — relis et adapte les sections "Auteur", "Date", et notes personnelles avant rendu.*
