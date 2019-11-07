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

namespace Viserio\Component\View\Engine;

use Exception;
use Parsedown;
use ParsedownExtra;
use Viserio\Contract\View\Engine as EngineContract;
use Viserio\Contract\View\Exception\RuntimeException;

class MarkdownEngine implements EngineContract
{
    /**
     * A Parsedown or ParsedownExtra instance.
     *
     * @var Parsedown|ParsedownExtra
     */
    protected $markdown;

    /**
     * Create a new markdown engine instance.
     *
     * @param null|Parsedown|ParsedownExtra $markdown
     *
     * @throws \Viserio\Contract\View\Exception\RuntimeException
     * @throws Exception
     */
    public function __construct(?Parsedown $markdown = null)
    {
        /** @codeCoverageIgnoreStart */
        if ($markdown === null) {
            if (\class_exists(ParsedownExtra::class)) {
                $markdown = new ParsedownExtra();
            } elseif (\class_exists(Parsedown::class)) {
                $markdown = new Parsedown();
            }
        }

        if ($markdown === null) {
            throw new RuntimeException('[\ParsedownExtra] or [\Parsedown] class not found.');
        }
        /** @codeCoverageIgnoreEnd */
        $this->markdown = $markdown;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultNames(): array
    {
        return ['md'];
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $fileInfo, array $data = []): string
    {
        return $this->markdown->text(\file_get_contents($fileInfo['path']));
    }
}
