<?php

namespace tomzx\Job;

class JobQueue
{
    /**
     * @var Job[]
     */
    private $queue = [];

    /**
     * @param \tomzx\Job\Job $job
     */
    public function push(Job $job)
    {
        $this->queue[] = $job;
    }

    /**
     * @return mixed|\tomzx\Job\Job
     */
    public function pop()
    {
        return array_shift($this->queue);
    }

    /**
     * @return \tomzx\Job\Job[]
     */
    public function all()
    {
        return $this->queue;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->queue);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->queue);
    }

    /**
     * @param \tomzx\Job\Job $job
     */
    public function remove(Job $job)
    {
        $index = array_search($job, $this->queue);
        if ($index !== false) {
            unset($this->queue[$index]);
        }
    }
}
