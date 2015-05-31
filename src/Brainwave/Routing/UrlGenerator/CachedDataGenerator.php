<?php

namespace Brainwave\Routing\UrlGenerator;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Brainwave\Contracts\Routing\DataGenerator as DataGeneratorContract;
use Brainwave\Filesystem\Filesystem;

/**
 * CachedDataGenerator.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class CachedDataGenerator implements DataGeneratorContract
{
    /**
     * Filesystem.
     *
     * @var \Brainwave\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Brainwave\Contracts\Routing\DataGenerator
     */
    protected $wrappedGenerator;

    /**
     * @var
     */
    protected $cacheFile;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * Constructor.
     *
     * @param \Brainwave\Filesystem\Filesystem           $files
     * @param \Brainwave\Contracts\Routing\DataGenerator $wrappedGenerator
     * @param string                                     $cacheFile
     * @param bool                                       $debug
     */
    public function __construct(
        Filesystem $files,
        DataGeneratorContract $wrappedGenerator,
        $cacheFile,
        $debug = false
    ) {
        $this->wrappedGenerator = $wrappedGenerator;
        $this->cacheFile = $cacheFile;
        $this->debug = $debug;

        $this->files = $files;
    }

    /**
     * Get formatted route data for use by a URL generator.
     *
     * @return array
     */
    public function getData()
    {
        $files = $this->files;
        $cache = $this->cacheFile;

        if (!$files->exists($cache) || !$this->debug) {
            $routes = $this->wrappedGenerator->getData();
            $files->put($cache, '<?php return '.var_export($routes, true).';');
        }

        return (array) $files->getRequire($this->cacheFile);
    }
}
