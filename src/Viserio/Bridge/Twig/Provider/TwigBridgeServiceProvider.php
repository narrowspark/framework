<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Twig\Environment as TwigEnvironment;
use Twig\Lexer;
use Viserio\Bridge\Twig\Extension\ConfigExtension;
use Viserio\Bridge\Twig\Extension\DumpExtension;
use Viserio\Bridge\Twig\Extension\SessionExtension;
use Viserio\Bridge\Twig\Extension\StrExtension;
use Viserio\Bridge\Twig\Extension\TranslatorExtension;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Session\Store as StoreContract;
use Viserio\Component\Contracts\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Component\Support\Str;

class TwigBridgeServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            TwigEnvironment::class => [self::class, 'extendTwigEnvironment'],
        ];
    }

    /**
     * Extend the twig environment.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return \Twig\Environment
     */
    public static function extendTwigEnvironment(ContainerInterface $container, ?callable $getPrevious = null): ?TwigEnvironment
    {
        $twig = \is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($twig !== null) {
            if ($container->has(Lexer::class)) {
                $twig->setLexer($container->get(Lexer::class));
            }

            if ($twig->isDebug() && \class_exists(VarCloner::class)) {
                $twig->addExtension(new DumpExtension());
            }

            self::registerViserioTwigExtension($twig, $container);
        }

        return $twig;
    }

    /**
     * Register viserio twig extension.
     *
     * @param \Twig\Environment                 $twig
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    protected static function registerViserioTwigExtension(TwigEnvironment $twig, ContainerInterface $container): void
    {
        if ($container->has(TranslationManagerContract::class)) {
            $twig->addExtension(new TranslatorExtension($container->get(TranslationManagerContract::class)));
        }

        if (\class_exists(Str::class)) {
            $twig->addExtension(new StrExtension());
        }

        if ($container->has(StoreContract::class)) {
            $twig->addExtension(new SessionExtension($container->get(StoreContract::class)));
        }

        if ($container->has(RepositoryContract::class)) {
            $twig->addExtension(new ConfigExtension($container->get(RepositoryContract::class)));
        }
    }
}
