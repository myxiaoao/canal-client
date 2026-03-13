# Canal Client

PHP 客户端，用于连接 [Alibaba Canal](https://github.com/alibaba/canal/wiki) — MySQL binlog 增量订阅与消费中间件。

Fork 自 [canal-php](https://github.com/xingwenge/canal-php)，支持 PHP 8.0+ 和 Swoole 5.0+。

## 环境要求

- PHP >= 8.0
- ext-sockets
- [google/protobuf](https://packagist.org/packages/google/protobuf) ^3.8

可选：
- [ext-swoole](https://www.swoole.com/) — 使用 Swoole 适配器时需要
- [clue/socket-raw](https://packagist.org/packages/clue/socket-raw) — 使用 Clue socket 适配器时需要

## 安装

```shell
composer require cooper/canal-client
```

## 适配器类型

| 类型 | 常量 | 说明 |
|------|------|------|
| 原生 Socket | `CanalClient::TYPE_SOCKET` | PHP 原生 socket（ext-sockets） |
| Swoole | `CanalClient::TYPE_SWOOLE` | Swoole 协程客户端 |
| Clue Socket | `CanalClient::TYPE_SOCKET_CLUE` | clue/socket-raw 库 |

## 使用示例

```php
<?php

use Cooper\CanalClient\CanalClient;
use Cooper\CanalClient\CanalConnectorFactory;
use Cooper\CanalClient\Fmt;

require_once __DIR__ . '/vendor/autoload.php';

try {
    // 创建客户端（选择适配器类型）
    $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET);
    // $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);
    // $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET_CLUE);

    // 连接 Canal 服务端
    $client->connect("127.0.0.1", 11111);

    // 订阅变更（clientId, destination, filter）
    $client->subscribe("1001", "example", ".*\\..*");
    // $client->subscribe("1001", "example", "db_name.tb_name"); // 指定库表过滤

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

- `createClient(int $clientType): BaseCanalConnector` — 创建连接器实例

### BaseCanalConnector

- `connect(string $host, int $port, string $user, string $password, ...)` — 连接 Canal 服务端
- `subscribe(string $clientId, string $destination, string $filter)` — 订阅 binlog 变更
- `unSubscribe()` — 取消订阅
- `get(int $size)` — 获取消息（自动 ack）
- `getWithoutAck(int $batchSize, int $timeout, int $unit)` — 获取消息（不自动 ack）
- `ack(mixed $messageId)` — 确认消息
- `rollback(int $batchId)` — 回滚
- `disConnect()` — 断开连接

### Fmt

- `Fmt::println(Entry $entry)` — 将 binlog entry 输出到标准输出

## 测试

```shell
composer test
```

## 许可证

[MIT](LICENSE)
