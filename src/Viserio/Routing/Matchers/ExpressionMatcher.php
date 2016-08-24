<?php
declare(strict_types=1);
namespace Viserio\Routing\Matchers;

class ExpressionMatcher extends AbstractMatcher
{
    /**
     * The expression string.
     *
     * @var string
     */
    protected $expression;

    /**
     * Create a new expression segment matcher instance.
     *
     * @param string $expression
     * @param array  $parameterKeys
     */
    public function __construct(string $expression, array $parameterKeys)
    {
        $this->expression = $expression;
        $this->parameterKeys = $parameterKeys;
    }

    /**
     * Returns the used expression.
     *
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionExpression(string $segmentVariable, int $uniqueKey = null): string
    {
        return str_replace('{segment}', $segmentVariable, $this->expression);
    }

    /**
     * {@inheritdoc}
     */
    protected function getMatchHash(): string
    {
        return $this->expression;
    }
}
