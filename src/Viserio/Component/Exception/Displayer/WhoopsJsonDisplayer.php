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

namespace Viserio\Component\Exception\Displayer;

use Whoops\Handler\Handler;
use Whoops\Handler\JsonResponseHandler;

class WhoopsJsonDisplayer extends AbstractWhoopsDisplayer
{
    /**
     * {@inheritdoc}
     */
    public function getContentType(): string
    {
        return 'application/json';
    }

    /**
     * {@inheritdoc}
     */
    public function isVerbose(): bool
    {
        return true;
    }

    /**
     * Get the Whoops handler.
     *
     * @return \Whoops\Handler\Handler
     */
    protected function getHandler(): Handler
    {
        $handler = new JsonResponseHandler();
        $handler->setJsonApi(true);

        return $handler;
    }
}
