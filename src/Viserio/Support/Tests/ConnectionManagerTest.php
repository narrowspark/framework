<?php
namespace Viserio\Support\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Support\Tests\Fixture\TestConnectionManager;

class ConnectionManagerTest extends \PHPUnit_Framework_TestCase
{
     /**
     * Get the configuration name.
     *
     * @return string
     */
    protected function getConfigName(): string
    {
        return 'connection';
    }
}
