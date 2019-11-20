<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

return [
    Psr\Container\ContainerInterface::class => true,
    Viserio\Contract\Container\Factory::class => true,
    Viserio\Contract\Container\TaggedContainer::class => true,
    'container' => true,
];
