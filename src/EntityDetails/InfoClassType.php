<?php
	namespace Alpa\EntityDetails;
	/**
	 * 	data type InfoClassType
	 */
	class InfoClassType {
		use ReflectInfoLib;
		
		function __construct($class) 
		{
			$this_class=__CLASS__;
			if ($class instanceof \ReflectionClass) {
				$reflect = $class;
			} else
			if($class instanceof $this_class){
				return $class;
			} else
			if (is_string($class) || is_object($class)) {
				$reflect = new \ReflectionClass($class);
			} else {
				trigger_error('No valid argument in __construct method from class ' . __CLASS__ . '. Argument must be name class or must be object \ReflectionObject.');
				return null;
			}
			$this->name = $reflect->getName();
			$this->file = $reflect->getFileName();
			$this->interfaces = [];
			foreach ($reflect->getInterfaces() as $interface => $reflect_interface) {
				$this->interfaces[$interface] = new InfoClassType($reflect_interface);
			}
			$this->traits = [];
			foreach ($reflect->getTraits() as $trait => $reflect_trait) {
				$this->traits[$trait] = new InfoClassType($reflect_trait);
			}
			$this->parents = [];
			$reflect_parent = $reflect->getParentClass();
			if ($reflect_parent !== false) {
				$this->parents[$reflect_parent->name] = [];
			}
			$this->constants = [];
			foreach ($reflect->getConstants() as $constant => &$value) {
				$reflect_constant = new \ReflectionClassConstant($reflect->getName(), $constant);
				$prefix = '';
				$name = $constant;
				$this->nameConvert(false);
				if ($reflect_constant->isPrivate()) {
					$name = $this->nameConvert($constant, 'private');
				} else
				if ($reflect_constant->isProtected()) {
					$name = $this->nameConvert($constant, 'protected');
				} else
				if ($reflect_constant->isPublic()) {
					$name = $this->nameConvert($constant, 'public');
				}
				$this->constants[$name] = ['value' => &$value];
			}
			unset($value);
			$this->methods = [];
			$this->native_methods = [];
			foreach ($reflect->getMethods() as &$method) {
				//$reflect_method=new \ReflectionMethod($reflect->getName(),$method->name);
				$nc=$reflect->getName();
				$this->nameConvert(false);
				$name = $method->name;
				if ($method->isConstructor()) {
					$name = $this->nameConvert($method->name, 'construct');
				}
				if ($method->isStatic()) {
					$name = $this->nameConvert($method->name, 'static');
				}
				if ($method->isAbstract()) {
					$name = $this->nameConvert($method->name, 'abstract');
				}
				if ($method->isFinal()) {
					$name = $this->nameConvert($method->name, 'final');
				} else
				if ($method->isPrivate()) {
					$name = $this->nameConvert($method->name, 'private');
				} else
				if ($method->isProtected()) {
					$name = $this->nameConvert($method->name, 'protected');
				} else
				if ($method->isPublic()) {
					$name = $this->nameConvert($method->name, 'public');
				}
				$this->methods[$name] = ['params' => [], 'return' => ''];
				foreach ($method->getParameters() as &$param) {
					$this->methods[$name]['params'][$param->name] = [];
					if ($param->hasType()) {
						$this->methods[$name]['params'][$param->name]['type'] = $param->getType()->getName();
					}
					if ($param->isDefaultValueAvailable()) {
						$this->methods[$name]['params'][$param->name]['value'] = $param->getDefaultValue();
					}
				}
				unset($param);
				if ($method->hasReturnType()) {
					$this->methods[$name]['return'] = ['type' => $method->getReturnType()->getName()];
				}
				if($method->class==$nc){
					$this->native_methods[$name]=&$this->methods[$name];
				}
			}
			unset($method);
			$this->properties = [];
			$this->native_properties = [];
			$default=$reflect->getDefaultProperties();
			foreach ($reflect->getProperties() as &$reflect_property) {
				$nc = $reflect->getName();
				$property = $reflect_property->name;
				$name = $property;
				$reflect_property->setAccessible(true);
				$this->nameConvert(false);
				if ($reflect_property->isStatic()) {
					$name = $this->nameConvert($property, 'static');
				}
				if ($reflect_property->isPrivate()) {
					$name = $this->nameConvert($property, 'private');
				} else
				if ($reflect_property->isProtected()) {
					$name = $this->nameConvert($property, 'protected');
				} else {
					if ($reflect_property->isPublic()) {
						$name = $this->nameConvert($property, 'public');
					}
				}
				if ($reflect_property->isStatic()) {
					$value=$reflect_property->getValue();
					if (is_object($value)){
						$name .= ':' . get_class($value);
					} else
					if (is_array($value)) {
						$name .= '☢';
					}
					if ($reflect_property->isPublic()) {
						$this->properties[$name] = &$nc::$$property; 
					} else {
						$this->properties[$name] = $value;
					}
				} else {
					 if (is_object($default[$property])){ 
						$name .= ':' . get_class($default[$property]);
					} else
					if (is_array($default[$property])) {
						$name .= '☢';
					}
					$this->properties[$name] =&$default[$property];
				}
				if($reflect_property->class==$nc){
					$this->native_properties[$name]=&$this->properties[$name];
				}
			}
			unset($reflect_property);

		}
	}