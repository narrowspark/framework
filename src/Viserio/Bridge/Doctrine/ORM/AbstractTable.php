<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

abstract class AbstractTable
{
    /**
     * The name of a table.
     *
     * @var string
     */
    protected $table;

    /**
     * Create a new table instance.
     *
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * Build a new table.
     *
     * @return \Doctrine\DBAL\Schema\Table
     */
    public function build(): Table
    {
        return new Table(
            $this->table,
            $this->columns(),
            $this->indices()
        );
    }

    /**
     * Create a new table column.
     *
     * @param string $name
     * @param string $type
     * @param bool   $autoincrement
     *
     * @return \Doctrine\DBAL\Schema\Column
     */
    protected function column(string $name, string $type, bool $autoincrement = false): Column
    {
        $column = new Column($name, Type::getType($type));
        $column->setAutoincrement($autoincrement);

        return $column;
    }

    /**
     * @param string $name
     * @param array  $columns
     * @param bool   $unique
     * @param bool   $primary
     *
     * @return \Doctrine\DBAL\Schema\Index
     */
    protected function index(
        string $name,
        array $columns,
        bool $unique = false,
        bool $primary = false
    ): Index {
        return new Index($name, $columns, $unique, $primary);
    }

    /**
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    abstract protected function columns(): array;

    /**
     * @return \Doctrine\DBAL\Schema\Index[]
     */
    abstract protected function indices(): array;
}
