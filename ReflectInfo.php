<?php
/**
 * Recursively provides information about the object or class.
 * for PHP version 7 and more
 * @autor Alexey Pakhomov  <AlexeyP0708@gmail.com>
 * @version 1.1
 */
namespace Alpa\EntityDetails;


/**
 * The class provides methods that output an array of data about objects and classes, 
 * preserving the tree of an array or object.
 */
class ReflectInfo {
    public $cache_class = [];
    private $hashes_buf = []; // буфер для хешей при выполнении множественных вызовов методов рекурсий.  
    private $reincarnation_array = [];
    private $cache_objects = [];
    private $exclude_hashes = [];
    private $analysis_check = false;
    public $loop = 0;
    /**
     * 	Provides information on the class.
     * 
     * 	@param \ReflectionClass|string $class - Name class. Input by reference.
     *  @return \Alpa\EntityDetails\InfoClassType Output by reference.
     */
    public function &getInfoClass(&$class) : InfoClassType
    {
        if (is_string($class) && isset($this->cache_class[$class])) {
            $answer = $this->cache_class[$class];
        } else {
            $answer = new InfoClassType($class);
            $this->cache_class[$answer->name] = &$answer;
        }
        return $answer;
    }

    /**
     * 	Provides information on the object.
     *  
     * 	@param \ReflectionObject|object $object Input by reference.
     *  @return \Alpa\EntityDetails\InfoObjectType Output by reference.
     */
    public function &getInfoObject(&$object) : InfoObjectType
    {
        $answer = new InfoObjectType($object);
        /* $hash=spl_object_hash($object);
          $cache_objects=&$this->cache_objects;
          if(isset($this->cache_objects[$hash])){
          $answer=&$this->cache_objects[$hash]['info_object'];
          } else {
          $answer=new InfoObjectType($object);
          $this->cache_objects[$hash]=['info_object'=>&$answer];
          } */
        return $answer;
    }

    /**
     *  Provides information about the class including information about its descendants (recursively).
     * @param \Alpa\EntityDetails\InfoClassType|\ReflectionClass|string $class  Name class. Input by reference.
     * @param string $path  service parameter. Indicates the route from the starting object. Intended for informative in the generated data.		
     * @return \Alpa\EntityDetails\InfoClassType Output by reference.
     */
    public function &getInfoClassRecurs(&$class, $path = 'root') : InfoClassType
    {	
        if ($class instanceof InfoClassType) {
            $answer = &$class;
        } else
		if (is_string($class) || is_object($class)) {
            $answer = &$this->getInfoClass($class);
        } else  {
            trigger_error('No valid argument 1 in ' . __METHOD__ . ' method . Argument  must be name class or object instanceof \ReflectionClass or object instanceof ' . __NAMESPACE__ . '\\InfoClassType.');
            return null;
        }
        $buf = [];
        foreach ($answer->properties as $key => &$data) {
            //unset($answer->properties[$key]);
            if (is_array($data)) {
                $answer->properties[$key] = &$this->getInfoArrayRecurs($data, trim($path . '.' . $key, '.'));
                // проверка на статическое свойство. т.к (стабильный не стабильный).
            } else
            if (is_object($data)) {
                $name_class = get_class($data);
                $answer->properties[$key] = &$this->getInfoObjectRecurs($data, trim($path . '.' . $key, '.'));
                $buf[$key . ':' . $name_class] = [$key, &$answer->properties[$key]];
            } else {
                $answer->properties[$key] = &$data;
            }
        }
		unset($data);
        foreach ($buf as $key => $val) {
            $answer->properties[$key] = &$val[1];
            unset($answer->properties[$val[0]]);
        }
        foreach ($answer->parents as $class_name => &$data) {
            //unset($answer->parents[$class]);
            if (empty($data) || is_string($data)) {
                $answer->parents[$class_name] = new \ReflectionClass($class_name);
            }
            if ($answer->parents[$class_name] instanceof \ReflectionClass || $answer->parents[$class_name] instanceof InfoClassType) {
                $answer->parents[$class_name] = &$this->getInfoClassRecurs($answer->parents[$class_name], trim($path . '.' . $class_name, '.'));
            }
        }
		unset($data);
        return $answer;
    }
    
