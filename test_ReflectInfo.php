<?php
	// classes  to assign an arguments type for methods and  output data.
	include_once __DIR__.'\ReflectInfo.php';
	use Alpa\EntityDetails\ReflectInfo;
	class InArg{
		const A='Hello';
	}
	
	class OutArg{
		
	}
	//Initializing objects in static properties
	class ForPropVar {
		public $obj=[];
	}
	interface ImplementsInterface {
		static public function publicStaticMethod_ImplementsInterface (InArg $arg):OutArg;
		public  function publicMethod_ImplementsInterface (InArg $arg):OutArg;
	}
	interface ImplementsInterface2 extends ImplementsInterface {
		static public function publicStaticMethod_ImplementsInterface2 (InArg $arg):OutArg;
		public  function publicMethod_ImplementsInterface2 (InArg $arg):OutArg;
	}
	trait ExtendsTrait{
		static public function publicStaticMethod_ImplementsInterface (InArg $arg):OutArg
		{
			return new OutArg();
		}
		public  function publicMethod_ImplementsInterface (InArg $arg):OutArg
		{
			return new OutArg();
		}
		static public function publicStaticMethod_ImplementsInterface2 (InArg $arg):OutArg
		{
			return new OutArg();
		}
		public  function publicMethod_ImplementsInterface2 (InArg $arg):OutArg
		{
			return new OutArg();
		}
	}
	class ExtendsClass implements ImplementsInterface2 {
		use ExtendsTrait;
		const CONST_A='Val CONST_A';
		// персональные свойства  (не переопределяются в примере)
		static private $private_static_prop_ExtendsClass='val for '.__CLASS__.'::$private_static_prop_ExtendsClass';
		static protected $protected_static_prop_ExtendsClass='val for '.__CLASS__.'::$protected_static_prop_ExtendsClass';
		static public $public_static_prop_ExtendsClass='val for '.__CLASS__.'::$public_static_prop_ExtendsClass';
		static public $public_static_obj;
		private $private_prop_ExtendsClass='val for '.__CLASS__.'::$private_prop_ExtendsClass';
		protected $protected_prop_ExtendsClass='val for '.__CLASS__.'::$protected_prop_ExtendsClass';
		public $public_prop_ExtendsClass='val for '.__CLASS__.'::$public_prop_ExtendsClass';
		// переопределяемые свойства 
		static private $private_static_prop='val for '.__CLASS__.'::$private_static_prop';
		static protected $protected_static_prop='val for '.__CLASS__.'::$protected_static_prop';
		static public $public_static_prop='val for '.__CLASS__.'::$public_static_prop';		
		private $private_prop='val for '.__CLASS__.'::$private_prop';
		protected $protected_prop='val for '.__CLASS__.'::$protected_prop';
		public $public_prop='val for '.__CLASS__.'::$public_prop';
		
		
		static public function publicStaticMethod_ExtendsClass (InArg $arg):OutArg
		{
			return new OutArg();
		}
		static protected function protectedStaticMethod_ExtendsClass (InArg $arg):OutArg
		{
			return new OutArg();
		}
		static private function privateStaticMethod_ExtendsClass (InArg $arg):OutArg
		{
			return new OutArg();
		}
		public function publicMethod_ExtendsClass (InArg $arg):OutArg
		{
			return new OutArg();
		}
		protected function protectedMethod_ExtendsClass (InArg $arg):OutArg
		{
			return new OutArg();
		}
		private function privateMethod_ExtendsClass (InArg $arg):OutArg
		{
			return new OutArg();
		}
	}
	\ExtendsClass::$public_static_obj=new \ForPropVar();
	class TestClass extends ExtendsClass{
		// персональные свойства  (не переопределяются )
		static private $private_static_prop_TestClass='val for '.__CLASS__.'::$private_static_prop_TestClass';
		static protected $protected_static_prop_TestClass='val for '.__CLASS__.'::$protected_static_prop_TestClass';
		static public $public_static_prop_TestClass='val for '.__CLASS__.'::$public_static_prop_TestClass';
		private $private_prop_TestClass='val for '.__CLASS__.'::$private_prop_TestClass';
		protected $protected_prop_TestClass='val for '.__CLASS__.'::$protected_prop_TestClass';
		public $public_prop_TestClass='val for '.__CLASS__.'::$public_prop_TestClass';
		
		// переопределяющие свойства 
		static private $private_static_prop='val for '.__CLASS__.'::$private_static_prop';
		static protected $protected_static_prop='val for '.__CLASS__.'::$protected_static_prop';
		static public $public_static_prop='val for '.__CLASS__.'::$public_static_prop';		
		private $private_prop='val for '.__CLASS__.'::$private_prop';
		protected $protected_prop='val for '.__CLASS__.'::$protected_prop';
		public $public_prop='val for '.__CLASS__.'::$public_prop';
		
		private  $testProp=[];
		static private $noStabileVal=['prop☢- Just remember - as an unsolved problem.it means that -The data in the array format in the static property will not be stable when calculating the loop. This is true for static classes and for protected properties of a static class object. To calculate the loop it is necessary that the links do not break. $ a = & self :: $ prop; - Inseparable connection with the data.
$ a = self :: $ prop; The data connection was terminated because a copy of the data was forward.'];
		public function setTestProp($default='default'){
			$this->testProp=[
				'sub_array1'=>['hello'],
				'sub_array2'=>[
						'sub_sub_array1'=>$this, // loop
						'sub_sub_array2'=>[]
						]
			];
			$this->testProp['sub_array2']['sub_sub_array2']=&$this->testProp;//loop
			$this->testProp['sub_array2']['sub_sub_array3']=&$this->testProp['sub_array2']['sub_sub_array1'];// loop
			$this->other=new OtherClass();
		}
		
	}
	class OtherClass{
		public $prop='Hello';
	}

$object=new TestClass();
$object->setTestProp();
$rf=new ReflectInfo(); 
//$res=$rf->getInfoClassRecurs($object);
$res=$rf->getInfoObjectArrayRecurs($object);
echo json_encode($res);
	


//add ../test_ReflectInfo.php in Google Chrome Browser
// in console Google Chrome : JSON.parse(document.body.childNodes[0].textContent); 