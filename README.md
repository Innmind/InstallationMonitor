# Installation Monitor

[![Build Status](https://github.com/Innmind/InstallationMonitor/workflows/CI/badge.svg)](https://github.com/Innmind/InstallationMonitor/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/Innmind/InstallationMonitor/branch/develop/graph/badge.svg)](https://codecov.io/gh/Innmind/InstallationMonitor)
[![Type Coverage](https://shepherd.dev/github/Innmind/InstallationMonitor/coverage.svg)](https://shepherd.dev/github/Innmind/InstallationMonitor)

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
use Innmind\Immutable\Map;

$client = bootstrap()['client']['ipc']();
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