    /**
     *  Provides information about the array including information about its descendants (recursively).
     * @param array $array  Input by reference. Input by reference.
     * @param string $path Service parameter. Indicates the route from the starting object. Intended for informative in the generated data.	
     * @return array Output by reference.
     */
    public function &getInfoArrayRecurs(&$array, $path = 'root')
    {
        if (is_array($array)) {
            $array = new \ArrayObject($array, \ArrayObject::ARRAY_AS_PROPS);
        } else {
            trigger_error('Argument 1 in ' . __METHOD__ . ' no valid. Must be Array.');
        }
        $hashes = &$this->hashes_buf;
        $cache_objects = &$this->cache_objects;
        $reincarnation_array = &$this->reincarnation_array;
        $hash = spl_object_hash($array);
        $reincarnation_array[$hash] = &$array;

        $hashes[$hash] = $path;
        $answer = [];


        $buf = [];
        foreach ($array as $prop => &$value) {
            if (is_object($value)) {
                $name_class = get_class($value);
                $answer[$prop] = &$this->getInfoObjectRecurs($value, trim($path . '.' . $prop, '.'));
                $buf[$prop . ':' . $name_class] = [$prop, &$answer[$prop]];
            } else
            if (is_array($value)) {
                $answer[$prop] = &$this->getInfoArrayRecurs($value, trim($path . '.' . $prop, '.'));
            } else {
                $answer[$prop] = &$value;
            }
        }
		unset($value);
        foreach ($buf as $key => $val) {
            $answer[$key] = &$val[1];
            unset($answer[$val[0]]);
        }
        unset($hashes[$hash]);
        $cache_objects[$hash] = ['binds' => [$path], 'object' => &$array, 'info_object' => &$answer];
        return $answer;
    }
    /**
     *  Provides information about the class including information about its descendants (recursively).
     * @param \Alpa\EntityDetails\InfoObjectType|object $class  Name class. Input by reference.
     * @param string $path  service parameter. Indicates the route from the starting object. Intended for informative in the generated data.		
     * @return \Alpa\EntityDetails\InfoObjectType Output by reference.
     */
    public function &getInfoObjectRecurs(&$object, $path = 'root')
    {
        $hashes = &$this->hashes_buf;
        $cache_objects = &$this->cache_objects;
        $exclude_hashes = &$this->exclude_hashes;
        $reincarnation_array = &$this->reincarnation_array;
        if ($object instanceof InfoObjectType) {
            $answer = &$object;
        } else
        if (is_object($object)) {
            /* $hash=spl_object_hash($object);
              if($object instanceof \ArrayObject && isset($reincarnation_array[$hash])){
              $answer=$this->getInfoArrayRecurs($object);
              } */
            $answer = &$this->getInfoObject($object);
        }
        $hash = $answer->hash;
        if (in_array($hash, $exclude_hashes)) {
            $answer = ['**object exclude in parsing**'];
            return $answer;
        }

        if (isset($hashes[$hash])) {
            $cache_objects[$hash]['binds'][] = $path;
            $answer = ['**recursive loop**' => ['loop' => $hashes[$hash], 'binds' => &$cache_objects[$hash]['binds']]];
            return $answer;
        }
        if (isset($cache_objects[$hash])) {
            $answer = &$cache_objects[$hash]['info_object'];
            $cache_objects[$hash]['binds'][] = $path;
            $cache_objects[$hash]['binds'][] = $path;
            return $answer;
        }
        $hashes[$hash] = $path;
        $buf = [];
        foreach ($answer->properties as $prop => &$value) {
            //unset($answer->properties[$prop]);
            if (is_object($value)) {
                $name_class = get_class($value);
                $answer->properties[$prop] = &$this->getInfoObjectRecurs($value, trim($path . '.' . $prop, '.'));
                $buf[$prop . ':' . $name_class] = [$prop, &$answer->properties[$prop]];
            } else
            if (is_array($value)) {
                $answer->properties[$prop] = &$this->getInfoArrayRecurs($value, trim($path . '.' . $prop, '.'));
            } else {
                $answer->properties[$prop] = &$value;
            }
        }
		unset($value);
        foreach ($buf as $key => $val) {
            $answer->properties[$key] = &$val[1];
            unset($answer->properties[$val[0]]);
        }
        $answer->class = &$this->getInfoClassRecurs($answer->class, $path);
        unset($hashes[$hash]);
        $cache_objects[$hash] = ['binds' => [$path], 'object' => &$object, 'info_object' => &$answer];
        //$cache_objects[$hash]['info_object']=&$answer;
        return $answer;
    }
    /**
     *  List of objects for exlude in parse that may generate redundant information.
     *  This happens when the object belongs to Alpa \ EntityDetails \ ReflectInfo 
     *  or is of type Alpa \ EntityDetails \ InfoObjectType 
     *  or Alpa \ EntityDetails \ InfoClassType
     * @param array $objects  $object Object exclusion list
     * @param string $path  service parameter. Indicates the route from the starting object. Intended for informative in the generated data.		
     * @return \Alpa\EntityDetails\InfoObjectType Output by reference.
     */
    public function excludeObjects($objects = []) 
    {
        if (!is_array($objects && is_object($objects))) {
            $objects = [$objects];
        }
        $exclude_hashes = &$this->exclude_hashes;
        foreach ($objects as $object) {
            $hash = spl_object_hash($object);
            if (!in_array($hash, $exclude_hashes)) {
                $exclude_hashes[] = $hash;
            }
        }
    }

