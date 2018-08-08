<?php

namespace SlmQueueRabbitMq\Queue;

use SlmQueue\Job\JobInterface;
use SlmQueue\Queue\QueueInterface;

interface RabbitMqQueueInterface extends QueueInterface
{
    /**
     * Put a job that was popped back to the queue
     *
     * @param  JobInterface $job
     * @param  array        $options
     * @return void
     */
    public function release(JobInterface $job, array $options = []);

    /**
     * Bury a job. When a job is buried, it won't be retrieved from the queue
     *
     * @param  JobInterface $job
     * @param  array        $options
     * @return void
     */
    public function bury(JobInterface $job, array $options = []);
}
