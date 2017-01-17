<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig;

use Twig_ExistsLoaderInterface;
use Twig_LoaderInterface;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;

class Loader implements Twig_LoaderInterface, Twig_ExistsLoaderInterface
{
    /**
     * @var \Viserio\Component\Contracts\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Viserio\Component\Contracts\View\Finder
     */
    protected $finder;

    /**
     * Twig file extension.
     *
     * @var string
     */
    protected $extension;

    /**
     * Template lookup cache.
     *
     * @var array
     */
    protected $cache = [];

    /**
     * @param \Viserio\Component\Contracts\Filesystem\Filesystem $files
     * @param \Viserio\Component\Contracts\View\Finder           $finder
     * @param string                                             $extension Twig file extension.
     */
    public function __construct(FilesystemContract $files, FinderContract $finder, string $extension = 'twig')
    {
        $this->files     = $files;
        $this->finder    = $finder;
        $this->extension = $extension;
    }
}
