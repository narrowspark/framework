<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Tests\Fixture;

use Viserio\Component\Config\ParameterProcessor\AbstractParameterProcessor;

class FixtureParameterProcessor extends AbstractParameterProcessor
{
    /**
     * {@inheritdoc}
     */
    public static function getReferenceKeyword(): string
    {
        return 'fixture';
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        $environmentVariable = $this->parseParameter($parameter);

        return \getenv($environmentVariable);
    }
}
