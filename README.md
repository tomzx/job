# Job

[![License](https://poser.pugx.org/tomzx/job/license.svg)](https://packagist.org/packages/tomzx/job)
[![Latest Stable Version](https://poser.pugx.org/tomzx/job/v/stable.svg)](https://packagist.org/packages/tomzx/job)
[![Latest Unstable Version](https://poser.pugx.org/tomzx/job/v/unstable.svg)](https://packagist.org/packages/tomzx/job)
[![Build Status](https://img.shields.io/travis/tomzx/job.svg)](https://travis-ci.org/tomzx/job)
[![Code Quality](https://img.shields.io/scrutinizer/g/tomzx/job.svg)](https://scrutinizer-ci.com/g/tomzx/job/code-structure)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tomzx/job.svg)](https://scrutinizer-ci.com/g/tomzx/job)
[![Total Downloads](https://img.shields.io/packagist/dt/tomzx/job.svg)](https://packagist.org/packages/tomzx/job)

`Job` is a small library which purpose is to handle the creation and execution of jobs that may be executed synchronously or asynchronously.

## Getting started
* In a console, `php composer.phar require tomzx/job`

## Example
```php
use tomzx\Job\Job;
use tomzx\Job\Throttler;

class SymfonyProcessJob extends Job
{
    /**
     * @var \Symfony\Component\Process\Process
     */
    private $process;

    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    public function handle()
    {
        $this->process->start();
    }

    public function getProcess()
    {
        return $this->process;
    }

    public function isResolved()
    {
        return $this->process->isTerminated();
    }
}

// Create a throttler with a maximum of 5 jobs in parallel
$throttler = new Throttler(5);

// Bind event handlers
$throttler->onJobWaiting(function (SymfonyProcessJob $job) {
    echo 'New job added!' . PHP_EOL;
});

$throttler->onJobRunning(function (SymfonyProcessJob $job) {
    echo 'Job running!' . PHP_EOL;
});

$throttler->onJobCompleted(function (SymfonyProcessJob $job) {
    echo $job->getProcess()->getOutput() . PHP_EOL;
});

// Create a couple of jobs
for ($i = 0; $i < 10; ++$i) {
	// Push the jobs, they will not be started until ->wait is called()
	$throttler->push(new SymfonyProcessJob(new Process('sleep 1 && time')));
	// Push the jobs and start them right away
	//$throttler->pushAndStart(new SymfonyProcessJob(new Process('sleep 1 && time')));
}

// Block until the jobs are completed
$throttler->wait();
```

Async jobs can also be handled in a more "promise-oriented" fashion
```php
class TestJob extends Job
{
    private $handled = false;

    public function handle()
    {
        static $i = 0;
        echo ++$i . PHP_EOL;
        sleep(1);
        $this->handled = true;
    }

    public function isResolved()
    {
        return $this->handled;
    }
}

$jobQueue = new JobQueue();

// Create a couple of jobs
for ($i = 0; $i < 10; ++$i) {
	$job = new TestJob();
	$job->handle();
	$jobQueue->push($job);
}

$awaiter = new Awaiter();
// Await completion of all jobs
// jobs = $awaiter->all($jobQueue);
// Await completion of any job
// $job = $awaiter->any($jobQueue);
// Await completion of a given amount of jobs
// $jobs = $awaiter->some($jobQueue, 2);
```

## License

The code is licensed under the [MIT license](http://choosealicense.com/licenses/mit/). See [LICENSE](LICENSE).
