<?php
declare(strict_types=1);
namespace Viserio\Component\View\Engine;

use Parsedown;
use ParsedownExtra;
use RuntimeException;
use Viserio\Component\Contract\View\Engine as EngineContract;

class MarkdownEngine implements EngineContract
{
    /**
     * A Parsedown or ParsedownExtra instance.
     *
     * @var \Parsedown|\ParsedownExtra
     */
    protected $markdown;

    /**
     * Create a new markdown engine instance.
     *
     * @param null|\Parsedown|\ParsedownExtra $markdown
     *
     * @throws \RuntimeException
     */
    public function __construct(Parsedown $markdown = null)
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
    public function get(array $fileInfo, array $data = []): string
    {
        return $this->markdown->text(\file_get_contents($fileInfo['path']));
    }
}
