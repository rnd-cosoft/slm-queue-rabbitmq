<?php

namespace SlmQueueRabbitMq;

use Laminas\ModuleManager\Feature;
use Laminas\Console\Adapter\AdapterInterface;

class Module implements
    Feature\ConfigProviderInterface,
    Feature\ConsoleUsageProviderInterface,
    Feature\DependencyIndicatorInterface
{
    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getConsoleUsage(AdapterInterface $console)
    {
        return [
            'queue <queueName> --start' => 'Process the jobs',

            ['<queueName>', 'Queue\'s name to process'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getModuleDependencies()
    {
        return ['SlmQueue'];
    }
}
