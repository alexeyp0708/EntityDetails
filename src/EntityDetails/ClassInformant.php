<?php

namespace Alpa\EntityDetails;
/**
 *    data type InfoClassType
 */
class ClassInformant extends Informant
{
    public string $name;
    public ? string $file;
    public array $interfaces = [];
    public array $traits = [];
    public array $parent=[];
    public array $constants = [];
    public array $methods = [];
    public array $native_methods = [];
    public array $properties = [];
    public array $native_properties = [];
    
    protected static array $cache=[];


    protected static function getCache($data) :? Informant
    {
        $name_class=$data;
        if(is_object($name_class)){
            $name_class= get_class($name_class);
        }
        return self::$cache[$name_class]??null;
    }
    
    protected static function setCache($data, Informant $object)
    {
        $class_name=$data;
        if(is_object($class_name)){
            $class_name=get_class($class_name);
        }
        if(!isset(self::$cache[$class_name])){
            self::$cache[$class_name]=$object;
        }
    }    
    
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
        foreach ($this->genMethods() as $key => &$value) {
            if (property_exists($this, $key)) {
                $this->$key =& $value;
            }
        }
        unset($value);
    }

    protected function initProperties()
    {
        foreach ($this->genProperties() as $key => &$value) {
            if (property_exists($this, $key)) {
                $this->$key =& $value;
            }
        }
        unset($value);
    }
}