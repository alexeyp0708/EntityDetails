<?php
	namespace Alpa\EntityDetails;
	/**
	 * 	data type InfoObjectType
	 */
	class ObjectInformant extends Informant
    {
		use InformantMethods;
		
		public ClassInformant $class;
		public array $constants=[];
		public array $properties=[];
		public array $native_properties=[];
		public array $native_methods=[];
		public array $methods=[];
        protected static array $cache=[];
		
		protected function init()
        {
            $this->initClass();
            $this->initConstants();
            $this->initProperties();
            $this->initMethods();
        }
        protected function initClass()
        {
            $this->class=$this->genClass();
        }
        protected function initConstants()
        {
            $this->constants=$this->genConstants();
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
        protected function initMethods()
        {
            foreach ($this->genMethods() as $key => &$value) {
                if (property_exists($this, $key)) {
                    $this->$key =& $value;
                }
            }
            unset($value);
        }
        protected static function getCache($data):?Informant
        {
            if(is_object($data)){
                $hash=spl_object_hash($data);
                return  self::$cache[$hash]??null;
            }
            return null;
        }

        protected static function setCache($data,Informant $object)
        {
            if(is_object($data)){
                $hash=spl_object_hash($data);
                self::$cache[$hash]=$object;
            }
        }
        
	}