<?php

declare(strict_types=1);

namespace SlmQueueRabbitMq\Job;

use PhpAmqpLib\Wire\AMQPTable;
use SlmQueue\Job\JobInterface;

use function current;

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

    public function canRetry(JobInterface $job, string $queueName): bool
    {
        return $this->isRetryEnabled($queueName) && ! $this->isRetryLimitReached($job, $queueName);
    }

    private function isRetryLimitReached(JobInterface $job, string $queueName): bool
    {
        $options = $this->getQueueOptions($queueName);

        return $this->getCountOfDeath($job->getMetadata()) >= (int) $options['retry_limit'];
    }

    private function isRetryEnabled(string $queueName): bool
    {
        return $this->getQueueOptions($queueName)['retry_limit'] > 0;
    }

    /**
     * @param array $metadata
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
     */
    private function getMessageHeaders(array $metadata): ?AMQPTable
    {
        if (! empty($metadata['application_headers'])) {
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
        if ($headers && ! empty($headers->getNativeData()['x-death'])) {
            $deathInfo = $headers->getNativeData()['x-death'];
        }

        return $deathInfo;
    }

    /**
     * @return array
     */
    private function getQueueOptions(string $queueName): array
    {
        return $this->options[$queueName]['options'];
    }
}
