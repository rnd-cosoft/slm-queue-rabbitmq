<?php

declare(strict_types=1);

namespace SlmQueueRabbitMq;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\DependencyIndicatorInterface;

class Module implements ConfigProviderInterface, DependencyIndicatorInterface
{
    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * @inheritDoc
     */
    public function getModuleDependencies()
    {
        return ['SlmQueue'];
    }
}
