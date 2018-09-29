# Installation Monitor

| `master` | `develop` |
|----------|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/InstallationMonitor/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/InstallationMonitor/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/InstallationMonitor/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/InstallationMonitor/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/InstallationMonitor/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/InstallationMonitor/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/InstallationMonitor/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/InstallationMonitor/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/InstallationMonitor/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/InstallationMonitor/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/Innmind/InstallationMonitor/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/InstallationMonitor/build-status/develop) |

Tool to listen and redispatch events from/to other applications.

This is useful to let applications configure themselves when installing them. Take for example an application B that depends on an application A, A can emit an event to this tool and when the installation of B starts it can ask this tool to send it all the events it has recorded.

## Installation

```sh
composer require innmind/installation-monitor
```

## Usage

First step is to start the server that will aggregate the events:

```sh
installation-monitor oversee --daemon
```

Then from your application you can send an event like so :

```php
use function Innmind\InstallationMonitor\bootstrap;
use Innmind\InstallationMonitor\Event;
use Innmind\Socket\Address\Unix as Address;
use Innmind\Immutable\Map;

$client = bootstrap()['client']['socket'](
    new Address($pathToInstallationMonitor.'/var/server')
);
$client->send(
    new Event(
        new Event\Name('foo'),
        $payload = new Map('string', 'variable')
    ),
    new Event(
        new Event\Name('bar'),
        $payload = new Map('string', 'variable')
    )
    // etc...
);
// or
$client->events(); // Stream<Event> all the events recorded by the server
```
