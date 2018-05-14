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

namespace Viserio\Component\Exception\Filter;

use Narrowspark\Http\Message\Util\InteractsWithContentTypes;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Viserio\Contract\Exception\Filter as FilterContract;

class ContentTypeFilter implements FilterContract
{
    /**
     * {@inheritdoc}
     */
    public function filter(
        array $displayers,
        ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array {
        foreach ($displayers as $index => $displayer) {
            if (! InteractsWithContentTypes::accepts([$displayer->getContentType()], $request)) {
                unset($displayers[$index]);
            }
        }

        return \array_values($displayers);
    }
}
