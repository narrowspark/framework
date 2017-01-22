<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Engine;

use Twig_Environment;
use Twig_LexerInterface;
use Twig_Loader_Array;
use Twig_LoaderInterface;
use Viserio\Bridge\Twig\Loader as TwigLoader;
use Viserio\Bridge\Twig\TwigEnvironment;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\View\Engines\TwigEngine as BaseTwigEngine;

class TwigEngine extends BaseTwigEngine implements ProvidesDefaultOptions
{
    /**
     * {@inheritdoc}
     */
    public function defaultOptions(): iterable
    {
        return [
            'twig' => [
                'options' => [
                    'file_extension' => 'twig',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getLoader(): Twig_LoaderInterface
    {
        $config  = $this->config;
        $loaders = [
            new TwigLoader(
                $this->container->get(FilesystemContract::class),
                $this->container->get(FinderContract::class),
                $config['twig']['file_extension']
            ),
        ];

        if (isset($config['twig']['templates']) && is_array($config['twig']['templates'])) {
            $loaders[] = new Twig_Loader_Array($config['twig']['templates']);
        }

        if (isset($config['twig']['loader']) && is_array($config['twig']['loader'])) {
            $loaders = array_merge($loaders, $config['twig']['loader']);
        }

        return new Twig_Loader_Chain($loaders);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTwigEnvironment(array $options): Twig_Environment
    {
        return new TwigEnvironment(
            $this->getLoader(),
            $options
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getInstance(): Twig_Environment
    {
        if (! $this->parserInstance) {
            $twig = parent::getInstance();

            if ($this->container->has(Twig_LexerInterface::class)) {
                $twig->setLexer($this->container->get(Twig_LexerInterface::class));
            }

            $this->parserInstance = $twig;
        }

        return $this->parserInstance;
    }
}
