<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
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
     */
    protected function getHandler(): Handler
    {
        $handler = new JsonResponseHandler();
        $handler->setJsonApi(true);

        return $handler;
    }
}
