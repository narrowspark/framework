<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Serializers;

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class ArrayEncoder implements EncoderInterface, DecoderInterface
{
    public const FORMAT = 'array';

    /**
     * {@inheritdoc}
     */
    public function encode($data, $format, array $context = [])
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = [])
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format): bool
    {
        return $format === self::FORMAT;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format): bool
    {
        return $format === self::FORMAT;
    }
}
