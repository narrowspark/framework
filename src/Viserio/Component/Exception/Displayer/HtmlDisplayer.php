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

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\Exception\Displayer as DisplayerContract;
use Viserio\Contract\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Viserio\Contract\OptionsResolver\ProvidesDefaultOption as ProvidesDefaultOptionContract;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;

class HtmlDisplayer implements DisplayerContract, ProvidesDefaultOptionContract, RequiresComponentConfigContract
{
    use OptionsResolverTrait;
    use ResponseFactoryAwareTrait;

    /**
     * The html template path.
     *
     * @var string
     */
    protected $path;

    /**
     * Resolved options.
     *
     * @var array
     */
    protected $resolvedOptions = [];

    /**
     * Create a new html displayer instance.
     *
     * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory
     * @param array|\ArrayAccess                         $config
     */
    public function __construct(ResponseFactoryInterface $responseFactory, $config = [])
    {
        $this->responseFactory = $responseFactory;
        $this->resolvedOptions = self::resolveOptions($config);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'exception', 'http', 'html'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'template_path' => \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'error.html',
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
        $body->write($this->render(ExceptionInfo::generate($id, $code)));
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
        return false;
    }

    /**
     * Render the page with given info.
     *
     * @param array $info
     *
     * @return string
     */
    protected function render(array $info): string
    {
        $content = \file_get_contents($this->resolvedOptions['template_path']);

        foreach ($info as $key => $val) {
            $content = \str_replace('{{ $' . $key . ' }}', $val, $content);
        }

        return $content;
    }
}
