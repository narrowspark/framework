<?php
declare(strict_types=1);
namespace Viserio\Component\View\Engines;

use Parsedown;
use Viserio\Component\Contracts\View\Engine as EngineContract;

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
     * @param \Parsedown|\ParsedownExtra
     */
    public function __construct(Parsedown $markdown)
    {
        $this->markdown = $markdown;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $fileInfo, array $data = []): string
    {
        return $this->markdown->text(file_get_contents($fileInfo['path']));
    }
}
