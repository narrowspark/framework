<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Providers;

use Interop\Config\ConfigurationTrait;
use Interop\Config\RequiresConfig;
use Interop\Config\RequiresMandatoryOptions;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Twig_Extension_Profiler;
use Twig_Profiler_Profile;
use Viserio\Bridge\Twig\DataCollector\TwigDataCollector;
use Viserio\Bridge\Twig\TwigEnvironment;
use Viserio\Component\Contracts\Support\Traits\CreateConfigurationTrait;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;

class TwigBridgeDataCollectorsServiceProvider implements ServiceProvider, RequiresConfig, RequiresMandatoryOptions
{
    use ConfigurationTrait;
    use CreateConfigurationTrait;

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
    public function dimensions(): iterable
    {
        return ['viserio', 'webprofiler'];
    }

    /**
     * {@inheritdoc}
     */
    public function mandatoryOptions(): iterable
    {
        return [
            'collector' => [
                'twig',
            ],
        ];
    }

    public static function createWebProfiler(ContainerInterface $container): WebProfilerContract
    {
        if (count($this->config) === 0) {
            $this->createConfiguration($container);
        }

        $profiler = $container->get(WebProfilerContract::class);

        if ($this->config['collector']['twig'] !== false) {
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
        if (count($this->config) === 0) {
            $this->createConfiguration($container);
        }

        $twig = $container->get(TwigEnvironment::class);

        if ($this->config['collector']['twig'] !== false) {
            $twig->addExtension(new Twig_Extension_Profiler(
                $container->get(Twig_Profiler_Profile::class)
            ));
        }

        return $twig;
    }
}
