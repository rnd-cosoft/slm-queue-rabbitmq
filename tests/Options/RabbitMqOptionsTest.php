<?php declare(strict_types = 1);

namespace SlmQueueRabbitMqTest\Options;

use PHPUnit\Framework\TestCase;
use SlmQueueRabbitMq\Options\RabbitMqOptions;

class RabbitMqOptionsTest extends TestCase
{
    public function testGet(): void
    {
        $options = [
            'vhost' => '/',
            'port' => 5671,
            'host' => 'my.host.com',
            'user' => 'admin',
            'password' => 'password',
            'channel_rpc_timeout' => 3,
        ];

        $rabbitMqOptions = new RabbitMqOptions($options);

        $this->assertSame('my.host.com', $rabbitMqOptions->getHost());
        $this->assertSame(5671, $rabbitMqOptions->getPort());
        $this->assertSame('admin', $rabbitMqOptions->getUser());
        $this->assertSame('password', $rabbitMqOptions->getPassword());
        $this->assertSame('/', $rabbitMqOptions->getVhost());
        $this->assertSame(3.0, $rabbitMqOptions->getChannelRpcTimeout());
        $this->assertNull($rabbitMqOptions->getContext());
        $this->assertSame([], $rabbitMqOptions->getSslOptions());
    }

    public function testGetWithSslContext(): void
    {
        $options = [
            'ssl_options' => [
                'ssl_on' => true,
            ],
        ];

        $rabbitMqOptions = new RabbitMqOptions($options);

        $this->assertIsResource($rabbitMqOptions->getContext());
        $this->assertSame(['ssl_on' => true], $rabbitMqOptions->getSslOptions());
    }
}
