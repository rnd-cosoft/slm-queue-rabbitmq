<?php

namespace SlmQueueRabbitMq\Factory;

use Interop\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SlmQueue\Factory\WorkerFactory;
use SlmQueue\Strategy\StrategyPluginManager;
use SlmQueueRabbitMq\Job\MessageRetryCounter;
use SlmQueueRabbitMq\Worker\RabbitMqWorker;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\FactoryInterface;

class RabbitMqWorkerFactory extends WorkerFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     * @return RabbitMqWorker
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var EventManager $eventManager */
        $eventManager = $container->has('EventManager') ? $container->get('EventManager') : new EventManager();

        $slmConfig = $container->get('config')['slm_queue'];
        $allOptionsArray = $slmConfig['queues'];
        $strategies = $slmConfig['worker_strategies']['default'];

        $listenerPluginManager = $container->get(StrategyPluginManager::class);
        $this->attachWorkerListeners($eventManager, $listenerPluginManager, $strategies);

        $retryCounter = new MessageRetryCounter($allOptionsArray);

        /** @var array $config */
        $config = $container->get('SlmQueueRabbitMq\Config');

        $logger = new NullLogger();

        if ($container->has($config['logger'])) {
            /** @var LoggerInterface $logger */
            $logger = $container->get($config['logger']);
        }

        return new RabbitMqWorker($eventManager, $retryCounter, $logger);
    }
}
