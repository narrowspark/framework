<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery\Configurator;

use Viserio\Component\Discovery\Package;

final class GitIgnoreConfigurator extends AbstractConfigurator
{
    /**
     * {@inheritdoc}
     */
    public function configure(Package $package): void
    {
        $this->write('Added entries to .gitignore.');

        $gitignore = getcwd() . '/.gitignore';

        if ($this->isFileMarked($package->getName(), $gitignore)) {
            return;
        }

        $data = '';

        foreach ($package->getConfiguratorOptions('git_ignore', Package::CONFIGURE) as $value) {
            $value = $this->expandTargetDir($this->options, $value);
            $data .= "$value\n";
        }

        \file_put_contents($gitignore, "\n" . \ltrim($this->markData($package->getName(), $data), "\r\n"), \FILE_APPEND);
    }

    /**
     * {@inheritdoc}
     */
    public function unconfigure(Package $package): void
    {
        $file = getcwd() . '/.gitignore';

        if (! \file_exists($file)) {
            return;
        }

        $count = 0;

        $contents = \preg_replace(
            \sprintf('{%s*###> %s ###.*###< %s ###%s+}s', "\n", $package->getName(), $package->getName(), "\n"),
            "\n",
            \file_get_contents($file),
            -1,
            $count
        );

        if (empty($count)) {
            return;
        }

        $this->write('Removed entries in .gitignore.');

        \file_put_contents($file, \ltrim($contents, "\r\n"));
    }
}
