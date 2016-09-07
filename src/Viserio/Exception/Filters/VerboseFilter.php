<?php
declare(strict_types=1);
namespace Viserio\Exception\Filters;

use Psr\Http\Message\RequestInterface;
use Throwable;
use Viserio\Contracts\Exception\Filter as FilterContract;

class VerboseFilter implements FilterContract
{
    /**
     * Is debug mode enabled?
     *
     * @var bool
     */
    protected $debug;

    /**
     * Create a new verbose filter instance.
     *
     * @param bool $debug
     */
    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(
        array $displayers,
        RequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array {
        if ($this->debug !== true) {
            foreach ($displayers as $index => $displayer) {
                if ($displayer->isVerbose()) {
                    unset($displayers[$index]);
                }
            }
        }

        return array_values($displayers);
    }
}
