<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Json\JsonManipulator;
use Composer\Package\Locker;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class Discovery implements PluginInterface, EventSubscriberInterface
{
    public const EXTRA_CONFIG_NAME = 'narrowspark';

    /**
     * A composer instance.
     *
     * @var \Composer\Composer
     */
    private $composer;

    /**
     * @var \Composer\IO\IOInterface
     */
    private $io;

    /**
     * A lock instance.
     *
     * @var \Viserio\Component\Discovery\Lock
     */
    private $lock;

    /**
     * A configurator instance.
     *
     * @var \Viserio\Component\Discovery\Configurator
     */
    private $configurator;

    /**
     * A composer config instance.
     *
     * @var \Composer\Config
     */
    private $config;

    /**
     * A array of project options.
     *
     * @var array
     */
    private $projectOptions;

    /**
     * The composer vendor path.
     *
     * @var string
     */
    private $vendorDir;

    /**
     * Check if composer.lock should be updated.
     *
     * @var bool
     */
    private $shouldUpdateComposerLock = false;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io       = $io;
        $this->config   = $composer->getConfig();

        $extra = $composer->getPackage()->getExtra();

        $this->projectOptions = $extra[static::EXTRA_CONFIG_NAME] ?? [];
        $this->vendorDir      = $this->config->get('vendor-dir');
        $this->configurator   = new Configurator($this->composer, $this->io);
        $this->lock           = new Lock(str_replace('composer.json', static::EXTRA_CONFIG_NAME . '.lock', Factory::getComposerFile()));
    }

    /**
     * Get the configurator instance.
     *
     * @return \Viserio\Component\Discovery\Configurator
     */
    public function getConfigurator(): Configurator
    {
        return $this->configurator;
    }

    /**
     * Execute on composer create project event.
     *
     * @param \Composer\Script\Event $event
     *
     * @throws \Exception
     */
    public function configureProject(Event $event): void
    {
        $json = new JsonFile(Factory::getComposerFile());

        $manipulator = new JsonManipulator(\file_get_contents($json->getPath()));

        // new projects are most of the time proprietary
        $manipulator->addMainKey('license', 'proprietary');

        // 'name' and 'description' are only required for public packages
        $manipulator->removeProperty('name');
        $manipulator->removeProperty('description');

        file_put_contents($json->getPath(), $manipulator->getContents());

        $this->updateComposerLock();
    }

    /**
     * Execute on composer install event.
     *
     * @param \Composer\Script\Event $event
     *
     * @return void
     */
    public function install(Event $event): void
    {
        $this->update($event);
    }

    /**
     * Execute on composer update event.
     *
     * @param \Composer\Script\Event $event
     * @param array                  $operations
     *
     * @return void
     */
    public function update(Event $event, array $operations = []): void
    {
        if (! \file_exists(getcwd() . '/.env') && \file_exists(getcwd() . '/.env.dist')) {
            \copy(getcwd() . '/.env.dist', getcwd() . '/.env');
        }
    }

    /**
     * Execute on composer dump event.
     *
     * @param \Composer\Script\Event $event
     *
     * @throws \Exception
     *
     * @return void
     */
    public function dump(Event $event): void
    {
        $this->io->writeError(\sprintf('<info>%s operations</>', \ucwords(static::EXTRA_CONFIG_NAME)));

        $options = array_merge(
            [
                'allow_auto_install' => false,
                'ignore'             => [
                    'package' => [],
                ],
            ],
            $this->projectOptions
        );

        $allowInstall = false;

        foreach ($this->getInstalledPackagesExtraConfiguration() as $name => $packageConfig) {
            if (\array_key_exists($name, $options['ignore']['package'])) {
                $this->io->write(\sprintf('<info>Package "%s" was ignored.</>', $name));

                continue;
            }

            if ($allowInstall === false && $options['allow_auto_install'] === false) {
                $answer = $this->io->askAndValidate(
                    $this->getPackageQuestion($packageConfig),
                    function ($value) {
                        if ($value === null) {
                            return 'n';
                        }

                        $value = mb_strtolower($value[0]);

                        if (! in_array($value, ['y', 'n', 'a', 'p'], true)) {
                            throw new \InvalidArgumentException('Invalid choice');
                        }

                        return $value;
                    },
                    null,
                    'n'
                );

                if ($answer === 'n') {
                    continue;
                } elseif ($answer === 'a') {
                    $allowInstall = true;
                } elseif ($answer === 'p') {
                    $allowInstall = true;

                    $this->manipulateComposerJsonWithAllowAutoInstall();

                    $this->shouldUpdateComposerLock = true;
                }
            }

            $package = new Package($name, $this->vendorDir, $packageConfig);

            if (isset($packageConfig['configure'])) {
                $this->io->writeError(\sprintf('  - Configuring %s', $name));

                $this->configurator->configure($package);
            }

            if (isset($packageConfig['unconfigure'])) {
                $this->io->writeError(\sprintf('  - Unconfiguring %s', $name));

                $this->configurator->unconfigure($package);
            }
        }

        $this->lock->write();

        if ($this->shouldUpdateComposerLock) {
            $this->updateComposerLock();
        }
    }

    /**
     * @param \Composer\Script\Event $event
     *
     * @return void
     */
    public function executeAutoScripts(Event $event): void
    {
        $event->stopPropagation();

        // force reloading scripts as we might have added and removed during this run
        $json = new JsonFile(Factory::getComposerFile());

        $jsonContents = $json->read();

        $executor = new ScriptExecutor($this->composer, $this->io, $this->projectOptions);

        foreach ($jsonContents['scripts']['auto-scripts'] as $cmd => $type) {
            $executor->execute($type, $cmd);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_CREATE_PROJECT_CMD => 'configureProject',
            ScriptEvents::POST_INSTALL_CMD        => 'install',
            ScriptEvents::POST_UPDATE_CMD         => 'update',
            ScriptEvents::POST_AUTOLOAD_DUMP      => 'dump',
            'auto-scripts'                        => 'executeAutoScripts',
        ];
    }

    /**
     * Update composer.lock file the composer.json do change.
     *
     * @throws \Exception
     *
     * @return void
     */
    private function updateComposerLock(): void
    {
        $lock         = \mb_substr(Factory::getComposerFile(), 0, -4) . 'lock';
        $composerJson = \file_get_contents(Factory::getComposerFile());

        $lockFile = new JsonFile($lock, null, $this->io);
        $locker   = new Locker(
            $this->io,
            $lockFile,
            $this->composer->getRepositoryManager(),
            $this->composer->getInstallationManager(),
            $composerJson
        );

        $lockData                 = $locker->getLockData();
        $lockData['content-hash'] = Locker::getContentHash($composerJson);

        $lockFile->write($lockData);
    }

    /**
     * Get found narrowspark configurations from installed packages.
     *
     * @throws \Exception
     *
     * @return array
     */
    private function getInstalledPackagesExtraConfiguration(): array
    {
        $composerInstalledFilePath    = $this->vendorDir . '/composer/installed.json';
        $composerInstalledFileContent = (array) \file_get_contents($composerInstalledFilePath);

        foreach ($composerInstalledFileContent as $package) {
            if (isset($package['extra'][static::EXTRA_CONFIG_NAME])) {
                $this->lock->add(
                    $package['name'],
                    \array_merge(
                        [
                            'package_version' => $package['version'],
                            'url'             => $package['support']['source'] ?? ($package['homepage'] ?? 'url not found'),
                        ],
                        (array) $package['extra'][static::EXTRA_CONFIG_NAME]
                    )
                );
            }
        }

        $this->lock->write();

        return $this->lock->read();
    }

    /**
     * Add extra option "allow_auto_install" to composer.json.
     *
     * @return void
     */
    private function manipulateComposerJsonWithAllowAutoInstall(): void
    {
        $json        = new JsonFile(Factory::getComposerFile());
        $manipulator = new JsonManipulator(file_get_contents($json->getPath()));
        $manipulator->addSubNode('extra', static::EXTRA_CONFIG_NAME . '.allow_auto_install', true);

        \file_put_contents($json->getPath(), $manipulator->getContents());
    }

    /**
     * @param $packageConfig
     *
     * @return string
     */
    private function getPackageQuestion($packageConfig): string
    {
        $question = sprintf('    Review the package at %s.
    Do you want to execute this package?
    [<comment>y</>] Yes
    [<comment>n</>] No
    [<comment>a</>] Yes for all packages, only for the current installation session
    [<comment>p</>] Yes permanently, never ask again for this project
    (defaults to <comment>n</>): ', $packageConfig['url']);

        return $question;
    }
}
