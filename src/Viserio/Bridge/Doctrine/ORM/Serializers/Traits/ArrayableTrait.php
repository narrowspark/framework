<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Serializers\Traits;

use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Viserio\Bridge\Doctrine\ORM\Serializers\ArrayEncoder;

trait ArrayableTrait
{
    /**
     * @return string
     */
    public function toArray()
    {
        $serializer = new Serializer(
            [
                new GetSetMethodNormalizer
            ],
            [
            'array' => new ArrayEncoder,
            ]
        );

        return $serializer->serialize($this, 'array');
    }
}
