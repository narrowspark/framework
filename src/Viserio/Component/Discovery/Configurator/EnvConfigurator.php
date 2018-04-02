<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery\Configurator;

use Viserio\Component\Discovery\Package;

final class EnvConfigurator extends AbstractConfigurator
{
    /**
     * {@inheritdoc}
     */
    public function configure(Package $package): void
    {
        $this->write('Added environment variable defaults');

        $distenv = getcwd() . '/.env.dist';

        if (! \is_file($distenv) || $this->isFileMarked($package->getName(), $distenv)) {
            return;
        }

        $data = '';

        foreach ($package->getConfiguratorOptions('env', Package::CONFIGURE) as $key => $value) {
            if ($key[0] === '#' && \is_numeric(\mb_substr($key, 1))) {
                $data .= '# ' . $value . "\n";

                continue;
            }

            $value = $this->expandTargetDir($this->options, $value);

            if (\strpbrk($value, " \t\n&!\"") !== false) {
                $value = '"' . \str_replace(['\\', '"', "\t", "\n"], ['\\\\', '\\"', '\t', '\n'], $value) . '"';
            }

            $data .= "$key=$value\n";
        }

        if (! file_exists(getcwd() . '/.env')) {
            \copy($distenv, getcwd() . '/.env');
        }

        $data = $this->markData($package->getName(), $data);

        \file_put_contents($distenv, $data, \FILE_APPEND);
        \file_put_contents(getcwd() . '/.env', $data, \FILE_APPEND);
    }

    /**
     * {@inheritdoc}
     */
    public function unconfigure(Package $package): void
    {
        $this->write('Remove environment variables');

        foreach (['.env', '.env.dist'] as $file) {
            $env = getcwd() . '/' . $file;

            if (! \file_exists($env)) {
                continue;
            }

            $count = 0;

            $contents = \preg_replace(
                \sprintf('{%s*###> %s ###.*###< %s ###%s+}s', "\n", $package->getName(), $package->getName(), "\n"),
                '',
                \file_get_contents($env),
                -1,
                $count
            );

            if (! $count) {
                continue;
            }

            $this->write(sprintf('Removing environment variables from %s', $file));

            \file_put_contents($env, $contents);
        }
    }
}
