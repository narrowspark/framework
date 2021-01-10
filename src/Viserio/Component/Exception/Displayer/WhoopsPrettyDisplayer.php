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
     * @param array|ArrayAccess $config
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
