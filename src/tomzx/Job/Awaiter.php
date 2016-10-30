<?php

namespace tomzx\Job;

use InvalidArgumentException;

class Awaiter
{
    /**
     * @var int
     */
    private $resolutionMicroSeconds;

    /**
     * @param int $resolutionMicroSeconds
     */
    public function __construct(int $resolutionMicroSeconds = 100000)
    {
        $this->resolutionMicroSeconds = $resolutionMicroSeconds;
    }

    /**
     * @param \tomzx\Job\JobQueue $queue
     * @return \tomzx\Job\JobQueue
     */
    public function all(JobQueue $queue): JobQueue
    {
        if ($queue->isEmpty()) {
            return $queue;
        }

        $jobs = $queue;
        while (true) {
            $jobsLeft = new JobQueue();
            foreach ($jobs->all() as $job) {
                if ( ! $job->isResolved()) {
                    $jobsLeft->push($job);
                }
            }

            if ($jobsLeft->isEmpty()) {
                return $queue;
            }

            $jobs = $jobsLeft;

            usleep($this->resolutionMicroSeconds);
        }
    }

    /**
     * @param \tomzx\Job\JobQueue $queue
     * @return \tomzx\Job\Job
     * @throws \InvalidArgumentException
     */
    public function any(JobQueue $queue): Job
    {
        if ($queue->isEmpty()) {
            throw new InvalidArgumentException('Cannot await any job when there are none.');
        }

        while (true) {
            foreach ($queue->all() as $job) {
                if ($job->isResolved()) {
                    return $job;
                }
            }

            usleep($this->resolutionMicroSeconds);
        }
    }

    /**
     * @param \tomzx\Job\JobQueue $queue
     * @param int $count
     * @return \tomzx\Job\JobQueue
     * @throws \InvalidArgumentException
     */
    public function some(JobQueue $queue, int $count): JobQueue
    {
        $jobs = $queue;
        $countJobsLeft = $jobs->count();

        if ($count > $countJobsLeft) {
            throw new InvalidArgumentException('Awaiting more jobs than available.');
        }

        $jobsCompleted = new JobQueue();
        while (true) {
            $jobsLeft = new JobQueue();
            foreach ($jobs->all() as $job) {
                if ($job->isResolved()) {
                    $jobsCompleted->push($job);
                    if ($jobsCompleted->count() === $count) {
                        break;
                    }
                } else {
                    $jobsLeft->push($job);
                }
            }

            if ($jobsCompleted->count() === $count) {
                return $jobsCompleted;
            }

            $jobs = $jobsLeft;

            usleep($this->resolutionMicroSeconds);
        }
    }
}
