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

namespace Viserio\Provider\Twig\Engine;

use ArrayAccess;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Viserio\Component\View\Engine\AbstractBaseEngine;
use Viserio\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Contract\OptionsResolver\ProvidesDefaultOption as ProvidesDefaultOptionContract;
use Viserio\Contract\View\Exception\RuntimeException;

class TwigEngine extends AbstractBaseEngine implements ProvidesDefaultOptionContract
{
    use ContainerAwareTrait;

    /**
     * Twig environment.
     *
     * @var \Twig\Environment
     */
    protected $twig;

    /**
     * Create a new engine instance.
     *
     * @param \Twig\Environment $twig
     * @param array|ArrayAccess $config
     */
    public function __construct(Environment $twig, $config)
    {
        $this->resolvedOptions = self::resolveOptions($config);
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
    {
        return \array_merge(
            parent::getMandatoryOptions(),
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
    public static function getDefaultOptions(): array
    {
        return [
            'engines' => [
                'twig' => [
                    'options' => [
                        'file_extension' => 'twig',
                    ],
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
        $twig = $this->addExtensions($this->twig, $this->resolvedOptions['engines']['twig']);

        return $twig->render($fileInfo['name'] ?? '', $data);
    }

    /**
     * Returns the engine names.
     *
     * @return array
     */
    public static function getDefaultNames(): array
    {
        return ['twig', 'html.twig'];
    }

    /**
     * Add extensions to twig environment.
     *
     * @param \Twig\Environment $twig
     * @param array             $config
     *
     * @throws \Viserio\Contract\View\Exception\RuntimeException
     *
     * @return \Twig\Environment
     */
    protected function addExtensions(Environment $twig, array $config): Environment
    {
        if (isset($config['extensions']) && \is_array($config['extensions'])) {
            foreach ($config['extensions'] as $extension) {
                if ($this->container !== null && \is_string($extension) && $this->container->has($extension)) {
                    $twig->addExtension($this->container->get($extension));
                } elseif (\is_object($extension) && $extension instanceof ExtensionInterface) {
                    $twig->addExtension($extension);
                } else {
                    throw new RuntimeException(\sprintf('Twig extension [%s] is not a object.', $extension));
                }
            }
        }

        return $twig;
    }
}
