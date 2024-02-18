<?php

namespace Cooper\CanalClient;

use Cooper\CanalClient\Adapter\BaseCanalConnector;
use Cooper\CanalClient\Adapter\Clue\CanalConnector as ClueCanalConnector;
use Cooper\CanalClient\Adapter\Socket\CanalConnector as SocketCanalConnector;
use Cooper\CanalClient\Adapter\Swoole\CanalConnector as SwooleCanalConnector;
use RuntimeException;

class CanalConnectorFactory
{
    /**
     * @param int $clientType
     * @return BaseCanalConnector
     */
    public static function createClient(int $clientType
    ): BaseCanalConnector {
        return match ($clientType) {
            CanalClient::TYPE_SOCKET => new SocketCanalConnector(),
            CanalClient::TYPE_SWOOLE => new SwooleCanalConnector(),
            CanalClient::TYPE_SOCKET_CLUE => new ClueCanalConnector(),
            default => throw new RuntimeException("Unknown client type"),
        };
    }
}