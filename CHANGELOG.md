# Changelog
All notable changes to this project will be documented in this file.

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