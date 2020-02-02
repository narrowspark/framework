<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Provider\Twig\Command;

use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;
use Viserio\Contract\Filesystem\Filesystem as FilesystemContract;

class CleanCommand extends AbstractCommand implements RequiresComponentConfigContract, RequiresMandatoryConfigContract
{
    /**
     * The default command name.
     *
     * @var string
     */
    protected static $defaultName = 'twig:clear';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Clean the Twig Cache';

    /**
     * The cache folder path.
     *
     * @var string
     */
    protected string $cacheDir;

    /**
     * A Filesystem instance.
     *
     * @var \Viserio\Contract\Filesystem\Filesystem
     */
    private FilesystemContract $filesystem;

    /**
     * Create a new CleanCommand instance.
     *
     * @param \Viserio\Contract\Filesystem\Filesystem $filesystem
     * @param string                                  $cache
     */
    public function __construct(FilesystemContract $filesystem, string $cache)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->cacheDir = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryConfig(): iterable
    {
        return [
            'engines' => [
                'twig' => [
                    'options' => [
                        'cache',
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        $this->filesystem->deleteDirectory($this->cacheDir);

        @\rmdir($this->cacheDir);

        if (\is_dir($this->cacheDir)) {
            $this->error('Twig cache failed to be cleaned.');

            return 1;
        }

        $this->info('Twig cache cleaned.');

        return 0;
    }
}
