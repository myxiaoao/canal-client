# Canal Client

[中文文档](README.zh-CN.md)

PHP client for [Alibaba Canal](https://github.com/alibaba/canal/wiki) — MySQL binlog incremental subscription and consumption middleware.

Forked from [canal-php](https://github.com/xingwenge/canal-php), with PHP 8.0+ and Swoole 5.0+ support.

## Requirements

- PHP >= 8.0
- ext-sockets
- [google/protobuf](https://packagist.org/packages/google/protobuf) ^3.8

Optional:
- [ext-swoole](https://www.swoole.com/) — for Swoole adapter
- [clue/socket-raw](https://packagist.org/packages/clue/socket-raw) — for Clue socket adapter

## Installation

```shell
composer require cooper/canal-client
```

## Adapter Types

| Type | Constant | Description |
|------|----------|-------------|
| Native Socket | `CanalClient::TYPE_SOCKET` | PHP native socket (ext-sockets) |
| Swoole | `CanalClient::TYPE_SWOOLE` | Swoole coroutine client |
| Clue Socket | `CanalClient::TYPE_SOCKET_CLUE` | clue/socket-raw library |

## Usage

```php
<?php

use Cooper\CanalClient\CanalClient;
use Cooper\CanalClient\CanalConnectorFactory;
use Cooper\CanalClient\Fmt;

require_once __DIR__ . '/vendor/autoload.php';

try {
    // Create client (choose adapter type)
    $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET);
    // $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);
    // $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET_CLUE);

    // Connect to Canal server
    $client->connect("127.0.0.1", 11111);

    // Subscribe to changes (clientId, destination, filter)
    $client->subscribe("1001", "example", ".*\\..*");
    // $client->subscribe("1001", "example", "db_name.tb_name");

    while (true) {
        $message = $client->get(100);
        if ($entries = $message->getEntries()) {
            foreach ($entries as $entry) {
                Fmt::println($entry);
            }
        }
        sleep(1);
    }
} catch (\Exception $e) {
    echo $e->getMessage(), PHP_EOL;
}
```

## API

### CanalConnectorFactory

- `createClient(int $clientType): BaseCanalConnector` — Create a connector instance

### BaseCanalConnector

- `connect(string $host, int $port, string $user, string $password, ...)` — Connect to Canal server
- `subscribe(string $clientId, string $destination, string $filter)` — Subscribe to binlog changes
- `unSubscribe()` — Unsubscribe
- `get(int $size)` — Get messages with auto ack
- `getWithoutAck(int $batchSize, int $timeout, int $unit)` — Get messages without ack
- `ack(mixed $messageId)` — Acknowledge message
- `rollback(int $batchId)` — Rollback
- `disConnect()` — Disconnect

### Fmt

- `Fmt::println(Entry $entry)` — Print binlog entry to stdout

## Testing

```shell
composer test
```

## License

[MIT](LICENSE)
