<?php

namespace SlmQueueRabbitMqTest\Queue;

use PhpAmqpLib\Channel\AMQPChannel as Channel;
use PhpAmqpLib\Connection\AbstractConnection as Connection;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SlmQueue\Job\JobInterface;
use SlmQueue\Job\JobPluginManager;
use PhpAmqpLib\Message\AMQPMessage;
use SlmQueueRabbitMq\Queue\RabbitMqQueue;

class RabbitMqQueueTest extends TestCase
{
    /** @var RabbitMqQueue */
    private $rabbitMqQueue;

    /** @var Connection|MockObject */
    private $connection;

    /** @var JobPluginManager|MockObject */
    private $jobPluginManager;

    /** @var Channel|MockObject $channel */
    private $channel;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->jobPluginManager = $this->createMock(JobPluginManager::class);

        $this->channel = $this->createMock(Channel::class);

        $this->connection->expects($this->once())->method('channel')->willReturn($this->channel);

        $this->rabbitMqQueue = new RabbitMqQueue(
            $this->connection,
            'some_exchange_or_queue_name',
            $this->jobPluginManager,
            []
        );
    }

    public function testChannelIsTheSame(): void
    {
        $job = $this->createJobMock(123, []);

        $this->rabbitMqQueue->pop();

        $this->rabbitMqQueue->delete($job);
    }

    public function testPushWithDefaultOptions(): void
    {
        $this->channel->expects($this->once())->method('basic_publish')->with($this->callback(
            function (AMQPMessage $message) {
                $this->assertSame(1, $message->get('delivery_mode'));

                return true;
            })
        );


        $id = 123;
        $content = [
            'some_key' => 'some_value',
        ];
        $job = $this->createJobMock($id, $content);

        $this->rabbitMqQueue = new RabbitMqQueue(
            $this->connection,
            'some_exchange_or_queue_name',
            $this->jobPluginManager,
            [
                'delivery_mode' => 1,
            ]
        );
        $this->rabbitMqQueue->push(
            $job,
            [
                'application_headers' => [
                    'target_application' => 'some_target_application',
                ],
            ]
        );
    }

    public function testPush(): void
    {
        $job = $this->createJobMock(123, [
            'some_key' => 'some_value',
        ]);

        /** @var AMQPMessage|MockObject $message */
        $message = $this->createMock(AMQPMessage::class);
        $message->method('getBody')->willReturn(
            '{"some_key":"some_value"}'
        );

        $this->channel->expects($this->once())->method('basic_publish')->willReturnCallback(
            function (
                AMQPMessage $msg,
                string $queueName
            ) use ($message) {
                $this->assertEquals(
                    '{"content":"a:1:{s:8:\"some_key\";s:10:\"some_value\";}","metadata":null}',
                    $msg->getBody(),
                    'Message does not match'
                );
                $this->assertEquals('some_exchange_or_queue_name', $queueName, 'Queue name does not match');
                $this->assertEquals(
                    ['application_headers' => new AMQPTable(['target_application' => 'some_target_application'])],
                    $msg->get_properties(),
                    'Message headers do not match'
                );
            }
        );

        $this->rabbitMqQueue->push(
            $job,
            [
                'application_headers' => [
                    'target_application' => 'some_target_application',
                ],
            ]
        );
    }

    public function testPopWhenNoMessageInQueue(): void
    {
        $this->channel->method('basic_get');

        $this->assertNull($this->rabbitMqQueue->pop());
    }

    public function testPop(): void
    {
        $message = $this->createMessageMock();
        $message->method('get_properties')->willReturn(['some_headers' => ['some_data',],]);

        /** @var JobInterface|MockObject $job */
        $job = $this->createMock(JobInterface::class);
        $job->method('getMetadata')->willReturn(['__name__' => 'some_job_class_fqn',]);
        $job->expects($this->exactly(2))->method('setMetadata')->withConsecutive(
            [['__name__' => 'some_job_class_fqn',],],
            [[
                '__name__' => 'some_job_class_fqn',
                'some_headers' => ['some_data',],
            ],]
        );

        $this->jobPluginManager->method('get')->willReturn($job);

        $this->channel->method('basic_get')->with('some_exchange_or_queue_name', false, false)->willReturn($message);

        $this->assertSame($job, $this->rabbitMqQueue->pop());
    }

    public function testDelete(): void
    {
        $job = $this->createJobMock(123, []);

        $this->channel->expects($this->once())->method('basic_ack')->with(123);

        $this->rabbitMqQueue->delete($job);
    }

    public function testRelease(): void
    {
        $rabbitMqQueue = new RabbitMqQueue(
            $this->connection,
            'some_exchange_or_queue_name',
            $this->jobPluginManager,
            []
        );

        $job = $this->createJobMock(123, []);

        $this->channel->expects($this->once())->method('basic_reject')->with(123, true);

        $rabbitMqQueue->release($job);
    }

    public function testBury(): void
    {
        $rabbitMqQueue = new RabbitMqQueue(
            $this->connection,
            'some_exchange_or_queue_name',
            $this->jobPluginManager,
            []
        );

        $this->channel->expects($this->once())->method('basic_reject')->with(123, false);

        $job = $this->createJobMock(123 ,[]);

        $rabbitMqQueue->bury($job);
    }

    /**
     * @return AMQPMessage|MockObject
     * @throws \ReflectionException
     */
    private function createMessageMock()
    {
        /** @var AMQPMessage|MockObject $message */
        $message = $this->createMock(AMQPMessage::class);
        $message->method('getBody')->willReturn(
            '{"content":"a:1:{s:8:\"some_key\";s:10:\"some_value\";}","metadata":{"__name__":"some_job_class_fqn"}}'
        );

        return $message;
    }

    /**
     * @param int $id
     * @param array $content
     * @return MockObject|JobInterface
     */
    private function createJobMock(int $id, array $content): JobInterface
    {
        /** @var JobInterface|MockObject $job */
        $job = $this->createMock(JobInterface::class);
        $job->method('getId')->willReturn($id);
        $job->method('getContent')->willReturn($content);

        return $job;
    }
}
