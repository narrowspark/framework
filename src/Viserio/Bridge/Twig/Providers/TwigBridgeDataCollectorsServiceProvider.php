<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Twig_Extension_Profiler;
use Twig_Profiler_Profile;
use Viserio\Bridge\Twig\DataCollector\TwigDataCollector;
use Viserio\Bridge\Twig\TwigEnvironment;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;

class TwigBridgeDataCollectorsServiceProvider implements ServiceProvider, RequiresConfig, RequiresMandatoryOptions
{
    public static function __callStatic($name, array $arguments)
    {
        if ($name !== 'configureOptionsStatic') {
            return;
        }

        return $this->configureOptions($arguments[0]);
    }

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Twig_Profiler_Profile::class => [self::class, 'createTwigProfilerProfile'],
            TwigEnvironment::class       => [self::class, 'createTwigEnvironment'],
            WebProfilerContract::class   => [self::class, 'createWebProfiler'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'webprofiler'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return [
            'collector' => [
                'twig',
            ],
        ];
    }

    public static function createWebProfiler(ContainerInterface $container): WebProfilerContract
    {
        self::configureOptionsStatic($container);

        $profiler = $container->get(WebProfilerContract::class);

        if ($this->options['collector']['twig'] !== false) {
            $profiler->addCollector(new TwigDataCollector(
                $container->get(Twig_Profiler_Profile::class)
            ));
        }

        return $profiler;
    }

    public static function createTwigProfilerProfile(): Twig_Profiler_Profile
    {
        return new Twig_Profiler_Profile();
    }

    public static function createTwigEnvironment(ContainerInterface $container): TwigEnvironment
    {
        self::configureOptionsStatic($container);

        $twig = $container->get(TwigEnvironment::class);

        if ($this->options['collector']['twig'] !== false) {
            $twig->addExtension(new Twig_Extension_Profiler(
                $container->get(Twig_Profiler_Profile::class)
            ));
        }

        return $twig;
    }
}
