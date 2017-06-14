<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Tests\Job;

use Aws\Sqs\SqsClient;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Viserio\Component\Queue\Job\SqsJob;

class SqsJobTest extends MockeryTestCase
{
    private $queueUrl;
    private $mockedReceiptHandle;

    public function setUp()
    {
        parent::setUp();

        // This is how the modified getQueue builds the queueUrl
        $this->queueUrl = 'https://sqs.someregion.amazonaws.com/1234567891011/emails';

        $this->mockedReceiptHandle = '0NNAq8PwvXuWv5gMtS9DJ8qEdyiUwbAjpp45w2m6M4SJ1Y+PxCh7R930NRB8ylSacEmoSnW18bgd4nK\/O6ctE+VFVul4eD23mA07vVoSnPI4F\/voI1eNCp6Iax0ktGmhlNVzBwaZHEr91BRtqTRM3QKd2ASF8u+IQaSwyl\/DGK+P1+dqUOodvOVtExJwdyDLy1glZVgm85Yw9Jf5yZEEErqRwzYz\/qSigdvW4sm2l7e4phRol\/+IjMtovOyH\/ukueYdlVbQ4OshQLENhUKe7RNN5i6bE\/e5x9bnPhfj2gbM';
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);
    }

    public function testFireProperlyCallsTheJobHandler()
    {
        $job = $this->getJob();
        $job->getContainer()->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn($handler = $this->mock(stdClass::class));
        $handler->shouldReceive('run')
            ->once()
            ->with($job, ['data']);

        $job->run();
    }

    public function testDeleteRemovesTheJobFromSqs()
    {
        $job = $this->getJob();
        $job->getSqs()->shouldReceive('deleteMessage')
            ->once()
            ->with(['QueueUrl' => $this->queueUrl, 'ReceiptHandle' => $this->mockedReceiptHandle]);

        $job->delete();
    }

    public function testReleaseProperlyReleasesTheJobOntoSqs()
    {
        $job = $this->getJob();
        $job->getSqs()->shouldReceive('changeMessageVisibility')
            ->once()
            ->with(['QueueUrl' => $this->queueUrl, 'ReceiptHandle' => $this->mockedReceiptHandle, 'VisibilityTimeout' => 0]);
        $job->release(0);

        self::assertTrue($job->isReleased());
    }

    protected function getJob()
    {
        $mockedPayload = json_encode(['job' => 'foo', 'data' => ['data'], 'attempts' => 1]);

        $mockedJobData = [
            'Body'          => $mockedPayload,
            'MD5OfBody'     => md5($mockedPayload),
            'ReceiptHandle' => $this->mockedReceiptHandle,
            'MessageId'     => 'e3cd03ee-59a3-4ad8-b0aa-ee2e3808ac81',
            'Attributes'    => ['ApproximateReceiveCount' => 1],
        ];

        return new SqsJob(
            $this->mock(ContainerInterface::class),
            $this->mock(SqsClient::class),
            $this->queueUrl,
            $mockedJobData
        );
    }
}
