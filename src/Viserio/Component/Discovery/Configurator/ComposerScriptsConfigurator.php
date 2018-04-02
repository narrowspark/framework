<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery\Configurator;

use Composer\Factory;
use Composer\Json\JsonFile;
use Composer\Json\JsonManipulator;
use Viserio\Component\Discovery\Package;

final class ComposerScriptsConfigurator extends AbstractConfigurator
{
    /**
     * {@inheritdoc}
     */
    public function configure(Package $package): void
    {
        [$json, $autoScripts] = $this->getComposerContentAndAutoScripts();

        $autoScripts = \array_merge($autoScripts, $package->getConfiguratorOptions('composer_script', Package::CONFIGURE));

        $this->manipulateAndWrite($json, $autoScripts);
    }

    /**
     * {@inheritdoc}
     */
    public function unconfigure(Package $package): void
    {
        [$json, $autoScripts] = $this->getComposerContentAndAutoScripts();

        foreach (\array_keys($package->getConfiguratorOptions('composer_script', Package::UNCONFIGURE)) as $cmd) {
            unset($autoScripts[$cmd]);
        }

        $this->manipulateAndWrite($json, $autoScripts);
    }

    /**
     * Get root composer.json content and the auto-scripts section.
     *
     * @return array
     */
    private function getComposerContentAndAutoScripts(): array
    {
        $json = new JsonFile(Factory::getComposerFile());

        $jsonContents = $json->read();

        $autoScripts = $jsonContents['scripts']['auto-scripts'] ?? [];

        return [$json, $autoScripts];
    }

    /**
     * Manipulate the root composer.json with given auto-scripts.
     *
     * @param \Composer\Json\JsonFile $json
     * @param array                   $autoScripts
     */
    private function manipulateAndWrite(JsonFile $json, array $autoScripts): void
    {
        $manipulator = new JsonManipulator(\file_get_contents($json->getPath()));
        $manipulator->addSubNode('scripts', 'auto-scripts', $autoScripts);

        \file_put_contents($json->getPath(), $manipulator->getContents());
    }
}
