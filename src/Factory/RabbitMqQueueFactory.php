<?php

declare(strict_types=1);

namespace SlmQueueRabbitMq\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use SlmQueue\Job\JobPluginManager;
use SlmQueueRabbitMq\Connection\Connection;
use SlmQueueRabbitMq\Options\RabbitMqOptions;
use SlmQueueRabbitMq\Queue\RabbitMqQueue;

class RabbitMqQueueFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return RabbitMqQueue
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $connection            = $this->createConnection($container, $requestedName);
        $jobPluginManager      = $container->get(JobPluginManager::class);
        $defaultMessageOptions = $container->get('SlmQueueRabbitMq\Config')['default_message_options'];

        return new RabbitMqQueue($connection, $requestedName, $jobPluginManager, $defaultMessageOptions);
    }

    protected function createConnection(ContainerInterface $container, string $queueName): Connection
    {
        $allOptionsArray   = $container->get('config')['slm_queue']['queues'];
        $queueOptionsArray = $allOptionsArray[$queueName];
        $options           = new RabbitMqOptions($queueOptionsArray['connection']);

        return new Connection(
            $options->getHost(),
            $options->getPort(),
            $options->getUser(),
            $options->getPassword(),
            $options->getVhost(),
            false,
            'AMQPLAIN',
            'null',
            'en_US',
            3.0,
            3.0,
            null,
            false,
            0,
            $options->getChannelRpcTimeout(),
            null
        );
    }
}
