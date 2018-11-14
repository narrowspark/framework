<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture\Alias;

class SomeAliasFactory
{
    public $alias;

    public function __construct(AliasEnvFixture $alias)
    {
        $this->alias = $alias;
    }
}
