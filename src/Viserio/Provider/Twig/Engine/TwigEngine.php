<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Engine;

use Psr\Container\ContainerInterface;
use RuntimeException;
use Twig\Environment;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\View\Engine\AbstractBaseEngine;

class TwigEngine extends AbstractBaseEngine implements ProvidesDefaultOptionsContract
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
     * @param \Twig\Environment                          $twig
     * @param \Psr\Container\ContainerInterface|iterable $data
     */
    public function __construct(Environment $twig, $data)
    {
        if ($data instanceof ContainerInterface) {
            $this->container = $data;
        }

        $this->resolvedOptions = self::resolveOptions($data);
        $this->twig            = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
    {
        return array_merge(
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
    public static function getDefaultOptions(): iterable
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
        $twig = $this->addExtensions($this->twig, $this->resolvedOptions['engines']['twig']);

        return $twig->render($fileInfo['name'] ?? '', $data);
    }

    /**
     * Add extensions to twig environment.
     *
     * @param \Twig\Environment $twig
     * @param array             $config
     */
    protected function addExtensions(Environment $twig, array $config): Environment
    {
        if (isset($config['extensions']) && is_array($config['extensions'])) {
            foreach ($config['extensions'] as $extension) {
                if ($this->container !== null && is_string($extension) && $this->container->has($extension)) {
                    $twig->addExtension($this->container->get($extension));
                } elseif (is_object($extension)) {
                    $twig->addExtension($extension);
                } else {
                    throw new RuntimeException(sprintf(
                        'Twig extension [%s] is not a object.',
                        (string) $extension
                    ));
                }
            }
        }

        return $twig;
    }
}
