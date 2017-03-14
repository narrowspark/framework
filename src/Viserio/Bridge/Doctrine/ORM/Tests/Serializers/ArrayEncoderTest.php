<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Tests\Serializers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Viserio\Bridge\Doctrine\ORM\Serializers\ArrayEncoder;
use Viserio\Bridge\Doctrine\ORM\Tests\Fixtures\ArrayableEntityFixture;

class ArrayEncoderTest extends TestCase
{
    public function testCanSerializeToArray()
    {
        $array = $this->serialize(new ArrayableEntityFixture());

        $this->assertEquals(
            [
                'id'   => 'IDVALUE',
                'name' => 'NAMEVALUE',
            ],
            $array
        );
    }

    public function testEntityCanSerializeToArrayWithArrayableTrait()
    {
        $this->assertEquals(
            [
                'id'   => 'IDVALUE',
                'name' => 'NAMEVALUE',
            ],
            (new ArrayableEntityFixture())->toArray()
        );
    }

    private function serialize($entity)
    {
        $serializer = new Serializer([new GetSetMethodNormalizer()], [
            'array' => new ArrayEncoder(),
        ]);

        return $serializer->serialize($entity, 'array');
    }
}
