<?php

namespace Cooper\CanalClient\Adapter;

use Com\Alibaba\Otter\Canal\Protocol\Ack;
use Com\Alibaba\Otter\Canal\Protocol\ClientAck;
use Com\Alibaba\Otter\Canal\Protocol\ClientAuth;
use Com\Alibaba\Otter\Canal\Protocol\ClientRollback;
use Com\Alibaba\Otter\Canal\Protocol\Entry;
use Com\Alibaba\Otter\Canal\Protocol\Get;
use Com\Alibaba\Otter\Canal\Protocol\Handshake;
use Com\Alibaba\Otter\Canal\Protocol\Messages;
use Com\Alibaba\Otter\Canal\Protocol\Packet;
use Com\Alibaba\Otter\Canal\Protocol\PacketType;
use Com\Alibaba\Otter\Canal\Protocol\Sub;
use Cooper\CanalClient\ICanalConnector;
use Cooper\CanalClient\Message;
use Exception;
use RuntimeException;

abstract class BaseCanalConnector implements ICanalConnector
{
    /** @var mixed */
    protected mixed $client;
    /** @var int */
    protected int $readTimeout;
    /** @var int */
    protected int $writeTimeout;
    /** @var int */
    protected int $packetLen = 4;
    /** @var string */
    protected string $destination;
    /** @var int */
    protected int $clientId;

    /**
     * @throws Exception
     */
    public function __destruct()
    {
        $this->disConnect();
    }

    /**
     * @return void
     */
    public function disConnect(): void
    {
        if ($this->client) {
            $this->rollback(0);
        }
    }

    /**
     * @param int $batchId
     * @return void
     */
    public function rollback(int $batchId = 0): void
    {
        $cb = new ClientRollback();
        $cb->setBatchId($batchId);
        $cb->setClientId($this->clientId);
        $cb->setDestination($this->destination);

        $packet = new Packet();
        $packet->setType(PacketType::CLIENTROLLBACK);
        $packet->setBody($cb->serializeToString());

        $this->writeWithHeader($packet->serializeToString());
    }

    /**
     * @param string $data
     * @return void
     */
    abstract protected function writeWithHeader(string $data): void;

    /**
     * @param string $host
     * @param int $port
     * @param string $user
     * @param string $password
     * @param int $connectionTimeout
     * @param int $readTimeout
     * @param int $writeTimeout
     * @throws Exception
     */
    public function connect(
        string $host = "127.0.0.1",
        int $port = 11111,
        string $user = "",
        string $password = "",
        int $connectionTimeout = 10,
        int $readTimeout = 30,
        int $writeTimeout = 30
    ): void {
        $this->doConnect($host, $port, $connectionTimeout, $readTimeout, $writeTimeout);

        $this->readTimeout = $readTimeout;
        $this->writeTimeout = $writeTimeout;

        $data = $this->readNextPacket();
        $packet = new Packet();
        $packet->mergeFromString($data);

        // 密码需要通过握手包返回的 seed 进行哈希
        if ($user && $password) {
            $handShake = new Handshake();
            $handShake->mergeFromString($packet->getBody());
            $password = bin2hex($this->scramble411($password, $handShake->getSeeds()));
        }
        $this->checkValid($user, $password);

        if ($packet->getType() !== PacketType::HANDSHAKE) {
            throw new RuntimeException("connect error.");
        }
    }

    /**
     * @param string $host
     * @param int $port
     * @param int $connectionTimeout
     * @param int $readTimeout
     * @param int $writeTimeout
     * @return void
     */
    abstract protected function doConnect(
        string $host = "127.0.0.1",
        int $port = 11111,
        int $connectionTimeout = 10,
        int $readTimeout = 30,
        int $writeTimeout = 30
    ): void;

    /**
     * @return string
     */
    abstract protected function readNextPacket(): string;

    /**
     * @param string $password
     * @param string $seed
     * @return false|string
     */
    public function scramble411(string $password, string $seed): bool|string
    {
        $pwd1 = sha1($password, true);
        $pwd2 = sha1($pwd1, true);
        $pwd3 = sha1($seed . $pwd2, true);

        $pwd1Bytes = $this->stringToBytes($pwd1);
        $pwd3Bytes = $this->stringToBytes($pwd3);
        foreach ($pwd3Bytes as $key => $pwd3Byte) {
            $pwd3Bytes[$key] ^= $pwd1Bytes[$key];
        }

        return $this->bytesToString($pwd3Bytes);
    }

    /**
     * @param string $string
     * @return array|false
     */
    public function stringToBytes(string $string): bool|array
    {
        return unpack("C*", $string);
    }

    /**
     * @param array $bytes
     * @return false|string
     */
    public function bytesToString(array $bytes): bool|string
    {
        return pack("C*", ...$bytes);
    }

