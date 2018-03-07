<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests\Fixture;

use Monolog\Logger;

class MyCustomLogger
{
    public static function handle(): Logger
    {
        return new Logger('customCallable');
    }
}
