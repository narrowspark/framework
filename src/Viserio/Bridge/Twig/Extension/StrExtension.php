<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Viserio\Component\Support\Str as ViserioStr;

class StrExtension extends AbstractExtension
{
    /**
     * @var array|callable
     */
    protected $callback = ViserioStr::class;

    /**
     * Return the string object callback.
     *
     * @return array|callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Set a new string callback.
     *
     * @param array|callable $callback
     *
     * @return void
     */
    public function setCallback($callback): void
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Viserio_Bridge_Twig_Extension_String';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'str_*',
                function (string $name) {
                    $arguments = \array_slice(\func_get_args(), 1);
                    $name      = ViserioStr::camelize($name);

                    return \call_user_func_array([$this->callback, (string) $name], $arguments);
                }
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'str_*',
                function (string $name) {
                    $arguments = \array_slice(\func_get_args(), 1);
                    $name      = ViserioStr::camelize($name);

                    return \call_user_func_array([$this->callback, (string) $name], $arguments);
                }
            ),
        ];
    }
}
