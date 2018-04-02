<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery\Configurator;

use Viserio\Component\Discovery\Package;

final class CopyFromPackageConfigurator extends AbstractConfigurator
{
    /**
     * {@inheritdoc}
     */
    public function configure(Package $package): void
    {
        $this->write('Copying files.');

        foreach ($package->getConfiguratorOptions('copy', Package::CONFIGURE) as $from => $to) {
            $status = $this->filesystem->copy(
                $this->path->concatenate([$package->getPackagePath(), $from]),
                $this->path->concatenate([$this->path->getWorkingDir(), $to])
            );

            if ($status === true) {
                $this->write(\sprintf('Created <fg=green>"%s"</>', $this->path->relativize($to)));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unconfigure(Package $package): void
    {
        $this->write('Removing files');

        foreach ($package->getConfiguratorOptions('copy', Package::UNCONFIGURE) as $source) {
            $status = $this->filesystem->remove($this->path->concatenate([$this->path->getWorkingDir(), $source]));

            if ($status === true) {
                $this->write(\sprintf('Removed <fg=green>"%s"</>', $this->path->relativize($source)));
            }
        }
    }
}
