<?php


namespace Alpa\EntityDetails\Tests\Fixtures;


class ExampleChildClass extends ExampleClass implements ExampleInterface
{
    use ExampleTrait;
    public $publicProp='hello';
    protected $protectedProp='hello';
    private $privateProp='hello';
    
    private static ?string $privateStaticProp='hello';
    protected static string $protectedStaticProp='hello';
    public static string $publicStaticProp='hello';
    
    public function & own(string $arg1,?int & $arg2=9):?string
    {
        
    }
    protected static function ownProtectedStatic(){} 
    private static function ownPrivateStatic(){} 
    final static function ownFinalStatic(){} 
    public static function ownStatic()
    {}
    public function parentReplace()
    {
        return '';
    }

    public function traitReplace()
    {

    }
/**
    private $privateProperty=null;
    protected $protectedProperty=null;
    public $publicProperty=null;
    // static
    private $privateStaticProperty=null;
    protected $protectedStaticProperty=null;
    public $publicStaticProperty=null;

    private function privateMethod()
    {

    }

    protected function protectedMethod()
    {

    }

    public function publicMethod()
    {

    }
    final public function finalPublicMethod()
    {

    }
    private static function privateStaticMethod()
    {

    }
    protected static function protectedStaticMethod()
    {

    }
    public static function publicStaticMethod()
    {

    }
    final public function finalPublicStaticMethod()
    {

    }*/
}