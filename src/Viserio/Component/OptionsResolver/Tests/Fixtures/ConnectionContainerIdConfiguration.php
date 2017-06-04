<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;

class ConnectionContainerIdConfiguration implements RequiresComponentConfigIdContract
{
    /**
     * {@inheritdoc}.
     */
    public function getDimensions(): iterable
    {
        return ['doctrine', 'connection'];
    }
}
