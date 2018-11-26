<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\ORM\EntityMapping as ViserioEntityMapping;
use Viserio\Bridge\Doctrine\ORM\Fluent as ViserioFluent;
use Viserio\Bridge\Doctrine\ORM\FluentDriver as ViserioFluentDriver;

/**
 * @internal
 */
final class ClassAliasesTest extends TestCase
{
    public function testAlias(): void
    {
        $this->assertTrue(\interface_exists(ViserioFluent::class));
        $this->assertTrue(class_exists(ViserioEntityMapping::class));
        $this->assertTrue(class_exists(ViserioFluentDriver::class));
    }
}
