<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Tests\Fixtures;

class ChildHydrateableClass extends BaseHydrateableClass
{
    private $description;

    public function getDescription()
    {
        return $this->description;
    }
}
