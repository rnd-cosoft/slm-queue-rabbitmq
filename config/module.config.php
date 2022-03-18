<?php

declare(strict_types=1);

use Laminas\ServiceManager\Factory\InvokableFactory;
use SlmQueueRabbitMq\ConfigFactory;
use SlmQueueRabbitMq\Factory\RabbitMqWorkerFactory;
use SlmQueueRabbitMq\Strategy\IdleNapStrategy;
use SlmQueueRabbitMq\Worker\RabbitMqWorker;

return [
    'slm_queue'          => [
        'worker_strategies' => [
            'default' => [
                IdleNapStrategy::class => ['nap_duration' => 1],
            ],
            'queues'  => [],
        ],
        'strategy_manager'  => [
            'factories' => [
                IdleNapStrategy::class => InvokableFactory::class,
            ],
        ],
        'worker_manager'    => [
            'factories' => [
                RabbitMqWorker::class => RabbitMqWorkerFactory::class,
            ],
        ],
    ],
    'service_manager'    => [
        'factories' => [
            'SlmQueueRabbitMq\Config' => ConfigFactory::class,
        ],
    ],
    'slm-queue-rabbitmq' => [
        //Set a logger accessible via service manager for logging exceptions that happened during job execution
        'logger'                  => 'MvLogger\Errors',
        'default_message_options' => [
            // see PhpAmqpLib\Message\AMQPMessage::propertyDefinitions message array for possible options
//            'delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ],
    ],
];
