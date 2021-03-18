<?php
	namespace Alpa\EntityDetails;
	class ReflectionObject extends \ReflectionObject implements Reflector
    {
		private $object;
		public function __construct($o) 
		{
			parent::__construct($o);
			$this->object = &$o;
		}

		public function getObject() 
		{
			return $this->object;
		}
	}