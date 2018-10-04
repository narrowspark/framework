<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition\Traits;

use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;

trait DeprecationTrait
{
    /**
     * Check if the definition is deprecated.
     *
     * @var bool
     */
    protected $deprecated = false;

    /**
     * {@inheritdoc}
     */
    public function isDeprecated(): bool
    {
        return $this->deprecated;
    }

    /**
     * {@inheritdoc}
     */
    public function setDeprecated(bool $status = true, string $template = null): void
    {
        if ($template !== null) {
            if (\mb_strpos($template, '%s') === false) {
                throw new InvalidArgumentException('The deprecation template must contain the [%s] placeholder.');
            }

            $this->deprecationTemplate = $template;
        }

        $this->deprecated = $status;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeprecationMessage(): string
    {
        return \sprintf($this->deprecationTemplate ?? $this->defaultDeprecationTemplate, $this->name);
    }
}
