<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Proxy;

use Closure;
use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use ProxyManager\Proxy\LazyLoadingInterface;

final class ProxyFactory
{
    /**
     * If true, write the proxies to disk to improve performances.
     *
     * @var bool
     */
    private $writeProxiesToFile;

    /**
     * Directory where to write the proxies (if $writeProxiesToFile is enabled).
     *
     * @var null|string
     */
    private $proxyDirectory;

    /**
     * A LazyLoadingValueHolderFactory instance.
     *
     * @var null|LazyLoadingValueHolderFactory
     */
    private $proxyManager;

    /**
     * Create a new ProxyFactory instance.
     *
     * @param bool        $writeProxiesToFile
     * @param null|string $proxyDirectory
     */
    public function __construct(bool $writeProxiesToFile = false, string $proxyDirectory = null)
    {
        $this->writeProxiesToFile = $writeProxiesToFile;
        $this->proxyDirectory     = $proxyDirectory;
    }

    /**
     * Creates a new lazy proxy instance of the given class with
     * the given initializer.
     *
     * @param string   $className   name of the class to be proxied
     * @param \Closure $initializer initializer to be passed to the proxy
     *
     * @return LazyLoadingInterface
     */
    public function createProxy(string $className, Closure $initializer): LazyLoadingInterface
    {
        $this->createProxyManager();

        return $this->proxyManager->createProxy($className, $initializer);
    }

    private function createProxyManager(): void
    {
        if ($this->proxyManager !== null) {
            return;
        }

        if (! class_exists(Configuration::class)) {
            throw new \RuntimeException('The ocramius/proxy-manager library is not installed. Lazy injection requires that library to be installed with Composer in order to work. Run "composer require ocramius/proxy-manager:^2.2".');
        }

        $config = new Configuration();

        if ($this->writeProxiesToFile) {
            $config->setProxiesTargetDir($this->proxyDirectory);
            $config->setGeneratorStrategy(new FileWriterGeneratorStrategy(new FileLocator($this->proxyDirectory)));

            \spl_autoload_register($config->getProxyAutoloader());
        } else {
            $config->setGeneratorStrategy(new EvaluatingGeneratorStrategy());
        }

        $this->proxyManager = new LazyLoadingValueHolderFactory($config);
    }
}
