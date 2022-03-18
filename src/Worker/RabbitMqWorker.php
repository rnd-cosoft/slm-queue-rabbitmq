<?php

declare(strict_types=1);

namespace SlmQueueRabbitMq\Worker;

use Exception;
use Laminas\EventManager\EventManagerInterface;
use Psr\Log\LoggerInterface;
use SlmQueue\Job\JobInterface;
use SlmQueue\Queue\QueueInterface;
use SlmQueue\Worker\AbstractWorker;
use SlmQueue\Worker\Event\ProcessJobEvent;
use SlmQueueRabbitMq\Job\MessageRetryCounter;
use SlmQueueRabbitMq\Queue\RabbitMqQueueInterface;
use Throwable;

class RabbitMqWorker extends AbstractWorker
{
    /** @var MessageRetryCounter */
    private $retryCounter;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        EventManagerInterface $eventManager,
        MessageRetryCounter $retryCounter,
        LoggerInterface $logger
    ) {
        parent::__construct($eventManager);

        $this->retryCounter = $retryCounter;
        $this->logger       = $logger;
    }

    /**
     * @inheritDoc
     * @return int
     */
    public function processJob(JobInterface $job, QueueInterface $queue): int
    {
        if (! $queue instanceof RabbitMqQueueInterface) {
            return ProcessJobEvent::JOB_STATUS_FAILURE;
        }

        try {
            $job->execute();
            $queue->delete($job);

            return ProcessJobEvent::JOB_STATUS_SUCCESS;
        } catch (Throwable $exception) {
            if ($this->retryCounter->canRetry($job, $queue->getName())) {
                $queue->bury($job);
                $this->logger->warning($exception->getMessage(), $this->createExceptionParams($exception));

                return ProcessJobEvent::JOB_STATUS_FAILURE_RECOVERABLE;
            }

            $queue->delete($job);
            $this->logger->error($exception->getMessage(), $this->createExceptionParams($exception));

            return ProcessJobEvent::JOB_STATUS_FAILURE;
        }
    }

    /**
     * @return array
     */
    public function createExceptionParams(Throwable $exception): array
    {
        return [
            'exception'   => new Exception($exception->getMessage(), $exception->getCode(), $exception),
            'stack_trace' => $exception->getTraceAsString(),
        ];
    }
}
