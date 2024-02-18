<?php

namespace Cooper\CanalClient\Adapter\Clue;

use Cooper\CanalClient\Adapter\BaseCanalConnector;
use Socket\Raw\Factory;
use Socket\Raw\Socket;

class CanalConnector extends BaseCanalConnector
{
    /** @var Socket */
    protected mixed $client;

    /**
     * @param string $host
     * @param int $port
     * @param int $connectionTimeout
     * @param int $readTimeout
     * @param int $writeTimeout
     */
    protected function doConnect(
        string $host = "127.0.0.1",
        int $port = 11111,
        int $connectionTimeout = 10,
        int $readTimeout = 30,
        int $writeTimeout = 30
    ): void {
        $this->client = (new Factory())
            ->createClient(sprintf("tcp://%s:%s", $host, $port), $connectionTimeout);
    }

    /**
     * @return string
     */
    protected function readNextPacket(): string
    {
        $data = $this->client->read($this->packetLen);
        $dataLen = unpack("N", $data)[1];

        return $this->client->recv($dataLen, MSG_WAITALL);
    }

    /**
     * @param string $data
     * @return void
     */
    protected function writeWithHeader(string $data): void
    {
        $this->client->write(pack("N", strlen($data)));
        $this->client->write($data);
    }
}
