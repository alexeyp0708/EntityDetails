<?php

namespace Alpa\EntityDetails;
/**
 *    data type InfoObjectType
 */
class ObjectInformant  implements IInformant
{
    use TObjectInformant;
    public ClassInformant $class;
    public array $constants = [];
    public array $properties = ['all'=>[],'native'=>[]];
    public array $methods = ['all'=>[],'native'=>[]];

    protected function init()
    {
        $this->initClass();
        $this->initConstants();
        $this->initProperties();
        $this->initMethods();
    }

    protected function initClass()
    {
        $this->class = $this->genClass();
    }

    protected function initConstants()
    {
        $this->constants = $this->genConstants();
    }

    protected function initProperties()
    {
        $this->properties = $this->genProperties();
    }

    protected function initMethods()
    {
        $this->methods = $this->genMethods();
    }
}