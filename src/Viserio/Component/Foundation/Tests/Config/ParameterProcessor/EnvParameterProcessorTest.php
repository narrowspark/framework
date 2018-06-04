<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Config\ParameterProcessor;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Foundation\Config\ParameterProcessor\EnvParameterProcessor;

/**
 * @internal
 */
final class EnvParameterProcessorTest extends TestCase
{
    public function testProcess(): void
    {
        \putenv('TEST_TRUE=true');

        $processor = new EnvParameterProcessor();

        $this->assertTrue($processor->process('%env:TEST_TRUE%'));

        \putenv('TEST_TRUE=');
        \putenv('TEST_TRUE');
    }
}
