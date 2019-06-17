<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Concerns;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
abstract class DoctrineEntitiesTestCase extends TestCase
{
    use InteractsWithEntities;
}
