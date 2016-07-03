<?php
namespace Viserio\Queue\Tests\Connectors;

use Aws\Result;
use Aws\Sqs\SqsClient;
use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;

class SqsQueueTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    private $sqs;
    private $account;
    private $queueName;
    private $baseUrl;
    private $prefix;
    private $queueUrl;

    public function setUp()
    {
        parent::setUp;

        $this->sqs = $this->mock(SqsClient::class);
        $this->account = '1234567801018';
        $this->queueName = 'emails';
        $this->baseUrl = 'https://sqs.someregion.amazonaws.com';

        // This is how the modified getQueue builds the queueUrl
        $this->prefix = $this->baseUrl.'/'.$this->account.'/';
        $this->queueUrl = $this->prefix.$this->queueName;
    }
}
