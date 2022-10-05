<?php

namespace SlmQueueRabbitMq\Options;

use Laminas\Stdlib\AbstractOptions;

class RabbitMqOptions extends AbstractOptions
{
    protected string $host;
    protected int $port;
    protected string $user;
    protected string $vhost;
    protected string $password;
    protected float $channelRpcTimeout;
    protected array $sslOptions = [];

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): RabbitMqOptions
    {
        $this->host = $host;

        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): RabbitMqOptions
    {
        $this->port = $port;

        return $this;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): RabbitMqOptions
    {
        $this->user = $user;

        return $this;
    }

    public function getVhost(): string
    {
        return $this->vhost;
    }

    public function setVhost(string $vhost): RabbitMqOptions
    {
        $this->vhost = $vhost;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): RabbitMqOptions
    {
        $this->password = $password;

        return $this;
    }

    public function getChannelRpcTimeout(): float
    {
        return $this->channelRpcTimeout;
    }

    public function setChannelRpcTimeout(float $channelRpcTimeout): RabbitMqOptions
    {
        $this->channelRpcTimeout = $channelRpcTimeout;

        return $this;
    }

    public function getSslOptions(): array
    {
        return $this->sslOptions;
    }

    public function setSslOptions(array $sslOptions): RabbitMqOptions
    {
        $this->sslOptions = $sslOptions;

        return $this;
    }

    /**
     * @return resource|null
     */
    public function getContext()
    {
        if ($this->sslOptions === []) {
            return null;
        }

        $sslContext = stream_context_create();
        foreach ($this->sslOptions as $key => $value) {
            stream_context_set_option($sslContext, 'ssl', $key, $value);
        }

        return $sslContext;
    }
}
