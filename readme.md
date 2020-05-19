# lsMessageWrapper

Wrapper php de l'api de lsmessage.
La lib vérifie aussi la validité du contenu avant l'envoi

### Prérequis
php >= 7.2

### Installation

```
composer require libriciel/ls-message-wrapper
```

### Initialisation
```
// Le plus simple reste d'injecter le service
$client = new ClientImplementation();
$requestFactory = new RequestFactoryImplementation()
lsMsg = new LsMessageWrapper($client, $requestFactory);

lsMsg->setKey("structure_api_key");
lsMsg->setUrl("https://lsmessageurl.fr");

```

#### Envoi d'un sms

```
$sms = new Sms('seance idelibre', '0601020304, 'Vous avez reçu une notification', 'libriciel');
lsMsg->sendOne($sms);
```

#### Envoi de sms par lots

```
$smsArray[] = new Sms('seance idelibre', '0601020304, 'Vous avez reçu une notification', 'libriciel');
$smsArray[] = new Sms('seance idelibre', '0601020305, 'Vous avez reçu une notification', 'libriciel');
$smsArray[] = new Sms('seance idelibre', '0601020306, 'Vous avez reçu une notification', 'libriciel');
lsMsg->sendOne($sms);
```
