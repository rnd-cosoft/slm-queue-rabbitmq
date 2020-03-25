<?php

namespace SlmQueueRabbitMq\Factory;

use SlmQueue\Job\JobPluginManager;
use SlmQueueRabbitMq\Connection\Connection;
use SlmQueueRabbitMq\Queue\RabbitMqQueue;
use SlmQueueRabbitMq\Options\RabbitMqOptions;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RabbitMqQueueFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return RabbitMqQueue
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $connection = $this->createConnection($container, $requestedName);
        $jobPluginManager = $container->get(JobPluginManager::class);
        $defaultMessageOptions = $container->get('SlmQueueRabbitMq\Config')['default_message_options'];

        return new RabbitMqQueue($connection, $requestedName, $jobPluginManager, $defaultMessageOptions);
    }

    /**
     * @param ContainerInterface $container
     * @param string $queueName
     * @return Connection
     */
    protected function createConnection(ContainerInterface $container, string $queueName): Connection
    {
        $allOptionsArray = $container->get('config')['slm_queue']['queues'];
        $queueOptionsArray = $allOptionsArray[$queueName];
        $options = new RabbitMqOptions($queueOptionsArray['connection']);

        return new Connection(
            $options->getHost(),
            $options->getPort(),
            $options->getUser(),
            $options->getPassword(),
            $options->getVhost()
        );
    }

    /**
     * @inheritdoc
     * @return RabbitMqQueue
     */
    public function createService(ServiceLocatorInterface $serviceLocator, $canonicalName = null, $requestedName = null)
    {
        return $this($serviceLocator->getServiceLocator(), $requestedName);
    }
}
