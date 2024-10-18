# Messenger FilesystemTransport

`FilesystemTransport` is a transport for [Symfony Messenger](https://symfony.com/doc/current/messenger.html) based on the file system. It allows for handling asynchronous messages without using a database or an external message broker such as RabbitMQ or Redis. This is a simple and lightweight solution, ideal for small to medium-sized projects.

## 📖 Documentation

- [English](#english)
- [Français](#français)

## English

---

`FilesystemTransport` is a transport for [Symfony Messenger](https://symfony.com/doc/current/messenger.html) based on the file system. It allows for handling asynchronous messages without using a database or an external message broker such as RabbitMQ or Redis. This is a simple and lightweight solution, ideal for small to medium-sized projects.

## 📦 Installation

To install the package via [Composer](https://getcomposer.org/), use the following command:

```bash
composer require alphasoft-fr/messenger-filesystem-transport
```

## 🚀 Features

- **File-based message storage**: Stores messages in a local directory for simple and lightweight management.
- **No external dependency**: No need for RabbitMQ, Redis, or a database, reducing infrastructure complexity.
- **Easy to debug**: Messages are stored as JSON files, making it easy to read and understand their content.
- **Suitable for development and testing environments**: Ideal for testing asynchronous tasks in environments where setting up additional services is not justified.

## 📚 Benefits

- **💡 Simplicity and minimal configuration**: Perfect for small projects where setting up a message broker is overkill. A simple directory is enough to store messages.
- **📂 Portability**: Works on all operating systems that support PHP, making the transport easy to configure and deploy.
- **⚡ Low resource impact**: Avoids database overload (as with `Messenger Doctrine`), which can improve application performance.

## 🛠️ Configuration

### Activating the Bundle

To activate the bundle, you need to add it manually in the `config/bundles.php` file of your Symfony project:

```php
// config/bundles.php

return [
    // ...
    \AlphaSoft\Messenger\FilesystemTransport\AsMessengerFilesystemTransportBundle::class => ['all' => true],
];
```

Here is an example configuration for using `FilesystemTransport` :

```yaml
# config/packages/messenger.yaml
framework:
  messenger:
    failure_transport: failed

    transports:
      filesystem:
        dsn: 'filesystem://'
        options:
          directory: '%kernel.project_dir%/var/messages'
          log : false
      failed:
        dsn: 'filesystem://'
        options:
          directory: '%kernel.project_dir%/var/messages/failed'
```

In this example, messages will be stored in the `var/messages` directory of your Symfony project.

### Configuration Options

- **DSN**: The transport configuration is done via the `filesystem://` DSN.
- **Failed message directory**: Failed messages are automatically moved to a `failed/` subdirectory for easier management.

## 🔧 Usage

### Sending Messages

To send asynchronous messages, you can use the transport like any other Symfony Messenger transport:

```php
use Symfony\Component\Messenger\MessageBusInterface;

class NotificationService
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function sendNotification(NotificationMessage $message): void
    {
        $this->bus->dispatch($message);
    }
}
```

## 📄 Example of Processing Logs

The "log" option must be enabled:

```yaml
# config/packages/messenger.yaml
framework:
  messenger:
    failure_transport: failed

    transports:
      filesystem:
        dsn: 'filesystem://'
        options:
          directory: '%kernel.project_dir%/var/messages'
          log : true
      failed:
        dsn: 'filesystem://'
        options:
          directory: '%kernel.project_dir%/var/messages/failed'
```

When a message is processed successfully, a log entry is added in `processed.log`:

```json
{
    "timestamp": "2024-10-18 14:32:05",
    "message_id": "20241018_143205_123456",
    "message_type": "App\\Message\\NotificationMessage",
    "status": "processed"
}
```

In the event of a failed message, a similar entry is added in `failed.log` to facilitate error tracking and analysis.

## 🧪 Tests

To run unit tests, clone the repository and use [PHPUnit](https://phpunit.de/):

```bash
git clone https://github.com/alphasoft-fr/messenger-filesystem-transport.git
cd messenger-filesystem-transport
composer install
php vendor/bin/phpunit
```

## 📝 License

This project is licensed under the [MIT](LICENSE). You are free to use, modify, and redistribute it under the terms of this license.

## 🤝 Contribution

Contributions are welcome! If you want to propose improvements or report an issue, feel free to open an issue or submit a pull request.

1. Fork the project.
2. Create a branch for your feature (`git checkout -b feature/new-feature`).
3. Commit your changes (`git commit -am 'Add new feature'`).
4. Push your branch (`git push origin feature/new-feature`).
5. Open a Pull Request.

-----
## Français

`FilesystemTransport` est un transport pour [Symfony Messenger](https://symfony.com/doc/current/messenger.html) basé sur le système de fichiers. Il permet de gérer des messages asynchrones sans avoir recours à une base de données ou à un broker de message externe tel que RabbitMQ ou Redis. C'est une solution simple et légère, idéale pour des projets de petite à moyenne envergure.

## 📦 Installation

Pour installer le package via [Composer](https://getcomposer.org/), utilisez la commande suivante :

```bash
composer require alphasoft-fr/messenger-filesystem-transport
```

## 🚀 Fonctionnalités

- **Stockage des messages en fichiers** : Enregistre les messages dans un répertoire local pour une gestion simple et légère.
- **Pas de dépendance externe** : Pas besoin de RabbitMQ, Redis ou d'une base de données, ce qui réduit la complexité de l'infrastructure.
- **Facile à déboguer** : Les messages sont stockés sous forme de fichiers JSON, ce qui permet de les lire directement et de comprendre leur contenu.
- **Adapté aux environnements de développement et de test** : Idéal pour tester les tâches asynchrones dans des environnements où la mise en place de services supplémentaires n'est pas justifiée.

## 📚 Avantages

- **💡 Simplicité et configuration minimale** : Parfait pour les petits projets où la mise en place d'un broker de message est excessive. Un simple répertoire est suffisant pour stocker les messages.
- **📂 Portabilité** : Fonctionne sur tous les systèmes d'exploitation qui supportent le PHP, rendant le transport facile à configurer et à déployer.
- **⚡ Faible impact sur les ressources** : Évite la surcharge de la base de données (comme avec `Messenger Doctrine`), ce qui peut améliorer les performances de l'application.

## 🛠️ Configuration

### Activation du Bundle

Pour activer le bundle, vous devez l'ajouter manuellement dans le fichier `config/bundles.php` de votre projet Symfony :

```php
// config/bundles.php

return [
    // ...
    \AlphaSoft\Messenger\FilesystemTransport\AsMessengerFilesystemTransportBundle::class => ['all' => true],
];
```
Voici un exemple de configuration pour utiliser FilesystemTransport :

```yaml
# config/packages/messenger.yaml
framework:
  messenger:
    failure_transport: failed

    transports:
      filesystem:
        dsn: 'filesystem://'
        options:
          directory: '%kernel.project_dir%/var/messages'
          log : false
      failed:
        dsn: 'filesystem://'
        options:
          directory: '%kernel.project_dir%/var/messages/failed'
```

Dans cet exemple, les messages seront stockés dans le répertoire `var/messages` de votre projet Symfony.

### Options de Configuration

- **DSN** : La configuration du transport se fait via le DSN `filesystem://`
- **Répertoire des messages échoués** : Les messages échoués sont automatiquement déplacés dans un sous-répertoire `failed/` pour faciliter leur gestion.

## 🔧 Utilisation

### Envoi de messages

Pour envoyer des messages asynchrones, vous pouvez utiliser le transport comme n'importe quel autre transport de Symfony Messenger :

```php
use Symfony\Component\Messenger\MessageBusInterface;

class NotificationService
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function sendNotification(NotificationMessage $message): void
    {
        $this->bus->dispatch($message);
    }
}
```

## 📄 Exemple de Log de Traitement
L'option "log" doit etre activé :

```yaml
# config/packages/messenger.yaml
framework:
  messenger:
    failure_transport: failed

    transports:
      filesystem:
        dsn: 'filesystem://'
        options:
          directory: '%kernel.project_dir%/var/messages'
          log : true
      failed:
        dsn: 'filesystem://'
        options:
          directory: '%kernel.project_dir%/var/messages/failed'
```

Lorsqu'un message est traité avec succès, une entrée de log est ajoutée dans `processed.log`  :

```json
{
    "timestamp": "2024-10-18 14:32:05",
    "message_id": "20241018_143205_123456",
    "message_type": "App\\Message\\NotificationMessage",
    "status": "processed"
}
```

En cas de message échoué, une entrée similaire est ajoutée dans `failed.log` pour faciliter le suivi et l'analyse des erreurs.

## 🧪 Tests

Pour exécuter les tests unitaires, clonez le dépôt et utilisez [PHPUnit](https://phpunit.de/) :

```bash
git clone https://github.com/alphasoft-fr/messenger-filesystem-transport.git
cd messenger-filesystem-transport
composer install
php vendor/bin/phpunit
```

## 📝 Licence

Ce projet est sous licence [MIT](LICENSE). Vous êtes libre de l'utiliser, de le modifier et de le redistribuer sous les termes de cette licence.

## 🤝 Contribution

Les contributions sont les bienvenues ! Si vous souhaitez proposer des améliorations ou signaler un problème, n'hésitez pas à ouvrir une issue ou à soumettre une pull request.

1. Forkez le projet.
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/nouvelle-fonctionnalite`).
3. Commitez vos changements (`git commit -am 'Ajoute une nouvelle fonctionnalité'`).
4. Poussez votre branche (`git push origin feature/nouvelle-fonctionnalite`).
5. Ouvrez une Pull Request.
