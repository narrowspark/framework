<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;

class ConnectionContainerIdConfiguration implements RequiresConfigIdContract
{
    /**
     * @interitdoc
     */
    public function getDimensions(): iterable
    {
        return ['doctrine', 'connection'];
    }
}
