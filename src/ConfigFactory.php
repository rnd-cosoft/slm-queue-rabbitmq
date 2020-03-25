<?php

namespace SlmQueueRabbitMq;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ConfigFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     * @return array
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $container->get('config')['slm-queue-rabbitmq'];
    }
}
