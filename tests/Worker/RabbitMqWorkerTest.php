<?php

namespace SlmQueueRabbitMqTest\Worker;

use TypeError;
use Exception;
use Throwable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SlmQueue\Job\JobInterface;
use SlmQueue\Queue\QueueInterface;
use SlmQueue\Worker\Event\ProcessJobEvent;
use SlmQueueRabbitMq\Job\MessageRetryCounter;
use SlmQueueRabbitMq\Queue\RabbitMqQueueInterface;
use SlmQueueRabbitMq\Worker\RabbitMqWorker;
use Laminas\EventManager\EventManagerInterface;

class RabbitMqWorkerTest extends TestCase
{
    /** @var RabbitMqWorker */
    private $rabbitMqWorker;

    /** @var EventManagerInterface|MockObject */
    private $eventManager;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var MessageRetryCounter|MockObject */
    private $messageRetryCounter;

    protected function setUp(): void
    {
        $this->eventManager = $this->createMock(EventManagerInterface::class);
        $this->messageRetryCounter = $this->createMock(MessageRetryCounter::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->rabbitMqWorker = new RabbitMqWorker($this->eventManager, $this->messageRetryCounter, $this->logger);
    }

    public function testProcessJobWhenQueueInterfaceIsNotRabbitMq()
    {
        /** @var JobInterface|MockObject $job */
        $job = $this->createMock(JobInterface::class);

        /** @var QueueInterface|MockObject $queue */
        $queue = $this->createMock(QueueInterface::class);

        $this->assertSame(
            ProcessJobEvent::JOB_STATUS_FAILURE,
            $this->rabbitMqWorker->processJob($job, $queue)
        );
    }

    public function testProcessJobWhenSuccess()
    {
        /** @var JobInterface|MockObject $job */
        $job = $this->createMock(JobInterface::class);
        /** @var QueueInterface|MockObject $queue */
        $queue = $this->createMock(RabbitMqQueueInterface::class);

        $job->expects($this->once())->method('execute');
        $queue->expects($this->once())->method('delete');
        $queue->method('getName')->willReturn('some_queue_name');

        $this->assertEquals(
            ProcessJobEvent::JOB_STATUS_SUCCESS,
            $this->rabbitMqWorker->processJob($job, $queue)
        );
    }

    public function testProcessJobWhenCannotRetry()
    {
        /** @var JobInterface|MockObject $job */
        $job = $this->createMock(JobInterface::class);
        $job->method('execute')->willThrowException(new \Exception());
        $this->logger->expects($this->once())->method('error');
        /** @var QueueInterface|MockObject $queue */
        $queue = $this->createMock(RabbitMqQueueInterface::class);

        $queue->expects($this->once())->method('delete');
        $queue->method('getName')->willReturn('some_queue_name');

        $this->assertEquals(
            ProcessJobEvent::JOB_STATUS_FAILURE,
            $this->rabbitMqWorker->processJob($job, $queue)
        );
    }

    public function testProcessJobWhenCanRetry()
    {
        $originalException = new TypeError('some error');

        /** @var JobInterface|MockObject $job */
        $job = $this->createMock(JobInterface::class);
        $job->method('execute')->will($this->throwException($originalException));
        $this->logger->expects($this->once())->method('warning')->willReturnCallback(
            function ($message, array $context = array()) use ($originalException)
            {
                $this->assertInstanceOf(Exception::class, $context['exception'], 'exception is of correct class type');
                /** @var Throwable $exception */
                $exception = $context['exception'];
                $this->assertEquals('some error', $exception->getMessage(), 'exception has original message');
                $this->assertEquals(0, $exception->getCode(), 'exception has original error code');
                $this->assertEquals($originalException, $exception->getPrevious(), 'exception has original exception');
            }
        );

        /** @var QueueInterface|MockObject $queue */
        $queue = $this->createMock(RabbitMqQueueInterface::class);
        $this->messageRetryCounter->method('canRetry')->willReturn(true);
        $queue->expects($this->once())->method('bury');
        $queue->method('getName')->willReturn('some_queue_name');

        $this->assertEquals(
            ProcessJobEvent::JOB_STATUS_FAILURE_RECOVERABLE,
            $this->rabbitMqWorker->processJob($job, $queue)
        );
    }
}
