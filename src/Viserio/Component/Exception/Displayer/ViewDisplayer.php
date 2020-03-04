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

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Contract\Exception\Displayer as DisplayerContract;
use Viserio\Contract\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Viserio\Contract\View\Factory as FactoryContract;

class ViewDisplayer implements DisplayerContract
{
    use ResponseFactoryAwareTrait;

    /**
     * The view factory instance.
     *
     * @var \Viserio\Contract\View\Factory
     */
    protected $factory;

    /**
     * Create a new html displayer instance.
     */
    public function __construct(ResponseFactoryInterface $responseFactory, FactoryContract $factory)
    {
        $this->responseFactory = $responseFactory;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function display(Throwable $exception, string $id, int $code, array $headers): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($code);

        foreach (\array_merge($headers, ['content-type' => $this->getContentType()]) as $header => $value) {
            $response = $response->withAddedHeader($header, $value);
        }

        $view = $this->factory->create("errors.{$code}", ExceptionInfo::generate($id, $code));
        $view->with('exception', $exception);

        $body = $response->getBody();
        $body->write((string) $view);
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
        return $this->factory->exists("errors.{$code}");
    }

    /**
     * {@inheritdoc}
     */
    public function isVerbose(): bool
    {
        return false;
    }
}
