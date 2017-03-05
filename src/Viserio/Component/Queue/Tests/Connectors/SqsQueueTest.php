<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Tests\Connectors;

use Aws\Result;
use Aws\Sqs\SqsClient;
use Cake\Chronos\Chronos;
use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Queue\Connectors\SqsQueue;
use Viserio\Component\Queue\Jobs\SqsJob;

class SqsQueueTest extends MockeryTestCase
{
    private $sqs;
    private $account;
    private $queueName;
    private $baseUrl;
    private $prefix;
    private $queueUrl;
    private $mockedJob;
    private $mockedData;
    private $mockedPayload;
    private $mockedDelay;
    private $mockedMessageId;
    private $mockedReceiptHandle;
    private $mockedSendMessageResponseModel;
    private $mockedReceiveMessageResponseModel;

    public function setUp()
    {
        parent::setUp();

        $this->sqs       = $this->mock(SqsClient::class);
        $this->account   = '1234567891011';
        $this->queueName = 'emails';
        $this->baseUrl   = 'https://sqs.someregion.amazonaws.com';

        // This is how the modified getQueue builds the queueUrl
        $this->prefix   = $this->baseUrl . '/' . $this->account . '/';
        $this->queueUrl = $this->prefix . $this->queueName;

        $this->mockedJob           = 'foo';
        $this->mockedData          = ['data'];
        $this->mockedPayload       = json_encode(['job' => $this->mockedJob, 'data' => $this->mockedData]);
        $this->mockedDelay         = 10;
        $this->mockedMessageId     = 'e3cd03ee-59a3-4ad8-b0aa-ee2e3808ac81';
        $this->mockedReceiptHandle = '0NNAq8PwvXuWv5gMtS9DJ8qEdyiUwbAjpp45w2m6M4SJ1Y+PxCh7R930NRB8ylSacEmoSnW18bgd4nK\/O6ctE+VFVul4eD23mA07vVoSnPI4F\/voI1eNCp6Iax0ktGmhlNVzBwaZHEr91BRtqTRM3QKd2ASF8u+IQaSwyl\/DGK+P1+dqUOodvOVtExJwdyDLy1glZVgm85Yw9Jf5yZEEErqRwzYz\/qSigdvW4sm2l7e4phRol\/+IjMtovOyH\/ukueYdlVbQ4OshQLENhUKe7RNN5i6bE\/e5x9bnPhfj2gbM';

        $this->mockedSendMessageResponseModel = new Result([
            'Body'          => $this->mockedPayload,
            'MD5OfBody'     => md5($this->mockedPayload),
            'ReceiptHandle' => $this->mockedReceiptHandle,
            'MessageId'     => $this->mockedMessageId,
            'Attributes'    => ['ApproximateReceiveCount' => 1],
        ]);
        $this->mockedReceiveMessageResponseModel = new Result([
            'Messages' => [0 => [
                'Body'              => $this->mockedPayload,
                    'MD5OfBody'     => md5($this->mockedPayload),
                    'ReceiptHandle' => $this->mockedReceiptHandle,
                    'MessageId'     => $this->mockedMessageId,
                ],
            ],
        ]);
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);
    }

    public function testPopProperlyPopsJobOffOfSqs()
    {
        $queue = $this->getMockBuilder(SqsQueue::class)
            ->setMethods(['getQueue'])
            ->setConstructorArgs([$this->sqs, $this->queueName, $this->account])
            ->getMock();
        $queue->setContainer($this->mock(ContainerInterface::class));
        $queue->expects($this->once())
            ->method('getQueue')
            ->with($this->queueName)
            ->will($this->returnValue($this->queueUrl));

        $this->sqs->shouldReceive('receiveMessage')
            ->once()
            ->with(['QueueUrl' => $this->queueUrl, 'AttributeNames' => ['ApproximateReceiveCount']])
            ->andReturn($this->mockedReceiveMessageResponseModel);

        $result = $queue->pop($this->queueName);

        self::assertInstanceOf(SqsJob::class, $result);
    }

    public function testDelayedPushWithDateTimeProperlyPushesJobOntoSqs()
    {
        $now = Chronos::now();
        $now->addSeconds(5);

        $queue = $this->getMockBuilder(SqsQueue::class)
            ->setMethods(['createPayload', 'getSeconds', 'getQueue'])
            ->setConstructorArgs([$this->sqs, $this->queueName, $this->account])
            ->getMock();
        $queue->expects($this->once())
            ->method('createPayload')
            ->with($this->mockedJob, $this->mockedData)
            ->will($this->returnValue($this->mockedPayload));
        $queue->expects($this->once())
            ->method('getSeconds')
            ->with($now)
            ->will($this->returnValue(5));
        $queue->expects($this->once())
            ->method('getQueue')
            ->with($this->queueName)
            ->will($this->returnValue($this->queueUrl));

        $this->sqs->shouldReceive('sendMessage')
            ->once()
            ->with(['QueueUrl' => $this->queueUrl, 'MessageBody' => $this->mockedPayload, 'DelaySeconds' => 5])
            ->andReturn($this->mockedSendMessageResponseModel);

        $id = $queue->later($now, $this->mockedJob, $this->mockedData, $this->queueName);

        self::assertEquals($this->mockedMessageId, $id);
    }

    public function testPopProperlyPopsJobOffOfSqsWithCustomJobCreator()
    {
        $queue = $this->getMockBuilder(SqsQueue::class)
            ->setMethods(['getQueue'])
            ->setConstructorArgs([$this->sqs, $this->queueName, $this->account])
            ->getMock();
        $queue->createJobsUsing(function () {
            return 'job!';
        });
        $queue->setContainer($this->mock(ContainerInterface::class));
        $queue->expects($this->once())
            ->method('getQueue')
            ->with($this->queueName)
            ->will($this->returnValue($this->queueUrl));

        $this->sqs->shouldReceive('receiveMessage')
            ->once()
            ->with(['QueueUrl' => $this->queueUrl, 'AttributeNames' => ['ApproximateReceiveCount']])
            ->andReturn($this->mockedReceiveMessageResponseModel);

        $result = $queue->pop($this->queueName);

        self::assertEquals('job!', $result);
    }

    public function testDelayedPushProperlyPushesJobOntoSqs()
    {
        $queue = $this->getMockBuilder(SqsQueue::class)
            ->setMethods(['createPayload', 'getSeconds', 'getQueue'])
            ->setConstructorArgs([$this->sqs, $this->queueName, $this->account])
            ->getMock();
        $queue->expects($this->once())
            ->method('createPayload')
            ->with($this->mockedJob, $this->mockedData)
            ->will($this->returnValue($this->mockedPayload));
        $queue->expects($this->once())
            ->method('getSeconds')
            ->with($this->mockedDelay)
            ->will($this->returnValue($this->mockedDelay));
        $queue->expects($this->once())
            ->method('getQueue')
            ->with($this->queueName)
            ->will($this->returnValue($this->queueUrl));

        $this->sqs->shouldReceive('sendMessage')
            ->once()
            ->with(['QueueUrl' => $this->queueUrl, 'MessageBody' => $this->mockedPayload, 'DelaySeconds' => $this->mockedDelay])
            ->andReturn($this->mockedSendMessageResponseModel);

        $id = $queue->later($this->mockedDelay, $this->mockedJob, $this->mockedData, $this->queueName);

        self::assertEquals($this->mockedMessageId, $id);
    }

    public function testPushProperlyPushesJobOntoSqs()
    {
        $queue = $this->getMockBuilder(SqsQueue::class)
            ->setMethods(['createPayload', 'getQueue'])
            ->setConstructorArgs([$this->sqs, $this->queueName, $this->account])
            ->getMock();
        $queue->expects($this->once())
            ->method('createPayload')
            ->with($this->mockedJob, $this->mockedData)
            ->will($this->returnValue($this->mockedPayload));
        $queue->expects($this->once())
            ->method('getQueue')
            ->with($this->queueName)
            ->will($this->returnValue($this->queueUrl));

        $this->sqs->shouldReceive('sendMessage')
            ->once()
            ->with(['QueueUrl' => $this->queueUrl, 'MessageBody' => $this->mockedPayload])
            ->andReturn($this->mockedSendMessageResponseModel);

        $id = $queue->push($this->mockedJob, $this->mockedData, $this->queueName);

        self::assertEquals($this->mockedMessageId, $id);
    }

    public function testGetQueueProperlyResolvesUrlWithPrefix()
    {
        $queue = new SqsQueue($this->sqs, $this->queueName, $this->prefix);

        self::assertEquals($this->queueUrl, $queue->getQueue(null));

        $queueUrl = $this->baseUrl . '/' . $this->account . '/test';

        self::assertEquals($queueUrl, $queue->getQueue('test'));
    }

    public function testGetQueueProperlyResolvesUrlWithoutPrefix()
    {
        $queue = new SqsQueue($this->sqs, $this->queueUrl);

        self::assertEquals($this->queueUrl, $queue->getQueue(null));

        $queueUrl = $this->baseUrl . '/' . $this->account . '/test';

        self::assertEquals($queueUrl, $queue->getQueue($queueUrl));
    }
}
