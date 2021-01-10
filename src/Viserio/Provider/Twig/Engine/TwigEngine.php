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

namespace Viserio\Provider\Twig\Engine;

use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Viserio\Component\View\Engine\AbstractBaseEngine;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Contract\View\Exception\RuntimeException;

class TwigEngine extends AbstractBaseEngine implements ProvidesDefaultConfigContract
{
    use ContainerAwareTrait;

    /**
     * Twig environment.
     */
    protected Environment $twig;

    /**
     * List of twig extensions.
     *
     * @var array<int, object|string>
     */
    protected array $extensions;

    /**
     * Create a new engine instance.
     *
     * @param array<int, object|string> $extensions
     */
    public function __construct(Environment $twig, array $extensions = [])
    {
        $this->extensions = $extensions;
        $this->twig = $twig;
    }

    /**
     * Returns the engine names.
     */
    public static function getDefaultNames(): array
    {
        return ['twig', 'html.twig'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryConfig(): iterable
    {
        return \array_merge(
            parent::getMandatoryConfig(),
            [
                'engines' => [
                    'twig' => [
                        'options' => [
                            'debug',
                            'cache',
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultConfig(): iterable
    {
        return [
            'engines' => [
                'twig' => [
                    'options' => [
                        'file_extension' => 'twig',
                    ],
                    'extensions' => [],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Viserio\Contract\View\Exception\RuntimeException
     */
    public function get(array $fileInfo, array $data = []): string
    {
        foreach ($this->extensions as $extension) {
            if ($this->container !== null && \is_string($extension) && $this->container->has($extension)) {
                $this->twig->addExtension($this->container->get($extension));
            } elseif (\is_object($extension) && $extension instanceof ExtensionInterface) {
                $this->twig->addExtension($extension);
            } else {
                throw new RuntimeException(\sprintf('Twig extension [%s] is not a object.', $extension));
            }
        }

        return $this->twig->render($fileInfo['name'] ?? '', $data);
    }
}
