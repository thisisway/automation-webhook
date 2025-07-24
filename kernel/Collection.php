<?php
namespace Kernel;

class Collection extends \ArrayObject
{
    public function pluck($column, $index = null)
    {
        return array_column((array)$this, $column, $index);
    }

    public function map($fn)
    {
        return array_map($fn, (array)$this);
    }

    public function toArray()
    {
        return (array)$this;
    }

    public function rowCount()
    {
        return count((array)$this);
    }
}