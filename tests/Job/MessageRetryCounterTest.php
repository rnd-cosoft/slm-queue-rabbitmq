<?php

namespace SlmQueueRabbitMqTest\Job;

use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SlmQueue\Job\JobInterface;
use SlmQueueRabbitMq\Job\MessageRetryCounter;

class MessageRetryCounterTest extends TestCase
{
    /** @var MessageRetryCounter */
    private $messageRetryCounter;

    protected function setUp()
    {
        $this->messageRetryCounter = new MessageRetryCounter([
            'some_queue_name' => [
                'options' => [
                    'retry_limit' => 2,
                ],
            ],
        ]);
    }

    public function testCanRetryWhenNoMessageHeaders()
    {
        $job = $this->createJobMock([]);

        $this->assertTrue($this->messageRetryCounter->canRetry($job, 'some_queue_name'));
    }

    public function testCanRetryWhenMessageIsNotDeadYet()
    {
        /** @var AMQPTable|MockObject $headers */
        $headers = $this->createMock(AMQPTable::class);

        $job = $this->createJobMock(['application_headers' => $headers]);

        $this->assertTrue($this->messageRetryCounter->canRetry($job, 'some_queue_name'));
    }

    public function testCanRetryWhenMessageIsDeadFirstTime()
    {
        $headers = new AMQPTable(['x-death' => [['count' => 1,],],]);

        $job = $this->createJobMock(['application_headers' => $headers]);

        $this->assertTrue($this->messageRetryCounter->canRetry($job, 'some_queue_name'));
    }

    public function testCanRetryWhenRetryLimitReached()
    {
        $headers = new AMQPTable(['x-death' => [['count' => 2,],],]);

        $job = $this->createJobMock(['application_headers' => $headers]);

        $this->assertFalse($this->messageRetryCounter->canRetry($job, 'some_queue_name'));
    }

    public function testCanRetryWhenRetryNotEnabled()
    {
        $job = $this->createJobMock([]);

        $this->assertFalse((new MessageRetryCounter([
            'some_queue_name' => [
                'options' => [
                    'retry_limit' => 0,
                ],
            ],
        ]))->canRetry($job, 'some_queue_name'));
    }

    /**
     * @param array $headers
     * @return JobInterface|MockObject
     * @throws \ReflectionException
     */
    private function createJobMock(array $headers)
    {
        /** @var JobInterface|MockObject $job */
        $job = $this->createMock(JobInterface::class);
        $job->method('getMetadata')->willReturn(array_merge(
            ['__name__' => 'some_job_class_fqn',],
            $headers
        ));

        return $job;
    }
}
