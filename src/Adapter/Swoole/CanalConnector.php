<?php

namespace Cooper\CanalClient\Adapter\Swoole;

use Cooper\CanalClient\Adapter\BaseCanalConnector;
use RuntimeException;
use Swoole\Client;

class CanalConnector extends BaseCanalConnector
{
    /** @var Client */
    protected mixed $client;

    /**
     * @param string $host
     * @param int $port
     * @param int $connectionTimeout
     * @param int $readTimeout
     * @param int $writeTimeout
     * @throws RuntimeException
     */
    protected function doConnect(
        string $host = "127.0.0.1",
        int $port = 11111,
        int $connectionTimeout = 10,
        int $readTimeout = 30,
        int $writeTimeout = 30
    ): void {
        $this->client = new Client(SWOOLE_SOCK_TCP);
        if (! $this->client->connect($host, $port, $connectionTimeout)) {
            throw new RuntimeException("swoole connect failed. Error: {$this->client->errCode}");
        }
    }

    /**
     * @return string
     */
    protected function readNextPacket(): string
    {
        $data = $this->client->recv($this->packetLen);
        $dataLen = unpack("N", $data)[1];

        return $this->client->recv($dataLen, true);
    }

    /**
     * @param string $data
     * @return void
     */
    protected function writeWithHeader(string $data): void
    {
        $this->client->send(pack("N", strlen($data)));
        $this->client->send($data);
    }
}
