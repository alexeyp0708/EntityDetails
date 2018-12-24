<?php
	namespace Alpa\EntityDetails;
	/**
	 * 	data type InfoObjectType
	 */
	class InfoObjectType {
		use ReflectInfoLib;
		public function __construct($object) 
		{	
			$this_class=__CLASS__;
		   if ($object instanceof ReflectionObject) {
				$reflect = &$object;
				$object = &$reflect->getObject();
			} else
			if($object instanceof $this_class){
				return $object;
			} else
			if (is_object($object)) {
				$reflect = new ReflectionObject($object);
			} else {
				trigger_error('No valid argument 1 in  __construct method from class  ' . __CLASS__ . '. Argument must be any object or must be object \ReflectionObject.');
				return;
			}
			$this->hash = spl_object_hash($object);
			$name_class = $reflect->getName();
			$this->class = new InfoClassType($name_class);
			$this->constants = [];
			foreach ($reflect->getConstants() as $constant => &$value) {
				$reflect_constant = new \ReflectionClassConstant($name_class, $constant);
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
			$this->methods = &$this->class->methods;
			/*foreach($reflect->getMethods() as $method){
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
			}*/
			$this->properties = [];
			$array = (array) $object;
			$prop_added = [];
			foreach ($reflect->getProperties() as $property) {
				//$reflect_property=new \ReflectionProperty($reflect->getName(),$property->name);
				$name = $property->name;
				$name_s = $property->name;
				$name_g = $property->name;
				$this->nameConvert(false);
				$property->setAccessible(true);
				if ($property->isStatic()) {
					$name = $this->nameConvert($property->name, 'static');
				}
				if ($property->isPrivate()) {
					$name = $this->nameConvert($property->name, 'private');
					$name_s = "\0" . $reflect->getName() . "\0" . $property->name; // хак. при преобразовании обьекта в массив именно так выглядят имена приватных свойств
				} else
				if ($property->isProtected()) {
					$name = $this->nameConvert($property->name, 'protected');
					$name_s = "\0" . "*" . "\0" . $property->name; // хак. при преобразовании обьекта в массив именно так выглядят имена ограниченых свойств
				} else {
					if ($property->isPublic()) {
						$name = $this->nameConvert($property->name, 'public');
					}
				}
				if ($property->isStatic()) {
					if ($property->isPublic()) {
						$prop_name = $property->name;
						$this->properties[$name] = &$object::$$prop_name;
						$buf_name=$name;
						$prop_added[] = $property->name;
					} else {
						$name_g = $name;
						$val=$property->getValue();
						if (is_array($val)) {
							$name_g = $name . '☢';
						}
						$this->properties[$name_g] = $val;
						$buf_name=$name_g;
						$prop_added[] = $property->name;
					}
				} else {   
					$this->properties[$name] = &$array[$name_s];
					$prop_added[] = $property->name;
					$buf_name=$name;
				}
			}	
			foreach ($array as $property => &$value) {
				$prop = explode("\0", $property);
				$this->nameConvert(false);
				if (count($prop) == 3 && !in_array($prop[2], $prop_added)) {
					if ($prop[1] === '*') {
						$name = $this->nameConvert($prop[2], 'protected');
						$this->properties[$name] = &$value;
					} else {
						$name = $this->nameConvert($prop[2], 'private');
						$reflect_prop = new \ReflectionProperty($prop[1], $prop[2]);
						if ($reflect_prop->isStatic()) {
							$name = $this->nameConvert($prop[2], 'static');
						}
						$this->properties[':' . $prop[1] . ':' . $name] = &$value;
					}
				}
			}
			unset($value);
		}
	}