<?php

namespace Cooper\CanalClient\Adapter\Socket;

use Cooper\CanalClient\Adapter\BaseCanalConnector;
use RuntimeException;

class CanalConnector extends BaseCanalConnector
{
    /** @var TcpClient */
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
        $this->client = new TcpClient($host, $port, true);
        $this->client->setConnectTimeout($connectionTimeout);
        $this->client->setRecvTimeout($readTimeout);
        $this->client->setSendTimeout($writeTimeout);
        $this->client->open();
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    protected function readNextPacket(): string
    {
        $data = $this->client->read($this->packetLen);
        $dataLen = unpack("N", $data)[1];

        return $this->client->read($dataLen);
    }

    /**
     * @param string $data
     * @throws RuntimeException
     */
    protected function writeWithHeader(string $data): void
    {
        $this->client->write(pack("N", strlen($data)));
        $this->client->write($data);
    }
}
