<?php
declare(strict_types=1);
namespace Viserio\Routing\Matchers;

use RuntimeException;
use Viserio\Routing\VarExporter;

class StaticMatcher extends AbstractMatcher
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
     * @param string     $segment
     * @param array|null $parameterKey
     */
    public function __construct(string $segment, array $parameterKey = null)
    {
        if (strpos($segment, '/') !== false) {
            throw new RuntimeException(
                sprintf('Cannot create %s: segment cannot contain \'/\', \'%s\' given', __CLASS__, $segment)
            );
        }

        $this->parameterKeys = $parameterKey ?? [];
        $this->segment = $segment;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionExpression(string $segmentVariable, int $uniqueKey = null): string
    {
        return $segmentVariable . ' === ' . VarExporter::export($this->segment);
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchedParameterExpressions(string $segmentVariable, int $uniqueKey = null): array
    {
        $keys = $this->parameterKeys;

        if (count($keys) > 0) {
            return [$keys[0] => $segmentVariable];
        }

        return [];
    }
}
