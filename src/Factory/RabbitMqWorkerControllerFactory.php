<?php

namespace SlmQueueRabbitMq\Factory;

use SlmQueue\Queue\QueuePluginManager;
use SlmQueueRabbitMq\Controller\RabbitMqWorkerController;
use SlmQueueRabbitMq\Worker\RabbitMqWorker;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RabbitMqWorkerControllerFactory implements FactoryInterface
{
    /**
     * @inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $worker = $container->get(RabbitMqWorker::class);
        $queuePluginManager = $container->get(QueuePluginManager::class);

        return new RabbitMqWorkerController($worker, $queuePluginManager);
    }
}
