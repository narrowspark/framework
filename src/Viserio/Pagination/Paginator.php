<?php
declare(strict_types=1);
namespace Viserio\Pagination;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Narrowspark\Collection\Collection;
use Viserio\Contracts\Pagination\Paginator as PaginatorContract;

class Paginator extends AbstractPaginator implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, PaginatorContract
{
    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
