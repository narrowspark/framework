<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Tests\Fixtures;

use Viserio\Bridge\Doctrine\ORM\Serializers\Traits\ArrayableTrait;

class ArrayableEntityFixture
{
    use ArrayableTrait;

    protected $id   = 'IDVALUE';
    protected $name = 'NAMEVALUE';

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
}
