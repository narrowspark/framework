<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Matchers;

use RuntimeException;
use Viserio\Component\Routing\VarExporter;

class StaticMatcher extends AbstractMatcher
{
    /**
     * The static string.
     *
     * @var string
     */
    protected $segment;

    /**
     * Create a new satic segment matcher instance.
     *
     * @param string     $segment
     * @param array|null $parameterKeys
     */
    public function __construct(string $segment, array $parameterKeys = null)
    {
        if (mb_strpos($segment, '/') !== false) {
            throw new RuntimeException(
                sprintf('Cannot create %s: segment cannot contain \'/\', \'%s\' given', __CLASS__, $segment)
            );
        }

        $this->parameterKeys = $parameterKeys ?? [];
        $this->segment       = $segment;
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

    /**
     * {@inheritdoc}
     */
    protected function getMatchHash(): string
    {
        return $this->segment;
    }
}
