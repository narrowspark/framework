<?php
namespace Viserio\Queue\Tests\Jobs;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Job as PheanstalkJob;
use stdClass;
use Viserio\Queue\{
    Jobs\BeanstalkdJob,
    Tests\Fixture\BeanstalkdJobTestFailed
};

class BeanstalkdJobTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testBuryProperlyBuryTheJobFromBeanstalkd()
    {
        $pheanstalk = $this->mock(Pheanstalk::class);
        $pheanstalk->shouldReceive('release');

        $job = new BeanstalkdJob(
            $this->mock(ContainerInterface::class),
            $pheanstalk,
            $this->mock(PheanstalkJob::class),
            'default'
        );

        $job->getPheanstalk()->shouldReceive('bury')
            ->once()
            ->with($job->getPheanstalkJob());
        $job->bury();
    }

    public function testDeleteRemovesTheJobFromBeanstalkd()
    {
        $job = $this->getJob();
        $job->getPheanstalk()->shouldReceive('delete')
            ->once()
            ->with($job->getPheanstalkJob());
        $job->delete();
    }

    public function testFailedProperlyCallsTheJobHandler()
    {
        $job = $this->getJob();
        $job->getPheanstalkJob()->shouldReceive('getData')
            ->once()
            ->andReturn(json_encode(['job' => 'foo', 'data' => ['data']]));
        $job->getContainer()->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn($handler = $this->mock(BeanstalkdJobTestFailed::class));

        $handler->shouldReceive('failed')
            ->once()
            ->with(['data']);

        $job->failed();
    }

    public function testRunProperlyCallsTheJobHandler()
    {
        $job = $this->getJob();
        $job->getPheanstalkJob()->shouldReceive('getData')
            ->once()
            ->andReturn(json_encode(['job' => 'foo', 'data' => ['data']]));
        $job->getContainer()->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn($handler = $this->mock(stdClass::class));

        $handler->shouldReceive('run')
            ->once()
            ->with($job, ['data']);

        $job->run();
    }

    protected function getJob(): BeanstalkdJob
    {
        $pheanstalk = $this->mock(PheanstalkJob::class);

        return new BeanstalkdJob(
            $this->mock(ContainerInterface::class),
            $this->mock(Pheanstalk::class),
            $pheanstalk,
            'default'
        );
    }
}
