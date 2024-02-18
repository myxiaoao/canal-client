<?php

namespace Cooper\CanalClient\Adapter\Socket;

use RuntimeException;

class TcpClient
{
    /**
     * Remote hostname
     *
     * @var string
     */
    protected string $host_ = 'localhost';
    /**
     * Remote port
     *
     * @var int
     */
    protected int $port_ = 9090;
    /**
     * Handle to PHP socket
     *
     * @var resource
     */
    private $handle_ = null;
    /**
     * Send timeout in milliseconds
     *
     * @var int
     */
    private int $sendTimeout = 1000000;

    /**
     * Recv timeout in milliseconds
     *
     * @var int
     */
    private int $recvTimeout = 1000000;

    /**
     * connect time out in second
     * @var int
     */
    private int $connectTimeout = 10;

    /**
     * Is send timeout set?
     *
     * @var bool
     */
    private bool $sendTimeoutSet_ = false;

    /**
     * Persistent socket or plain?
     *
     * @var bool
     */
    private bool $persist_;

    /**
     * Socket constructor
     *
     * @param string $host
     *            Remote hostname
     * @param int $port
     *            Remote port
     * @param bool $persist
     *            Whether to use a persistent socket
     */
    public function __construct(string $host = 'localhost', int $port = 9090, bool $persist = false)
    {
        $this->host_ = $host;
        $this->port_ = $port;
        $this->persist_ = $persist;
    }

    /**
     *
     * @param resource $handle
     * @return void
     */
    public function setHandle($handle): void
    {
        $this->handle_ = $handle;
    }

    /**
     * Sets the send timeout.
     *
     * @param int $timeout
     *            Timeout in milliseconds.
     */
    public function setSendTimeout(int $timeout): void
    {
        $this->sendTimeout = $timeout;
    }

    /**
     * Sets the receive timeout.
     *
     * @param int $timeout
     *            Timeout in milliseconds.
     */
    public function setRecvTimeout(int $timeout): void
    {
        $this->recvTimeout = $timeout;
    }

    /**
     * @return int
     */
    public function getConnectTimeout(): int
    {
        return $this->connectTimeout;
    }

    /**
     * @param $connectTimeout
     *            Timeout in seconds.
     */
    public function setConnectTimeout($connectTimeout): void
    {
        $this->connectTimeout = $connectTimeout;
    }

    /**
     * Get the host that this socket is connected to
     *
     * @return string host
     */
    public function getHost(): string
    {
        return $this->host_;
    }

    /**
     * Get the remote port that this socket is connected to
     *
     * @return int port
     */
    public function getPort(): int
    {
        return $this->port_;
    }

    /**
     * Connects the socket.
     *
     * @throws RuntimeException
     */
    public function open(): void
    {
        if ($this->isOpen()) {
            throw new RuntimeException('Socket already connected');
        }

        if (empty($this->host_)) {
            throw new RuntimeException('Cannot open null host');
        }

        if ($this->port_ <= 0) {
            throw new RuntimeException('Cannot open without port');
        }

        if ($this->persist_) {
            $this->handle_ = @pfsockopen($this->host_, $this->port_, $errno, $errstr, $this->connectTimeout);
        } else {
            $this->handle_ = @fsockopen($this->host_, $this->port_, $errno, $errstr, $this->connectTimeout);
        }

        // Connect failed?
        if ($this->handle_ === false) {
            $error = 'Socket: Could not connect to ' . $this->host_ . ':' . $this->port_ . ' (' . $errstr . ' [' . $errno . '])';
            throw new RuntimeException($error, 10);
        }

        stream_set_timeout($this->handle_, 0, $this->sendTimeout);
        $this->sendTimeoutSet_ = true;
    }

    /**
     * Tests whether this is open
     *
     * @return bool true if the socket is open
     */
    public function isOpen(): bool
    {
        return is_resource($this->handle_);
    }

    /**
     * Closes the socket.
     */
    public function close(): void
    {
        if (! $this->persist_) {
            @fclose($this->handle_);
            $this->handle_ = null;
        }
    }

    /**
     * Uses stream get contents to do the reading
     *
     * @param int $len How many bytes
     * @return string|null Binary data
     * @throws RuntimeException
     */
    public function read(int $len): ?string
    {
        if ($this->sendTimeoutSet_) {
            stream_set_timeout($this->handle_, 0, $this->recvTimeout * 1000000);
            $this->sendTimeoutSet_ = false;
        }
        // This call does not obey stream_set_timeout values!
        // $buf = @stream_get_contents($this->handle_, $len);

        $pre = null;
        while (true) {
            $buf = @fread($this->handle_, $len);
            if ($buf === false || $buf === '') {
                $md = stream_get_meta_data($this->handle_);
                if ($md['timed_out']) {
                    throw new RuntimeException(
                        'TSocket: timed out reading ' . $len . ' bytes from ' . $this->host_ . ':' . $this->port_
                    );
                }

                throw new RuntimeException(
                    'TSocket: Could not read ' . $len . ' bytes from ' . $this->host_ . ':' . $this->port_
                );
            }

            if (($sz = strlen($buf)) < $len) {
                $md = stream_get_meta_data($this->handle_);
                if ($md['timed_out']) {
                    throw new RuntimeException(
                        'TSocket: timed out reading ' . $len . ' bytes from ' . $this->host_ . ':' . $this->port_
                    );
                }

                $pre .= $buf;
                $len -= $sz;
            } else {
                return $pre . $buf;
            }
        }
    }

    /**
     * @param bool|string $buf Binary data
     * @throws RuntimeException
     */
    public function write(bool|string $buf): void
    {
        if (! $this->sendTimeoutSet_) {
            stream_set_timeout($this->handle_, 0, $this->sendTimeout);
            $this->sendTimeoutSet_ = true;
        }
        while ($buf !== '') {
            $got = @fwrite($this->handle_, $buf);
            if ($got === 0 || $got === false) {
                $md = stream_get_meta_data($this->handle_);
                if ($md['timed_out']) {
                    throw new RuntimeException(
                        'TSocket: timed out writing ' . strlen(
                            $buf
                        ) . ' bytes from ' . $this->host_ . ':' . $this->port_
                    );
                }

                throw new RuntimeException(
                    'TSocket: Could not write ' . strlen($buf) . ' bytes ' . $this->host_ . ':' . $this->port_
                );
            }
            $buf = substr($buf, $got);
        }
    }

    /**
     * Flush output to the socket.
     * @throws RuntimeException
     */
    public function flush(): void
    {
        $ret = fflush($this->handle_);
        if ($ret === false) {
            throw new RuntimeException('TSocket: Could not flush: ' . $this->host_ . ':' . $this->port_);
        }
    }
}
