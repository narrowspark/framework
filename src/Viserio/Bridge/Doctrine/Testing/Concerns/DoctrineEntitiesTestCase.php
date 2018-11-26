<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Concerns;

use PHPUnit\Framework\TestCase;

abstract class DoctrineEntitiesTestCase extends TestCase
{
    use InteractsWithEntities;
}
