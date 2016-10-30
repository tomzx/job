<?php

namespace tomzx\Job;

use Evenement\EventEmitter;

class Throttler
{
    /**
     * @var int
     */
    private $maxCount;
    /**
     * @var \tomzx\Job\JobQueue
     */
    private $waitingJobs;
    /**
     * @var \tomzx\Job\JobQueue
     */
    private $runningJobs;
    /**
     * @var \tomzx\Job\JobQueue
     */
    private $completedJobs;
    /**
     * @var \tomzx\Job\JobQueue
     */
    private $eventEmitter;

    /**
     * @param int $maxCount
     * @param int $resolutionMicroSeconds
     */
    public function __construct(int $maxCount, int $resolutionMicroSeconds = 100000)
    {
        $this->maxCount = $maxCount;
        $this->waitingJobs = new JobQueue();
        $this->runningJobs = new JobQueue();
        $this->completedJobs = new JobQueue();
        $this->eventEmitter = new EventEmitter();
        $this->awaiter = new Awaiter($resolutionMicroSeconds);
    }

    /**
     * @param \tomzx\Job\Job $job
     */
    public function push(Job $job)
    {
        $this->waitingJobs->push($job);

        $this->eventEmitter->emit('job.waiting', [$job]);
    }

    /**
     * @param \tomzx\Job\Job $job
     */
    public function pushAndStart(Job $job)
    {
        $this->push($job);

        $this->tryStart();
    }

    public function wait()
    {
        while ( ! $this->runningJobs->isEmpty() || ! $this->waitingJobs->isEmpty()) {
            $this->tryStart();

            if ($this->runningJobsIsFull() || $this->waitingJobs->isEmpty()) {
                $completedJob = $this->awaiter->any($this->runningJobs);
                $this->runningJobs->remove($completedJob);
                $this->completedJobs->push($completedJob);

                $this->eventEmitter->emit('job.completed', [$completedJob]);
            }
        }
    }

    /**
     * @return \tomzx\Job\JobQueue
     */
    public function getCompletedJobs()
    {
        return $this->completedJobs;
    }

    /**
     * @param callable $callback
     */
    public function onJobWaiting(callable $callback)
    {
        $this->eventEmitter->on('job.waiting', $callback);
    }

    /**
     * @param callable $callback
     */
    public function onJobRunning(callable $callback)
    {
        $this->eventEmitter->on('job.running', $callback);
    }

    /**
     * @param callable $callback
     */
    public function onJobCompleted(callable $callback)
    {
        $this->eventEmitter->on('job.completed', $callback);
    }

    /**
     * @return \tomzx\Job\Job|void
     */
    private function tryStart()
    {
        if ($this->waitingJobs->isEmpty()) {
            return;
        }

        if ($this->runningJobsIsFull()) {
            return;
        }

        $job = $this->waitingJobs->pop();
        $job->handle();
        $this->runningJobs->push($job);

        $this->eventEmitter->emit('job.running', [$job]);

        return $job;
    }

    /**
     * @return bool
     */
    private function runningJobsIsFull()
    {
        return $this->runningJobs->count() >= $this->maxCount;
    }
}
