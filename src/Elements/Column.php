<?php


namespace BewarHizirvan\LaravelGrid\Elements;

use Illuminate\Http\Request;

class Column
{
    protected $name = 'id';
    protected $label = 'id';
    protected $filter = null;
    protected $sortable = false;
    protected $ValueCalculator = null;
    protected $ValueFormatter = null;
    protected $isIP = false;

    public function __construct($name, $label)
    {
        $this->name = $name;
        $this->label = $label;
    }

    public function setValueCalculator($ValueCalculator)
    {
        $this->ValueCalculator = $ValueCalculator;
    }

    public function setValueFormatter($ValueFormatter)
    {
        $this->ValueFormatter = $ValueFormatter;
    }

    public function addFilter($name, $label)
    {
        $this->filter = new Filter($name, $label);
    }

    public function setSortable()
    {
        $names_array = explode(".", $this->name);
        if(count($names_array) <= 2)
            $this->sortable = true;

    }

    public function setIPsortable()
    {
        $this->isIP = true;

    }

    public function getName()
    {
        return $this->name;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function getSortable()
    {
        return $this->sortable;
    }

    public function getIPsortable()
    {
        return $this->isIP;
    }

    public function getValueCalculator()
    {
        return $this->ValueCalculator;
    }

    public function getValueFormatter()
    {
        return $this->ValueFormatter;
    }
}
