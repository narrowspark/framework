<?php
declare(strict_types=1);
namespace Viserio\Routing\Matchers;

use RuntimeException;

class StaticMatcher extends AbstractSegmentMatcher
{
    /**
     * The static string
     *
     * @var string
     */
    protected $segment;

    /**
     * Create a new satic segment matcher instance.
     *
     * @param string     $value
     * @param array|null $parameterKey
     */
    public function __construct(string $segment, $parameterKey = null)
    {
        if (strpos($segment, '/') !== false) {
            throw new RuntimeException(
                sprintf('Cannot create %s: segment cannot contain \'/\', \'%s\' given', __CLASS__, $segment)
            );
        }

        $this->parameterKeys = $parameterKey === null ? [] : [$parameterKey];
        $this->segment = $segment;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionExpression($segmentVariable, $uniqueKey): string
    {
        return $segmentVariable . ' === ' . VarExporter::export($this->segment);
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchedParameterExpressions($segmentVariable, $uniqueKey): array
    {
        return $this->parameterKeys ? [$this->parameterKeys[0] => $segmentVariable] : [];
    }
}