    /**
     * 	Preparation for analysis.
     * 	Clears the buffer  of the object,
     * Restoration of the original data (if previously for some reason were not restored). 
     * Converts ArrayObject (s) back into arrays that were previously converted to ArrayObject. 
     */
    public function initAnalysis() 
    {
        $this->hashes_buf = [];
        foreach ($this->reincarnation_array as &$v) {
            $v = (array) $v;
        }
        $this->cache_objects = [];
        $this->reincarnation_array = [];
        $this->hashes_buf = [];
        //$this->cache_class=[];
    }

    /**
     *  Return to the original data.		
     */
    public function endAnalysis() 
    {    
        $this->initAnalysis();
    }

    /**
     * Provides information about the array or object including information about its descendants (recursively).
     * @param mixed $data  Input by reference. Input by reference.
     * @param string $path Service parameter. Indicates the route from the starting object. Intended for informative in the generated data.	
     * @return array Output by reference.
     */
    public function &getInfoObjectArrayRecurs(&$data, $path = 'root')
    {
        $this->initAnalysis();
        if (is_array($data)) {
            $answer = &$this->getInfoArrayRecurs($data, $path);
        } else if (is_object($data)) {
            $buf = &$this->getInfoObjectRecurs($data, $path);
            $answer = [':' . get_class($data) => &$buf];
        } else {
            $answer = &$data;
        }
        $this->endAnalysis();
        return $answer;
    }
	    /**
     *  Prepares a array to the output buffer content. eliminates loops in arrays and objects.
     * @param mixed $data
     * @return mixed | array
     */
    public function repeatRecursiveDataIntoArray($data,$is_loop=true) 
	{
        $hashes = [];
        $hashes_objects = [];
        $answer = [];
        $reincarnation_array = [];
        $recurs_call = function &(&$data, $path = '') use (&$is_loop,&$recurs_call, &$hashes, &$reincarnation_array) {
			if(is_array($data) || is_object($data)){
				if (is_array($data)) {
					$data = new \ArrayObject($data, \ArrayObject::ARRAY_AS_PROPS);
					$hash = spl_object_hash($data);
					$reincarnation_array[$hash] = &$data;
				} else {
					$hash = spl_object_hash($data);
				} 
				if (!isset($hashes[$hash])){
					$object = (array) $data;
					$answer=[];
					$hashes[$hash] = ['path'=>$path,'object'=>&$answer];
					foreach ($object as $prop => &$value) {
                        $answer[$prop] = $recurs_call($value, trim($path . '.' . $prop, '.'));
					}
					unset($hashes[$hash]);
				} else {
					if($is_loop==true){
						$answer = '**recursive loop** => ' . $hashes[$hash]['path'];
					} else {
						$answer =&$hashes[$hash]['object'];
					}
				}
			} else {
				$answer=&$data;
			}
            return $answer;
        };
        $answer = $recurs_call($data,'root');
        foreach ($reincarnation_array as &$v) {
            $v = (array) $v;
        }
        return $answer;
    }
}

trait ReflectInfoLib {

    private $buf_name_prop = [];
    protected $label_var = [
        'final' => '***',
        'private' => '**',
        'protected' => '*',
        'public' => '',
        'abstract' => '~',
        'static' => '%'
    ];

    /**
     *  Конвектор имени свойства обьекта.
     *  @param string $name имя свойства обьекта которое надо конвертировать для данных в InfoObject или InfoClass
     *  @param string $type тип конвертации  'construct' 'static' 'abstract' 'final' 'private' 'protected' 'public' 
     */
    protected function nameConvert($name = false, $type = false) 
    {
        $bnp = &$this->buf_name_prop;
        switch ($type) {
            case false : $bnp = ['abstract' => '', 'static' => '', 'type' => '', 'name' => ''];
                break;
            case 'construct':
                $name = '__construct';
                $bnp['name'] = $name;
                break;
            case 'static':
            case 'abstract':
                $bnp[$type] = $this->label_var[$type];
            default:
                if (!empty($bnp['name'])) {
                    $name = $bnp['name'];
                }
                $bnp['type'] = $this->label_var[$type];
                $name = trim($bnp['abstract'] . $bnp['static'] . $bnp['type'] . ' ' . $name);
                break;
        }
        return $name;
    }

}

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
        foreach ($reflect->getMethods() as &$method) {
            //$reflect_method=new \ReflectionMethod($reflect->getName(),$method->name);
			if($method->class==$reflect->getName()){
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
			}
        }
		unset($method);
        $this->properties = [];
		$default=$reflect->getDefaultProperties();
        foreach ($reflect->getProperties() as &$reflect_property) {
            $nc = $reflect->getName();
			if($reflect_property->class==$nc){
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
			}
        }
		unset($reflect_property);

    }
}

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
        //$this->methods = &$this->class->methods;
		foreach($reflect->getMethods() as $method){
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
		}
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
class ReflectionObject extends \ReflectionObject {
    private $object;
    public function __construct($o) 
    {
        parent::__construct($o);
        $this->object = &$o;
    }

    public function &getObject() 
    {
        return $this->object;
    }
}