    /**
     * @param string $username
     * @param string $password
     * @throws Exception
     */
    public function checkValid(string $username = "", string $password = ""): void
    {
        $ca = new ClientAuth();
        $ca->setUsername($username);
        $ca->setPassword($password);
        $ca->setNetReadTimeout($this->readTimeout * 1000);
        $ca->setNetWriteTimeout($this->writeTimeout * 1000);

        $packet = new Packet();
        $packet->setType(PacketType::CLIENTAUTHENTICATION);
        $packet->setBody($ca->serializeToString());
        $this->writeWithHeader($packet->serializeToString());

        $data = $this->readNextPacket();
        $packet = new Packet();
        $packet->mergeFromString($data);
        if ($packet->getType() !== PacketType::ACK) {
            throw new RuntimeException("Auth error.");
        }
        $ack = new Ack();
        $ack->mergeFromString($packet->getBody());
        if ($ack->getErrorCode() > 0) {
            throw new RuntimeException(
                sprintf(
                    "something goes wrong when doing authentication. error code:%s, error message:%s",
                    $ack->getErrorCode(),
                    $ack->getErrorMessage()
                )
            );
        }
    }

    /**
     * @param int $clientId
     * @param string $destination
     * @param string $filter
     * @throws Exception
     */
    public function subscribe(int $clientId = 1001, string $destination = "example", string $filter = ".*\\..*"): void
    {
        $this->clientId = $clientId;
        $this->destination = $destination;

        $this->rollback(0);

        $sub = new Sub();
        $sub->setDestination($this->destination);
        $sub->setClientId($this->clientId);
        $sub->setFilter($filter);

        $packet = new Packet();
        $packet->setType(PacketType::SUBSCRIPTION);
        $packet->setBody($sub->serializeToString());
        $this->writeWithHeader($packet->serializeToString());

        $data = $this->readNextPacket();
        $packet = new Packet();
        $packet->mergeFromString($data);

        if ($packet->getType() !== PacketType::ACK) {
            throw new RuntimeException("Subscribe error.");
        }

        $ack = new Ack();
        $ack->mergeFromString($packet->getBody());
        if ($ack->getErrorCode() > 0) {
            throw new RuntimeException(
                sprintf(
                    "Failed to subscribe. error code:%s, error message:%s",
                    $ack->getErrorCode(),
                    $ack->getErrorMessage()
                )
            );
        }
    }

    /**
     * @return void
     */
    public function unSubscribe(): void
    {
        // TODO: Implement unSubscribe() method.
    }

    /**
     * @param int $size
     * @return Message
     * @throws Exception
     */
    public function get(int $size = 100): Message
    {
        $message = $this->getWithoutAck($size);

        $this->ack($message->getId());


        return $message;
    }

    /**
     * @param int $batchSize
     * @param int $timeout
     * @param int $unit
     * @return Message
     * @throws Exception
     */
    public function getWithoutAck(int $batchSize = 10, int $timeout = -1, int $unit = -1): Message
    {
        $get = new Get();
        $get->setClientId($this->clientId);
        $get->setDestination($this->destination);
        $get->setAutoAck(false);
        $get->setFetchSize($batchSize);
        $get->setTimeout($timeout);
        $get->setUnit($unit);

        $packet = new Packet();
        $packet->setType(PacketType::GET);
        $packet->setBody($get->serializeToString());

        $this->writeWithHeader($packet->serializeToString());

        $data = $this->readNextPacket();
        $packet = new Packet();
        $packet->mergeFromString($data);

        $message = new Message();

        switch ($packet->getType()) {
            case PacketType::MESSAGES:
                $messages = new Messages();
                $messages->mergeFromString($packet->getBody());

                if ($messages->getBatchId() > 0) {
                    $message->setId($messages->getBatchId());

                    foreach ($messages->getMessages()->getIterator() as $v) {
                        $entry = new Entry();
                        $entry->mergeFromString($v);
                        $message->addEntries($entry);
                    }
                }

                break;
            case PacketType::ACK:
                $ack = new Ack();
                $ack->mergeFromString($packet->getBody());
                if ($ack->getErrorCode() > 0) {
                    throw new RuntimeException(
                        sprintf(
                            "get data error. error code:%s, error message:%s",
                            $ack->getErrorCode(),
                            $ack->getErrorMessage()
                        )
                    );
                }
                break;
            default:
                throw new RuntimeException(sprintf("unexpected packet type:%s", $packet->getType()));
        }

        return $message;
    }

    /**
     * @param int $messageId
     * @return void
     */
    public function ack(mixed $messageId = 0): void
    {
        if ($messageId) {
            $clientAck = new ClientAck();
            $clientAck->setDestination($this->destination);
            $clientAck->setClientId($this->clientId);
            $clientAck->setBatchId($messageId);

            $packet = new Packet();
            $packet->setType(PacketType::CLIENTACK);
            $packet->setBody($clientAck->serializeToString());

            $this->writeWithHeader($packet->serializeToString());
        }
    }
}