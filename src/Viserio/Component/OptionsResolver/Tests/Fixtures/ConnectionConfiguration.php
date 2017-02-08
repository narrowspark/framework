<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;

class ConnectionConfiguration implements RequiresConfigContract
{
    /**
     * @interitdoc
     */
    public function getDimensions(): iterable
    {
        return ['doctrine', 'connection'];
    }
}
