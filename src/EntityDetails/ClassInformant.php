<?php

namespace Alpa\EntityDetails;
/**
 *    data type InfoClassType
 */
class ClassInformant  implements IInformant
{
    use TClassInformant;
    public string $name;
    public ?string $file;
    public array $interfaces = [];
    public array $traits = [];
    public array $parent = [];
    public array $constants = [];
    public array $methods = ['all'=>[],'native'=>[]];
    public array $properties = ['all'=>[],'native'=>[]];

    

    protected function init()
    {
        $this->initName();
        $this->initFile();
        $this->initInterfaces();
        $this->initTraits();
        $this->initConstants();
        $this->initMethods();
        $this->initProperties();
    }

    protected function initName()
    {
        $this->name = $this->genName();
    }

    protected function initFile()
    {
        $this->file = $this->genFile();
    }

    protected function initInterfaces()
    {
        $this->interfaces = $this->genInterfaces();
    }

    protected function initTraits()
    {
        $this->traits = $this->genTraits();
    }

    protected function initParentClass()
    {
        $this->parent = $this->genParentClass();
    }

    protected function initConstants()
    {
        $this->constants = $this->genConstants();
    }

    protected function initMethods()
    {
        $this->methods = $this->genMethods();
    }

    protected function initProperties()
    {
        $this->properties = $this->genProperties();
    }
}