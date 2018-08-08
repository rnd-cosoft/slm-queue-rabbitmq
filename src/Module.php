<?php

namespace SlmQueueRabbitMq;

use Zend\Console\Adapter\AdapterInterface;
use Zend\ModuleManager\Feature;

class Module implements
    Feature\ConfigProviderInterface,
    Feature\ConsoleUsageProviderInterface,
    Feature\DependencyIndicatorInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        return [
            'queue rabbitmq <queueName> --start' => 'Process the jobs',

            ['<queueName>', 'Queue\'s name to process'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getModuleDependencies()
    {
        return ['SlmQueue'];
    }
}
