<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery\Configurator;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Viserio\Component\Discovery\Package;
use Viserio\Component\Discovery\Path;
use Viserio\Component\Discovery\Traits\ExpandTargetDirTrait;

abstract class AbstractConfigurator
{
    use ExpandTargetDirTrait;

    /**
     * @var \Composer\Composer
     */
    protected $composer;

    /**
     * @var \Composer\IO\IOInterface
     */
    protected $io;

    /**
     * @var \Composer\Util\Filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Path
     */
    protected $path;

    /**
     * AbstractConfigurator constructor.
     *
     * @param \Composer\Composer       $composer
     * @param \Composer\IO\IOInterface $io
     * @param array                    $options
     */
    public function __construct(Composer $composer, IOInterface $io, array $options = [])
    {
        $this->composer   = $composer;
        $this->io         = $io;
        $this->options    = $options;
        $this->path       = new Path(getcwd());
        $this->filesystem = new Filesystem();
    }

    /**
     * @param \Viserio\Component\Discovery\Package $package
     *
     * @return void
     */
    abstract public function configure(Package $package): void;

    /**
     * @param \Viserio\Component\Discovery\Package $package
     *
     * @return void
     */
    abstract public function unconfigure(Package $package): void;

    /**
     * @param array|string $messages
     *
     * @return void
     */
    protected function write($messages): void
    {
        if (! is_array($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $i => $message) {
            $messages[$i] = '    ' . $message;
        }

        $this->io->writeError($messages, true, IOInterface::VERBOSE);
    }

    /**
     * @param string $packageName
     * @param string $file
     *
     * @return bool
     */
    protected function isFileMarked(string $packageName, string $file): bool
    {
        return \is_file($file) && \mb_strpos(\file_get_contents($file), \sprintf('###> %s ###', $packageName)) !== false;
    }

    /**
     * @param string $packageName
     * @param string $data
     *
     * @return string
     */
    protected function markData(string $packageName, string $data): string
    {
        return sprintf('###> %s ###%s%s%s###< %s ###%s', $packageName, "\n", \rtrim($data, "\r\n"), "\n", $packageName, "\n");
    }
}
