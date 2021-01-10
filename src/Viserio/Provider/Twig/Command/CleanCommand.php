<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
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
     */
    protected string $cacheDir;

    /**
     * A Filesystem instance.
     */
    private FilesystemContract $filesystem;

    /**
     * Create a new CleanCommand instance.
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
