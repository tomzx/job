<?php

namespace tomzx\Job;

abstract class Job
{
    /**
     * @return mixed
     */
    public abstract function handle();

    /**
     * @return bool
     */
    public function isResolved(): bool
    {
        return true;
    }
}
