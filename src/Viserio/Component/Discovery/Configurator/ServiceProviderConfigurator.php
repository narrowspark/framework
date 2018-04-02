<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery\Configurator;

use Viserio\Component\Discovery\Package;
use Viserio\Component\Foundation\AbstractKernel;

final class ServiceProviderConfigurator extends AbstractConfigurator
{
    /**
     * {@inheritdoc}
     */
    public function configure(Package $package): void
    {
        $this->write('Enabling the package as a Service Provider.');

        $file = $this->getConfFile();

        if ($this->isPHPFileMarked($package->getName(), $file)) {
            return;
        }

        $global = [];
        $local  = [];

        foreach ($package->getConfiguratorOptions('providers', Package::CONFIGURE) as $name => $providers) {
            if ($name === 'global') {
                foreach ($providers as $provider) {
                    $class = \mb_strpos($provider, '::class') !== false ? $provider : $provider . '::class';

                    $global[$class] = $class;
                }
            }

            if ($name === 'local') {
                foreach ($providers as $provider) {
                    $class = \mb_strpos($provider, '::class') !== false ? $provider : $provider . '::class';

                    $local[$class] = $class;
                }
            }
        }

        if (\count($global) === 0 && \count($local) === 0) {
            return;
        }

        if (\file_exists($file)) {
            $this->extendFileContent($file, $package, $global, $local);
        } else {
            $this->generateServiceProviderFile($file, $package, $global, $local);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unconfigure(Package $package): void
    {
    }

    /**
     * Build and dump serviceproviders config file.
     *
     * @param string                               $file
     * @param \Viserio\Component\Discovery\Package $package
     * @param array                                $global
     * @param array                                $local
     *
     * @return void
     */
    private function generateServiceProviderFile(string $file, Package $package, array $global, array $local): void
    {
        $kernelExists = \class_exists(AbstractKernel::class);

        $content = '<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Autoloaded Service Providers
|--------------------------------------------------------------------------
|
| The service providers listed here will be automatically loaded on the
| request to your application. Feel free to add your own services to
| this array to grant expanded functionality to your applications.
|
*/
$providers = [
';
        $content .= $this->buildGlobalServiceProviderContent($package, $global, $local, $kernelExists);

        $content .= '];';

        if ($kernelExists === true && \count($local) !== 0) {
            $content .= '
/*
 |--------------------------------------------------------------------------
 | Testing And Local Autoloaded Service Providers
 |--------------------------------------------------------------------------
 |
 | Some providers are only used while developing the application or during
 | the unit and functional tests. Therefore, they are only registered
 | when the application runs in \'local\' or \'testing\' environments. This allows
 | to increase application performance in the production environment.
 |
 */
if ($kernel->isLocal() || $kernel->isRunningUnitTests()) {
';
            $content .= $this->buildLocalServiceProviderContent($package, $local);

            $content .='}';
        }

        $content .= "\n\nreturn \$providers;\n";

        $this->dump($file, $content);
    }

    /**
     * @param string                               $file
     * @param \Viserio\Component\Discovery\Package $package
     * @param array                                $global
     * @param array                                $local
     *
     * @return void
     */
    private function extendFileContent(string $file, Package $package, array $global, array $local): void
    {
        $content            = \file_get_contents($file);
        $kernelExists       = \class_exists(AbstractKernel::class);

        $endOfArrayPosition = \mb_strpos($content, "];\n");
        $globalContent      = $this->buildGlobalServiceProviderContent($package, $global, $local, $kernelExists);
        $content            = $this->stringInsert($content, $globalContent, $endOfArrayPosition);

        if ($kernelExists === true) {
            $endOfIfPosition = \mb_strpos($content, "}\n");

            $localContent = $this->buildLocalServiceProviderContent($package, $local);

            $content = $this->stringInsert($content, $localContent, $endOfIfPosition);
        }

        \unlink($file);

        $this->dump($file, $content);
    }

    /**
     * Insert string at specified position.
     *
     * @param string $string
     * @param string $insertStr
     * @param int    $pos
     *
     * @return string
     */
    private function stringInsert(string $string, string $insertStr, int $pos): string
    {
        return \mb_substr($string, 0, $pos) . $insertStr . \mb_substr($string, $pos);
    }

    /**
     * Get service providers config file.
     *
     * @return string
     */
    private function getConfFile(): string
    {
        return $this->expandTargetDir($this->options, '%CONFIG_DIR%/serviceproviders.php');
    }

    /**
     * Dump file content.
     *
     * @param string $file
     * @param string $content
     *
     * @return void
     */
    private function dump(string $file, string $content): void
    {
        \file_put_contents($file, $content);

        if (\function_exists('opcache_invalidate')) {
            \opcache_invalidate($file);
        }
    }

    /**
     * @param \Viserio\Component\Discovery\Package $package
     * @param array                                $global
     * @param array                                $local
     * @param bool                                 $kernelExists
     *
     * @return string
     */
    private function buildGlobalServiceProviderContent(Package $package, array $global, array $local, bool $kernelExists): string
    {
        $globalContent = $this->markPHPData($package->getName());

        foreach ($global as $provider) {
            $globalContent .= '    ' . $provider . ",\n";

            $this->write(\sprintf('Enabling "%s" as a global service provider.', $provider));
        }

        return $globalContent;
    }

    /**
     * @param \Viserio\Component\Discovery\Package $package
     * @param array                                $local
     *
     * @return string
     */
    private function buildLocalServiceProviderContent(Package $package, array $local): string
    {
        $localContent = $this->markPHPData($package->getName());

        foreach ($local as $provider) {
            $localContent .= '    $providers[] = ' . $provider . ";\n";

            $this->write(\sprintf('Enabling "%s" as a local service provider.', $provider));
        }

        return $localContent;
    }

    /**
     * @param string $packageName
     * @param string $file
     *
     * @return bool
     */
    private function isPHPFileMarked(string $packageName, string $file): bool
    {
        return \is_file($file) && \mb_strpos(\file_get_contents($file), \sprintf("* > %s\n    */", $packageName)) !== false;
    }

    /**
     * Mark a php file with the package name.
     *
     * @param string $packageName
     *
     * @return string
     */
    private function markPHPData(string $packageName): string
    {
        return \sprintf("   /**\n    * > %s\n    */\r\n", $packageName);
    }
}
