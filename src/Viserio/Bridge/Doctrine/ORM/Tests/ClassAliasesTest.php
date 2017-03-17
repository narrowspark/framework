<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Tests;

use LaravelDoctrine\Fluent\EntityMapping;
use LaravelDoctrine\Fluent\Fluent;
use LaravelDoctrine\Fluent\FluentDriver;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\ORM\EntityMapping as ViserioEntityMapping;
use Viserio\Bridge\Doctrine\ORM\Fluent as ViserioFluent;
use Viserio\Bridge\Doctrine\ORM\FluentDriver as ViserioFluentDriver;

class ClassAliasesTest extends TestCase
{
    public function testAlias()
    {
        self::assertTrue(interface_exists(ViserioFluent::class));
        self::assertTrue(class_exists(ViserioEntityMapping::class));
        self::assertTrue(class_exists(ViserioFluentDriver::class));
    }
}
