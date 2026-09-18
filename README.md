# lsMessageWrapper

Wrapper PHP de l'API LsMessage. La lib vérifie aussi la validité du contenu des SMS avant l'envoi.

### Prérequis

- PHP 8.3 ou supérieur, avec les extensions `json` et `mbstring`
- Un client HTTP [PSR-18](https://www.php-fig.org/psr/psr-18/) et une fabrique de requêtes
[PSR-17](https://www.php-fig.org/psr/psr-17/)

### Installation

```bash
composer require libriciel/ls-message-wrapper
```

### Mise à jour depuis la version 1.x

```bash
composer require libriciel/ls-message-wrapper:^2.0
```

La version 2.0 introduit des changements incompatibles :

- **PHP 8.3 minimum** (au lieu de 7.4). La lib est testée sur PHP 8.3, 8.4 et 8.5.
- **URL en `https://` uniquement** : `setUrl()` refuse désormais les URL en `http://`, pour que la clé d'API ne
transite jamais en clair. Avant de mettre à jour, vérifier qu'aucune URL LsMessage enregistrée n'utilise `http://`.
- **La classe `Sms` est `final`** et ne peut plus être étendue.
- **`Sms::jsonSerialize()`** déclare le type de retour `array`.

En dehors de ces points, l'API publique est inchangée : aucune autre modification n'est nécessaire, sauf pour un projet
qui étendait `Sms`.

## Utilisation

### Initialisation

Dans un projet Symfony, le plus simple est de déclarer `LsMessageWrapper` comme sérvice et de l'injécter.
Sinon, par exemple avec Guzzle :

```php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Libriciel\LsMessageWrapper\LsMessageWrapper;

$lsMessage = new LsMessageWrapper(new Client(), new HttpFactory());
$lsMessage->setUrl('https://lsmessageurl.fr');
$lsMessage->setApiKey('structure_api_key');
```

#### Envoi d'un SMS

```php
use Libriciel\LsMessageWrapper\Sms;

// nom, numéro de téléphone, contenu, expéditeur
$sms = new Sms('seance idelibre', '0601020304', 'Vous avez reçu une notification', 'libriciel');
$result = $lsMessage->sendOne($sms);
```

Le constructeur de `Sms` vérifie les données et lève une `LsMessageException` (code 400) si :

- le nom est vide ou fait 255 caractères ou plus ;
- le contenu est vide ou dépasse `Sms::MAX_CHARACTERS_MESSAGE` (160) caractères GSM ;
- l'éxpediteur ne fait pas entre 3 et 11 caractères alphanumériques (sans espace ni caractère spécial).

#### Envoi de SMS par lots

```php
$smsArray = [
    new Sms('seance idelibre', '0601020304', 'Vous avez reçu une notification', 'libriciel'),
    new Sms('seance idelibre', '0601020305', 'Vous avez reçu une notification', 'libriciel'),
];
$result = $lsMessage->sendMultiple($smsArray);
```

Le nom et l'expéditeur du premier SMS sont utilisés pour tout le lot.

### Informations du compte

```php
$info = $lsMessage->info();
```

### Comptage de caractères

Certains caractères sont remplacés avant l'envoi (voir `Sms::REMPLACEMENTS_CARACTERES`) et d'autres comptent double (`€`
, retour à la ligne) :

```php
LsMessageWrapper::countGsmCharacters('Salut €'); // 8
```

### Gestion des erreurs

Toutes les erreurs sont levées sous forme de `LsMessageException`. Son code vaut :

- le statut HTTP renvoyé par l'API en cas d'erreur de celle-ci ;
- 400 si les données fournies sont invalides ;
- 500 si la réponse de l'API n'est pas un JSON valide.

```php
use Libriciel\LsMessageWrapper\LsMessageException;

try {
    $lsMessage->sendOne($sms);
} catch (LsMessageException $e) {
    // $e->getMessage(), $e->getCode(), $e->getPrevious()
}
```

## Développement

L'environnement de développement tourne dans Docker (PHP 8.3, la version minimale supportée) :

| Commande | Rôle |
|---|---|
| `make build` | Construit l'image |
| `make composer-install` | Installe les dépendances |
| `make test` | Lance les tests (PHPUnit) |
| `make phpstan` | Analyse statique (PHPStan, niveau 10) |
| `make ecs` | Vérifie le style de code (ECS, PSR-12) |
| `make ecs-fix` | Corrige le style de code |
| `make shell` | Ouvre un terminal dans le conteneur |

La CI exècute les tests sur PHP 8.3, 8.4 et 8.5.
