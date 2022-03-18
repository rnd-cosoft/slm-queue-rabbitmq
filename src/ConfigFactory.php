<?php

declare(strict_types=1);

namespace SlmQueueRabbitMq;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ConfigFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return $container->get('config')['slm-queue-rabbitmq'];
    }
}
