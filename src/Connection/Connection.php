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
    public function __construct(string $host, string $port, string $user, string $password, string $vhost = '/')
    {
        parent::__construct($host, $port, $user, $password, $vhost);

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
