<?php

namespace SlmQueueRabbitMq\Connection;

use PhpAmqpLib\Connection\AMQPLazyConnection;

class Connection extends AMQPLazyConnection
{
    /** @var bool */
    private $connectionBlocked = false;

    /**
     * @inheritdoc
     */
    public function __construct(
        string $host,
        string $port,
        string $user,
        string $password,
        string $vhost = '/',
        bool $insist = false,
        string $login_method = 'AMQPLAIN',
        $login_response = null,
        string $locale = 'en_US',
        $connection_timeout = 3.0,
        $read_write_timeout = 3.0,
        $context = null,
        bool $keepalive = false,
        int $heartbeat = 0,
        $channel_rpc_timeout = 0.0,
        ?string $ssl_protocol = null
    ) {
        parent::__construct(
            $host,
            $port,
            $user,
            $password,
            $vhost,
            $insist,
            $login_method,
            $login_response,
            $locale,
            $connection_timeout,
            $read_write_timeout,
            $context,
            $keepalive,
            $heartbeat,
            $channel_rpc_timeout,
            $ssl_protocol
        );

        $this->set_connection_block_handler(function() {
            $this->connectionBlocked = true;
            throw new \Exception('connection.blocked is sent from the server');
        });
    }

    public function __destruct()
    {
        if ($this->connectionBlocked) {
            // If the connection is blocked from alarm we don't want to safeClose()
            // as it will wait for a timeout. RabbitMQ will clear up blocked connection
            // once it's alarms are taken care of.
            $this->set_close_on_destruct(false);
        }

        parent::__destruct();
    }
}
