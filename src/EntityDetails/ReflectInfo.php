<?php
	/**
	 * Recursively provides information about the object or class.
	 * for PHP version 7 and more
	 * @autor Alexey Pakhomov  <AlexeyP0708@gmail.com>
	 * @version 1.1
	 */
	namespace Alpa\EntityDetails;


	/**
    The class provides methods that output an array of data about objects and classes, 
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
		 *  @return \Alpa\EntityDetails\InfoClass Output by reference.
		 */
		public function &getInfoClass(&$class) : InfoClass
		{
			if (is_string($class) && isset($this->cache_class[$class])) {
				$answer = $this->cache_class[$class];
			} else {
				$answer = new InfoClass($class);
				$this->cache_class[$answer->name] = &$answer;
			}
			return $answer;
		}

		/**
		 * 	Provides information on the object.
		 *  
		 * 	@param \ReflectionObject|object $object Input by reference.
		 *  @return \Alpa\EntityDetails\InfoObject Output by reference.
		 */
		public function &getInfoObject(&$object) : InfoObject
		{
			$answer = new InfoObject($object);
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
		 * @param \Alpa\EntityDetails\InfoClass|\ReflectionClass|object|string $class  Name class. Input by reference.
		 * @param string $path  service parameter. Indicates the route from the starting object. Intended for informative in the generated data.		
		 * @return \Alpa\EntityDetails\InfoClass Output by reference.
		 */
		public function &getInfoClassRecurs(&$class, $path = 'root') : InfoClass
		{	
			if ($class instanceof InfoClass) {
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
					//$buf[$key . ':' . $name_class] = [$key, &$answer->properties[$key]];
					//$buf[$key] = [$key, &$answer->properties[$key]];
				} else {
					$answer->properties[$key] = &$data;
				}
			}
			unset($data);
			/*foreach ($buf as $key => $val) {
				$answer->properties[$key] = &$val[1];
				unset($answer->properties[$val[0]]);
			}*/
			foreach ($answer->parents as $class_name => &$data) {
				//unset($answer->parents[$class]);
				if (empty($data) || is_string($data)) {
					$answer->parents[$class_name] = new \ReflectionClass($class_name);
				}
				if ($answer->parents[$class_name] instanceof \ReflectionClass || $answer->parents[$class_name] instanceof InfoClass) {
					$answer->parents[$class_name] = &$this->getInfoClassRecurs($answer->parents[$class_name], trim($path . '.' . $class_name, '.'));
				}
			}
			unset($data);
			return $answer;
		}
		
		/**
		 * Provides information about the array including information about its descendants (recursively).
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
				} else if(is_resource($value)){
					$answer->properties[$prop]='Resource type: '.get_resource_type($value);
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
		 * @param \Alpa\EntityDetails\InfoObject|object $class  Name class. Input by reference.
		 * @param string $path  service parameter. Indicates the route from the starting object. Intended for informative in the generated data.		
		 * @return \Alpa\EntityDetails\InfoObject Output by reference.
		 */
		public function &getInfoObjectRecurs(&$object, $path = 'root')
		{
			$hashes = &$this->hashes_buf;
			$cache_objects = &$this->cache_objects;
			$exclude_hashes = &$this->exclude_hashes;
			$reincarnation_array = &$this->reincarnation_array;
			if ($object instanceof InfoObject) {
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
				} else if(is_resource($value)){
					$answer->properties[$prop]='Resource type: '.get_resource_type($value);
				}else{
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
		 * @return \Alpa\EntityDetails\InfoObject Output by reference.
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
		 * @return mixed|array Output by reference.
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
					if(is_callable($data) && is_object($data)){
						$hash = spl_object_hash($data);
					}else
					if (is_array($data)) {
						$data = new \ArrayObject($data, \ArrayObject::ARRAY_AS_PROPS);
						$hash = spl_object_hash($data);
						$reincarnation_array[$hash] = &$data;
					} else {
						$hash = spl_object_hash($data);
					} 
					if (!isset($hashes[$hash])){
						if(is_callable($data)){
							$answer='**(Object Closure)**';
						} else {
							$object = (array) $data;
							$answer=[];
							$hashes[$hash] = ['path'=>$path,'object'=>&$answer];
							
							foreach ($object as $prop => &$value) {
								$answer[$prop] = $recurs_call($value, trim($path . '.' . $prop, '.'));
							}
							unset($hashes[$hash]);
						}
					} else {
						if($is_loop==true){
							$answer = '**recursive loop** => ' . $hashes[$hash]['path'];
						} else {
							$answer =&$hashes[$hash]['object'];
						}
					}
				} else if(is_resource($data)){
					$answer='Resource type: '.get_resource_type($data);
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







