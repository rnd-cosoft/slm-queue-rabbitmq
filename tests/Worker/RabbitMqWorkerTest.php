<?php

namespace SlmQueueRabbitMqTest\Worker;

use Exception;
use Laminas\EventManager\EventManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SlmQueue\Job\JobInterface;
use SlmQueue\Queue\QueueInterface;
use SlmQueue\Worker\Event\ProcessJobEvent;
use SlmQueueRabbitMq\Job\MessageRetryCounter;
use SlmQueueRabbitMq\Queue\RabbitMqQueueInterface;
use SlmQueueRabbitMq\Worker\RabbitMqWorker;
use TypeError;

class RabbitMqWorkerTest extends TestCase
{
    private RabbitMqWorker $rabbitMqWorker;

    private MockObject $logger;
    private MockObject $messageRetryCounter;
    private MockObject $job;
    private MockObject $queue;

    protected function setUp(): void
    {
        $eventManager = $this->createMock(EventManagerInterface::class);
        $this->messageRetryCounter = $this->createMock(MessageRetryCounter::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->job = $this->createMock(JobInterface::class);
        $this->queue = $this->createMock(RabbitMqQueueInterface::class);

        $this->rabbitMqWorker = new RabbitMqWorker($eventManager, $this->messageRetryCounter, $this->logger);
    }

    public function testProcessJobWhenQueueInterfaceIsNotRabbitMq(): void
    {
        $queue = $this->createMock(QueueInterface::class);

        $this->assertSame(
            ProcessJobEvent::JOB_STATUS_FAILURE,
            $this->rabbitMqWorker->processJob($this->job, $queue)
        );
    }

    public function testProcessJobWhenSuccess(): void
    {
        $this->job->expects($this->once())->method('execute');
        $this->queue->expects($this->once())->method('delete');
        $this->queue->method('getName')->willReturn('some_queue_name');

        $this->assertSame(
            ProcessJobEvent::JOB_STATUS_SUCCESS,
            $this->rabbitMqWorker->processJob($this->job, $this->queue)
        );
    }

    public function testProcessJobWhenCannotRetry(): void
    {
        $this->job->method('execute')->willThrowException(new Exception());
        $this->logger->expects($this->once())->method('error');

        $this->queue->expects($this->once())->method('delete');
        $this->queue->method('getName')->willReturn('some_queue_name');

        $this->assertSame(
            ProcessJobEvent::JOB_STATUS_FAILURE,
            $this->rabbitMqWorker->processJob($this->job, $this->queue)
        );
    }

    public function testProcessJobWhenCanRetry(): void
    {
        $originalException = new TypeError('some error');

        $this->job->method('execute')->willThrowException($originalException);
        $this->logger->expects($this->once())->method('warning')->with(
            'some error',
            ['exception' => $originalException],
        );

        $this->messageRetryCounter->method('canRetry')->willReturn(true);
        $this->queue->expects($this->once())->method('bury');
        $this->queue->method('getName')->willReturn('some_queue_name');

        $this->assertSame(
            ProcessJobEvent::JOB_STATUS_FAILURE_RECOVERABLE,
            $this->rabbitMqWorker->processJob($this->job, $this->queue)
        );
    }
}
