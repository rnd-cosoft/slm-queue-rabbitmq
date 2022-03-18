<?php

declare(strict_types=1);

namespace SlmQueueRabbitMq\Options;

use Laminas\Stdlib\AbstractOptions;

class RabbitMqOptions extends AbstractOptions
{
    /** @var string */
    protected $host;

    /** @var int */
    protected $port;

    /** @var string */
    protected $user;

    /** @var string */
    protected $vhost;

    /** @var string */
    protected $password;

    /** @var float */
    protected $channelRpcTimeout;

    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return $this
     */
    public function setHost(string $host)
    {
        $this->host = $host;

        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return $this
     */
    public function setPort(int $port)
    {
        $this->port = $port;

        return $this;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return $this
     */
    public function setUser(string $user)
    {
        $this->user = $user;

        return $this;
    }

    public function getVhost(): string
    {
        return $this->vhost;
    }

    /**
     * @return $this
     */
    public function setVhost(string $vhost)
    {
        $this->vhost = $vhost;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return $this
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param float $channelRpcTimeout
     * @return $this
     */
    public function setChannelRpcTimeout($channelRpcTimeout)
    {
        $this->channelRpcTimeout = $channelRpcTimeout;

        return $this;
    }

    /**
     * @return float
     */
    public function getChannelRpcTimeout()
    {
        return $this->channelRpcTimeout;
    }
}
