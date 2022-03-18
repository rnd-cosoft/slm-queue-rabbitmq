<?php

declare(strict_types=1);

namespace SlmQueueRabbitMq\Strategy;

use Laminas\EventManager\EventManagerInterface;
use SlmQueue\Strategy\AbstractStrategy;
use SlmQueue\Worker\Event\AbstractWorkerEvent;
use SlmQueue\Worker\Event\ProcessIdleEvent;
use SlmQueueRabbitMq\Queue\RabbitMqQueueInterface;

use function sleep;

class IdleNapStrategy extends AbstractStrategy
{
    /**
     * How long should we sleep when the worker is idle before trying again
     *
     * @var int
     */
    protected $napDuration = 1;

    /**
     * @param int $napDuration
     */
    public function setNapDuration($napDuration)
    {
        $this->napDuration = (int) $napDuration;
    }

    /**
     * @return int
     */
    public function getNapDuration()
    {
        return $this->napDuration;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            AbstractWorkerEvent::EVENT_PROCESS_IDLE,
            [$this, 'onIdle'],
            1
        );
    }

    public function onIdle(ProcessIdleEvent $event)
    {
        $queue = $event->getQueue();

        if ($queue instanceof RabbitMqQueueInterface) {
            sleep($this->napDuration);
        }
    }
}
