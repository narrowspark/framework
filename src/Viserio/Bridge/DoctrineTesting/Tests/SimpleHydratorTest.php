<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\Testing\SimpleHydrator;
use Viserio\Bridge\Doctrine\Testing\Tests\Fixtures\BaseHydrateableClass;
use Viserio\Bridge\Doctrine\Testing\Tests\Fixtures\ChildHydrateableClass;

class SimpleHydratorTest extends TestCase
{
    public function testCanHydrateClass(): void
    {
        $entity = SimpleHydrator::hydrate(BaseHydrateableClass::class, [
            'name' => 'Ghost',
        ]);

        $this->assertInstanceOf(BaseHydrateableClass::class, $entity);
        $this->assertEquals('Ghost', $entity->getName());
    }

    public function testCanHydrateWithExtensionOfPrivateProperties(): void
    {
        $entity = SimpleHydrator::hydrate(ChildHydrateableClass::class, [
            'name'        => 'Ghost',
            'description' => 'Hello World',
        ]);

        $this->assertInstanceOf(ChildHydrateableClass::class, $entity);
        $this->assertEquals('Ghost', $entity->getName());
        $this->assertEquals('Hello World', $entity->getDescription());
    }
}
