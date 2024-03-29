<?php


namespace BewarHizirvan\LaravelGrid\Elements;

use Illuminate\Http\Request;

class Filter
{
    protected $name = 'id';
    protected $label = 'id';
    protected $operator = 'like';
    protected $options = null;
    protected $evaluate = true;
    protected $value = null;

    public function __construct($name, $label, $operator = 'like', $options = null, $evaluate = true)
    {
        $this->name = $name;
        $this->label = $label;
        $this->operator = $operator;
        $this->options = $options;
        $this->evaluate = $evaluate;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getOptions()
    {
        return $this->options;
    }
    
    public function getEvaluate()
    {
        return $this->evaluate;
    }

}
