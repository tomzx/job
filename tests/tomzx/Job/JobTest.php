<?php

namespace tomzx\Job\Tests;

use tomzx\Job\Job;

class JobTest extends \PHPUnit_Framework_TestCase
{
    private $job;

    protected function setUp()
    {
        $this->job = new class extends Job
        {
            public function handle()
            {
            }
        };
    }

    public function testIsResolvedByDefault()
    {
        $this->assertTrue($this->job->isResolved());
    }
}

