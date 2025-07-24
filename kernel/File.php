<?php

namespace Kernel;

#[\AllowDynamicProperties]
class File {
    public $name;
    public $size;
    public $type;
    public $extension;
    public $path;

    public function __construct($name = null, $size = null, $type = null, $extension = null, $path = null)
    {
        $this->name = $name;
        $this->size = $size;
        $this->type = $type;
        $this->extension = $extension;
        $this->path = $path;
    }

    public function __set($attr, $value)
    {
        $this->{$attr} = $value;
    }

    public function __get($attr)
    {
        return $this->{$attr} ?? false;
    }
}