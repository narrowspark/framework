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
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Throwable;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Exception\Displayer as DisplayerContract;
use Viserio\Contract\HttpFactory\Traits\ResponseFactoryAwareTrait;

class SymfonyDisplayer implements DisplayerContract,
    ProvidesDefaultConfigContract,
    RequiresComponentConfigContract
{
    use ResponseFactoryAwareTrait;

    /**
     * Configurations list for whoops.
     *
     * @var array
     */
    private $resolvedOptions;

    /**
     * Create a new symfony displayer instance.
     *
     * @param array|ArrayAccess $config
     */
    public function __construct(ResponseFactoryInterface $responseFactory, $config = [])
    {
        $this->responseFactory = $responseFactory;
        $this->resolvedOptions = self::resolveOptions($config);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'exception', 'http', 'symfony'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultConfig(): iterable
    {
        return [
            'ide_links' => [
                'textmate' => 'txmt://open?url=file://%%f&line=%%l',
                'macvim' => 'mvim://open?url=file://%%f&line=%%l',
                'emacs' => 'emacs://open?url=file://%%f&line=%%l',
                'sublime' => 'subl://open?url=file://%%f&line=%%l',
                'phpstorm' => 'phpstorm://open?file=%%f&line=%%l',
                'atom' => 'atom://core/open/file?filename=%%f&line=%%l',
                'vscode' => 'vscode://file/%%f:%%l',
            ],
            'ide' => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function display(Throwable $exception, string $id, int $code, array $headers): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($code);

        foreach (\array_merge($headers, ['Content-Type' => $this->getContentType()]) as $header => $value) {
            $response = $response->withAddedHeader($header, $value);
        }

        $body = $response->getBody();
        $body->write($this->renderExceptionWithSymfony($exception));
        $body->rewind();

        return $response->withBody($body);
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
    public function canDisplay(Throwable $original, Throwable $transformed, int $code): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isVerbose(): bool
    {
        return true;
    }

    /**
     * Render an exception to a string using Symfony.
     */
    private function renderExceptionWithSymfony(Throwable $exception): string
    {
        $displayer = new SymfonyExceptionHandler(
            true,
            null,
            \str_replace('%', '%%', \ini_get('xdebug.file_link_format') ?? \get_cfg_var('xdebug.file_link_format')) ?? ($this->resolvedOptions['ide_links'][$this->resolvedOptions['ide']] ?? $this->resolvedOptions['ide'])
        );

        return $displayer->getHtml($exception);
    }
}
