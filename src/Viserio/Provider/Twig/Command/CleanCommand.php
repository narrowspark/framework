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

use Symfony\Component\Filesystem\Filesystem;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOption as RequiresMandatoryOptionContract;

class CleanCommand extends AbstractCommand implements RequiresComponentConfigContract, RequiresMandatoryOptionContract
{
    use OptionsResolverTrait;

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
     * Resolved options.
     *
     * @var array
     */
    protected $resolvedOptions = [];

    /**
     * Create a new CleanCommand instance.
     *
     * @param array|\ArrayAccess $config
     */
    public function __construct($config)
    {
        parent::__construct();

        $this->resolvedOptions = self::resolveOptions($config);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
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
        $cacheDir = $this->resolvedOptions['engines']['twig']['options']['cache'];

        (new Filesystem())->remove($cacheDir);

        @\rmdir($cacheDir);

        if (\is_dir($cacheDir)) {
            $this->error('Twig cache failed to be cleaned.');

            return 1;
        }

        $this->info('Twig cache cleaned.');

        return 0;
    }
}
