# Changelog
All notable changes to this project will be documented in this file.

## [2.0.0] - 2026-09-18

Version majeure : voir la section « Mise à jour depuis la version 1.x » du README pour les changements incompatibles.

### Evolutions socle technique
- Mise à jour de l'environnement : image Docker PHP 8.3, PHPUnit 12, PHPStan (niveau 10), ECS (PSR-12),
  CI sur PHP 8.3, 8.4 et 8.5.

### Evolutions
- Les exceptions `LsMessageException` conservent l'exception d'origine (`getPrevious()`), par exemple l'erreur réseau du
client HTTP.
- Typage complet de l'API publique (types natifs et annotations), exploitable par l'analyse statique des projets qui
utilisent la lib.

### Modifications
- Modernisation du code (syntaxe PHP 8, `declare(strict_types=1)`), sans changement de l'API publique.

### Corrections
- Un message de 160 caractères est désormais accepté (il était refusé alors que la limite annoncée est de 160).
- Une réponse de l'API qui n'est pas du JSON valide lève une `LsMessageException` (code 500) au lieu d'une `TypeError`.
- Un contenu de SMS mal encodé (UTF-8 invalide) lève une `LsMessageException` (code 400) au lieu d'envoyer une requête vide.
- Une URL ou une clé d'API se terminant par un retour à la ligne est désormais refusée.
- La barre finale de l'URL est supprimée : `https://exemple.fr/` n'aboutit plus à des appels du type `https://exemple.fr//api/v1/send`.

### Suppressions
- Suppression du champ `version` du `composer.json` : la version est désormais déterminée par les tags git.
- Suppression des cibles `make up`, `up-log`, `down`, `logs` et `ps`, inutiles pour une bibliothèque.

## [1.1.5] - 2025-10-08

### Evolutions
- Mise à jour de sécurité

### Modifications
- Calcul du nombre de caractères suite au correctif du prestataire.
- Modification des caractères remplacés suite au correctif du prestataire.

## [1.1.4] - 2025-09-17

### Evolutions
- Mise à jour de sécurité
- Mise en place de la constante "REMPLACEMENTS_CARACTERES" qui permet de connaître tous les caractères qui sont remplacés.

### Corrections
- Correction du calcul du nombre de caractères en fonction de l'encodage GSM.

### Suppressions
- Suppression de la validation du numéro de téléphone.

## [1.1.3] - 2025-08-20

### Evolutions
- Mise à jour de sécurité

### Corrections
- Correction du calcul du nombre de caractères en fonction de l'encodage GSM.

## [1.1.2] - 2025-01-07

### Evolutions
- Mise à jour de sécurité

### Modifications
- [Expéditeur] Modification des règles de validation. L’expéditeur doit être composé uniquement de caractères alphabétiques (lettres) ou alphanumériques (lettres et chiffres). Il ne doit contenir ni espaces ni caractères spéciaux.

## [1.1.1] - 2024-04-30

### Evolutions
- Mise à jour de sécurité
- Amélioration du message d'erreur quand le nombre de caractère du contenu du message est dépassé.
- Amélioration du message d'erreur quand le contenu du message est vide.
- Amélioration du message d'erreur quand l'expéditeur du message est vide.
- Amélioration du message d'erreur quand le nombre de caractère maximum de l'expéditeur est dépassé (max 11 caractères).
- Amélioration du message d'erreur quand le nombre de caractère minimum de l'expéditeur est dépassé (min 3 caractères).
- Amélioration du message d'erreur quand un caractère spécial est utilisé pour l'expéditeur.
- Ajout de la règle de validation concernant un nombre minimum de caractères pour l'expéditeur (min 3 caractères).

### Corrections
- Correction du nombre maximum de caractère autorisé pour le contenu du message par API (160 caractères)
- Correction du nombre minimum de caractère autorisé pour l'expéditeur du message par API et front (3 caractères)

### Suppressions
- Suppression de la règle de validation concernant l'obligation pour l'expéditeur de commercé par une lettre.

## [1.1.0] - 2023-07-24