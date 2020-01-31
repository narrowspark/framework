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

use ArrayAccess;
use Psr\Http\Message\ResponseFactoryInterface;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Whoops\Handler\Handler;
use Whoops\Handler\PrettyPageHandler;

class WhoopsPrettyDisplayer extends AbstractWhoopsDisplayer implements ProvidesDefaultConfigContract,
    RequiresComponentConfigContract
{
    /**
     * Configurations list for whoops.
     *
     * @var array
     */
    private $resolvedOptions;

    /**
     * Create a new whoops displayer instance.
     *
     * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory
     * @param array|ArrayAccess                          $config
     */
    public function __construct(ResponseFactoryInterface $responseFactory, $config = [])
    {
        parent::__construct($responseFactory);

        $this->resolvedOptions = self::resolveOptions($config);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'exception', 'http', 'whoops'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultConfig(): iterable
    {
        return [
            'blacklist' => [],
            'application_paths' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType(): string
    {
        return 'text/html';
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
        $handler = new PrettyPageHandler();

        $handler->handleUnconditionally(true);

        foreach ($this->resolvedOptions['blacklist'] as $key => $secrets) {
            foreach ($secrets as $secret) {
                $handler->blacklist($key, $secret);
            }
        }

        $handler->setApplicationPaths($this->resolvedOptions['application_paths']);

        return $handler;
    }
}
