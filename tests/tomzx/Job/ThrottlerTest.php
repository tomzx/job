<?php

namespace tomzx\Job\Tests;

use tomzx\Job\Job;
use tomzx\Job\Throttler;

class ThrottlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \tomzx\Job\Throttler
     */
    private $throttler;

    protected function setUp()
    {
        $this->throttler = new Throttler(2);
    }

    public function testPush()
    {
        $job = $this->getMockJob();
        $this->throttler->push($job);
        $this->assertFalse($job->handled);
    }

    public function testPushAndStart()
    {
        $job = $this->getMockJob();
        $this->throttler->pushAndStart($job);
        $this->assertTrue($job->handled);
    }

    public function testWaitWithoutJob()
    {
        $this->throttler->wait();
        $this->assertSame(0, $this->throttler->getCompletedJobs()->count());
    }

    public function testWaitWithAJob()
    {
        $job = $this->getMockJob();
        $this->throttler->push($job);
        $this->throttler->wait();
        $this->assertSame(1, $this->throttler->getCompletedJobs()->count());
    }

    public function testWaitWithManyJobs()
    {
        $job = $this->getMockJob();
        $this->throttler->pushAndStart($job);
        $this->throttler->pushAndStart($job);
        $this->throttler->pushAndStart($job);
        $this->throttler->wait();
        $this->assertSame(3, $this->throttler->getCompletedJobs()->count());
    }

    public function testOnJobWaiting()
    {
        $called = false;
        $this->throttler->onJobWaiting(function () use (&$called) {
            $called = true;
        });
        $this->throttler->push($this->getMockJob());
        $this->throttler->wait();
        $this->assertTrue($called);
    }

    public function testOnJobRunning()
    {
        $called = false;
        $this->throttler->onJobRunning(function () use (&$called) {
            $called = true;
        });
        $this->throttler->push($this->getMockJob());
        $this->throttler->wait();
        $this->assertTrue($called);
    }

    public function testOnJobCompleted()
    {
        $called = false;
        $this->throttler->onJobCompleted(function () use (&$called) {
            $called = true;
        });
        $this->throttler->push($this->getMockJob());
        $this->throttler->wait();
        $this->assertTrue($called);
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
