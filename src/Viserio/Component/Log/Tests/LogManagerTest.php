<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Log\LogManager;

class LogManagerTest extends MockeryTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->manager = new LogManager();
    }
}
