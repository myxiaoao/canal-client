<?php

namespace Cooper\CanalClient;

interface ICanalConnector
{
    public function connect(
        string $host = "127.0.0.1",
        int $port = 11111,
        string $user = "",
        string $password = "",
        int $connectionTimeout = 10,
        int $readTimeout = 30,
        int $writeTimeout = 30
    ): void;

    public function disConnect(): void;

    public function subscribe(string $clientId = '1001', string $destination = "example", string $filter = ".*\\..*"): void;

    public function unSubscribe(): void;

    public function get(int $size = 100): Message;

    public function getWithoutAck(int $batchSize = 10, int $timeout = -1, int $unit = -1): Message;

    public function ack(int $messageId = 0): void;

    public function rollback(int $batchId = 0): void;
}
