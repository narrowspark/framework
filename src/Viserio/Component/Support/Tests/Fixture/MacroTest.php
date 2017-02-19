<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests\Fixture;

use Viserio\Component\Support\Traits\MacroableTrait;

class MacroTest
{
    use MacroableTrait;

    protected $protectedVariable = 'instance';

    protected static function getProtectedStatic()
    {
        return 'static';
    }
}
