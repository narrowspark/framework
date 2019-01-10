<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Tests\Fixtures;

class BaseHydrateableClass
{
    private $name;

    public function getName()
    {
        return $this->name;
    }
}
