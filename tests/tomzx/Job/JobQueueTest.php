<?php

namespace tomzx\Job\Tests;

use tomzx\Job\Job;
use tomzx\Job\JobQueue;

class JobQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \tomzx\Job\JobQueue
     */
    private $jobQueue;

    protected function setUp()
    {
        $this->jobQueue = new JobQueue();
    }

    public function testPush()
    {
        $job = $this->getMockJob();
        $this->jobQueue->push($job);
        $this->assertSame([$job], $this->jobQueue->all());
    }

    public function testPop()
    {
        $job = $this->getMockJob();
        $this->jobQueue->push($job);
        $this->assertSame([$job], $this->jobQueue->all());
        $poppedJob = $this->jobQueue->pop();
        $this->assertSame($job, $poppedJob);
        $this->assertSame([], $this->jobQueue->all());
    }

    public function testAll()
    {
        $job = $this->getMockJob();
        $this->jobQueue->push($job);
        $this->assertSame([$job], $this->jobQueue->all());
    }

    public function testIsEmpty()
    {
        $this->assertSame(true, $this->jobQueue->isEmpty());
        $job = $this->getMockJob();
        $this->jobQueue->push($job);
        $this->assertSame(false, $this->jobQueue->isEmpty());
    }

    public function testCount()
    {
        $this->assertSame(0, $this->jobQueue->count());
        $job = $this->getMockJob();
        $this->jobQueue->push($job);
        $this->assertSame(1, $this->jobQueue->count());
    }

    public function testRemove()
    {
        $job = $this->getMockJob();
        $this->jobQueue->push($job);
        $this->assertSame([$job], $this->jobQueue->all());
        $this->jobQueue->remove($job);
        $this->assertSame([], $this->jobQueue->all());
    }

    public function testRemoveWithUnknownJobRemovesNothing()
    {
        $this->assertSame([], $this->jobQueue->all());
        $job = $this->getMockJob();

        $this->jobQueue->remove($job);
        $this->assertSame([], $this->jobQueue->all());
    }

    private function getMockJob()
    {
        return new class extends Job
        {
            public $handled = false;
            public function handle()
            {
                $this->handled = true;
            }
        };
    }

}
