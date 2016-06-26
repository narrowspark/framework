<?php
namespace Viserio\Translation;

use RuntimeException;
use Viserio\Contracts\{
    Filesystem\Filesystem as FilesystemContract,
    Translation\TransPublisher as TransPublisherContract
};

class TransPublisher implements TransPublisherContract
{
    /**
     * The filesystem instance.
     *
     * @var \Viserio\Contracts\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * The TransManager instance.
     *
     * @var TranslationManager
     */
    private $manager;

     /**
     * The application lang path.
     *
     * @var string
     */
    private $langPath;

    /**
     * Make TransPublisher instance.
     *
     * @param \Viserio\Contracts\Filesystem\Filesystem $filesystem
     * @param TranslationManager                       $manager
     * @param string                                   $langPath
     */
    public function __construct(FilesystemContract $filesystem, TranslationManager $manager, $langPath)
    {
        $this->filesystem = $filesystem;
        $this->manager = $manager;
        $this->langPath = realpath($langPath);
    }

    /**
     * {@inheritdoc}
     */
    public function publish(string $localeKey, bool $force = false): bool
    {
        $localeKey = trim($localeKey);

        if ($this->isDefault($localeKey)) {
            return true;
        }

        if ( ! $this->isSupported($locale)) {
            throw new RuntimeException("The locale [$locale] is not supported.");
        }

        $srcPath  = $localeKey;
        $destPath = $this->langPath . '/' . $localeKey;

        $this->isPublishable($localeKey, $destPath, $force);

        return $this->filesystem->copyDirectory($srcPath, $destPath);
    }

    /**
     * {@inheritdoc}
     */
    public function isDefault(string $locale): bool
    {
        return $locale === 'en';
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported(string $key): bool
    {
        return $this->manager->has($key);
    }

    /**
     * Check if locale is publishable.
     *
     * @param string $locale
     * @param string $path
     * @param bool   $force
     *
     * @throws \RuntimeException
     */
    private function isPublishable(string $locale, string $path, bool $force)
    {
        if (! (
            $this->filesystem->exists($path) &&
            $this->filesystem->isDirectory($path)
            ) ||
            empty($this->filesystem->files($path))
        ) {
            return;
        }

        if (! $force) {
            throw new RuntimeException(
                "You can't publish the translations because the [$locale] folder is not empty. ".
                "To override the translations, try to clean/delete the [$locale] folder or force the publication."
            );
        }
    }
}
