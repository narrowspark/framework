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
}
