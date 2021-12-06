<?php

namespace SlmQueueRabbitMq;

use Laminas\ModuleManager\Feature;

class Module implements
    Feature\ConfigProviderInterface,
    Feature\DependencyIndicatorInterface
{
    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * @inheritdoc
     */
    public function getModuleDependencies()
    {
        return ['SlmQueue'];
    }
}
