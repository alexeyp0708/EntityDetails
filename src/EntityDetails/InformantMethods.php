<?php


namespace Alpa\EntityDetails;

use Alpa\EntityDetails\NamePropertyGenerator;

trait InformantMethods
{
    protected Reflector $reflect;
    protected bool $is_recursive = false;
    protected function genName(): string
    {
        return $this->reflect->getName();
    }

    /**
     * generate path file name of observable class
     * @return string|null
     */
    protected function genFile(): ?string
    {
        $result = $this->reflect->getFileName();
        return $result !== false ? $result : null;
    }

    /**
     * generates a list of interfaces of the observable class.
     * If this object is created with recursive analysis,
     * then the interfaces are also analyzed.
     * Where keys are interface names. The value is null if the analysis is not recursive
     * @return array
     */
    protected function genInterfaces(): array
    {
        $interfaces = [];
        foreach ($this->reflect->getInterfaces() as $interface => $reflect_interface) {
            $interfaces[$interface] = $this->is_recursive ? ClassInformant::newObject($reflect_interface->getName(), $this->is_recursive) : null;
        }
        return $interfaces;
    }

    /**
     * generates a list of traits of the observable class.
     * If this object is created with recursive analysis,
     * then the traits are also analyzed.
     * @return array
     * Where keys are trait names. The value is null if the analysis is not recursive
     */
    protected function genTraits(): array
    {
        $traits = [];
        foreach ($this->reflect->getTraits() as $trait => $reflect_trait) {
            $traits[$trait] = $this->is_recursive ? ClassInformant::newObject($reflect_trait->getName(), $this->is_recursive) : null;
        }
        return $traits;
    }

    /**
     * generates a list of parents of the observable class.
     * If this object is created with recursive analysis,
     * then the parents are also analyzed.
     * @return array
     * Where keys are parent class names. The value is null if the analysis is not recursive
     */
    protected function genParentClass(): array
    {
        $reflect_parent = $this->reflect->getParentClass();
        if ($reflect_parent !== false) {
            return [$reflect_parent->name => $this->is_recursive ? ClassInformant::newObject($reflect_parent->getName(), $this->is_recursive) : null];
        }
        return [];
    }

    /**
     * generates a list of constants
     * @return array
     *
     */
    protected function genConstants(): array
    {
        $constants = [];
        foreach ($this->reflect->getConstants() as $constant => &$value) {
            $reflect_constant = new \ReflectionClassConstant($this->reflect->getName(), $constant);
            $name = $constant;
            $types=[];
            if ($reflect_constant->isPrivate()) {
                $types[]='private';
            } else
            if ($reflect_constant->isProtected()) {
                $types[]='protected';
            } else
            if ($reflect_constant->isPublic()) {
                $types[]='public';
            }
            $name=(new NamePropertyGenerator($name))->setTypes($types)->getName();
            $constants[$name] = ['value' => &$value, 'owner' => $reflect_constant->getDeclaringClass()->getName()];
        }
        unset($value);
        return $constants;
    }


