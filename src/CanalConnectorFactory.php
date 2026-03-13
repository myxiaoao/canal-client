<?php

namespace Cooper\CanalClient;

use Cooper\CanalClient\Adapter\BaseCanalConnector;
use Cooper\CanalClient\Adapter\Clue\CanalConnector as ClueCanalConnector;
use Cooper\CanalClient\Adapter\Socket\CanalConnector as SocketCanalConnector;
use Cooper\CanalClient\Adapter\Swoole\CanalConnector as SwooleCanalConnector;
use RuntimeException;

class CanalConnectorFactory
{
    public static function createClient(int $clientType): BaseCanalConnector
    {
        return match ($clientType) {
            CanalClient::TYPE_SOCKET => new SocketCanalConnector(),
            CanalClient::TYPE_SWOOLE => self::createSwooleConnector(),
            CanalClient::TYPE_SOCKET_CLUE => self::createClueConnector(),
            default => throw new RuntimeException("Unknown client type"),
        };
    }

    private static function createSwooleConnector(): SwooleCanalConnector
    {
        if (!extension_loaded('swoole')) {
            throw new RuntimeException('ext-swoole is required for Swoole adapter. Install it via: pecl install swoole');
        }

        return new SwooleCanalConnector();
    }

    private static function createClueConnector(): ClueCanalConnector
    {
        if (!class_exists(\Socket\Raw\Factory::class)) {
            throw new RuntimeException('clue/socket-raw is required for Clue adapter. Install it via: composer require clue/socket-raw');
        }

        return new ClueCanalConnector();
    }
}
