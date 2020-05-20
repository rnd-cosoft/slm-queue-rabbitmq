<?php

namespace SlmQueueRabbitMq\Queue;

use PhpAmqpLib\Channel\AMQPChannel as Channel;
use PhpAmqpLib\Connection\AbstractConnection as Connection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use SlmQueue\Job\JobInterface;
use SlmQueue\Job\JobPluginManager;
use SlmQueue\Queue\AbstractQueue;

class RabbitMqQueue extends AbstractQueue implements RabbitMqQueueInterface
{
    /** @var Channel */
    private $channel;

    /** @var Connection */
    private $connection;

    /** @var array */
    private $defaultMessageOptions;

    /**
     * @param Connection $connection
     * @param string $name
     * @param JobPluginManager $jobPluginManager
     * @param array $defaultMessageOptions
     */
    public function __construct(
        Connection $connection,
        $name,
        JobPluginManager $jobPluginManager,
        array $defaultMessageOptions
    ) {
        $this->connection = $connection;
        $this->defaultMessageOptions = $defaultMessageOptions;

        parent::__construct($name, $jobPluginManager);
    }

    public function __destruct()
    {
        if ($this->channel) {
            $this->channel->close();
        }
    }

    /**
     * @inheritdoc
     */
    public function push(JobInterface $job, array $options = []): void
    {
        $options = array_merge($this->defaultMessageOptions, $options);
        $message = new AMQPMessage($this->serializeJob($job), $options);

        if (isset($options['application_headers'])) {
            $message->set('application_headers', new AMQPTable($options['application_headers']));
        }

        $this->getChannel()->basic_publish($message, $this->getName());
    }

    /**
     * @inheritdoc
     * @return JobInterface|null
     */
    public function pop(array $options = []): ?JobInterface
    {
        $prefetchSize = null;
        $prefetchCount = 1;
        $aGlobal = null;

        $this->getChannel()->basic_qos($prefetchSize, $prefetchCount, $aGlobal);

        $message = $this->getChannel()->basic_get($this->getName());
        if ($message instanceof AMQPMessage) {
            $job = $this->unserializeJob($message->getBody());

            $metadata = array_merge($job->getMetadata(), $message->get_properties());
            $job->setMetadata($metadata);

            $job->setId($message->get('delivery_tag'));

            return $job;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function delete(JobInterface $job): void
    {
        $this->getChannel()->basic_ack($job->getId());
    }

    /**
     * @inheritdoc
     */
    public function release(JobInterface $job, array $options = [])
    {
        $this->getChannel()->basic_reject($job->getId(), true);
    }

    /**
     * @inheritdoc
     */
    public function bury(JobInterface $job, array $options = [])
    {
        $this->getChannel()->basic_reject($job->getId(), false);
    }

    /**
     * @return Channel
     */
    private function getChannel(): Channel
    {
        if (!$this->channel) {
            $this->channel = $this->connection->channel();
        }

        return $this->channel;
    }
}
