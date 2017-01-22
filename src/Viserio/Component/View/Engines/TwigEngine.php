<?php
declare(strict_types=1);
namespace Viserio\Component\View\Engines;

use ErrorException;
use RuntimeException;
use Twig_Environment;
use Twig_Error;
use Twig_Loader_Filesystem;
use Twig_LoaderInterface;
use Viserio\Component\Contracts\View\Engine as EngineContract;

class TwigEngine extends AbstractBaseEngine
{
    /**
     * The Twig environment for rendering templates.
     *
     * @var \Twig_Environment
     */
    protected $parserInstance;

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
                        'file_extension',
                        'options' => [
                            'debug',
                            'cache'
                        ]
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $fileInfo, array $data = []): string
    {
        try {
            $content = $this->getInstance()->render($fileInfo['name'], $data);
        } catch (Twig_Error $exception) {
            $this->handleTwigError($exception);
        }

        return $content;
    }

    /**
     * Creates new Twig_Environment if it doesn't already exist, and returns it.
     *
     * @throws \RuntimeException
     *
     * @return \Twig_Environment
     */
    protected function getInstance(): Twig_Environment
    {
        if (! $this->parserInstance) {
            $config = $this->config['engines']['twig'];
            $twig   = $this->getTwigEnvironment($config['options']);

            // @codeCoverageIgnoreStart
            if (isset($config['extensions']) && is_array($config['extensions'])) {
                foreach ($config['extensions'] as $extension) {
                    if (is_object($extension)) {
                        $twig->addExtension($extension);
                    } else {
                        throw new RuntimeException(sprintf(
                            'Plates extension [%s] is not a object.',
                            (string) $extension
                        ));
                    }
                }
            }
            // @codeCoverageIgnoreEnd

            $this->parserInstance = $twig;
        }

        return $this->parserInstance;
    }

    /**
     * Get the twig environment.
     *
     * @param array $options
     *
     * @return \Twig_Environment
     */
    protected function getTwigEnvironment(array $options): Twig_Environment
    {
        return new Twig_Environment(
            $this->getLoader(),
            $options
        );
    }

    /**
     * Twig paths loader.
     *
     * @return \Twig_LoaderInterface
     */
    protected function getLoader(): Twig_LoaderInterface
    {
        return new Twig_Loader_Filesystem($config['paths']);
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
        $templateFile = $exception->getTemplateFile();
        $templateLine = $exception->getTemplateLine();
        $file         = null;

        if ($templateFile && file_exists($templateFile)) {
            $file = $templateFile;
        }

        if ($file !== null) {
            $exception = new ErrorException(
                $exception->getMessage(),
                0,
                1,
                $file,
                $templateLine,
                $exception
            );
        }

        throw $exception;
    }
}
