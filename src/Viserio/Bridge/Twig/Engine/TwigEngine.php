<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Engine;

use ErrorException;
use RuntimeException;
use Twig_Environment;
use Twig_Error;
use Twig_Loader_Filesystem;
use Twig_LoaderInterface;
use Interop\Config\ProvidesDefaultOptions;
use Interop\Container\ContainerInterface;
use Viserio\Component\View\Engines\AbstractBaseEngine;

class TwigEngine extends AbstractBaseEngine implements ProvidesDefaultOptions
{
    /**
     * Twig environment.
     *
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Create a new twig engine instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->twig = $container->get(Twig_Environment::class);
    }

    /**
     * {@inheritdoc}
     */
    public function mandatoryOptions(): iterable
    {
        return array_merge(
            parent::mandatoryOptions(),
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
    public function defaultOptions(): iterable
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
     */
    public function get(array $fileInfo, array $data = []): string
    {
        $twig = $this->addExtensions($this->twig, $this->config['engines']['twig']);

        try {
            $content = $twig->render($fileInfo['name'], $data);
        } catch (Twig_Error $exception) {
            $this->handleTwigError($exception);
        }

        return $content;
    }

    /**
     * Handle a TwigError exception.
     *
     * @param \Twig_Error $exception
     *
     * @throws \Twig_Error|\ErrorException
     */
    protected function handleTwigError(Twig_Error $exception)
    {
        $source       = $exception->getSourceContext();
        $templateLine = $exception->getTemplateLine();
        $file         = null;

        if ($file !== null) {
            $exception = new ErrorException(
                $exception->getMessage(),
                0,
                1,
                $source->getName(),
                $templateLine,
                $exception
            );
        }

        throw $exception;
    }

    /**
     * Add extensions to twig environment.
     *
     * @param \Twig_Environment $twig
     * @param array             $config
     *
     * @codeCoverageIgnore
     */
    protected function addExtensions(Twig_Environment $twig, array $config): Twig_Environment
    {
        if (isset($config['extensions']) && is_array($config['extensions'])) {
            foreach ($config['extensions'] as $extension) {
                if (is_string($extension) && $this->container->has($extension)) {
                    $twig->addExtension($this->container->get($extension));
                } elseif (is_object($extension)) {
                    $twig->addExtension($extension);
                } else {
                    throw new RuntimeException(sprintf(
                        'Plates extension [%s] is not a object.',
                        (string) $extension
                    ));
                }
            }
        }

        return $twig;
    }
}
