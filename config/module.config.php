<?php

use SlmQueueRabbitMq\ConfigFactory;
use SlmQueueRabbitMq\Controller\RabbitMqWorkerController;
use SlmQueueRabbitMq\Factory\RabbitMqWorkerControllerFactory;
use SlmQueueRabbitMq\Strategy\IdleNapStrategy;
use SlmQueueRabbitMq\Worker\RabbitMqWorker;
use SlmQueueRabbitMq\Factory\RabbitMqWorkerFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'slm_queue' => [
        'worker_strategies' => [
            'default' => [
                IdleNapStrategy::class => ['nap_duration' => 1],
            ],
            'queues' => [
            ],
        ],
        'strategy_manager' => [
            'factories' => [
                IdleNapStrategy::class => InvokableFactory::class,
            ],
        ],
        'worker_manager' => [
            'factories' => [
                RabbitMqWorker::class => RabbitMqWorkerFactory::class,
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            RabbitMqWorker::class => RabbitMqWorkerFactory::class,
            'SlmQueueRabbitMq\Config' => ConfigFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            RabbitMqWorkerController::class => RabbitMqWorkerControllerFactory::class,
        ],
    ],
    'console' => [
        'router' => [
            'routes' => [
                'slm-queue-rabbit_mq-worker' => [
                    'type' => 'Simple',
                    'options' => [
                        'route' => 'queue <queue> --start',
                        'defaults' => [
                            'controller' => RabbitMqWorkerController::class,
                            'action' => 'process',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'slm-queue-rabbitmq' => [
        //Set a logger accessible via service manager for logging exceptions that happened during job execution
        'logger' => 'MvLogger\Errors',
        'default_message_options' => [
            // see PhpAmqpLib\Message\AMQPMessage::propertyDefinitions message array for possible options
//            'delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ],
    ],
];
