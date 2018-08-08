<?php

namespace SlmQueueRabbitMq\Job;

use PhpAmqpLib\Wire\AMQPTable;
use SlmQueue\Job\JobInterface;

class MessageRetryCounter
{
    /** @var array */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param JobInterface $job
     * @param string $queueName
     * @return bool
     */
    public function canRetry(JobInterface $job, string $queueName): bool
    {
        return $this->isRetryEnabled($queueName) && !$this->isRetryLimitReached($job, $queueName);
    }

    /**
     * @param JobInterface $job
     * @param string $queueName
     * @return bool
     */
    private function isRetryLimitReached(JobInterface $job, string $queueName): bool
    {
        $options = $this->getQueueOptions($queueName);

        return $this->getCountOfDeath($job->getMetadata()) >= (int)$options['retry_limit'];
    }

    /**
     * @param string $queueName
     * @return bool
     */
    private function isRetryEnabled(string $queueName): bool
    {
        return $this->getQueueOptions($queueName)['retry_limit'] > 0;
    }

    /**
     * @param array $metadata
     * @return int
     */
    private function getCountOfDeath(array $metadata): int
    {
        $deathInfo = $this->getMessageDeathInfo($metadata);
        if ($deathInfo) {
            return current($deathInfo)['count'];
        }

        return 0;
    }

    /**
     * @param array $metadata
     * @return null|AMQPTable
     */
    private function getMessageHeaders(array $metadata): ?AMQPTable
    {
        if (!empty($metadata['application_headers'])) {
            return $metadata['application_headers'];
        }

        return null;
    }

    /**
     * @param array $metadata
     * @return array|null
     */
    private function getMessageDeathInfo(array $metadata): ?array
    {
        $deathInfo = null;

        $headers = $this->getMessageHeaders($metadata);
        if ($headers && !empty($headers->getNativeData()['x-death'])) {
            $deathInfo = $headers->getNativeData()['x-death'];
        }

        return $deathInfo;
    }

    /**
     * @param string $queueName
     * @return array
     */
    private function getQueueOptions(string $queueName): array
    {
        return $this->options[$queueName]['options'];
    }
}
