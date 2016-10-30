<?php

namespace tomzx\Job\Tests;

use tomzx\Job\Awaiter;
use tomzx\Job\Job;
use tomzx\Job\JobQueue;

class AwaiterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \tomzx\Job\Awaiter
     */
    private $awaiter;

    protected function setUp()
    {
        $this->awaiter = new Awaiter();
    }

    public function testAll()
    {
        $jobs = $this->buildJobQueue();
        $returnedJobs = $this->awaiter->all($jobs);
        $this->assertNotNull($jobs, $returnedJobs);
    }

    public function testAllWithEmptyQueue()
    {
        $jobQueue = new JobQueue();
        $this->awaiter->all($jobQueue);
    }

    public function testAny()
    {
        $job = $this->awaiter->any($this->buildJobQueue());
        $this->assertNotNull($job);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAnyWithEmptyQueueShouldThrowAnException()
    {
        $jobQueue = new JobQueue();
        $this->awaiter->any($jobQueue);
    }

    public function testSome()
    {
        $jobs = $this->awaiter->some($this->buildJobQueue(), 2);
        $this->assertSame(2, $jobs->count());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSomeWithInvalidCountShouldThrowAnException()
    {
        $jobQueue = new JobQueue();
        $this->awaiter->some($jobQueue, 10);
    }

    private function buildJobQueue()
    {
        $jobQueue = new JobQueue();
        for ($i = 1; $i <= 5; ++$i) {
            $job = $this->getMockJob($i * 100);
            $job->handle();
            $jobQueue->push($job);
        }
        return $jobQueue;
    }

    private function getMockJob($delay)
    {
        return new class($delay) extends Job
        {
            private $handledAt;
            private $delayMS;

            public function __construct(int $delayMS)
            {
                $this->delayMS = $delayMS;
            }

            public function handle()
            {
                $this->handledAt = microtime(true) + ($this->delayMS * 0.001);
            }

            public function isResolved(): bool
            {
                return $this->handledAt < microtime(true);
            }
        };
    }
}