    /**
     * @return array
     * [
     *      'properties'=>'array' // list all properties
     *      'native_properties'=>'array' // a sheet of own properties
     * ];
     * @throws \Exception
     */
    protected function genProperties(): array
    { // декомпизировать
        $properties = [];
        $native_properties = [];
        $default = $this->reflect->getDefaultProperties();
        $object = $this->reflect instanceof ReflectionObject ? $this->reflect->getObject() : null;
        foreach ($this->reflect->getProperties() as $reflect_property) {
            $nc = $this->reflect->getName();
            $property_name = $reflect_property->name;
            $name = $property_name;
            $reflect_property->setAccessible(true);
            $types=[];
            
            if ($reflect_property->isStatic()) {
                $types[]='static';
            }
            
            if ($reflect_property->isPrivate()) {
                $types[]='private';
            } else
            if ($reflect_property->isProtected()) {
                $types[]='protected';
            } else
            if ($reflect_property->isPublic()) {
                $types[]='public';
            }
            
            $name=(new NamePropertyGenerator($name))->setTypes($types)->getName();
            $property = ['owner' => $reflect_property->getDeclaringClass()->getName()];
            $value = null;
            
            if ($reflect_property->isStatic()) {
                $value = $reflect_property->getValue();
                if ($this->is_recursive) {
                    if (is_object($value) && !($value instanceof Informant) && !($value instanceof \Reflector)) {
                        $value = ObjectInformant::newObject($value, $this->is_recursive);//new InfoObjectType($value,$this->is_recursive);
                    } else if (is_array($value) && $reflect_property->isPublic()) {
                        $value = ArrayInformant::newObject($value, $this->is_recursive); //new InfoArrayType($value,$this->is_recursive);
                    }
                } else if (is_object($value) && !$reflect_property->hasType()) {
                    $property['value_type'] = get_class($value);
                }
                $property['value'] = $value;
                if ($reflect_property->hasType()) {
                    $type = $reflect_property->getType();
                    $property['type'] = ($type->allowsNull() ? '? ' : '') . $type->getName();
                }
            } else {
                if($object!==null){
                    $value = $reflect_property->getValue($object);
                } else
                if (isset($default[$property_name])) {
                    $value = $default[$property_name];
                }
                if ($this->is_recursive) {
                    if (is_object($value) && !($value instanceof Informant) && !($value instanceof \Reflector)) {
                        $value = ObjectInformant::newObject($value, $this->is_recursive);//new InfoObjectType($value,$this->is_recursive);
                    } else if ($object !== null && is_array($value)) {
                        $value = ArrayInformant::newObject($value, $this->is_recursive);
                    }
                } else if (is_object($value) && !$reflect_property->hasType()) {
                    $property['value_type'] = get_class($value);
                }
                $property['value'] = $value;
            }
            $properties[$name] = $property;
            if ($nc === $reflect_property->getDeclaringClass()->getName()) {
                $native_properties[$name] = &$properties[$name];
            }
        }
        unset($reflect_property);
        return compact(['properties', 'native_properties']);
    }

    /**
     * generates a list of methods
     * @return  array
     * [
     *      'methods'=>'array' // list all methods
     *      'native_methods'=>'array' // a sheet of own methods
     * ];
     * @throws \ReflectionException
     */
    protected function genMethods(): array
    {
        $methods = [];
        $native_methods = [];
        foreach ($this->reflect->getMethods() as &$method) {
            //$reflect_method=new \ReflectionMethod($reflect->getName(),$method->name);
            $name_class = $this->reflect->getName();
            $name = $method->name;
            $types=[];
            /*if ($method->isConstructor()) {
                $types[]='constructor';
            }*/
            
            if ($method->isStatic()) {
                $types[]='static';
            }
            
            if ($method->isAbstract()) {
                $types[]='abstract';
            }
            
            if ($method->isFinal()) {
                $types[]='final';
            } else
            if ($method->isPrivate()) {
                $types[]='private';
            } else
            if ($method->isProtected()) {
                $types[]='protected';
            } else
            if ($method->isPublic()) {
                $types[]='public';
            }
            $name=(new NamePropertyGenerator($name))->setTypes($types)->getName();
            $methods[$name] = ['params' => [], 'return' => '', 'owner' => $method->class];//$method->getDeclaringClass()->getName()
            foreach ($method->getParameters() as $k => &$param) {
                $methods[$name]['params'][$k] = ['name' => ($param->isPassedByReference() ? '& ' : '') . $param->name];
                if ($param->hasType()) {
                    $type = $param->getType();
                    $methods[$name]['params'][$k]['type'] = ($type->allowsNull() ? '? ' : '') . $type->getName();
                }
                if ($param->isDefaultValueAvailable()) {
                    $methods[$name]['params'][$k]['value'] = $param->getDefaultValue();
                }
            }
            unset($param);
            if ($method->hasReturnType()) {
                $type = $method->getReturnType();
                $methods[$name][($method->returnsReference() ? '& ' : '') . 'return'] = ['type' => ($type->allowsNull() ? '? ' : '') . $type->getName()];
            } else {
                $methods[$name][($method->returnsReference() ? '& ' : '') . 'return'] = [];
            }
            if ($method->class === $name_class) {
                $native_methods[$name] = $methods[$name];
            }
        }
        unset($method);
        return compact(['methods', 'native_methods']);
        //return ['methods'=>$methods,'native_methods'=>$native_methods];
    }

    protected function genClass() : ClassInformant
    {
        return ClassInformant::newObject($this->reflect->getName());
    }
}
