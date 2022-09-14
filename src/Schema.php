<?php

namespace Mongoose;

class Schema
{

    private $fields;
    private $options;

    public function __construct($fields = [], $options = [])
    {
        $this->fields  = $fields;
        $this->options = $options;

        return $this;
    }

    public function add($fields)
    {
        $this->fields = array_merge($this->fields, $fields);
    }

    public function addMethod($name, $method)
    {
        if (!isset($this->options)) {
            $this->options['methods'] = [];
        }

        $this->options['methods'][$name] = $method;
    }

    public function addVirtual($name, $methods)
    {
        if (!isset($this->options)) {
            $this->options['virtuals'] = [];
        }

        $this->options['virtuals'][$name] = $methods;
    }

    public function makeModel($name)
    {
        Models::addModel($name, $this->fields, $this->options);
    }

}