<?php

use Cooper\CanalClient\CanalClient;
use Cooper\CanalClient\CanalConnectorFactory;
use Cooper\CanalClient\Fmt;

require_once __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', 'On');
error_reporting(E_ALL);

try {
    // $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET_CLUE);
    $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);

    $client->connect("127.0.0.1", 11111);
    $client->subscribe("1001", "example", ".*\\..*");
    # $client->subscribe("1001", "example", "db_name.tb_name"); # 设置过滤

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